<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\WorkApplication;
use Carbon\Carbon;

class WorkApplicationFactory extends Factory
{
    protected $model = WorkApplication::class;

    public function definition()
    {
        $clockIn = $this->faker->dateTimeBetween('-5 months', 'now');
        $clockOut = (clone $clockIn)->modify('+'.rand(7,9).' hours');

        return [
            'work_id' => null, // Seederで上書き
            'clock_in_at' => $clockIn,
            'clock_out_at' => $clockOut,
            'reason' => $this->faker->sentence(),
            'approve_at' => rand(0,1) ? $this->faker->dateTimeBetween($clockOut, 'now') : null,
        ];
    }
}

