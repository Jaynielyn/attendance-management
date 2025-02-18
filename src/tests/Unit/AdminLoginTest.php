<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Admin;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function メールアドレスが未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->post(route('admin.login'), [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください。']);
    }

    /** @test */
    public function パスワードが未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->post(route('admin.login'), [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください。']);
    }

    /** @test */
    public function 登録内容と一致しない場合バリデーションメッセージが表示される()
    {
        // ダミーの管理者ユーザーを作成
        Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('correct-password'),
        ]);

        // 間違ったパスワードでログインを試みる
        $response = $this->post(route('admin.login'), [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        // エラーメッセージが「ログイン情報が登録されていません。」であることを確認
        $response->assertSessionHasErrors(['login_failed' => 'ログイン情報が登録されていません。']);
    }

}
