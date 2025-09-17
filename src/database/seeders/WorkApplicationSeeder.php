<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Work;
use App\Models\WorkApplication;

class WorkApplicationSeeder extends Seeder
{
    public function run()
    {
        $works = Work::all();

        foreach ($works as $work) {
            // 5%の確率で申請
            if (rand(1,100) <= 5) {
                WorkApplication::factory()->create([
                    'work_id' => $work->id,
                    'clock_in_at' => $work->clock_in_at,
                    'clock_out_at' => $work->clock_out_at,
                ]);
            }
        }
    }
}

