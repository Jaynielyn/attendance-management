<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Models\Attendance;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤ボタンが表示され、処理後にステータスが「勤務中」に更新される()
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

        // 3. 勤怠がまだ作成されていない状態でホーム画面（`/`）にアクセス
        $response = $this->get('/');

        // 4. 出勤ボタンが表示されていることを確認
        $response->assertSee('出勤');

        // 5. 出勤ボタンをクリックして処理を実行
        $response = $this->post(route('attendance.checkIn'));

        // 6. 出勤処理後、ステータスが「勤務中」に更新されたか確認
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

        $this->assertEquals('working', $attendance->status);

        // 7. 画面に「勤務中」が表示されるか確認
        $response = $this->get('/');
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 出勤は一日一回のみできる()
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

        // 3. 出勤処理を実行（初回）
        $this->post(route('attendance.checkIn'));

        // 4. 2回目の出勤ボタンを押す
        $response = $this->post(route('attendance.checkIn'));

        // 5. 出勤ボタンを2回目に押すと、リダイレクトされることを確認
        $response->assertStatus(302); // リダイレクトが発生するはず

        // 6. リダイレクト先のURLがホーム画面（`/`）であることを確認
        $response->assertRedirect('/'); // 出勤済みでホーム画面にリダイレクトされることを期待
    }

    /** @test */
    public function 勤務外のユーザーが出勤し管理画面で確認できる()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // ユーザーを作成
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

        // 管理者が勤怠一覧ページにアクセス
        $response = $this->get(route('admin.admin_list', ['date' => $today->toDateString()]));

        // ユーザー名と出勤時刻が正しく表示されているか確認
        $response->assertSee($user1->name);
        $response->assertSee($attendance1->check_in->format('H:i'));
    }

    /** @test */
    public function 退勤済になった場合は画面上に「出勤」ボタンが表示されない()
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

        // 3. 出勤処理を実行
        $this->post(route('attendance.checkIn'));
        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', today())->first();
        $this->assertEquals('working', $attendance->status);

        // 4. 退勤処理を実行
        $this->post(route('attendance.checkOut'));
        $attendance->refresh();
        $this->assertEquals('finished', $attendance->status);

        // 5. 退勤後にホーム画面にアクセス
        $response = $this->get('/');

        // 6. 退勤済みであるため「出勤」ボタンが表示されないことを確認
        $response->assertDontSee('出勤');
    }

    /** @test */
    public function 勤務中の場合画面上に退勤ボタンが表示され処理後にステータスが「退勤済」になる()
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

        // 4. ホーム画面（`/`）にアクセスし、「退勤」ボタンが表示されているか確認
        $response = $this->get('/');
        $response->assertSee('退勤');

        // 5. 「退勤」ボタンをクリック（退勤処理を実行）
        $response = $this->post(route('attendance.checkOut'));

        // 6. ステータスが「退勤済」に更新されたか確認
        $attendance->refresh();  // 最新のデータを再取得
        $this->assertEquals('finished', $attendance->status); // 'finished' になっていることを確認

        // 7. 画面上に「退勤済」と表示されているか確認
        $response = $this->get('/');
        $response->assertSee('退勤済');
    }

    /** @test */
    public function 退勤時刻が管理画面で確認できる()
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
            'check_out' => $today->copy()->setTime(18, 0), // 退勤時刻 18:00
            'status' => 'finished',
        ]);

        // 管理者が勤怠一覧ページにアクセス
        $response = $this->get(route('admin.admin_list', ['date' => $today->toDateString()]));

        // ユーザー1の名前と退勤時刻が正しく表示されているか確認
        $response->assertSee($user1->name);
        $response->assertSee('18:00'); // ユーザー1の退勤時刻（18:00）
    }

}