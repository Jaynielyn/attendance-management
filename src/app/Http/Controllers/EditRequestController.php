<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EditRequest;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\EditBreakTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AttendanceRequest;

class EditRequestController extends Controller
{
    public function update(AttendanceRequest $request, $id)
    {
        $attendance = Attendance::with('breakTimes')->findOrFail($id);

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

        $editRequest = EditRequest::create([
            'user_id' => auth()->id(),
            'attendance_id' => $attendance->id,
            'reason' => $request->input('remarks'),
            'new_date' => $newDate,
            'new_check_in' => $checkIn ? $checkIn->format('H:i') : null,
            'new_check_out' => $checkOut ? $checkOut->format('H:i') : null,
            'approval_status' => '承認待ち',
            'requested_at' => now(),
        ]);

        // 修正後の休憩時間を保存
        if ($request->has('break_times')) {
            foreach ($request->input('break_times') as $break) {
                if (!empty($break['start']) && !empty($break['end']) && !empty($break['id'])) {
                    $breakId = $break['id'];

                    EditBreakTime::create([
                        'edit_request_id' => $editRequest->id,
                        'break_id' => $breakId,
                        'new_break_start' => \Carbon\Carbon::createFromFormat('H:i', $break['start'])->format('H:i'),
                        'new_break_end' => \Carbon\Carbon::createFromFormat('H:i', $break['end'])->format('H:i'),
                    ]);
                }
            }
        }

        return redirect()->route('attendance.detail', $id);
    }

    public function userRequests(Request $request)
    {
        $userId = auth()->id();
        $status = $request->get('status', '承認待ち');

        $editRequests = EditRequest::where('user_id', $userId)
            ->where('approval_status', $status)
            ->orderBy('requested_at', 'desc')
            ->get();

        return view('request_list', compact('editRequests', 'status'));
    }

    public function showRequestDetail($id)
    {
        $editRequest = EditRequest::with('editBreakTimes', 'user')->findOrFail($id);

        $attendanceId = $editRequest->attendance_id;

        return redirect()->route('attendance.detail', ['id' => $attendanceId]);
    }
}