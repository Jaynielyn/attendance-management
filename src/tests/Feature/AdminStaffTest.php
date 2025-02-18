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

        // 2. 一般ユーザーを複数作成
        $users = User::factory()->count(3)->create();

        // 3. 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // 4. スタッフ一覧ページにアクセス
        $response = $this->get(route('admin.staff_list'));

        // 5. 各ユーザーの「氏名」「メールアドレス」が表示されているか確認
        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }

        // 6. ステータスコードが 200 であることを確認
        $response->assertStatus(200);
    }

    /** @test */
    public function 選択したユーザーの勤怠情報が正しく表示される()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();

        // 2. 一般ユーザーを作成
        $user = User::factory()->create();

        // 3. ユーザーの勤怠データを作成（3件）
        $attendances = \App\Models\Attendance::factory()->count(3)->create([
            'user_id' => $user->id,
        ]);

        // 4. 各勤怠データに休憩時間を追加
        foreach ($attendances as $attendance) {
            \App\Models\BreakTime::factory()->count(2)->create([
                'attendance_id' => $attendance->id,
                'break_start' => \Carbon\Carbon::parse($attendance->check_in)->addHours(3)->format('H:i'),
                'break_end' => \Carbon\Carbon::parse($attendance->check_in)->addHours(3)->addMinutes(30)->format('H:i'),
            ]);
        }

        // 5. 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // 6. 管理者がユーザーの勤怠一覧ページにアクセス
        $response = $this->get(route('admin.staff_attendance', ['id' => $user->id]));

        // 7. 各勤怠情報が正しく表示されているか確認
        foreach ($attendances as $attendance) {
            // 日付
            $response->assertSee($attendance->date->format('Y-m-d'));

            // 出勤・退勤時間
            $response->assertSee($attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-');
            $response->assertSee($attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '-');

            // 休憩時間の計算（合計）
            $totalBreakMinutes = $attendance->breakTimes->sum(function ($break) {
                return \Carbon\Carbon::parse($break->break_start)->diffInMinutes(\Carbon\Carbon::parse($break->break_end));
            });
            $formattedBreakTime = sprintf('%02d:%02d', intdiv($totalBreakMinutes, 60), $totalBreakMinutes % 60);
            $response->assertSee($formattedBreakTime);

            // 勤務時間（休憩時間を除く合計）
            if ($attendance->check_in && $attendance->check_out) {
                $totalWorkMinutes = \Carbon\Carbon::parse($attendance->check_in)->diffInMinutes(\Carbon\Carbon::parse($attendance->check_out)) - $totalBreakMinutes;
                $formattedWorkTime = sprintf('%02d:%02d', intdiv($totalWorkMinutes, 60), $totalWorkMinutes % 60);
                $response->assertSee($formattedWorkTime);
            }
        }

        // 8. ステータスコードが 200 であることを確認
        $response->assertStatus(200);
    }

    /** @test */
    public function 前月を押下した時に表示月の前月の情報が表示される()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();

        // 2. 一般ユーザーを作成
        $user = User::factory()->create();

        // 3. 今月と前月の勤怠データを作成
        $currentMonth = now()->format('Y-m'); // 例: 2025-02
        $previousMonth = now()->subMonth()->format('Y-m'); // 例: 2025-01

        // 今月の勤怠データ
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->startOfMonth()->toDateString(), // 今月の1日
            'check_in' => '09:00',
            'check_out' => '18:00',
        ]);

        // 前月の勤怠データ
        $previousAttendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-01-01',
            'check_in' => '08:30',
            'check_out' => '17:30',
        ]);

        // 休憩時間の作成（例: 12:00~12:30）
        $break1 = BreakTime::create([
            'attendance_id' => $previousAttendance->id,
            'break_start' => '12:00',
            'break_end' => '12:30',
        ]);

        // 休憩合計時間を計算
        $totalBreakMinutes = Carbon::parse($break1->break_start)->diffInMinutes(Carbon::parse($break1->break_end));
        $totalBreakTime = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);

        // 出勤合計時間を計算（勤務時間 - 休憩時間）
        $workMinutes = Carbon::parse($previousAttendance->check_in)->diffInMinutes(Carbon::parse($previousAttendance->check_out));
        $totalWorkMinutes = $workMinutes - $totalBreakMinutes;
        $totalWorkTime = sprintf('%02d:%02d', floor($totalWorkMinutes / 60), $totalWorkMinutes % 60);

        // 4. 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // 5. 勤怠一覧の初期表示（今月のデータ）
        $response = $this->get(route('admin.staff_attendance', ['id' => $user->id, 'month' => $currentMonth]));
        $response->assertStatus(200);
        $response->assertSee(now()->startOfMonth()->format('Y-m-d')); // 今月の日付が表示されていることを確認

        // 6. 「前月へ」ボタンを押下（GETリクエストで前月のデータを取得）
        $response = $this->get(route('admin.staff_attendance', ['id' => $user->id, 'month' => $previousMonth]));
        $response->assertStatus(200);

        // 勤怠データが正しく表示されているか確認
        $response->assertSee($previousAttendance->date->format('Y-m-d'));
        $response->assertSee(Carbon::parse($previousAttendance->check_in)->format('H:i'));
        $response->assertSee(Carbon::parse($previousAttendance->check_out)->format('H:i'));
        $response->assertSee($totalBreakTime); // 休憩合計時間
        $response->assertSee($totalWorkTime); // 出勤合計時間
    }

    /** @test */
    public function 翌月を押下した時に表示月の翌月の情報が表示される()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();

        // 2. 一般ユーザーを作成
        $user = User::factory()->create();

        // 3. 今月と翌月の勤怠データを作成
        $currentMonth = now()->format('Y-m'); // 例: 2025-02
        $nextMonth = now()->addMonth()->format('Y-m'); // 例: 2025-03

        // 今月の勤怠データ
        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->startOfMonth()->toDateString(), // 今月の1日
            'check_in' => '09:00',
            'check_out' => '18:00',
        ]);

        // 翌月の勤怠データ
        $nextAttendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2025-03-01',
            'check_in' => '08:00',
            'check_out' => '17:00',
        ]);

        // 休憩時間の作成（例: 12:30~13:00）
        $break1 = BreakTime::create([
            'attendance_id' => $nextAttendance->id,
            'break_start' => '12:30',
            'break_end' => '13:00',
        ]);

        // 休憩合計時間を計算
        $totalBreakMinutes = Carbon::parse($break1->break_start)->diffInMinutes(Carbon::parse($break1->break_end));
        $totalBreakTime = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);

        // 出勤合計時間を計算（勤務時間 - 休憩時間）
        $workMinutes = Carbon::parse($nextAttendance->check_in)->diffInMinutes(Carbon::parse($nextAttendance->check_out));
        $totalWorkMinutes = $workMinutes - $totalBreakMinutes;
        $totalWorkTime = sprintf('%02d:%02d', floor($totalWorkMinutes / 60), $totalWorkMinutes % 60);

        // 4. 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // 5. 勤怠一覧の初期表示（今月のデータ）
        $response = $this->get(route('admin.staff_attendance', ['id' => $user->id, 'month' => $currentMonth]));
        $response->assertStatus(200);
        $response->assertSee(now()->startOfMonth()->format('Y-m-d')); // 今月の日付が表示されていることを確認

        // 6. 「翌月へ」ボタンを押下（GETリクエストで翌月のデータを取得）
        $response = $this->get(route('admin.staff_attendance', ['id' => $user->id, 'month' => $nextMonth]));
        $response->assertStatus(200);

        // 勤怠データが正しく表示されているか確認
        $response->assertSee($nextAttendance->date->format('Y-m-d'));
        $response->assertSee(Carbon::parse($nextAttendance->check_in)->format('H:i'));
        $response->assertSee(Carbon::parse($nextAttendance->check_out)->format('H:i'));
        $response->assertSee($totalBreakTime); // 休憩合計時間
        $response->assertSee($totalWorkTime); // 出勤合計時間
    }

    /** @test */
    public function 詳細を押下するとその日の勤怠詳細画面に遷移する()
    {
        /** @var \App\Models\Admin $admin */
        $admin = Admin::factory()->create();

        // 2. 一般ユーザーを作成
        $user = User::factory()->create();

        // 3. 勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'check_in' => '09:00',
            'check_out' => '18:00',
        ]);

        // 4. 管理者としてログイン
        $this->actingAs($admin, 'admin');

        // 5. 勤怠一覧画面を開く
        $response = $this->get(route('admin.staff_attendance', ['id' => $user->id]));
        $response->assertStatus(200);

        // 6. 「詳細」リンクをクリック（GETリクエストを送信）
        $response = $this->get(route('admin.attendance.detail', [
            'userId' => $user->id,
            'date' => $attendance->date,
        ]));

        // 7. 詳細画面が表示されているか確認
        $response->assertStatus(200);
        $response->assertViewIs('admin.admin_detail');  // admin_detail ビューが表示されているか確認
    }
}
