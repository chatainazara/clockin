<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void{

        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {
            public function toResponse($request){
                return redirect('/login');
            }
        });

        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
            public function toResponse($request){
                $user = $request->user();
                if ($user->role === 'admin') {
                    return redirect('/admin/attendance/list');
                }
                if ($user->role === 'user' && is_null($user->email_verified_at)) {
                    return redirect('/email/verify');
                }else{
                    return redirect('/attendance');
                }
                return redirect('/login');
            }
        });

        $this->app->instance(RegisterResponse::class, new class implements RegisterResponse {
            public function toResponse($request){
                 // 登録直後フラグをセッションにセット
                session(['just_registered' => true]);
                return redirect()->route('verification.notice');
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void{
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::registerView(function () {
            return view('register');
        });

        Fortify::loginView(function (Request $request) {
            if ($request->is('admin/login')) {
            return view('admin.login'); // 管理者用ログイン画面
            }
            return view('login'); // 一般ユーザー
        });

        RateLimiter::for('login', function (Request $request) {
            $request = app(LoginRequest::class);
            $email = (string) $request->email;
            return Limit::perMinute(10)->by($email . $request->ip());
        });

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();
            if (!$user || !Hash::check($request->password, $user->password)) {
                return null;
            }
            if ($request->has('is_admin') && $user->role === 'admin') {
                return $user;
            }
            if (!$request->has('is_admin') && $user->role === 'user') {
                return $user;
            }
            return null; // role が一致しない場合はログイン不可
        });
    }
}
