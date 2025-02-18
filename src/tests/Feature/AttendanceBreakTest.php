<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;

class AttendanceBreakTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤務中の場合休憩入ボタンが表示され、処理後にステータスが「休憩中」になる()
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

        // 3. 勤怠を「勤務中」に設定
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'status' => 'working',  // 勤務中
        ]);

        // 4. ホーム画面（`/`）にアクセスし、「休憩入」ボタンが表示されているか確認
        $response = $this->get('/');
        $response->assertSee('休憩入');

        // 5. 「休憩入」ボタンをクリック（休憩開始処理を実行）
        $response = $this->post(route('break.start'));

        // 6. ステータスが「休憩中」に更新されたか確認
        $attendance->refresh();  // データを最新の状態に更新
        $this->assertEquals('break', $attendance->status);

        // 7. 画面上に「休憩中」と表示されているか確認
        $response = $this->get('/');
        $response->assertSee('休憩中');
    }

    /** @test */
    public function 出勤中の場合画面上に休憩入ボタンが表示される()
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

        // 3. 勤怠を「出勤中」に設定
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'status' => 'working',  // 出勤中
        ]);

        // 4. ホーム画面（`/`）にアクセスし、「休憩入」ボタンが表示されているか確認
        $response = $this->get('/');

        // 「休憩入」ボタンが表示されているか確認
        $response->assertSee('休憩入');
    }

    /** @test */
    public function 休憩入と休憩戻を繰り返す場合休憩戻ボタンが再度表示されること()
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

        // 3. 勤怠を「出勤中」に設定
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'status' => 'working',  // 出勤中
        ]);

        // 4. 最初に「休憩入」ボタンをクリック（休憩開始処理を実行）
        $response = $this->post(route('break.start'));

        // 5. 「休憩戻」ボタンが表示されているか確認
        $response = $this->get('/');
        $response->assertSee('休憩戻');

        // 6. 「休憩戻」ボタンをクリック（休憩終了処理を実行）
        $response = $this->post(route('break.end'));

        // 7. 休憩終了後にステータスが「勤務中」に更新されたか確認
        $attendance->refresh();
        $this->assertEquals('working', $attendance->status);

        // 8. 再度「休憩入」ボタンをクリック（再度休憩開始処理を実行）
        $response = $this->post(route('break.start'));

        // 9. 再度「休憩戻」ボタンが表示されているか確認
        $response = $this->get('/');
        $response->assertSee('休憩戻');
    }

    /** @test */
    public function 休憩入後休憩戻を行いステータスが「出勤中」に変更されるか()
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

        // 3. 勤怠を「出勤中」に設定
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'status' => 'working',  // 出勤中
        ]);

        // 4. 休憩入ボタンをクリック（休憩開始処理を実行）
        $response = $this->post(route('break.start'));

        // 5. ステータスが「休憩中」に変更されたか確認
        $attendance->refresh();  // データベースから最新の状態を再取得
        $this->assertEquals('break', $attendance->status); // 'break' になっていることを確認

        // 6. 休憩戻ボタンをクリック（休憩終了処理を実行）
        $response = $this->post(route('break.end'));

        // 7. ステータスが「出勤中」に変更されたか確認
        $attendance->refresh();  // 再度最新の状態を取得
        $this->assertEquals('working', $attendance->status); // 'working' になっていることを確認
    }

    /** @test */
    public function その日になされたユーザー1の合計休憩時間が管理画面で確認できる()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin'); // admin guardを指定

        // ユーザー1を作成
        $user1 = User::factory()->create();

        // 今日の日付
        $today = Carbon::today();

        // ユーザー1の勤怠データを作成
        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'date' => $today,
            'check_in' => $today->copy()->setTime(9, 0),
            'check_out' => $today->copy()->setTime(18, 0),
            'status' => 'finished',
        ]);

        // ユーザー1の休憩時間（12:00〜12:30, 15:00〜15:15）
        BreakTime::create([
            'attendance_id' => $attendance1->id,
            'break_start' => $today->copy()->setTime(12, 0),
            'break_end' => $today->copy()->setTime(12, 30),
        ]);
        BreakTime::create([
            'attendance_id' => $attendance1->id,
            'break_start' => $today->copy()->setTime(15, 0),
            'break_end' => $today->copy()->setTime(15, 15),
        ]);

        // 管理者が勤怠一覧ページにアクセス
        $response = $this->get(route('admin.admin_list', ['date' => $today->toDateString()]));

        // ユーザー1の名前と合計休憩時間が正しく表示されているか確認
        $response->assertSee($user1->name);
        $response->assertSee('00:45');
    }

}