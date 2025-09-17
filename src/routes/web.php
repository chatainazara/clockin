<?php

use Illuminate\Support\Facades\Route;

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

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/home', [UserController::class, 'index']);
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index']);
});

Route::get('/attendance', function () {
return view('auth.attendance');
});

Route::get('/attendance/list', function () {
    return view('auth.attendance_list');
});

Route::get('/attendance/detail/{id}', function () {
    return view('auth.attendance_detail');
});

Route::get('/stamp_correction_request/list', function () {
    return view('auth.request_list');
});

Route::get('/admin/login', function () {
    return view('admin.login');
});

Route::get('/admin/attendance/list', function () {
    return view('admin.attendance_list');
});

Route::get('admin/attendance/{id}', function () {
    return view('admin.attendance_detail');
});

Route::get('/admin/staff/list', function () {
    return view('admin.staff_list');
});

Route::get('/admin/attendance/staff/{id}', function () {
    return view('admin.staff_detail');
});

Route::get('/stamp_correction_request/list', function () {
    return view('admin.request_list');
});//一般ユーザーと同じパスを使用

Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', function () {
    return view('admin.request_approve');
});

// mailhogによる認証ルート
Route::get('/email/verify', function () {
    return view('auth.verify_email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
        return redirect('/mypage/profile');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');