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

    public function breakStart(Request $request)
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', Carbon::today()->toDateString())
            ->where('status', 'working')
            ->first();

        if ($attendance) {
            $attendance->status = 'break';
            $attendance->break_start = Carbon::now();
            $attendance->save();

            return redirect()->back()->with('success', '休憩を開始しました。');
        }

        return redirect()->back()->with('error', '休憩を開始できませんでした。');
    }

    public function breakEnd(Request $request)
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->where('date', Carbon::today()->toDateString())
            ->where('status', 'break')
            ->first();

        if ($attendance) {
            $attendance->status = 'working';
            $attendance->break_end = Carbon::now();
            $attendance->save();

            return redirect()->back()->with('success', '休憩が終了しました。');
        }

        return redirect()->back()->with('error', '休憩を終了できませんでした。');
    }
}
