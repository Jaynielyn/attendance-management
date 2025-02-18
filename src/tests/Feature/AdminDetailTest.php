<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Carbon;

class AdminDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面に表示されるデータが選択したものになっているか()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin'); // admin guardを指定

        // ユーザーを作成
        $user = User::factory()->create();

        // 今日の日付を取得
        $today = Carbon::today();

        // ユーザーの勤怠データを作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'check_in' => $today->copy()->setTime(9, 0),
            'check_out' => $today->copy()->setTime(18, 0),
            'status' => 'finished',
        ]);

        // ユーザーの休憩時間（12:00〜12:30）
        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => $today->copy()->setTime(12, 0),
            'break_end' => $today->copy()->setTime(12, 30),
        ]);

        // 管理者が勤怠詳細ページにアクセス
        $response = $this->get(route('admin.attendance.detail', [
            'userId' => $user->id,
            'date' => $today->toDateString(),
        ]));

        // ユーザーの名前、出勤時間、退勤時間、休憩時間が表示されているか確認
        $response->assertSee($user->name);
        $response->assertSee($attendance->check_in->format('H:i'));
        $response->assertSee($attendance->check_out->format('H:i'));

        // 休憩時間が正しく表示されているか確認
        $breakStart = Carbon::parse($attendance->breakTimes->first()->break_start); // Carbonに変換
        $breakEnd = Carbon::parse($attendance->breakTimes->first()->break_end);     // Carbonに変換

        // `assertSee` で休憩時間の表示を確認
        $response->assertSee($breakStart->format('H:i'));
        $response->assertSee($breakEnd->format('H:i'));
    }

    /** @test */
    public function 出勤時間が退勤時間より後になっている場合エラーメッセージ()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // サンプルユーザーを作成
        $user = User::factory()->create();

        // 勤怠情報の作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'check_in' => '09:00',
            'check_out' => '18:00',
        ]);

        // 出勤時間を退勤時間より後に設定してリクエスト
        $response = $this->put(route('admin.attendance.update', [
            'userId' => $user->id,
            'date' => $attendance->date
        ]), [
            'check_in' => '19:00', // 変更された出勤時間
            'check_out' => '18:00', // 変更された退勤時間
            'remarks' => '修正テスト'
        ]);

        // エラーメッセージが表示されるか確認
        $response->assertSessionHasErrors([
            'check_out' => '出勤時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // サンプルユーザーを作成
        $user = User::factory()->create();

        // 勤怠情報の作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'check_in' => '09:00',
            'check_out' => '18:00',
        ]);

        // 休憩開始時間を退勤時間より後に設定してリクエスト
        $response = $this->put(route('admin.attendance.update', [
            'userId' => $user->id,
            'date' => $attendance->date
        ]), [
            'check_in' => '09:00',
            'check_out' => '18:00',
            'break_start' => ['19:30'], // 休憩開始時間を退勤後に設定
            'break_end' => ['20:00'], // 休憩終了時間
            'remarks' => '休憩時間が退勤後です。',
        ]);

        // エラーメッセージが表示されるか確認
        $response->assertSessionHasErrors([
            'check_out' => '出勤時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // サンプルユーザーを作成
        $user = User::factory()->create();

        // 勤怠情報の作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'check_in' => '09:00',
            'check_out' => '18:00',
        ]);

        // 休憩終了時間を退勤時間より後に設定してリクエスト
        $response = $this->put(route('admin.attendance.update', [
            'userId' => $user->id,
            'date' => $attendance->date
        ]), [
            'check_in' => '09:00',
            'check_out' => '18:00',
            'break_start' => ['17:30'], // 休憩開始時間（退勤前）
            'break_end' => ['19:00'], // 休憩終了時間（退勤後）
            'remarks' => '休憩時間が退勤後です。',
        ]);

        // エラーメッセージが表示されるか確認
        $response->assertSessionHasErrors([
            'check_out' => '出勤時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /** @test */
    public function 備考欄が未入力の場合エラーメッセージが表示される()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        // サンプルユーザーを作成
        $user = User::factory()->create();

        // 勤怠情報の作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'check_in' => '09:00',
            'check_out' => '18:00',
        ]);

        // 備考欄を未入力にしてリクエスト
        $response = $this->put(route('admin.attendance.update', [
            'userId' => $user->id,
            'date' => $attendance->date
        ]), [
            'check_in' => '09:00',
            'check_out' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['12:30'],
            'remarks' => '', // 未入力
        ]);

        // エラーメッセージが表示されるか確認
        $response->assertSessionHasErrors([
            'remarks' => '備考を記入してください。',
        ]);
    }

}