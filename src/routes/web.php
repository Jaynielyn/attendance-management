<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\BreakTimeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware('auth')->group(function () {
    Route::get('/', [AttendanceController::class, 'index']);

    // 出勤・退勤機能
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.checkIn');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkOut');

    // 休憩機能
    Route::post('/attendance/break_start', [BreakTimeController::class, 'start'])->name('break.start');
    Route::post('/attendance/break_end', [BreakTimeController::class, 'end'])->name('break.end');

    // 勤怠一覧
    Route::get('/attendance/list', [AttendanceListController::class, 'list'])->name('attendance.list');
});