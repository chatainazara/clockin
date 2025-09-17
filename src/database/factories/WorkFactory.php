<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Work;
use Carbon\Carbon;

class WorkFactory extends Factory
{
    protected $model = Work::class;

    public function definition()
    {
        $start = Carbon::today()->setHour(8)->addMinutes(rand(0, 30));
        $end   = (clone $start)->setHour(16)->addMinutes(rand(30, 90));

        return [
            'user_id'      => 1, // Seederで上書き
            'work_date'    => $start->toDateString(),
            'clock_in_at'  => $start,
            'clock_out_at' => $end,
        ];
    }
}
