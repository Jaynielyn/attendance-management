<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::factory(15)->create();

        \App\Models\Attendance::factory(15)->create();

        \App\Models\BreakTime::factory(13)->create();

        \App\Models\EditRequest::factory(10)->create();

        \App\Models\EditBreakTime::factory(10)->create();
    }
}
