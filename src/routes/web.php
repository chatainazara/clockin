<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminAttendanceController;
// use App\Http\Controllers\WorkApplicationController;

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

// 一般ユーザーが使うルート
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/attendance', [AttendanceController::class,'register']);
    Route::post('/attendance/action', [AttendanceController::class, 'action']);
    Route::get('/attendance/list', [AttendanceController::class,'list']);
    Route::get('/attendance/detail/{id}', [AttendanceController::class,'detailShow']);
    Route::post('/attendance/detail/{id}', [AttendanceController::class,'application']);
    Route::get('/stamp_correction_request/list', function () {});
});

// 管理者が使うルート
Route::get('/admin/login', function () {return view('admin.login');})->middleware('guest');
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->middleware('guest');
Route::middleware(['auth','role:admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'list'])->name('admin.attendance.list');
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show');
    Route::get('/stamp_correction_request/list', function () {});//一般ユーザーと同じパスを使用
});


// Route::get('admin/attendance/{id}', function () {
//     return view('admin.attendance_detail');
// });

Route::get('/admin/staff/list', function () {
    return view('admin.staff_list');
});

Route::get('/admin/attendance/staff/{id}', function () {
    return view('admin.staff_detail');
});

Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', function () {
    return view('admin.request_approve');
});

// mailhogによる認証ルート
Route::get('/email/verify', function () {
    return view('verify_email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
        return redirect('/attendance');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');