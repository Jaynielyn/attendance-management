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
        $this->actingAs($admin, 'admin');

        $user = User::factory()->create();

        $today = Carbon::today();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'check_in' => $today->copy()->setTime(9, 0),
            'check_out' => $today->copy()->setTime(18, 0),
            'status' => 'finished',
        ]);

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => $today->copy()->setTime(12, 0),
            'break_end' => $today->copy()->setTime(12, 30),
        ]);

        $response = $this->get(route('admin.attendance.detail', [
            'userId' => $user->id,
            'date' => $today->toDateString(),
        ]));

        $response->assertSee($user->name);
        $response->assertSee($attendance->check_in->format('H:i'));
        $response->assertSee($attendance->check_out->format('H:i'));

        $breakStart = Carbon::parse($attendance->breakTimes->first()->break_start);
        $breakEnd = Carbon::parse($attendance->breakTimes->first()->break_end);

        $response->assertSee($breakStart->format('H:i'));
        $response->assertSee($breakEnd->format('H:i'));
    }

    /** @test */
    public function 出勤時間が退勤時間より後になっている場合エラーメッセージ()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'check_in' => '09:00',
            'check_out' => '18:00',
        ]);

        $response = $this->put(route('admin.attendance.update', [
            'userId' => $user->id,
            'date' => $attendance->date
        ]), [
            'check_in' => '19:00',
            'check_out' => '18:00',
            'remarks' => '修正テスト'
        ]);

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

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'check_in' => '09:00',
            'check_out' => '18:00',
        ]);

        $response = $this->put(route('admin.attendance.update', [
            'userId' => $user->id,
            'date' => $attendance->date
        ]), [
            'check_in' => '09:00',
            'check_out' => '18:00',
            'break_start' => ['19:30'],
            'break_end' => ['20:00'],
            'remarks' => '休憩時間が退勤後です。',
        ]);

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

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'check_in' => '09:00',
            'check_out' => '18:00',
        ]);

        $response = $this->put(route('admin.attendance.update', [
            'userId' => $user->id,
            'date' => $attendance->date
        ]), [
            'check_in' => '09:00',
            'check_out' => '18:00',
            'break_start' => ['17:30'],
            'break_end' => ['19:00'],
            'remarks' => '休憩時間が退勤後です。',
        ]);

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

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'check_in' => '09:00',
            'check_out' => '18:00',
        ]);

        $response = $this->put(route('admin.attendance.update', [
            'userId' => $user->id,
            'date' => $attendance->date
        ]), [
            'check_in' => '09:00',
            'check_out' => '18:00',
            'break_start' => ['12:00'],
            'break_end' => ['12:30'],
            'remarks' => '',
        ]);

        $response->assertSessionHasErrors([
            'remarks' => '備考を記入してください。',
        ]);
    }
}