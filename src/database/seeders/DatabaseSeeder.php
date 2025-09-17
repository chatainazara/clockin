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
        // 1. 出退勤データ
        $this->call(WorkSeeder::class);

        // 2. 休憩データ
        $this->call(RestSeeder::class);

        // 3. 出退勤修正申請
        $this->call(WorkApplicationSeeder::class);

        // 4. 休憩修正申請
        $this->call(RestApplicationSeeder::class);
    }
}
