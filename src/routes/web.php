<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\BreakTimeController;
use App\Http\Controllers\Admin\AuthenticatedSessionController;

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

    //詳細
    Route::get('/attendance/{id}', [AttendanceListController::class, 'detail'])->name('attendance.detail');
    Route::put('/attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');

});

Route::prefix('admin')->group(function () {
    // 管理者ログインページ
    Route::get('/login', function () {
        return view('admin.login');
    })->name('admin.login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware(['guest:admin'])
        ->name('admin.login.post');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware(['auth:admin'])
        ->name('admin.logout');

    // 管理者専用ルート（認証が必要）
    Route::middleware('auth:admin')->group(function () {
        Route::get('/admin_list', function () {
            return view('admin.admin_list');
        })->name('admin.admin_list');
    });
});