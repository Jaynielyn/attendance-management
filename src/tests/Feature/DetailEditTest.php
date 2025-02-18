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

        // 2. ログイン
        $this->actingAs($user);

        // 3. 勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'check_in' => now()->subHours(1), // 1時間前に出勤
            'check_out' => now()->subHours(2), // 2時間前に退勤（出勤より前の時間）
        ]);

        // 4. 勤怠修正リクエストを送信（出勤時間 > 退勤時間）
        $response = $this->put(route('attendance.update', ['id' => $attendance->id]), [
            'check_in' => Carbon::now()->format('H:i'), // 現在時刻を出勤時間に設定
            'check_out' => Carbon::now()->subHours(2)->format('H:i'), // 2時間前を退勤時間に設定（出勤より前）
        ]);

        // 5. エラーメッセージが表示されるか確認
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

        // 勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'check_in' => '08:00',
            'check_out' => '17:00',
        ]);

        // 休憩開始時間を退勤時間より後に設定
        $response = $this->put(route('attendance.update', ['id' => $attendance->id]), [
            'check_in' => '08:00',
            'check_out' => '17:00',
            'break_times' => [
                ['start' => '18:00', 'end' => '18:30'], // 退勤時間より後に休憩を開始
            ],
            'remarks' => 'テスト用の備考',
        ]);

        // エラーメッセージが表示されるか確認
        $response->assertSessionHasErrors([
            'check_out' => '出勤時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後の場合エラーメッセージが表示されるか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // ユーザーとしてログイン
        $this->actingAs($user);

        // 勤怠情報の作成
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'check_in' => '09:00',
            'check_out' => '18:00',
        ]);

        // 退勤時間が出勤時間より前になっている場合のリクエスト
        $response = $this->put(route('attendance.update', [
            'id' => $attendance->id,  // 勤怠データのIDを渡す
        ]), [
            'check_in' => '09:00',
            'check_out' => '08:00', // 退勤時間が出勤時間より前
            'break_start' => ['12:00'],
            'break_end' => ['13:00'],
            'remarks' => '退勤時間が出勤時間より前です。',
        ]);

        // エラーメッセージが表示されるか確認
        $response->assertSessionHasErrors([
            'check_out' => '出勤時間もしくは退勤時間が不適切な値です。',
        ]);
    }

    /** @test */
    public function 備考欄が未入力の場合エラーメッセージが表示されるか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. ログイン
        $this->actingAs($user);

        // 3. 勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'check_in' => now()->subHours(8),
            'check_out' => now()->subHours(2),
        ]);

        // 4. 備考を未入力でリクエストを送信
        $response = $this->put(route('attendance.update', ['id' => $attendance->id]), [
            'check_in' => $attendance->check_in->format('H:i'),
            'check_out' => $attendance->check_out->format('H:i'),
            'break_times' => [
                ['start' => now()->subHours(4)->format('H:i'), 'end' => now()->subHours(3)->format('H:i')],
            ],
            'remarks' => '', // 未入力
        ]);

        // 6. エラーメッセージが表示されるか確認
        $response->assertSessionHasErrors(['remarks' => '備考を記入してください。']);
    }

    /** @test */
    public function 承認待ちの修正申請が全て表示され承認後に反映されるか()
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

        // **申請内容が正しく表示されているか確認**
        $response->assertSee($user->name);  // ユーザー名
        // 日付のフォーマットをチェック（ゼロ埋めありの月）
        $response->assertSee(Carbon::parse($editRequest->new_date)->format('Y年')); // 年表示
        $response->assertSee(Carbon::parse($editRequest->new_date)->format('m月d日'));  // 月日表示
        $response->assertSee($editRequest->new_check_in);  // 出勤時間
        $response->assertSee($editRequest->new_check_out);  // 退勤時間
        $response->assertSee($editRequest->reason);  // 申請理由
    }

    /** @test */
    public function 承認待ちの申請がログインユーザーの申請一覧に表示されるか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. ログイン
        $this->actingAs($user);

        // 3. 勤怠データを作成
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user->id,
            'check_in' => now()->subHours(8),
            'check_out' => now()->subHours(2),
        ]);

        // 4. 修正申請を作成（承認待ち）
        $editRequest1 = \App\Models\EditRequest::factory()->create([
            'attendance_id' => $attendance1->id,
            'user_id' => $user->id,
            'approval_status' => '承認待ち',
        ]);

        // 5. 申請一覧画面にアクセス
        $response = $this->get(route('user.requests'));  // ユーザーの申請一覧画面にアクセス

        // 6. 申請一覧画面にアクセスできるか確認
        $response->assertStatus(200);

        // 7. 申請一覧に「承認待ち」の申請が表示されているか確認
        $response->assertSee(Carbon::parse($editRequest1->new_date)->format('Y/m/d'));

        $response->assertSee('承認待ち');  // 申請の状態が表示されていることを確認
    }

    /** @test */
    public function 承認済みの修正申請が一般ユーザーの申請一覧の「承認済み」タブに表示されるか()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');  // 管理者としてログイン

        /** @var \App\Models\User $user */
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

        // 管理者が修正申請を承認
        $response = $this->post(route('admin.approve_request', $editRequest->id));  // 承認アクション
        $editRequest->refresh();  // 承認後のステータスを取得
        $this->assertEquals('承認済み', $editRequest->approval_status);  // ステータスが「承認済み」になっていることを確認

        // 一般ユーザーとしてログイン
        $this->actingAs($user);

        // 申請一覧ページの「承認済み」タブを開く
        $response = $this->get(route('user.requests', ['status' => '承認済み']));  // 申請一覧で「承認済み」を表示

        // ステータスコード 200 を確認
        $response->assertStatus(200);

        // 承認済みの修正申請が一覧に表示されていることを確認
        $response->assertSee($editRequest->user->name);
        $response->assertSee(Carbon::parse($editRequest->new_date)->format('Y/m/d'));
        $response->assertSee($editRequest->reason);  // 申請理由
        $response->assertSee(Carbon::parse($editRequest->requested_at)->format('Y/m/d'));
        $response->assertSee('承認済み'); // 承認済みステータスが表示されること
    }

    /** @test */
    public function 各申請の詳細を押下すると勤怠詳細画面に遷移するか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. ログイン
        $this->actingAs($user);

        // 3. 勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'check_in' => now()->subHours(8),
            'check_out' => now()->subHours(2),
        ]);

        // 4. 修正申請を作成
        $editRequest = \App\Models\EditRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'approval_status' => '承認待ち',
        ]);

        // 5. 申請一覧画面にアクセス
        $response = $this->get(route('user.requests'));

        // 6. 「詳細」ボタンを押す（GETリクエスト）
        $response = $this->get(route('user.request.detail', ['id' => $editRequest->id]));

        // 7. 勤怠詳細画面へリダイレクトされることを確認
        $response->assertStatus(302);
        $response->assertRedirect(route('attendance.detail', ['id' => $attendance->id]));
    }
}
