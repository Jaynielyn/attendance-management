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
        // 新規ユーザー作成処理を設定
        Fortify::createUsersUsing(CreateNewUser::class);

        // ユーザー用の登録画面
        Fortify::registerView(function () {
            return view('auth.register');
        });

        $this->app->singleton(VerifyEmailViewResponse::class, function () {
            return new SimpleViewResponse('auth.verify-email');
        });

        // ユーザー & 管理者のログイン画面
        Fortify::loginView(function () {
            return view('auth.login');
        });

        /**
         * カスタム認証ロジック（管理者 & ユーザー）
         */
        Fortify::authenticateUsing(function (Request $request) {
            // ✅ バリデーション
            $validated = Validator::make($request->all(), (new UserLoginRequest())->rules(), (new UserLoginRequest())->messages())->validate();

            if ($request->is('admin/*')) {
                // 管理者認証
                $admin = Admin::where('email', $validated['email'])->first();
                if (!$admin || !Hash::check($validated['password'], $admin->password)) {
                    throw ValidationException::withMessages([
                        'email' => [Lang::get('auth.failed')],
                    ])->redirectTo('/login'); // /login にリダイレクト
                }
                return $admin;
            } else {
                // ユーザー認証
                $user = User::where('email', $validated['email'])->first();
                if (!$user || !Hash::check($validated['password'], $user->password)) {
                    throw ValidationException::withMessages([
                        'email' => [Lang::get('auth.failed')],
                    ])->redirectTo('/login'); // /login にリダイレクト
                }
                return $user;
            }
        });

        // ログイン試行回数の制限
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });
    }
}