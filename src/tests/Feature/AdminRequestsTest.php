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

class AdminRequestsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 承認待ちの修正申請が全て表示されているか()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin'); 
        $user = User::factory()->create();

        $pendingRequests = EditRequest::factory()->count(3)->create([
            'user_id' => $user->id,
            'approval_status' => '承認待ち',
        ]);

        $response = $this->get(route('admin.requests', ['status' => '承認待ち']));

        $response->assertStatus(200);

        foreach ($pendingRequests as $request) {
            $response->assertSee($request->user->name);
            $response->assertSee(Carbon::parse($request->new_date)->format('Y/m/d'));
            $response->assertSee($request->reason);
            $response->assertSee('承認待ち');
        }
    }

    /** @test */
    public function 承認済みの修正申請が表示されるか()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $users = User::factory()->count(3)->create();

        foreach ($users as $user) {
            EditRequest::factory()->create([
                'user_id' => $user->id,
                'approval_status' => '承認済み',
            ]);
        }

        $response = $this->get(route('admin.requests', ['status' => '承認済み']));

        $response->assertStatus(200);

        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee('承認済み');
        }
    }

    /** @test */
    public function 修正申請の詳細画面が表示され申請内容が正しく表示されているか()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin'); 

        // 一般ユーザーを作成
        $user = User::factory()->create();

        // 承認待ちの修正申請を作成
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
    public function 修正申請の詳細画面で「承認」ボタンを押すと勤怠情報が更新されるか()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->addDays(1), 
            'check_in' => '08:00',
            'check_out' => '17:00',
        ]);

        $editRequest = EditRequest::factory()->create([
            'user_id' => $user->id,
            'approval_status' => '承認待ち',
            'new_date' => $attendance->date, 
            'new_check_in' => '09:00',
            'new_check_out' => '18:00',
            'reason' => 'テスト用の申請理由', 
        ]);

        $response = $this->get(route('admin.approval_request', $editRequest->id)); 

        $response->assertStatus(200);

        $response = $this->post(route('admin.approve_request', $editRequest->id));

        $editRequest->refresh();
        $this->assertEquals('承認済み', $editRequest->approval_status);

        $attendance->refresh();
    }
}
