<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\EditRequest;
use App\Models\User;
use App\Models\Attendance;

class EditRequestFactory extends Factory
{
    protected $model = EditRequest::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'attendance_id' => Attendance::factory(),
            'reason' => $this->faker->sentence(),
            'new_date' => now()->format('Y-m-d'),
            'new_check_in' => now()->subHours(7)->format('H:i'),
            'new_check_out' => now()->subHours(1)->format('H:i'),
            'approval_status' => '承認待ち',
            'requested_at' => now(),
        ];
    }
}
