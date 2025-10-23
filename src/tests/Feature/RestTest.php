<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Work;
use App\Models\Rest;
use Carbon\Carbon;

class RestTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_rest_start_at()
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
        $response->assertSeeText('休憩入');
        // 休憩入処理
        $response = $this->followingRedirects()->post('/attendance/action', [
            'action' => 'restStart',
        ]);
        $response->assertSeeText('休憩中');
    }

    public function test_rest_start_at_repeatable()
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
        $response->assertSeeText('休憩入');
        // 休憩入処理
        $response = $this->followingRedirects()->post('/attendance/action', [
            'action' => 'restStart',
        ]);
        $response->assertSeeText('休憩中');
        // 休憩戻処理
        $response = $this->followingRedirects()->post('/attendance/action', [
            'action' => 'restEnd',
        ]);
        $response->assertSeeText('休憩入');
    }

    public function test_rest_end_at()
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
        // 休憩入処理
        $response = $this->followingRedirects()->post('/attendance/action', [
            'action' => 'restStart',
        ]);
        $response->assertSeeText('休憩戻');
        // 休憩戻処理
        $response = $this->followingRedirects()->post('/attendance/action', [
            'action' => 'restEnd',
        ]);
        $response->assertSeeText('出勤中');
    }

    public function test_rest_end_at_repeatable()
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
        // 休憩入処理
        $response = $this->followingRedirects()->post('/attendance/action', [
            'action' => 'restStart',
        ]);
        // 休憩戻処理
        $response = $this->followingRedirects()->post('/attendance/action', [
            'action' => 'restEnd',
        ]);
        // 休憩入処理2回目
        $response = $this->followingRedirects()->post('/attendance/action', [
            'action' => 'restStart',
        ]);
        $response->assertSeeText('休憩戻');
    }

    public function test_rest_check()
    {
        // 時間ズレを防ぐため時間を固定
        $now = Carbon::create(2025, 10, 17, 9, 3, 19);
        Carbon::setTestNow($now);
        // 出勤中の状態を作成
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        Work::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => Carbon::now(),
        ]);
        $this->actingAs($user);
        $response = $this->get('/attendance');
        Carbon::setTestNow($now->addMinutes(1));
        // 休憩入処理
        $response = $this->followingRedirects()->post('/attendance/action', [
            'action' => 'restStart',
        ]);
        Carbon::setTestNow($now->addMinutes(1));
        // 休憩戻処理
        $response = $this->followingRedirects()->post('/attendance/action', [
            'action' => 'restEnd',
        ]);
        $response = $this->get('/attendance/list');
        $today = Carbon::today()->format('m/d');
        $response->assertSeeInOrder([
            $today,
            $now->subMinutes(2)->format('H:i'),
            '0:01',//休憩時間
        ]);
    }
}
