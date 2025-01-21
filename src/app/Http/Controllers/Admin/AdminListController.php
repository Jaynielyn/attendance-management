<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\BreakTime;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateAttendanceRequest;
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

    public function detail($userId, Request $request)
    {
        $date = $request->input('date') ?? Carbon::today()->toDateString();

        $attendance = Attendance::with(['user', 'breakTimes'])
        ->where('user_id', $userId)
            ->whereDate('date', $date)
            ->first();

        if (!$attendance) {
            return redirect()->route('admin.attendance.list')->withErrors('指定された勤怠情報が見つかりません。');
        }

        return view('admin.admin_detail', compact('attendance'));
    }

    public function updateDetail(UpdateAttendanceRequest $request, $userId, $date)
    {
        // 勤怠データを取得
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $date)
            ->first();

        if (!$attendance) {
            return redirect()->back()->with('error', '勤怠情報が見つかりません。');
        }

        // 年と月日を処理
        $year = intval(preg_replace('/[^0-9]/', '', $request->input('year')));
        preg_match('/(\d{1,2})月(\d{1,2})日/', $request->input('month_day'), $matches);
        if (count($matches) === 3) {
            $month = $matches[1];
            $day = $matches[2];
        } else {
            return back()->withErrors(['month_day' => '月日が正しい形式ではありません。']);
        }
        $newDate = \Carbon\Carbon::create($year, $month, $day);

        $checkIn = $request->input('check_in') ? \Carbon\Carbon::createFromFormat('H:i', $request->input('check_in')) : null;
        $checkOut = $request->input('check_out') ? \Carbon\Carbon::createFromFormat('H:i', $request->input('check_out')) : null;

        // 休憩時間のバリデーションと保存
        $breakStarts = $request->input('break_start');
        $breakEnds = $request->input('break_end');

        $attendance->breakTimes()->delete();

        foreach ($breakStarts as $index => $breakStart) {
            if (!empty($breakStart)) {
                $breakStartTime = \Carbon\Carbon::createFromFormat('H:i', $breakStart);

                if ($checkIn && $breakStartTime->lt($checkIn)) {
                    return back()->withErrors(['break_start.' . $index => '休憩時間が勤務時間外です。']);
                }
                if ($checkOut && $breakStartTime->gt($checkOut)) {
                    return back()->withErrors(['break_start.' . $index => '休憩時間が勤務時間外です。']);
                }

                if (!empty($breakEnds[$index])) {
                    $breakEndTime = \Carbon\Carbon::createFromFormat('H:i', $breakEnds[$index]);

                    if ($checkIn && $breakEndTime->lt($checkIn)) {
                        return back()->withErrors(['break_end.' . $index => '休憩時間が勤務時間外です。']);
                    }
                    if ($checkOut && $breakEndTime->gt($checkOut)) {
                        return back()->withErrors(['break_end.' . $index => '休憩時間が勤務時間外です。']);
                    }

                    if ($breakStartTime->gt($breakEndTime)) {
                        return back()->withErrors(['break_start.' . $index => '休憩開始時間が終了時間より後になっています。']);
                    }

                    // 休憩データを保存
                    $attendance->breakTimes()->create([
                        'break_start' => $breakStartTime->format('H:i'),
                        'break_end' => $breakEndTime->format('H:i'),
                    ]);
                }
            }
        }

        $attendance->date = $newDate;
        $attendance->check_in = $request->input('check_in');
        $attendance->check_out = $request->input('check_out');
        $attendance->remarks = $request->input('remarks');
        $attendance->save();

        return redirect()->route('admin.attendance.detail', ['userId' => $userId, 'date' => $newDate->format('Y-m-d')])
            ->with('success', '勤怠情報を更新しました。');
    }

}
