<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\BreakTimeController;
use App\Http\Controllers\Admin\AuthenticatedSessionController;
use App\Http\Controllers\Admin\AdminListController;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\Admin\AdminRequestController;
use App\Http\Controllers\EditRequestController;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;

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

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [AttendanceController::class, 'index'])->name('index');
    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.checkIn');
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.checkOut');

    Route::post('/attendance/break-start', [BreakTimeController::class, 'start'])->name('break.start');
    Route::post('/attendance/break-end', [BreakTimeController::class, 'end'])->name('break.end');

    Route::get('/attendance/list', [AttendanceListController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/{id}', [AttendanceListController::class, 'detail'])->name('attendance.detail');

    Route::put('/attendance/{id}', [EditRequestController::class, 'update'])->name('attendance.update');
    Route::get('/user/requests', [EditRequestController::class, 'userRequests'])->name('user.requests');
    Route::get('/user/request/detail/{id}', [EditRequestController::class, 'showRequestDetail'])
        ->name('user.request.detail');
});

Route::prefix('admin')->group(function () {
    Route::get('/login', function () {
        return view('admin.login');
    })->name('admin.login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('guest:admin')
        ->name('admin.login.post');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware('auth:admin')
        ->name('admin.logout');

    Route::middleware('auth:admin')->group(function () {
        Route::get('/attendance/list', [AdminListController::class, 'index'])->name('admin.admin_list');
        Route::get('/attendance/detail/{userId}', [AdminListController::class, 'detail'])->name('admin.attendance.detail');
        Route::put('/attendance/{userId}/{date}', [AdminListController::class, 'updateDetail'])->name('admin.attendance.update');

        Route::get('/staff/list', [AdminStaffController::class, 'index'])->name('admin.staff_list');
        Route::get('/staff/{id}/attendance', [AdminStaffController::class, 'attendance'])->name('admin.staff_attendance');
        Route::get('/staff/{staffId}/attendance/export', [AdminStaffController::class, 'exportCsv'])->name('admin.staff.attendance.export');

        Route::get('/requests', [AdminRequestController::class, 'index'])->name('admin.requests');
        Route::get('/requests/{id}/approval', [AdminRequestController::class, 'approvalRequest'])->name('admin.approval_request');
        Route::post('/requests/{id}/approve', [AdminRequestController::class, 'approveRequest'])->name('admin.approve_request');
    });
});
