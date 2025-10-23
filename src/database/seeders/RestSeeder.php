<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Work;
use App\Models\Rest;
use Carbon\Carbon;

class RestSeeder extends Seeder
{
    public function run()
    {
        $works = Work::all();
        foreach ($works as $work) {
            $workDate = Carbon::parse($work->work_date);
            // 昼休憩（必ず作成）
            Rest::factory()->create([
                'work_id'       => $work->id,
                'rest_start_at' => $workDate->copy()->setHour(12)->setMinute(0),
                'rest_end_at'   => $workDate->copy()->setHour(13)->setMinute(0),
            ]);
            // 小休憩（30%の確率で追加）
            if (rand(1, 10) <= 3) {
                $patterns = [
                    ['hour' => 10, 'minutes' => 0, 'duration' => 15],
                    ['hour' => 15, 'minutes' => 0, 'duration' => 30],
                    ['hour' => 16, 'minutes' => 0, 'duration' => 10],
                ];
                $pattern = $patterns[array_rand($patterns)];
                $start = $workDate->copy()->setHour($pattern['hour'])->setMinute($pattern['minutes']);
                $end   = (clone $start)->addMinutes($pattern['duration']);
                Rest::factory()->create([
                    'work_id'       => $work->id,
                    'rest_start_at' => $start,
                    'rest_end_at'   => $end,
                ]);
            }
        }
    }
}
