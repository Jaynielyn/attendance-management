<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BreakTimeController extends Controller
{
    public function start()
    {
        $user = Auth::id();
        $attendance = Attendance::where('user_id', $user)
            ->whereDate('date', Carbon::today())
            ->where('status', 'working')
            ->first();

        if (!$attendance) {
            return redirect()->back();
        }

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now(),
        ]);

        $attendance->update(['status' => 'break']);

        return redirect()->back();
    }

    public function end()
    {
        $user = Auth::id();
        $attendance = Attendance::where('user_id', $user)
            ->whereDate('date', Carbon::today())
            ->where('status', 'break')
            ->first();

        if (!$attendance) {
            return redirect()->back();
        }

        // 開始中の休憩データを取得
        $breakTime = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->first();

        if (!$breakTime) {
            return redirect()->back();
        }

        $breakTime->update([
            'break_end' => now(),
        ]);

        $attendance->update(['status' => 'working']);

        return redirect()->back();
    }
}
