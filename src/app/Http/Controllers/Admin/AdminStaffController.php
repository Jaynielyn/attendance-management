<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
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
                // 休憩時間の合計を計算
                $totalBreakMinutes = 0;

                foreach ($attendance->breakTimes as $break) {
                    $start = Carbon::parse($break->break_start);
                    $end = Carbon::parse($break->break_end);

                    if ($end > $start) {
                        $totalBreakMinutes += $start->diffInMinutes($end);
                    }
                }

                // 分を "hh:mm" 形式に変換
                $attendance->break_time = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);

                // 合計作業時間を計算
                if ($attendance->check_in && $attendance->check_out) {
                    $checkIn = Carbon::parse($attendance->check_in);
                    $checkOut = Carbon::parse($attendance->check_out);

                    $workMinutes = $checkIn->diffInMinutes($checkOut) - $totalBreakMinutes;

                    // 作業時間を "hh:mm" 形式に変換
                    $attendance->total_work_time = sprintf('%02d:%02d', floor($workMinutes / 60), $workMinutes % 60);
                } else {
                    $attendance->total_work_time = '00:00';
                }

                return $attendance;
            });

        return view('admin.staff_attendance', compact('staff', 'attendances', 'currentMonth'));
    }
}

