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
        $this->actingAs($admin, 'admin');

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $today = Carbon::today();

        $attendance1 = Attendance::create([
            'user_id' => $user1->id,
            'date' => $today,
            'check_in' => $today->copy()->setTime(9, 0),
            'check_out' => $today->copy()->setTime(18, 0),
            'status' => 'finished',
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user2->id,
            'date' => $today,
            'check_in' => $today->copy()->setTime(10, 0),
            'check_out' => $today->copy()->setTime(19, 0),
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

        BreakTime::create([
            'attendance_id' => $attendance2->id,
            'break_start' => $today->copy()->setTime(13, 0),
            'break_end' => $today->copy()->setTime(13, 45),
        ]);

        $response = $this->get(route('admin.admin_list'));

        $response->assertSee($user1->name);
        $response->assertSee($user2->name);

        $response->assertSee($attendance1->check_in->format('H:i'));
        $response->assertSee($attendance1->check_out->format('H:i'));
        $response->assertSee($attendance2->check_in->format('H:i'));
        $response->assertSee($attendance2->check_out->format('H:i'));

        $response->assertSee('00:45');
        $response->assertSee('00:45');

        $response->assertSee('08:15');
        $response->assertSee('08:15');
    }

    /** @test */
    public function 遷移した際に現在の日付が表示される()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $today = Carbon::today();

        $response = $this->get(route('admin.admin_list'));

        $response->assertSee($today->format('Y/m/d'));
    }

    /** @test */
    public function 前日を押下した時に前の日の勤怠情報が表示される()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $today = Carbon::today();
        $yesterday = $today->copy()->subDay();

        $user = User::factory()->create();

        $attendanceYesterday = Attendance::create([
            'user_id' => $user->id,
            'date' => $yesterday,
            'check_in' => $yesterday->copy()->setTime(9, 0),
            'check_out' => $yesterday->copy()->setTime(18, 0),
            'status' => 'finished',
        ]);

        $response = $this->get(route('admin.admin_list', ['date' => $yesterday->format('Y-m-d')]));

        $response->assertSee($yesterday->format('Y/m/d'));

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

        $today = Carbon::today();
        $tomorrow = $today->copy()->addDay();

        $user = User::factory()->create();

        $attendanceTomorrow = Attendance::create([
            'user_id' => $user->id,
            'date' => $tomorrow,
            'check_in' => $tomorrow->copy()->setTime(9, 0),
            'check_out' => $tomorrow->copy()->setTime(18, 0),
            'status' => 'finished',
        ]);

        $response = $this->get(route('admin.admin_list', ['date' => $tomorrow->format('Y-m-d')]));

        $response->assertSee($tomorrow->format('Y/m/d'));

        $response->assertSee($user->name);
        $response->assertSee($attendanceTomorrow->check_in->format('H:i'));
        $response->assertSee($attendanceTomorrow->check_out->format('H:i'));
    }
}