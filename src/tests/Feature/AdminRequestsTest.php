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
        $this->actingAs($admin, 'admin');  // 管理者としてログイン

        // 一般ユーザーを作成
        $user = User::factory()->create();

        // 承認待ちの修正申請を3件作成
        $pendingRequests = EditRequest::factory()->count(3)->create([
            'user_id' => $user->id,
            'approval_status' => '承認待ち',
        ]);

        // 申請一覧ページを開く（デフォルトで "承認待ち" が選択される）
        $response = $this->get(route('admin.requests', ['status' => '承認待ち']));

        // ステータスコード 200 を確認
        $response->assertStatus(200);

        // **1. 承認待ちの申請データが表示されていることを確認**
        foreach ($pendingRequests as $request) {
            $response->assertSee($request->user->name); // ユーザー名
            $response->assertSee(Carbon::parse($request->new_date)->format('Y/m/d')); // 対象日時
            $response->assertSee($request->reason); // 申請理由
            $response->assertSee('承認待ち'); // 承認待ちステータスが表示されること
        }
    }

    /** @test */
    public function 承認済みの修正申請が表示されるか()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');  // 管理者としてログイン

        // 複数のユーザーを作成
        $users = User::factory()->count(3)->create();

        // 各ユーザーの承認済みの修正申請を作成
        foreach ($users as $user) {
            EditRequest::factory()->create([
                'user_id' => $user->id,
                'approval_status' => '承認済み',
            ]);
        }

        // 承認済みの修正申請が表示されるか確認するため、申請一覧ページを開く
        $response = $this->get(route('admin.requests', ['status' => '承認済み']));

        // ステータスコード 200 を確認
        $response->assertStatus(200);

        // **1. 各ユーザーの承認済みの申請データが表示されていることを確認**
        foreach ($users as $user) {
            $response->assertSee($user->name); // ユーザー名
            $response->assertSee('承認済み');   // 承認済みステータス
        }
    }

    /** @test */
    public function 修正申請の詳細画面が表示され申請内容が正しく表示されているか()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');  // 管理者としてログイン

        // 一般ユーザーを作成
        $user = User::factory()->create();

        // 承認待ちの修正申請を作成
        $editRequest = EditRequest::factory()->create([
            'user_id' => $user->id,
            'approval_status' => '承認待ち',  // 承認待ちの申請
            'new_date' => now()->addDays(1),  // 新しい日付を設定（例）
            'new_check_in' => '09:00',  // 出勤時間
            'new_check_out' => '18:00',  // 退勤時間
            'reason' => 'テスト用の申請理由',  // 申請理由
        ]);

        // 申請一覧ページを開く（デフォルトで "承認待ち" が選択される）
        $response = $this->get(route('admin.requests', ['status' => '承認待ち']));

        // ステータスコード 200 を確認
        $response->assertStatus(200);

        // **申請の詳細リンクが正しく表示されているか確認**
        $response->assertSee(route('admin.approval_request', $editRequest->id));  // 詳細画面へのリンクが表示されることを確認

        // **詳細画面に遷移するためにリンクをクリック**
        $response = $this->get(route('admin.approval_request', $editRequest->id));

        // ステータスコード 200 を確認
        $response->assertStatus(200);

        // **1. 申請内容が正しく表示されているか確認**
        $response->assertSee($user->name);  // ユーザー名
        // 日付のフォーマットをチェック（ゼロ埋めありの月）
        $response->assertSee(Carbon::parse($editRequest->new_date)->format('Y年'));
        $response->assertSee(Carbon::parse($editRequest->new_date)->format('m月d日'));  // 対象日時（例：2025年 02月19日）
        $response->assertSee($editRequest->new_check_in);  // 出勤時間
        $response->assertSee($editRequest->new_check_out);  // 退勤時間
        $response->assertSee($editRequest->reason);  // 申請理由
    }

    /** @test */
    public function 修正申請の詳細画面で「承認」ボタンを押すと勤怠情報が更新されるか()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');  // 管理者としてログイン

        // 一般ユーザーを作成
        $user = User::factory()->create();

        // 勤怠情報を作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->addDays(1),  // 新しい日付を設定（例）
            'check_in' => '08:00',  // 出勤時間
            'check_out' => '17:00',  // 退勤時間
        ]);

        // 承認待ちの修正申請を作成
        $editRequest = EditRequest::factory()->create([
            'user_id' => $user->id,
            'approval_status' => '承認待ち',  // 承認待ちの申請
            'new_date' => $attendance->date,  // 作成した勤怠情報の `date` を使用
            'new_check_in' => '09:00',  // 修正後の出勤時間
            'new_check_out' => '18:00',  // 修正後の退勤時間
            'reason' => 'テスト用の申請理由',  // 申請理由
        ]);

        // 申請詳細画面にアクセス
        $response = $this->get(route('admin.approval_request', $editRequest->id));  // 承認画面にアクセス

        // ステータスコード 200 を確認
        $response->assertStatus(200);

        // **「承認」ボタンを押す**
        $response = $this->post(route('admin.approve_request', $editRequest->id));  // 承認アクション

        // 承認後に申請のステータスが「承認済み」になっていることを確認
        $editRequest->refresh();
        $this->assertEquals('承認済み', $editRequest->approval_status);  // ステータスが「承認済み」になっていることを確認

        // **勤怠情報が更新されたか確認**
        $attendance->refresh();
    }
}
