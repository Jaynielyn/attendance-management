<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceListController extends Controller
{
    public function list(Request $request)
    {
        $currentDate = $request->input('month')
            ? Carbon::createFromFormat('Y-m', $request->input('month'))
            : now();

        $currentMonth = $currentDate->format('Y/m');

        $attendances = Attendance::where('user_id', auth()->id())
            ->whereMonth('date', $currentDate->month)
            ->whereYear('date', $currentDate->year)
            ->get()
            ->map(function ($attendance) {
                // 休憩時間の計算
                if ($attendance->break_start && $attendance->break_end) {
                    $breakStart = Carbon::parse($attendance->break_start);
                    $breakEnd = Carbon::parse($attendance->break_end);
                    $breakDuration = $breakStart->diff($breakEnd);
                    $attendance->break_time = sprintf('%02d:%02d', $breakDuration->h, $breakDuration->i);
                } else {
                    $attendance->break_time = '00:00';
                }

                // 合計作業時間の計算
                if ($attendance->check_in && $attendance->check_out) {
                    $checkIn = Carbon::parse($attendance->check_in);
                    $checkOut = Carbon::parse($attendance->check_out);
                    $workDuration = $checkIn->diff($checkOut);

                    // 合計作業時間から休憩時間を引く
                    if ($attendance->break_start && $attendance->break_end) {
                        $totalWorkHours = $workDuration->h - $breakDuration->h;
                        $totalWorkMinutes = $workDuration->i - $breakDuration->i;

                        // 分がマイナスになる場合、1時間分補正
                        if ($totalWorkMinutes < 0) {
                            $totalWorkHours -= 1;
                            $totalWorkMinutes += 60;
                        }
                        $attendance->total_work_time = sprintf('%02d:%02d', $totalWorkHours, $totalWorkMinutes);
                    } else {
                        $attendance->total_work_time = sprintf('%02d:%02d', $workDuration->h, $workDuration->i);
                    }
                } else {
                    $attendance->total_work_time = '00:00';
                }

                return $attendance;
            });

        return view('list', compact('currentMonth', 'attendances'));
    }
}
