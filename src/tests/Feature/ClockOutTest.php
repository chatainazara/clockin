<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Work;
use Carbon\Carbon;

class ClockOutTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_clock_out()
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
        $response->assertSeeText('退勤');
        $response = $this->followingRedirects()->post('/attendance/action', [
            'action' => 'clockOut',
        ]);
        $response->assertSeeText('退勤済');
    }

    public function test_clock_out_check()
    {
        // 時間ズレを防ぐため時間を固定
        $now = Carbon::create(2025, 10, 17, 9, 3, 19);
        Carbon::setTestNow($now);
        // 出勤前の状態を作成
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        $this->actingAs($user);
        $response = $this->get('/attendance');
        // 出勤ボタンを押す
        $response = $this->followingRedirects()->post('/attendance/action', [
            'action' => 'clockIn',
        ]);
        Carbon::setTestNow($now->addMinutes(1));
        // 退勤ボタンを押す
        $response = $this->followingRedirects()->post('/attendance/action', [
            'action' => 'clockOut',
        ]);
        $response = $this->get('/attendance/list');
        $today = Carbon::today()->format('m/d');
        $response->assertSeeInOrder([
            $today,
            $now->copy()->subMinutes(1)->format('H:i'),
            $now->format('H:i'),
        ]);
    }
}
