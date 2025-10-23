<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkApplication;
use App\Models\Rest;
use App\Models\RestApplication;
use Carbon\Carbon;

class RestApplicationSeeder extends Seeder
{
    public function run()
    {
        $workApplications = WorkApplication::all();
        foreach ($workApplications as $workApp) {
            $rests = $workApp->work->rests; // 既存の休憩
            // 既存休憩に対して10%で申請
            foreach ($rests as $rest) {
                if (rand(1,100) <= 10) {
                    RestApplication::factory()->create([
                        'work_application_id' => $workApp->id,
                        'rest_id'            => $rest->id,
                        'rest_start_at'      => $rest->rest_start_at,
                        'rest_end_at'        => $rest->rest_end_at,
                    ]);
                }
            }
            // 10%の確率で新規休憩申請を作成（既存と重複しないようにランダム時間）
            if (rand(1,100) <= 10) {
                $existingTimes = $rests->map(fn($r)=>[$r->rest_start_at, $r->rest_end_at])->toArray();
                $newStart = Carbon::parse($workApp->clock_in_at)->copy()->addHours(rand(2,7));
                $newEnd   = (clone $newStart)->addMinutes(rand(10,60));
                // 既存休憩と重複する場合はスキップ（簡易チェック）
                foreach ($existingTimes as [$s,$e]) {
                    if (($newStart < $e) && ($newEnd > $s)) {
                        continue 2; // foreach(workApplications) をスキップ
                    }
                }
                RestApplication::factory()->create([
                    'work_application_id' => $workApp->id,
                    'rest_id'            => null,
                    'rest_start_at'      => $newStart,
                    'rest_end_at'        => $newEnd,
                ]);
            }
        }
    }
}
