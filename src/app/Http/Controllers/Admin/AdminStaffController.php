<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminStaffController extends Controller
{
    public function index()
    {
        $staffs = User::where('role', 'staff')->orderBy('id', 'asc')->get();

        return view('admin.staff_list', compact('staffs'));
    }

    public function attendance($staffId, Request $request)
    {
        $currentMonth = $request->query('month', now()->format('Y-m'));

        $startOfMonth = Carbon::parse($currentMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($currentMonth)->endOfMonth();

        $staff = User::findOrFail($staffId);

        $attendances = Attendance::where('user_id', $staffId)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($attendance) {
                $totalBreakMinutes = 0;
                foreach ($attendance->breakTimes as $break) {
                    $start = Carbon::parse($break->break_start);
                    $end = Carbon::parse($break->break_end);
                    if ($end > $start) {
                        $totalBreakMinutes += $start->diffInMinutes($end);
                    }
                }
                $attendance->break_time = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);

                if ($attendance->check_in && $attendance->check_out) {
                    $checkIn = Carbon::parse($attendance->check_in);
                    $checkOut = Carbon::parse($attendance->check_out);
                    $workMinutes = $checkIn->diffInMinutes($checkOut) - $totalBreakMinutes;
                    $attendance->total_work_time = sprintf('%02d:%02d', floor($workMinutes / 60), $workMinutes % 60);
                } else {
                    $attendance->total_work_time = '00:00';
                }

                return $attendance;
            });

        return view('admin.staff_attendance', compact('staff', 'attendances', 'currentMonth'));
    }

    public function exportCsv($staffId, Request $request)
    {
        $currentMonth = $request->query('month', now()->format('Y-m'));
        $startOfMonth = Carbon::parse($currentMonth)->startOfMonth();
        $endOfMonth = Carbon::parse($currentMonth)->endOfMonth();

        $staff = User::findOrFail($staffId);

        $attendances = Attendance::where('user_id', $staffId)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($attendance) {
                $totalBreakMinutes = 0;
                foreach ($attendance->breakTimes as $break) {
                    $start = Carbon::parse($break->break_start);
                    $end = Carbon::parse($break->break_end);
                    if ($end > $start) {
                        $totalBreakMinutes += $start->diffInMinutes($end);
                    }
                }
                $attendance->break_time = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);

                if ($attendance->check_in && $attendance->check_out) {
                    $checkIn = Carbon::parse($attendance->check_in);
                    $checkOut = Carbon::parse($attendance->check_out);
                    $workMinutes = $checkIn->diffInMinutes($checkOut) - $totalBreakMinutes;
                    $attendance->total_work_time = sprintf('%02d:%02d', floor($workMinutes / 60), $workMinutes % 60);
                } else {
                    $attendance->total_work_time = '00:00';
                }

                return $attendance;
            });

        $response = new StreamedResponse(function () use ($attendances, $staff, $currentMonth) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['日付', '出勤時間', '退勤時間', '休憩時間', '合計勤務時間']);

            $weekdays = ['日', '月', '火', '水', '木', '金', '土'];

            foreach ($attendances as $attendance) {
                $date = Carbon::parse($attendance->date);
                $weekday = $weekdays[$date->dayOfWeek];
                fputcsv($handle, [
                    $date->format('m/d') . "({$weekday})",
                    $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i') : '',
                    $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i') : '',
                    $attendance->break_time,
                    $attendance->total_work_time,
                ]);
            }

            fclose($handle);
        });

        $filename = "{$staff->name} " . Carbon::parse($currentMonth)->format('Y年n月') . ".csv";
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', "attachment; filename={$filename}");

        return $response;
    }
}