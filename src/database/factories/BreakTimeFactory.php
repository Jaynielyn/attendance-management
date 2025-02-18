<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakTime;
use App\Models\Attendance;

class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'break_start' => now()->subHours(rand(6, 10)),
            'break_end' => now()->subHours(rand(4, 5)),
        ];
    }
}
