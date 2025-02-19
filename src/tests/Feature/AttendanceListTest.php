<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;
use App\Models\BreakTime;
use App\Models\Attendance;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 自分が行った勤怠情報が全て表示されているか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $attendance1 = Attendance::factory()->create(['user_id' => $user->id]);
        $attendance2 = Attendance::factory()->create(['user_id' => $user->id]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance1->id,
            'break_start' => now()->subHours(6),
            'break_end' => now()->subHours(5),
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance1->id,
            'break_start' => now()->subHours(4),
            'break_end' => now()->subHours(3),
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance2->id,
            'break_start' => now()->subHours(8),
            'break_end' => now()->subHours(7),   
        ]);

        $response = $this->get(route('attendance.list'));

        $response->assertSee($attendance1->date->format('m/d')); 
        $response->assertSee($attendance2->date->format('m/d'));

        $response->assertSee($attendance1->check_in ? $attendance1->check_in->format('H:i') : '-');
        $response->assertSee($attendance1->check_out ? $attendance1->check_out->format('H:i') : '-'); 

        $response->assertSee($attendance2->check_in ? $attendance2->check_in->format('H:i') : '-');
        $response->assertSee($attendance2->check_out ? $attendance2->check_out->format('H:i') : '-');

        $response->assertSee($attendance1->break_time);
        $response->assertSee($attendance2->break_time); 

        $response->assertSee($attendance1->total_work_time);
        $response->assertSee($attendance2->total_work_time);
    }

    /** @test */
    public function 現在の月が表示されるか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('attendance.list'));

        $currentMonth = now()->format('Y/m');

        $response->assertSee($currentMonth);
    }

    /** @test */
    public function 前月ボタンを押すと前月の情報が表示されるか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $currentMonth = now()->format('Y/m');
        $previousMonth = now()->subMonth()->format('Y/m');

        $response = $this->get(route('attendance.list'));

        $response->assertSee($currentMonth);

        $response = $this->get(route('attendance.list', ['month' => now()->subMonth()->format('Y-m')]));

        $response->assertSee($previousMonth);
    }

    /** @test */
    public function 翌月ボタンを押すと翌月の情報が表示されるか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $currentMonth = now()->format('Y/m');
        $nextMonth = now()->addMonth()->format('Y/m');

        $response = $this->get(route('attendance.list'));

        $response->assertSee($currentMonth);

        $response = $this->get(route('attendance.list', ['month' => now()->addMonth()->format('Y-m')]));

        $response->assertSee($nextMonth);
    }

    /** @test */
    public function 詳細ボタンを押すと勤怠詳細画面に遷移するか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        $response->assertStatus(200);

        $response->assertSee($attendance->year);
        $response->assertSee($attendance->month_day);
    }
}
