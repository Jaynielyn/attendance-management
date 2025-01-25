<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EditRequest;

class AdminRequestController extends Controller
{
    public function index(Request $request)
    {
        // タブの選択状況に応じて表示を切り替える
        $status = $request->query('status', '承認待ち');

        // 状態に応じてデータを取得
        $editRequests = EditRequest::with('user', 'attendance')
        ->where('status', $status)
            ->orderBy('requested_at', 'desc')
            ->get();

        return view('admin.admin_request', compact('editRequests', 'status'));
    }

    public function approve()
    {
        return view('admin.approval_request');
    }
}
