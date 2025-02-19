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
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => Carbon::now(),
        ]);

        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'status' => 'working', 
        ]);

        $response = $this->get('/');
        $response->assertSee('休憩入');

        $response = $this->post(route('break.start'));

        $attendance->refresh();
        $this->assertEquals('break', $attendance->status);

        $response = $this->get('/');
        $response->assertSee('休憩中');
    }

    /** @test */
    public function 出勤中の場合画面上に休憩入ボタンが表示される()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => Carbon::now(),
        ]);

        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'status' => 'working',
        ]);

        $response = $this->get('/');

        $response->assertSee('休憩入');
    }

    /** @test */
    public function 休憩入と休憩戻を繰り返す場合休憩戻ボタンが再度表示されること()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => Carbon::now(),
        ]);

        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'status' => 'working',
        ]);

        $response = $this->post(route('break.start'));

        $response = $this->get('/');
        $response->assertSee('休憩戻');

        $response = $this->post(route('break.end'));

        $attendance->refresh();
        $this->assertEquals('working', $attendance->status);

        $response = $this->post(route('break.start'));

        $response = $this->get('/');
        $response->assertSee('休憩戻');
    }

    /** @test */
    public function 休憩入後休憩戻を行いステータスが「出勤中」に変更されるか()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => Carbon::now(),
        ]);

        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today(),
            'status' => 'working',
        ]);

        $response = $this->post(route('break.start'));

        $attendance->refresh();
        $this->assertEquals('break', $attendance->status); 

        $response = $this->post(route('break.end'));

        $attendance->refresh();
        $this->assertEquals('working', $attendance->status);
    }

    /** @test */
    public function その日になされたユーザー1の合計休憩時間が管理画面で確認できる()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $user1 = User::factory()->create();

        $today = Carbon::today();

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'date' => $today,
            'check_in' => $today->copy()->setTime(9, 0),
            'check_out' => $today->copy()->setTime(18, 0),
            'status' => 'finished',
        ]);

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

        $response = $this->get(route('admin.admin_list', ['date' => $today->toDateString()]));

        $response->assertSee($user1->name);
        $response->assertSee('00:45');
    }
}