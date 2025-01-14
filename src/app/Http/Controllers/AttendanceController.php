<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $date = today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->first();

        $dateChanged = $attendance ? today()->isAfter($attendance->date) : false;

        return view('index', compact('attendance', 'date', 'dateChanged'));
    }

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

    public function checkOut()
    {
        $user = Auth::user();
        $date = today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->first();

        if (!$attendance) {
            return back()->with('error', '出勤データが存在しません。');
        }

        $attendance->update([
            'check_out' => now(),
            'status' => 'finished',
        ]);

        return back()->with('success', '退勤打刻が完了しました。');
    }
}
