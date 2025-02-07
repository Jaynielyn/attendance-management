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
            return back();
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'check_in' => now(),
            'status' => 'working',
        ]);

        return back();
    }

    public function checkOut()
    {
        $user = Auth::user();
        $date = today();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->first();

        if (!$attendance) {
            return back();
        }

        $attendance->update([
            'check_out' => now(),
            'status' => 'finished',
        ]);

        return back();
    }
}
