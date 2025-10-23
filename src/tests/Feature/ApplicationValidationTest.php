<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Work;
use App\Models\Rest;
use App\Models\WorkApplication;
use Carbon\Carbon;

class ApplicationValidationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_clock_in_before_clock_out()
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
        $response = $this->actingAs($user)->post("/attendance/detail/{$work->id}", [
            'work_id' => $work->id,
            'work_application' => [
                'clock_in_at' => '18:00',
                'clock_out_at' => '09:00',
                'reason' => 'テスト：逆転時間',
            ],
        ]);
        $response->assertSessionHasErrors([
            'work_application.clock_in_at',
        ]);
        $this->assertEquals('出勤時間もしくは退勤時間が不適切な値です', session('errors')->first('work_application.clock_in_at'));
    }

    public function test_rest_start_before_clock_out()
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
        $response = $this->actingAs($user)->post("/attendance/detail/{$work->id}", [
            'work_id' => $work->id,
            'work_application' => [
                'clock_in_at' => '09:00',
                'clock_out_at' => '18:00',
                'reason' => 'テスト：逆転時間',
            ],
            'rest_applications' => [
                [
                'rest_start_at' => '18:30',
                'rest_end_at' => '18:45'
                ]
            ]
        ]);
        $response->assertSessionHasErrors([
            'rest_applications.0.rest_start_at',
        ]);
        $this->assertEquals('休憩時間が不適切な値です', session('errors')->first('rest_applications.0.rest_start_at'));
    }

    public function test_clock_out_before_rest_end()
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
        $response = $this->actingAs($user)->post("/attendance/detail/{$work->id}", [
            'work_id' => $work->id,
            'work_application' => [
                'clock_in_at' => '09:00',
                'clock_out_at' => '18:00',
                'reason' => 'テスト：逆転時間',
            ],
            'rest_applications' => [
                [
                'rest_start_at' => '17:30',
                'rest_end_at' => '18:45'
                ]
            ]
        ]);
        $response->assertSessionHasErrors([
            'rest_applications.0.rest_end_at',
        ]);
        $this->assertEquals('休憩時間もしくは退勤時間が不適切な値です', session('errors')->first('rest_applications.0.rest_end_at'));
    }

    public function test_empty_reason()
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
        $response = $this->actingAs($user)->post("/attendance/detail/{$work->id}", [
            'work_id' => $work->id,
            'work_application' => [
                'reason' => '',
            ],
        ]);
        $response->assertSessionHasErrors([
            'work_application.reason',
        ]);
        $this->assertEquals('備考を記入してください', session('errors')->first('work_application.reason'));
    }

}
