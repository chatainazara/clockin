<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Work;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use HolidayJp\HolidayJp;

class WorkSeeder extends Seeder
{
    public function run()
    {
        // 直近5ヶ月分の期間
        $startDate = Carbon::today()->subMonths(5)->startOfMonth();
        $endDate   = Carbon::today()->endOfMonth();
        $period    = CarbonPeriod::create($startDate, $endDate);

        // 先頭6人のユーザー
        $users = User::take(6)->get();

        foreach ($users as $user) {
            foreach ($period as $date) {
                // 土日祝を除外
                if ($date->isWeekend() || HolidayJp::isHoliday($date)) {
                    continue;
                }

                // 90%の確率でif内の処理を実行する
                if (rand(1, 10) <= 9) {
                    $start = $date->copy()->setHour(8)->addMinutes(rand(0, 30));
                    $end   = $date->copy()->setHour(16)->addMinutes(rand(30, 90));

                    Work::factory()->create([
                        'user_id'      => $user->id,
                        'work_date'    => $date->toDateString(),
                        'clock_in_at'  => $start,
                        'clock_out_at' => $end,
                    ]);
                }
            }
        }
    }
}


