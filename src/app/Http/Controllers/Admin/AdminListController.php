<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\BreakTime;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminListController extends Controller
{
    public function index(Request $request)
    {
        $currentDate = $request->input('date')
            ? Carbon::createFromFormat('Y-m-d', $request->input('date'))
            : Carbon::today();

        $attendances = Attendance::with('user')
            ->whereDate('date', $currentDate)
            ->get()
            ->map(function ($attendance) {
                $totalBreakHours = 0;
                $totalBreakMinutes = 0;

                $attendance->breakTimes->each(function ($break) use (&$totalBreakHours, &$totalBreakMinutes) {
                    $start = Carbon::parse($break->break_start);
                    $end = Carbon::parse($break->break_end);

                    if ($end > $start) {
                        $totalBreakHours += $start->diffInHours($end);
                        $totalBreakMinutes += $start->diffInMinutes($end) % 60;
                    }
                });

                // 分が60を超えた場合、時間に変換
                if ($totalBreakMinutes >= 60) {
                    $totalBreakHours += floor($totalBreakMinutes / 60);
                    $totalBreakMinutes %= 60;
                }

                $attendance->break_time = sprintf('%02d:%02d', $totalBreakHours, $totalBreakMinutes);

                // 合計作業時間の計算
                if ($attendance->check_in && $attendance->check_out) {
                    $checkIn = Carbon::parse($attendance->check_in);
                    $checkOut = Carbon::parse($attendance->check_out);
                    $workDurationInMinutes = $checkIn->diffInMinutes($checkOut);

                    $totalWorkMinutes = $workDurationInMinutes - ($totalBreakHours * 60 + $totalBreakMinutes);
                    $attendance->total_work_time = $totalWorkMinutes > 0
                        ? sprintf('%02d:%02d', floor($totalWorkMinutes / 60), $totalWorkMinutes % 60)
                        : '00:00';
                } else {
                    $attendance->total_work_time = '00:00';
                }

                return $attendance;
            });

        return view('admin.admin_list', compact('attendances', 'currentDate'));
    }
}
