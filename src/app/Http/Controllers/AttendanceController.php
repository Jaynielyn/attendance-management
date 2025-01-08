<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $date = today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->first();

        return view('index', compact('attendance', 'date'));
    }

    /**
     * 出勤打刻を行う
     *
     * @return \Illuminate\Http\Response
     */
    public function checkIn()
    {
        $user = Auth::user();
        $date = today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->first();

        if ($attendance) {
            return back()->with('error', '今日の出勤は既に打刻されています。');
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'check_in' => now(),
            'status' => 'working',
        ]);

        return back()->with('success', '出勤打刻が完了しました。');
    }
}
