<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EditRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class EditRequestController extends Controller
{
    public function update(Request $request, $id)
    {
        // 勤怠データの取得
        $attendance = Attendance::findOrFail($id);

        // 年と月日の処理
        $year = intval(preg_replace('/[^0-9]/', '', $request->input('year')));
        preg_match('/(\d{1,2})月(\d{1,2})日/', $request->input('month_day'), $matches);
        if (count($matches) === 3) {
            $month = $matches[1];
            $day = $matches[2];
        } else {
            return back()->withErrors(['month_day' => '月日が正しい形式ではありません。']);
        }
        $newDate = \Carbon\Carbon::create($year, $month, $day);

        // 出勤・退勤時間の処理
        $checkIn = $request->input('check_in') ? \Carbon\Carbon::createFromFormat('H:i', $request->input('check_in')) : null;
        $checkOut = $request->input('check_out') ? \Carbon\Carbon::createFromFormat('H:i', $request->input('check_out')) : null;

        // 修正後の休憩時間の処理
        $breakStart = $request->input('break_times.0.start') ? \Carbon\Carbon::createFromFormat('H:i', $request->input('break_times.0.start'))->format('H:i') : null;
        $breakEnd = $request->input('break_times.0.end') ? \Carbon\Carbon::createFromFormat('H:i', $request->input('break_times.0.end'))->format('H:i') : null;

        // 修正リクエストを保存
        EditRequest::create([
            'user_id' => auth()->id(),
            'attendance_id' => $attendance->id,
            'reason' => $request->input('remarks'),
            'new_date' => $newDate,
            'new_check_in' => $checkIn ? $checkIn->format('H:i') : null,
            'new_check_out' => $checkOut ? $checkOut->format('H:i') : null,
            'new_break_start' => $breakStart,
            'new_break_end' => $breakEnd,
            'approval_status' => '承認待ち',
            'requested_at' => now(),
        ]);

        return redirect()->route('attendance.detail', $id)
            ->with('success', '修正が申請されました。');
    }
}
