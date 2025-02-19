<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Attendance;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'date' => now()->subDays(rand(0, 5)),
            'check_in' => $this->faker->time('H:i:s'),
            'check_out' => $this->faker->time('H:i:s'),
            'status' => 'finished',
        ];
    }
}
