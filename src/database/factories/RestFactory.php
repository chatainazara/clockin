<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Rest;
use Carbon\Carbon;

class RestFactory extends Factory
{
    protected $model = Rest::class;

    public function definition()
    {
        $start = Carbon::today()->setHour(12)->setMinute(0);
        $end   = (clone $start)->addHour();

        return [
            'work_id'       => null, // Seederで上書き
            'rest_start_at' => $start,
            'rest_end_at'   => $end,
        ];
    }
}



