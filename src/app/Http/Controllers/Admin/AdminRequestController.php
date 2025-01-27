<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EditRequest;

class AdminRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', '承認待ち');
        $editRequests = EditRequest::with('user', 'attendance')
        ->where('approval_status', $status)
            ->orderBy('requested_at', 'desc')
            ->get();

        return view('admin.admin_request', compact('editRequests', 'status'));
    }

    public function approvalRequest($id)
    {
        // 修正申請を取得
        $editRequest = EditRequest::with('user', 'attendance', 'editBreakTimes')->findOrFail($id);

        return view('admin.approval_request', compact('editRequest'));
    }

    public function approveRequest(Request $request, $id)
    {
        $editRequest = EditRequest::with('attendance')->findOrFail($id);

        $editRequest->update(['approval_status' => '承認済み']);

        $attendance = $editRequest->attendance;

        $attendance->update([
            'date' => $editRequest->new_date,
            'check_in' => $editRequest->new_check_in,
            'check_out' => $editRequest->new_check_out,
        ]);

        // 休憩時間の更新処理
        if ($editRequest->editBreakTimes) {
            foreach ($editRequest->editBreakTimes as $editBreakTime) {
                // 休憩時間が既に存在する場合は更新
                $existingBreakTime = $attendance->breakTimes->where('id', $editBreakTime->break_id)->first();

                if ($existingBreakTime) {
                    // 休憩時間が一致する場合、既存の休憩時間を更新
                    $existingBreakTime->update([
                        'break_start' => $editBreakTime->new_break_start,
                        'break_end' => $editBreakTime->new_break_end,
                    ]);
                } else {
                    // 存在しない場合は新たに追加
                    $attendance->breakTimes()->create([
                        'break_start' => $editBreakTime->new_break_start,
                        'break_end' => $editBreakTime->new_break_end,
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', '申請を承認しました。');
    }
}
