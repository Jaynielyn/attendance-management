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
        $user = User::create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => Carbon::now(),
        ]);

        $this->actingAs($user);

        $now = Carbon::now()->locale('ja')->isoFormat('YYYY年MM月DD日(ddd) HH:mm');

        $response = $this->get('/');

        $response->assertSee($now);
    }

    public function 勤務外ステータスが表示される()
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
            'status' => 'off',
        ]);

        $response = $this->get('/');

        $response->assertSee('勤務外');
    }

    /** @test */
    public function 出勤中ステータスが表示される()
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

        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩中ステータスが表示される()
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
            'status' => 'break',
        ]);

        $response = $this->get('/');

        $response->assertSee('休憩中');
    }

    /** @test */
    public function 退勤済ステータスが表示される()
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
            'status' => 'finished',
        ]);

        $response = $this->get('/');

        $response->assertSee('退勤済');
    }
}
