<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginTest extends TestCase
{
    public function test_user_can_login()
    {
        $email = 'test' . rand(1, 1000) . '@example.com'; // 動的なメールアドレスを生成

        // ユーザー作成
        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make('password123'),
        ]);

        // ログインテスト
        $response = $this->post('/login', [
            'email' => $email,
            'password' => 'password123',
        ]);

        // ログイン成功を確認
        $response->assertStatus(302);
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_without_email()
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        // セッションにエラーが含まれていることを確認
        $response->assertSessionHasErrors('email');
    }

    public function test_user_cannot_login_without_password()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        // セッションにエラーが含まれていることを確認
        $response->assertSessionHasErrors('password');
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'invalid@example.com',
            'password' => 'wrongpassword',
        ]);

        // リダイレクト先が '/login' であることを確認
        $response->assertRedirect('/login');

        // リダイレクト後の /login ページにアクセスしてエラーメッセージが表示されていることを確認
        $response = $this->get('/login');
        $response->assertSee('ログイン情報が登録されていません'); // フラッシュメッセージとして表示されることを確認
    }


    public function test_user_cannot_login_with_invalid_password()
    {
        // テスト用のユーザーを作成
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // 間違ったパスワードでログインを試みる
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        // 302（リダイレクト）を期待する
        $response->assertStatus(302);

        // ユーザーが認証されていないことを確認
        $this->assertGuest();
    }

    protected function setUp(): void
    {
        parent::setUp();
        // 毎回テストの前にDBをリセット
        $this->artisan('migrate:fresh');
    }
}
