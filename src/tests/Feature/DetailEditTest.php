<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;
use App\Models\User;
use App\Models\BreakTime;
use App\Models\Attendance;
use App\Models\EditRequest;
use App\Models\EditBreakTime;
use Carbon\Carbon;

class DetailEditTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤時間が退勤時間より後の場合エラーメッセージが表示されるか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'check_in' => now()->subHours(1),
            'check_out' => now()->subHours(2),
        ]);

        $response = $this->put(route('attendance.update', ['id' => $attendance->id]), [
            'check_in' => Carbon::now()->format('H:i'),
            'check_out' => Carbon::now()->subHours(2)->format('H:i'),
        ]);

        $response->assertSessionHasErrors([
            'check_out' => '出勤時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'check_in' => '08:00',
            'check_out' => '17:00',
        ]);

        $response = $this->put(route('attendance.update', ['id' => $attendance->id]), [
            'check_in' => '08:00',
            'check_out' => '17:00',
            'break_times' => [
                ['start' => '18:00', 'end' => '18:30'],
            ],
            'remarks' => 'テスト用の備考',
        ]);

        $response->assertSessionHasErrors([
            'check_out' => '出勤時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後の場合エラーメッセージが表示されるか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'check_in' => '09:00',
            'check_out' => '18:00',
        ]);

        $response = $this->put(route('attendance.update', [
            'id' => $attendance->id, 
        ]), [
            'check_in' => '09:00',
            'check_out' => '08:00',
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'remarks' => '退勤時間が出勤時間より前です。',
        ]);

        $response->assertSessionHasErrors([
            'check_out' => '出勤時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /** @test */
    public function 備考欄が未入力の場合エラーメッセージが表示されるか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'check_in' => now()->subHours(8),
            'check_out' => now()->subHours(2),
        ]);

        $response = $this->put(route('attendance.update', ['id' => $attendance->id]), [
            'check_in' => $attendance->check_in->format('H:i'),
            'check_out' => $attendance->check_out->format('H:i'),
            'break_times' => [
                ['start' => now()->subHours(4)->format('H:i'), 'end' => now()->subHours(3)->format('H:i')],
            ],
            'remarks' => '',
        ]);

        $response->assertSessionHasErrors(['remarks' => '備考を記入してください。']);
    }

    /** @test */
    public function 承認待ちの修正申請が全て表示され承認後に反映されるか()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $user = User::factory()->create();

        $editRequest = EditRequest::factory()->create([
            'user_id' => $user->id,
            'approval_status' => '承認待ち',
            'new_date' => now()->addDays(1),
            'new_check_in' => '09:00',
            'new_check_out' => '18:00', 
            'reason' => 'テスト用の申請理由', 
        ]);

        $response = $this->get(route('admin.requests', ['status' => '承認待ち']));

        $response->assertStatus(200);

        $response->assertSee(route('admin.approval_request', $editRequest->id)); 

        $response = $this->get(route('admin.approval_request', $editRequest->id));

        $response->assertStatus(200);

        $response->assertSee($user->name);
        $response->assertSee(Carbon::parse($editRequest->new_date)->format('Y年'));
        $response->assertSee(Carbon::parse($editRequest->new_date)->format('m月d日'));
        $response->assertSee($editRequest->new_check_in);
        $response->assertSee($editRequest->new_check_out);
        $response->assertSee($editRequest->reason);
    }

    /** @test */
    public function 承認待ちの申請がログインユーザーの申請一覧に表示されるか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user->id,
            'check_in' => now()->subHours(8),
            'check_out' => now()->subHours(2),
        ]);

        $editRequest1 = \App\Models\EditRequest::factory()->create([
            'attendance_id' => $attendance1->id,
            'user_id' => $user->id,
            'approval_status' => '承認待ち',
        ]);

        $response = $this->get(route('user.requests'));

        $response->assertStatus(200);

        $response->assertSee(Carbon::parse($editRequest1->new_date)->format('Y/m/d'));

        $response->assertSee('承認待ち');
    }

    /** @test */
    public function 承認済みの修正申請が一般ユーザーの申請一覧の「承認済み」タブに表示されるか()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $editRequest = EditRequest::factory()->create([
            'user_id' => $user->id,
            'approval_status' => '承認待ち',
            'new_date' => now()->addDays(1),
            'new_check_in' => '09:00',
            'new_check_out' => '18:00',
            'reason' => 'テスト用の申請理由',
        ]);

        $response = $this->post(route('admin.approve_request', $editRequest->id));
        $editRequest->refresh(); 
        $this->assertEquals('承認済み', $editRequest->approval_status);

        $this->actingAs($user);

        $response = $this->get(route('user.requests', ['status' => '承認済み'])); 
        $response->assertStatus(200);

        $response->assertSee($editRequest->user->name);
        $response->assertSee(Carbon::parse($editRequest->new_date)->format('Y/m/d'));
        $response->assertSee($editRequest->reason); 
        $response->assertSee(Carbon::parse($editRequest->requested_at)->format('Y/m/d'));
        $response->assertSee('承認済み');
    }

    /** @test */
    public function 各申請の詳細を押下すると勤怠詳細画面に遷移するか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'check_in' => now()->subHours(8),
            'check_out' => now()->subHours(2),
        ]);

        $editRequest = \App\Models\EditRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'approval_status' => '承認待ち',
        ]);

        $response = $this->get(route('user.requests'));

        $response = $this->get(route('user.request.detail', ['id' => $editRequest->id]));

        $response->assertStatus(302);
        $response->assertRedirect(route('attendance.detail', ['id' => $attendance->id]));
    }
}
