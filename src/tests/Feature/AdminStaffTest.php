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

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者ユーザーが全一般ユーザーの氏名とメールアドレスを正しく確認できる()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();

        $users = User::factory()->count(3)->create();

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.staff_list'));

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function 選択したユーザーの勤怠情報が正しく表示される()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();

        $user = User::factory()->create();

        $attendances = \App\Models\Attendance::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        foreach ($attendances as $attendance) {
            \App\Models\BreakTime::factory()->count(2)->create([
                'attendance_id' => $attendance->id,
                'break_start' => \Carbon\Carbon::parse($attendance->check_in)->addHours(3)->format('H:i'),
                'break_end' => \Carbon\Carbon::parse($attendance->check_in)->addHours(3)->addMinutes(30)->format('H:i'),
            ]);
        }

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.staff_attendance', ['id' => $user->id]));

        foreach ($attendances as $attendance) {
            $response->assertSee($attendance->date->format('Y-m-d'));

            $response->assertSee($attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-');
            $response->assertSee($attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '-');

            $totalBreakMinutes = $attendance->breakTimes->sum(function ($break) {
                return \Carbon\Carbon::parse($break->break_start)->diffInMinutes(\Carbon\Carbon::parse($break->break_end));
            });
            $formattedBreakTime = sprintf('%02d:%02d', intdiv($totalBreakMinutes, 60), $totalBreakMinutes % 60);
            $response->assertSee($formattedBreakTime);

            if ($attendance->check_in && $attendance->check_out) {
                $totalWorkMinutes = \Carbon\Carbon::parse($attendance->check_in)->diffInMinutes(\Carbon\Carbon::parse($attendance->check_out)) - $totalBreakMinutes;
                $formattedWorkTime = sprintf('%02d:%02d', intdiv($totalWorkMinutes, 60), $totalWorkMinutes % 60);
                $response->assertSee($formattedWorkTime);
            }
        }

        $response->assertStatus(200);
    }

    /** @test */
    public function 前月を押下した時に表示月の前月の情報が表示される()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();

        $user = User::factory()->create();

        $currentMonth = now()->format('Y-m'); 
        $previousMonth = now()->subMonth()->format('Y-m');

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->startOfMonth()->toDateString(),
            'check_in' => '09:00',
            'check_out' => '18:00',
        ]);

        $previousAttendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-01-01',
            'check_in' => '08:30',
            'check_out' => '17:30',
        ]);

        $break1 = BreakTime::create([
            'attendance_id' => $previousAttendance->id,
            'break_start' => '12:00',
            'break_end' => '12:30',
        ]);

        $totalBreakMinutes = Carbon::parse($break1->break_start)->diffInMinutes(Carbon::parse($break1->break_end));
        $totalBreakTime = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);

        $workMinutes = Carbon::parse($previousAttendance->check_in)->diffInMinutes(Carbon::parse($previousAttendance->check_out));
        $totalWorkMinutes = $workMinutes - $totalBreakMinutes;
        $totalWorkTime = sprintf('%02d:%02d', floor($totalWorkMinutes / 60), $totalWorkMinutes % 60);

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.staff_attendance', ['id' => $user->id, 'month' => $currentMonth]));
        $response->assertStatus(200);
        $response->assertSee(now()->startOfMonth()->format('Y-m-d'));

        $response = $this->get(route('admin.staff_attendance', ['id' => $user->id, 'month' => $previousMonth]));
        $response->assertStatus(200);

        $response->assertSee($previousAttendance->date->format('Y-m-d'));
        $response->assertSee(Carbon::parse($previousAttendance->check_in)->format('H:i'));
        $response->assertSee(Carbon::parse($previousAttendance->check_out)->format('H:i'));
        $response->assertSee($totalBreakTime);
        $response->assertSee($totalWorkTime);
    }

    /** @test */
    public function 翌月を押下した時に表示月の翌月の情報が表示される()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();

        $user = User::factory()->create();

        $currentMonth = now()->format('Y-m');
        $nextMonth = now()->addMonth()->format('Y-m');

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->startOfMonth()->toDateString(),
            'check_in' => '09:00',
            'check_out' => '18:00',
        ]);

        $nextAttendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-03-01',
            'check_in' => '08:00',
            'check_out' => '17:00',
        ]);

        $break1 = BreakTime::create([
            'attendance_id' => $nextAttendance->id,
            'break_start' => '12:30',
            'break_end' => '13:00',
        ]);

        $totalBreakMinutes = Carbon::parse($break1->break_start)->diffInMinutes(Carbon::parse($break1->break_end));
        $totalBreakTime = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);

        $workMinutes = Carbon::parse($nextAttendance->check_in)->diffInMinutes(Carbon::parse($nextAttendance->check_out));
        $totalWorkMinutes = $workMinutes - $totalBreakMinutes;
        $totalWorkTime = sprintf('%02d:%02d', floor($totalWorkMinutes / 60), $totalWorkMinutes % 60);

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.staff_attendance', ['id' => $user->id, 'month' => $currentMonth]));
        $response->assertStatus(200);
        $response->assertSee(now()->startOfMonth()->format('Y-m-d'));

        $response = $this->get(route('admin.staff_attendance', ['id' => $user->id, 'month' => $nextMonth]));
        $response->assertStatus(200);

        $response->assertSee($nextAttendance->date->format('Y-m-d'));
        $response->assertSee(Carbon::parse($nextAttendance->check_in)->format('H:i'));
        $response->assertSee(Carbon::parse($nextAttendance->check_out)->format('H:i'));
        $response->assertSee($totalBreakTime);
        $response->assertSee($totalWorkTime); 
    }

    /** @test */
    public function 詳細を押下するとその日の勤怠詳細画面に遷移する()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'check_in' => '09:00',
            'check_out' => '18:00',
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.staff_attendance', ['id' => $user->id]));
        $response->assertStatus(200);

        $response = $this->get(route('admin.attendance.detail', [
            'userId' => $user->id,
            'date' => $attendance->date,
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('admin.admin_detail');
    }
}
