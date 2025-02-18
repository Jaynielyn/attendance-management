<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;


class HomePageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 画面に表示される日時が現在の日時と一致する()
    {
        // 1. メール認証済みのユーザーを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => Carbon::now(), // メール認証済み
        ]);

        // 2. ログイン状態にする
        $this->actingAs($user);

        // 3. 現在の日時を取得（日本語の曜日付き）
        $now = Carbon::now()->locale('ja')->isoFormat('YYYY年MM月DD日(ddd) HH:mm');

        // 4. ホーム画面（`/`）にアクセス
        $response = $this->get('/');

        // 5. 画面に現在の日時がサーバーサイドで渡された日時と一致するか確認
        $response->assertSee($now);
    }

    public function 勤務外ステータスが表示される()
    {
        // 1. メール認証済みのユーザーを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => Carbon::now(), // メール認証済み
        ]);

        // 2. ログイン状態にする
        $this->actingAs($user);

        // 3. 勤務外のステータスで勤怠情報を作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'status' => 'off',  // 勤務外
        ]);

        // 4. ホーム画面（`/`）にアクセス
        $response = $this->get('/');

        // 5. 勤務外ステータスが正しく表示されることを確認
        $response->assertSee('勤務外');
    }

    /** @test */
    public function 出勤中ステータスが表示される()
    {
        // 1. メール認証済みのユーザーを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => Carbon::now(), // メール認証済み
        ]);

        // 2. ログイン状態にする
        $this->actingAs($user);

        // 3. 出勤中のステータスで勤怠情報を作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'status' => 'working',  // 出勤中
        ]);

        // 4. ホーム画面（`/`）にアクセス
        $response = $this->get('/');

        // 5. 出勤中ステータスが正しく表示されることを確認
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩中ステータスが表示される()
    {
        // 1. メール認証済みのユーザーを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => Carbon::now(), // メール認証済み
        ]);

        // 2. ログイン状態にする
        $this->actingAs($user);

        // 3. 休憩中のステータスで勤怠情報を作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'status' => 'break',  // 休憩中
        ]);

        // 4. ホーム画面（`/`）にアクセス
        $response = $this->get('/');

        // 5. 休憩中ステータスが正しく表示されることを確認
        $response->assertSee('休憩中');
    }

    /** @test */
    public function 退勤済ステータスが表示される()
    {
        // 1. メール認証済みのユーザーを作成
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => Carbon::now(), // メール認証済み
        ]);

        // 2. ログイン状態にする
        $this->actingAs($user);

        // 3. 退勤済のステータスで勤怠情報を作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'status' => 'finished',  // 退勤済
        ]);

        // 4. ホーム画面（`/`）にアクセス
        $response = $this->get('/');

        // 5. 退勤済ステータスが正しく表示されることを確認
        $response->assertSee('退勤済');
    }
}
