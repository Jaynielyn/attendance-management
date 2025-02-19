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
use Laravel\Fortify\Fortify;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Laravel\Fortify\Contracts\VerifyEmailViewResponse;
use Laravel\Fortify\Http\Responses\SimpleViewResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserLoginRequest;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::registerView(function () {
            return view('auth.register');
        });

        $this->app->singleton(VerifyEmailViewResponse::class, function () {
            return new SimpleViewResponse('auth.verify-email');
        });

        Fortify::loginView(function () {
            return view('auth.login');
        });

        /**
         * カスタム認証ロジック（管理者 & ユーザー）
         */
        Fortify::authenticateUsing(function (Request $request) {
            $validated = Validator::make($request->all(), (new UserLoginRequest())->rules(), (new UserLoginRequest())->messages())->validate();

            if ($request->is('admin/*')) {
                $admin = Admin::where('email', $validated['email'])->first();
                if (!$admin || !Hash::check($validated['password'], $admin->password)) {
                    throw ValidationException::withMessages([
                        'email' => [Lang::get('auth.failed')],
                    ])->redirectTo('/login');
                }
                return $admin;
            } else {
                $user = User::where('email', $validated['email'])->first();
                if (!$user || !Hash::check($validated['password'], $user->password)) {
                    throw ValidationException::withMessages([
                        'email' => [Lang::get('auth.failed')],
                    ])->redirectTo('/login');
                }
                return $user;
            }
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });
    }
}