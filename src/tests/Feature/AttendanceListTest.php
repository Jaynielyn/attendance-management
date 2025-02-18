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

        // 2. ログイン
        $this->actingAs($user);

        // 3. 勤怠データを作成
        $attendance1 = Attendance::factory()->create(['user_id' => $user->id]);
        $attendance2 = Attendance::factory()->create(['user_id' => $user->id]);

        // 4. 休憩データを作成
        BreakTime::factory()->create([
            'attendance_id' => $attendance1->id,
            'break_start' => now()->subHours(6),  // 休憩開始時間
            'break_end' => now()->subHours(5),    // 休憩終了時間
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance1->id,
            'break_start' => now()->subHours(4),
            'break_end' => now()->subHours(3),
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance2->id,
            'break_start' => now()->subHours(8),  // 休憩開始時間
            'break_end' => now()->subHours(7),    // 休憩終了時間
        ]);

        // 5. 勤怠一覧画面にアクセス
        $response = $this->get(route('attendance.list'));

        // 6. 各勤怠の日付が表示されているか
        $response->assertSee($attendance1->date->format('m/d')); // 日付
        $response->assertSee($attendance2->date->format('m/d')); // 日付

        // 7. 出勤時間と退勤時間が表示されるか確認
        $response->assertSee($attendance1->check_in ? $attendance1->check_in->format('H:i') : '-');  // 出勤時間
        $response->assertSee($attendance1->check_out ? $attendance1->check_out->format('H:i') : '-'); // 退勤時間

        $response->assertSee($attendance2->check_in ? $attendance2->check_in->format('H:i') : '-');  // 出勤時間
        $response->assertSee($attendance2->check_out ? $attendance2->check_out->format('H:i') : '-'); // 退勤時間

        // 8. 休憩時間の合計が正しく表示されるか
        $response->assertSee($attendance1->break_time); // 休憩時間
        $response->assertSee($attendance2->break_time); // 休憩時間

        // 9. 休憩時間を差し引いた作業時間（total_work_time）が正しく表示されるか
        $response->assertSee($attendance1->total_work_time); // 作業時間（休憩を差し引いた時間）
        $response->assertSee($attendance2->total_work_time); // 作業時間（休憩を差し引いた時間）
    }

    /** @test */
    public function 現在の月が表示されるか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. ログイン
        $this->actingAs($user);

        // 3. 勤怠一覧画面にアクセス
        $response = $this->get(route('attendance.list'));

        // 4. 現在の月を取得（フォーマットは 'Y/m' 形式）
        $currentMonth = now()->format('Y/m');

        // 5. 画面に現在の月が表示されているか確認
        $response->assertSee($currentMonth);  // 現在の月が表示されているか
    }

    /** @test */
    public function 前月ボタンを押すと前月の情報が表示されるか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. ログイン
        $this->actingAs($user);

        // 3. 現在の月を取得
        $currentMonth = now()->format('Y/m'); // 例: 2025/02
        $previousMonth = now()->subMonth()->format('Y/m'); // 例: 2025/01

        // 4. 勤怠一覧画面にアクセス（現在の月を確認）
        $response = $this->get(route('attendance.list'));

        // 5. まず現在の月が表示されているか確認
        $response->assertSee($currentMonth);

        // 6. 「前月」ボタンを押して、前月の勤怠データを取得
        $response = $this->get(route('attendance.list', ['month' => now()->subMonth()->format('Y-m')]));

        // 7. 前月の情報が表示されているか確認
        $response->assertSee($previousMonth);
    }

    /** @test */
    public function 翌月ボタンを押すと翌月の情報が表示されるか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. ログイン
        $this->actingAs($user);

        // 3. 現在の月と翌月を取得
        $currentMonth = now()->format('Y/m'); // 例: 2025/02
        $nextMonth = now()->addMonth()->format('Y/m'); // 例: 2025/03

        // 4. 勤怠一覧画面にアクセス（現在の月を確認）
        $response = $this->get(route('attendance.list'));

        // 5. まず現在の月が表示されているか確認
        $response->assertSee($currentMonth);

        // 6. 「翌月」ボタンを押して、翌月の勤怠データを取得
        $response = $this->get(route('attendance.list', ['month' => now()->addMonth()->format('Y-m')]));

        // 7. 翌月の情報が表示されているか確認
        $response->assertSee($nextMonth);
    }

    /** @test */
    public function 詳細ボタンを押すと勤怠詳細画面に遷移するか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. ログイン
        $this->actingAs($user);

        // 3. 勤怠データを作成
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        // 4. 勤怠詳細画面にアクセス
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        // 5. 勤怠詳細画面が表示されているか確認
        $response->assertStatus(200);

        // 修正: 年と月日がビューに渡されているので、それを使って確認
        $response->assertSee($attendance->year); // 年が表示されているか確認（例: 2025年）
        $response->assertSee($attendance->month_day); // 月日が表示されているか確認（例: 2月9日）
    }
}
