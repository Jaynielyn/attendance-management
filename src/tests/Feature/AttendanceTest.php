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
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => Carbon::now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/');

        $response->assertSee('出勤');

        $response = $this->post(route('attendance.checkIn'));

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', today())
            ->first();

        $this->assertEquals('working', $attendance->status);

        $response = $this->get('/');
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 出勤は一日一回のみできる()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => Carbon::now(),
        ]);

        $this->actingAs($user);

        $this->post(route('attendance.checkIn'));

        $response = $this->post(route('attendance.checkIn'));

        $response->assertStatus(302); 
        $response->assertRedirect('/');
    }

    /** @test */
    public function 勤務外のユーザーが出勤し管理画面で確認できる()
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

        $response = $this->get(route('admin.admin_list', ['date' => $today->toDateString()]));

        $response->assertSee($user1->name);
        $response->assertSee($attendance1->check_in->format('H:i'));
    }

    /** @test */
    public function 退勤済になった場合は画面上に「出勤」ボタンが表示されない()
    {
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => Carbon::now(),
        ]);

        $this->actingAs($user);

        $this->post(route('attendance.checkIn'));
        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', today())->first();
        $this->assertEquals('working', $attendance->status);

        $this->post(route('attendance.checkOut'));
        $attendance->refresh();
        $this->assertEquals('finished', $attendance->status);

        $response = $this->get('/');

        $response->assertDontSee('出勤');
    }

    /** @test */
    public function 勤務中の場合画面上に退勤ボタンが表示され処理後にステータスが「退勤済」になる()
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
        $response->assertSee('退勤');

        $response = $this->post(route('attendance.checkOut'));

        $attendance->refresh();
        $this->assertEquals('finished', $attendance->status); 

        $response = $this->get('/');
        $response->assertSee('退勤済');
    }

    /** @test */
    public function 退勤時刻が管理画面で確認できる()
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

        $response = $this->get(route('admin.admin_list', ['date' => $today->toDateString()]));

        $response->assertSee($user1->name);
        $response->assertSee('18:00');
    }
}