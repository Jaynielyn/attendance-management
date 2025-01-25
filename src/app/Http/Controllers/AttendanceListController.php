<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\EditRequest;
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
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($attendance) {
                // 休憩時間の合計を取得（時と分のみ計算）
                $totalBreakHours = 0;
                $totalBreakMinutes = 0;

                BreakTime::where('attendance_id', $attendance->id)->get()->each(function ($break) use (&$totalBreakHours, &$totalBreakMinutes) {
                    $start = Carbon::parse($break->break_start);
                    $end = Carbon::parse($break->break_end);

                    if ($end > $start) {
                        $totalBreakHours += $start->diffInHours($end);
                        $totalBreakMinutes += $start->diffInMinutes($end) % 60;
                    }
                });

                // 分が60を超える場合、時間に変換
                if ($totalBreakMinutes >= 60) {
                    $totalBreakHours += floor($totalBreakMinutes / 60);
                    $totalBreakMinutes = $totalBreakMinutes % 60;
                }

                // 休憩時間を "hh:mm" 形式に変換
                $attendance->break_time = sprintf('%02d:%02d', $totalBreakHours, $totalBreakMinutes);

                // 合計作業時間の計算
                if ($attendance->check_in && $attendance->check_out) {
                    $checkIn = Carbon::parse($attendance->check_in);
                    $checkOut = Carbon::parse($attendance->check_out);
                    $workDurationInMinutes = $checkIn->diffInHours($checkOut) * 60 + $checkIn->diffInMinutes($checkOut) % 60;

                    // 休憩時間を差し引いた合計作業時間
                    $totalWorkMinutes = $workDurationInMinutes - ($totalBreakHours * 60 + $totalBreakMinutes);

                    if ($totalWorkMinutes > 0) {
                        $attendance->total_work_time = sprintf('%02d:%02d', floor($totalWorkMinutes / 60), $totalWorkMinutes % 60);
                    } else {
                        $attendance->total_work_time = '00:00';
                    }
                } else {
                    $attendance->total_work_time = '00:00';
                }

                return $attendance;
            });

        return view('list', compact('currentMonth', 'attendances'));
    }

    public function detail($id)
    {
        $attendance = Attendance::with('breakTimes')->findOrFail($id);

        // 日付を「年」と「月日」に分割
        $attendance->year = Carbon::parse($attendance->date)->format('Y年');
        $attendance->month_day = Carbon::parse($attendance->date)->format('m月d日');
        $attendance->check_in_time = $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i') : null;
        $attendance->check_out_time = $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i') : null;

        // 休憩時間を取得（時と分のみ）
        $breakTimes = $attendance->breakTimes->map(function ($break) {
            return [
                'start' => $break->break_start ? Carbon::parse($break->break_start)->format('H:i') : null,
                'end' => $break->break_end ? Carbon::parse($break->break_end)->format('H:i') : null,
            ];
        });

        $editRequest = EditRequest::where('attendance_id', $id)
            ->where('approval_status', '承認待ち')
            ->first();

        return view('detail', compact('attendance', 'breakTimes', 'editRequest'));
    }
}