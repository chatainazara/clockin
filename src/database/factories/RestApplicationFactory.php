<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\RestApplication;
use Carbon\Carbon;

class RestApplicationFactory extends Factory
{
    protected $model = RestApplication::class;

    public function definition()
    {
        $start = Carbon::today()->setHour(rand(12,16))->setMinute(0);
        $end   = (clone $start)->addMinutes(rand(10,60));

        return [
            'work_application_id' => null, // Seederで上書き
            'rest_id'            => null, // Seederで決定
            'rest_start_at'      => $start,
            'rest_end_at'        => $end,
        ];
    }
}

