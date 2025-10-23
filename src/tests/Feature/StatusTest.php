<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Work;
use App\Models\Rest;
use Carbon\Carbon;

class StatusTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_off_duty_status()
    {
        // 登録したばかりのユーザーは出勤の打刻がないので絶対に勤務外
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    public function test_on_duty_status()
    {
        // 出勤中の状態を作成
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        Work::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => Carbon::now()->subMinutes(1),
        ]);
        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function test_on_rest_status()
    {
        // 出勤後、休憩中の状態を作成
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        $work = Work::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => Carbon::now()->subMinutes(2),
        ]);
        Rest::create([
            'work_id' => $work->id,
            'rest_start_at' =>  Carbon::now()->subMinutes(1),
        ]);
        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    public function test_after_work_status()
    {
        // 退勤後の状態を作成
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        Work::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => Carbon::now()->subMinutes(2),
            'clock_out_at' => Carbon::now()->subMinutes(1),
        ]);
        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }
}