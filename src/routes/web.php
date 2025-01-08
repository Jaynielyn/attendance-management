<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;

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
    // 出勤打刻
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.checkIn');
    // 退勤ボタン
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkOut');
    // 休憩入ボタン
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.breakStart');
});