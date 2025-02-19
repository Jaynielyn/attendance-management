<?php

namespace Database\Factories;

use App\Models\EditRequest;
use App\Models\BreakTime;
use Illuminate\Database\Eloquent\Factories\Factory;

class EditBreakTimeFactory extends Factory
{
    protected $model = \App\Models\EditBreakTime::class;

    public function definition()
    {
        return [
            'edit_request_id' => EditRequest::factory(),
            'new_break_start' => $this->faker->time('H:i:s'),
            'new_break_end' => $this->faker->time('H:i:s'),
            'break_id' => BreakTime::factory(),
        ];
    }
}
