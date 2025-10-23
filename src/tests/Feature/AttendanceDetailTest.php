<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Work;
use App\Models\Rest;
use Carbon\Carbon;

class AttendanceDetailTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_attendance_detail_name()
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
        $response->assertSee($user->name);
    }

    public function test_attendance_detail_date()
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
        $response->assertSee($work->work_date->format('Y年m月d日'));
    }

    public function test_attendance_detail_time()
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
        $response->assertSeeInOrder([
            $now->copy()->subMinutes(2)->format('H:i'),
            $now->copy()->subMinutes(1)->format('H:i'),
        ]);
    }


    public function test_attendance_detail_rest_time()
    {
        $now = Carbon::create(2025, 10, 17, 9, 3, 19);
        Carbon::setTestNow($now);
        $user = User::factory()->create();
        $work = Work::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => Carbon::now()->subMinutes(20),
            'clock_out_at' => Carbon::now()->subMinutes(1),
        ]);
        Rest::create([
            'work_id' => $work->id,
            'rest_start_at' => Carbon::now()->subMinutes(10),
            'rest_end_at' => Carbon::now()->subMinutes(5),
        ]);
        $response = $this->actingAs($user)->get("/attendance/detail/{$work->id}");
        $response->assertSeeInOrder([
            $now->copy()->subMinutes(10)->format('H:i'),
            $now->copy()->subMinutes(5)->format('H:i'),
        ]);
    }
}
