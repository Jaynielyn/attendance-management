<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class DetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面の名前がログインユーザーの氏名になっているか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['name' => 'テストユーザー']);

        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);

        $response->assertSee('テストユーザー');
    }

    /** @test */
    public function 勤怠詳細画面の日付が選択した日付になっているか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $selectedDate = now()->subDays(3);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $selectedDate,
        ]);

        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);

        $response->assertSee($selectedDate->format('Y年'));
        $response->assertSee(ltrim($selectedDate->format('n月j日'), '0'));
    }

    /** @test */
    public function 勤怠詳細画面の出勤退勤時間がログインユーザーの打刻と一致しているか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $checkInTime = now()->subHours(8);
        $checkOutTime = now()->subHours(1);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'check_in' => $checkInTime,
            'check_out' => $checkOutTime,
        ]);

        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);

        $response->assertSee($checkInTime->format('H:i'));
        $response->assertSee($checkOutTime->format('H:i'));
    }

    /** @test */
    public function 勤怠詳細画面の休憩時間がログインユーザーの打刻と一致しているか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'check_in' => now()->subHours(8),
            'check_out' => now()->subHours(1),
        ]);

        $break1Start = now()->subHours(6);
        $break1End = now()->subHours(5);
        $break2Start = now()->subHours(3);
        $break2End = now()->subHours(2);

        \App\Models\BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => $break1Start,
            'break_end' => $break1End,
        ]);

        \App\Models\BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => $break2Start,
            'break_end' => $break2End,
        ]);

        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);

        $response->assertSee($break1Start->format('H:i'));
        $response->assertSee($break1End->format('H:i'));
        $response->assertSee($break2Start->format('H:i'));
        $response->assertSee($break2End->format('H:i'));
    }
}