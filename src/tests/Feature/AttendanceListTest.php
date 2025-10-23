<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Work;
use Carbon\Carbon;


class AttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_attendance_list_check()
    {
        $user = User::factory()->create();
        $works = collect();
        // 3日分の勤怠を作る
        for ($i = 0; $i < 3; $i++) {
            // 日付をずらす
            $workDate = Carbon::today()->subDays($i);
            // Factory 作成時に work_date を指定
            $work = Work::factory()->create([
                'user_id' => $user->id,
                'work_date' => $workDate->toDateString(),
                'clock_in_at' => $workDate->copy()->setHour(8)->addMinutes(rand(0,30)),
                'clock_out_at' => $workDate->copy()->setHour(16)->addMinutes(rand(30,90)),
            ]);
            $works->push($work);
        }
        // ログインして勤怠一覧ページを開く
        $response = $this->actingAs($user)->get('/attendance/list');
        // 自分の勤怠データが全て表示されていること
        foreach ($works as $work) {
            $response->assertSee(Carbon::parse($work->work_date)->format('m/d'), false);
            $response->assertSee(Carbon::parse($work->clock_in_at)->format('H:i'), false);
            $response->assertSee(Carbon::parse($work->clock_out_at)->format('H:i'), false);
        }
    }

    public function test_attendance_list_month_check()
    {
        $now = Carbon::create(2025, 10, 17, 9, 3, 19);
        Carbon::setTestNow($now);
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/attendance/list');
        $month = Carbon::now()->format('Y/m');
        $response->assertSee($month);
    }

    public function test_attendance_list_sub_month_check()
    {
        $now = Carbon::create(2025, 10, 17, 9, 3, 19);
        Carbon::setTestNow($now);
        $user = User::factory()->create();
        Work::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->subMonth(),
            'clock_in_at' => Carbon::now()->subMonth()->subMinutes(2),
            'clock_out_at' => Carbon::now()->subMonth()->subMinutes(1),
        ]);
        $response = $this->actingAs($user)->get('/attendance/list');
        $monthParam = $now->copy()->subMonth()->format('Y-m');
        $response = $this->get("/attendance/list?month={$monthParam}");
        $response->assertSeeInOrder([
            $now->copy()->subMonth()->format('m/d'),
            $now->copy()->subMonth()->subMinutes(2)->format('H:i'),
            $now->copy()->subMonth()->subMinutes(1)->format('H:i'),
        ]);
    }

    public function test_attendance_list_add_month_check()
    {
        $now = Carbon::create(2025, 8, 17, 9, 3, 19);
        Carbon::setTestNow($now);
        $user = User::factory()->create();
        Work::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->addMonth(),
            'clock_in_at' => Carbon::now()->addMonth()->subMinutes(2),
            'clock_out_at' => Carbon::now()->addMonth()->subMinutes(1),
        ]);
        $response = $this->actingAs($user)->get('/attendance/list');
        $monthParam = $now->copy()->addMonth()->format('Y-m');
        $response = $this->get("/attendance/list?month={$monthParam}");
        $response->assertSeeInOrder([
            $now->copy()->addMonth()->format('m/d'),
            $now->copy()->addMonth()->subMinutes(2)->format('H:i'),
            $now->copy()->addMonth()->subMinutes(1)->format('H:i'),
        ]);
    }

    public function test_attendance_detail_show()
    {
        $now = Carbon::create(2025, 10, 17, 9, 3, 19);
        Carbon::setTestNow($now);
        $user = User::factory()->create();
        $work = Work::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => Carbon::now()->subMinutes(2),
            'clock_out_at' => Carbon::now()->subMinutes(1),
        ]);
        $response = $this->actingAs($user)->get("/attendance/detail/{$work->id}");
        $response->assertViewIs('auth.attendance_detail');
    }

}
