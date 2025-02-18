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

        // 2. ログイン
        $this->actingAs($user);

        // 3. 勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        // 4. 勤怠詳細画面にアクセス
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        // 5. 勤怠詳細画面が正しく表示されるか確認
        $response->assertStatus(200);

        // 6. 「名前」にログインユーザーの氏名が表示されているか確認
        $response->assertSee('テストユーザー');
    }

    /** @test */
    public function 勤怠詳細画面の日付が選択した日付になっているか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. ログイン
        $this->actingAs($user);

        // 3. 特定の日付の勤怠データを作成
        $selectedDate = now()->subDays(3); // 3日前の日付
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $selectedDate,
        ]);

        // 4. 勤怠詳細画面にアクセス
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        // 5. 勤怠詳細画面が正しく表示されるか確認
        $response->assertStatus(200);

        // 6. 選択した日付が正しく表示されているか確認（フォーマットをビューに合わせる）
        $response->assertSee($selectedDate->format('Y年'));
        $response->assertSee(ltrim($selectedDate->format('n月j日'), '0')); // 月・日付のゼロ埋めを削除
    }

    /** @test */
    public function 勤怠詳細画面の出勤退勤時間がログインユーザーの打刻と一致しているか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. ログイン
        $this->actingAs($user);

        // 3. 特定の出勤・退勤時間で勤怠データを作成
        $checkInTime = now()->subHours(8);  // 8時間前に出勤
        $checkOutTime = now()->subHours(1); // 1時間前に退勤

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'check_in' => $checkInTime,
            'check_out' => $checkOutTime,
        ]);

        // 4. 勤怠詳細画面にアクセス
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        // 5. 勤怠詳細画面が正しく表示されるか確認
        $response->assertStatus(200);

        // 6. 出勤・退勤時間が正しく表示されているか確認
        $response->assertSee($checkInTime->format('H:i'));  // 例: 08:30
        $response->assertSee($checkOutTime->format('H:i')); // 例: 17:45
    }

    /** @test */
    public function 勤怠詳細画面の休憩時間がログインユーザーの打刻と一致しているか()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        // 2. ログイン
        $this->actingAs($user);

        // 3. 勤怠データを作成（出勤・退勤時間あり）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'check_in' => now()->subHours(8),  // 出勤8時間前
            'check_out' => now()->subHours(1), // 退勤1時間前
        ]);

        // 4. 休憩データを作成（2回の休憩）
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

        // 5. 勤怠詳細画面にアクセス
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        // 6. 勤怠詳細画面が正しく表示されるか確認
        $response->assertStatus(200);

        // 7. 休憩時間が正しく表示されているか確認
        $response->assertSee($break1Start->format('H:i'));
        $response->assertSee($break1End->format('H:i'));
        $response->assertSee($break2Start->format('H:i'));
        $response->assertSee($break2End->format('H:i'));
    }

}