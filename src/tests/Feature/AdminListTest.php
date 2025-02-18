<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\BreakTime;
use App\Models\Attendance;
use Carbon\Carbon;

class AdminListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin'); // admin guardを指定

        // ユーザーを作成
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

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

        // ユーザー2の勤怠データを作成
        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'date' => $today,
            'check_in' => $today->copy()->setTime(10, 0),
            'check_out' => $today->copy()->setTime(19, 0),
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

        // ユーザー2の休憩時間（13:00〜13:45）
        BreakTime::create([
            'attendance_id' => $attendance2->id,
            'break_start' => $today->copy()->setTime(13, 0),
            'break_end' => $today->copy()->setTime(13, 45),
        ]);

        // 管理者が勤怠一覧ページにアクセス
        $response = $this->get(route('admin.admin_list'));

        // ユーザー名が表示されているか確認
        $response->assertSee($user1->name);
        $response->assertSee($user2->name);

        // 出勤時間・退勤時間が正しく表示されているか確認
        $response->assertSee($attendance1->check_in->format('H:i'));
        $response->assertSee($attendance1->check_out->format('H:i'));
        $response->assertSee($attendance2->check_in->format('H:i'));
        $response->assertSee($attendance2->check_out->format('H:i'));

        // 休憩時間の合計が正しく表示されているか確認
        $response->assertSee('00:45'); // ユーザー1の休憩（30分 + 15分 = 45分）
        $response->assertSee('00:45'); // ユーザー2の休憩（45分）

        // 勤務時間の合計が正しく表示されているか確認
        $response->assertSee('08:15'); // ユーザー1の勤務時間（9時間 - 45分 = 8時間15分）
        $response->assertSee('08:15'); // ユーザー2の勤務時間（9時間 - 45分 = 8時間15分）
    }

    /** @test */
    public function 遷移した際に現在の日付が表示される()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 今日の日付を取得
        $today = Carbon::today();

        // 管理者が勤怠一覧ページにアクセス
        $response = $this->get(route('admin.admin_list'));

        $response->assertSee($today->format('Y/m/d'));
    }

    /** @test */
    public function 前日を押下した時に前の日の勤怠情報が表示される()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 今日の日付を取得
        $today = Carbon::today();
        $yesterday = $today->copy()->subDay(); // 前日を取得

        // ユーザーを作成
        $user = User::factory()->create();

        // 前日の勤怠データを作成
        $attendanceYesterday = Attendance::create([
            'user_id' => $user->id,
            'date' => $yesterday,
            'check_in' => $yesterday->copy()->setTime(9, 0),
            'check_out' => $yesterday->copy()->setTime(18, 0),
            'status' => 'finished',
        ]);

        // 「前日」ボタンを押した状態で勤怠一覧ページにアクセス
        $response = $this->get(route('admin.admin_list', ['date' => $yesterday->format('Y-m-d')]));

        // 前日の日付が正しく表示されているか確認
        $response->assertSee($yesterday->format('Y/m/d'));   // 例: "2025/02/15"

        // 勤怠情報が正しく表示されているか確認
        $response->assertSee($user->name);
        $response->assertSee($attendanceYesterday->check_in->format('H:i'));
        $response->assertSee($attendanceYesterday->check_out->format('H:i'));
    }

    /** @test */
    public function 翌日を押下した時に次の日の勤怠情報が表示される()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // 今日の日付を取得
        $today = Carbon::today();
        $tomorrow = $today->copy()->addDay(); // 翌日を取得

        // ユーザーを作成
        $user = User::factory()->create();

        // 翌日の勤怠データを作成
        $attendanceTomorrow = Attendance::create([
            'user_id' => $user->id,
            'date' => $tomorrow,
            'check_in' => $tomorrow->copy()->setTime(9, 0),
            'check_out' => $tomorrow->copy()->setTime(18, 0),
            'status' => 'finished',
        ]);

        // 「翌日」ボタンを押した状態で勤怠一覧ページにアクセス
        $response = $this->get(route('admin.admin_list', ['date' => $tomorrow->format('Y-m-d')]));

        // 翌日の日付が正しく表示されているか確認
        $response->assertSee($tomorrow->format('Y/m/d'));   // 例: "2025/02/17"

        // 勤怠情報が正しく表示されているか確認
        $response->assertSee($user->name);
        $response->assertSee($attendanceTomorrow->check_in->format('H:i'));
        $response->assertSee($attendanceTomorrow->check_out->format('H:i'));
    }
}