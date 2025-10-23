<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Work;
use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class AttendanceTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_clock_in()
    {
        //出勤前のユーザーを作成
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertDontSeeText('出勤中');
        $response->assertSeeText('出勤');
        $response = $this->followingRedirects()->post('/attendance/action', [
            'action' => 'clockIn',
        ]);
        $response->assertSeeText('出勤中');
    }

    public function test_clock_out()
    {
        //退勤後のユーザーを作成
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
        $html = $response->getContent();
        $crawler = new Crawler($html);
        // ボタン部分をブロック化
        $buttonBlock = $crawler->filter('.content__btn')->text();
        // 出勤ボタンが存在しないことを確認
        $this->assertStringNotContainsString('出勤', $buttonBlock);
    }

    public function test_clock_in_at_check()
    {
        // 時間ズレを防ぐため時間を固定
        $now = Carbon::create(2025, 10, 17, 9, 3, 19);
        Carbon::setTestNow($now);
        //出勤前のユーザーを作成
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        $this->actingAs($user);
        $response = $this->get('/attendance');
        // 出勤処理
        $response = $this->followingRedirects()->post('/attendance/action', [
            'action' => 'clockIn',
        ]);
        $response = $this->get('/attendance/list');
        $today = Carbon::today()->format('m/d');
        $response->assertSeeInOrder([
            $today,
            $now->format('H:i'),
        ]);
    }
}
