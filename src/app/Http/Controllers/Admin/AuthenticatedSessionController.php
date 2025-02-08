<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AdminLoginRequest;

class AuthenticatedSessionController extends Controller
{
    /**
     * 管理者ログイン処理
     */
    public function store(AdminLoginRequest $request)
    {
        if (Auth::guard('admin')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/admin/attendance/list');
        }

        return back()->withErrors([
            'login_failed' => 'ログイン情報が登録されていません。',
        ])->onlyInput('email');
    }


    /**
     * 管理者ログアウト処理
     */
    public function destroy(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
