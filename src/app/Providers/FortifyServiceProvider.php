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

        // ユーザー用のログイン画面
        Fortify::loginView(function () {
            return view('auth.login');
        });

        /**
         * カスタム認証ロジック
         */
        Fortify::authenticateUsing(function (Request $request) {
            // URLによって認証モデルを切り替え
            if ($request->is('admin/*')) {
                // 管理者認証
                $admin = Admin::where('email', $request->email)->first();
                if ($admin && Hash::check($request->password, $admin->password)) {
                    return $admin;
                }
            } else {
                // ユーザー認証
                $user = User::where('email', $request->email)->first();
                if ($user && Hash::check($request->password, $user->password)) {
                    return $user;
                }
            }

            return null; // 認証失敗
        });

        // ログイン試行回数の制限
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(10)->by($email . $request->ip());
        });
    }
}
