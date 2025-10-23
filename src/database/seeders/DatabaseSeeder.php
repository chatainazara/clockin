<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        // 出退勤データ
        $this->call(WorkSeeder::class);

        // 休憩データ
        $this->call(RestSeeder::class);

        // 出退勤修正申請
        $this->call(WorkApplicationSeeder::class);

        // 休憩修正申請
        $this->call(RestApplicationSeeder::class);
    }
}
