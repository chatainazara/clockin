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

class ApplicationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_application_auth_list_all()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 17, 9, 3, 19));
        // ユーザーと勤怠作成
        $user = User::factory()->create(['role' => 'user']);
        $work = Work::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => Carbon::now()->subMinutes(2),
            'clock_out_at' => Carbon::now()->subMinutes(1),
        ]);
        // 申請作成
        $application = [
            'work_id' => $work->id,
            'work_application' => [
                'clock_in_at' => '09:00',
                'clock_out_at' => '18:00',
                'reason' => 'テスト',
            ],
        ];
        $this->actingAs($user)->post("/attendance/detail/{$work->id}", $application);
        // 管理者作成
        $admin = User::factory()->create(['role' => 'admin']);
        // pending タブ
        $response = $this->actingAs($admin)->get("/stamp_correction_request/list?status=pending");
        // テーブルに申請情報が表示されているか
        $response->assertSeeText($work->work_date->format('m/d'));
        $response->assertSeeText($user->name);
        // 承認画面に表示されるか
        $firstApplication = WorkApplication::first();
        $response = $this->actingAs($admin)->get("/stamp_correction_request/approve/{$firstApplication->id}");
        $response->assertSeeText($user->name);
        $response->assertSeeText($work->work_date->format('m月d日'));
    }

    public function test_application_auth_pending_list()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 17, 9, 0, 0));
        // 一般ユーザーと勤怠を複数作成
        $user = User::factory()->create(['role' => 'user']);
        $works = Work::factory()
            ->count(3)
            ->sequence(
                ['work_date' => Carbon::today()],
                ['work_date' => Carbon::yesterday()],
                ['work_date' => Carbon::today()->subDays(2)],
            )
            ->create(['user_id' => $user->id]);
        // 各勤怠に対して WorkApplication を紐付けて作成
        foreach ($works as $work) {
            WorkApplication::factory()->create([
                'work_id' => $work->id,
                'approve_at' => NULL,
            ]);
        }
        // 承認待ち一覧を開く
        $response = $this->actingAs($user)->get('/stamp_correction_request/list?status=pending');
        // 一覧に自分の申請が含まれていないるを確認
        foreach ($works as $work) {
            $response->assertSeeText($work->work_date->format('m/d'));
        }
        // 承認済み一覧に含まれないことを確認
        $responseApprove = $this->actingAs($user)->get('/stamp_correction_request/list?status=approved');
        foreach ($works as $work) {
            $responseApprove->assertDontSeeText($work->work_date->format('m/d'));
        }
    }

    public function test_application_auth_approved_list()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 17, 9, 0, 0));
        // 一般ユーザーと勤怠を複数作成
        $user = User::factory()->create(['role' => 'user']);
        $works = Work::factory()
            ->count(3)
            ->sequence(
                ['work_date' => Carbon::today()],
                ['work_date' => Carbon::yesterday()],
                ['work_date' => Carbon::today()->subDays(2)],
            )
            ->create(['user_id' => $user->id]);
        // 各勤怠に対して WorkApplication を紐付けて作成
        foreach ($works as $work) {
            WorkApplication::factory()->create([
                'work_id' => $work->id,
                'approve_at' => Carbon::now(),
            ]);
        }
        // 承認待ち一覧を開く
        $response = $this->actingAs($user)->get('/stamp_correction_request/list?status=pending');
        // 一覧に自分の申請が含まれていないことを確認
        foreach ($works as $work) {
            $response->assertDontSeeText($work->work_date->format('m/d'));
        }
        // 承認済み一覧に含まれることを確認
        $responseApprove = $this->actingAs($user)->get('/stamp_correction_request/list?status=approved');
        foreach ($works as $work) {
            $responseApprove->assertSeeText($work->work_date->format('m/d'));
        }
    }

    public function test_application_detail()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 17, 9, 0, 0));
        // 一般ユーザーと勤怠を複数作成
        $user = User::factory()->create(['role' => 'user']);
        $work = Work::factory()->create(['user_id' => $user->id]);
        // 各勤怠に対して WorkApplication を紐付けて作成
        $app = WorkApplication::factory()->create([
                'work_id' => $work->id,
                'approve_at' => NULL,
            ]);
        // 申請の一覧
        $response = $this->actingAs($user)->get('/stamp_correction_request/list?status=pending');
        // 申請の詳細
        $response = $this->actingAs($user)->get("/attendance/detail/{$app->work_id}");
        $response -> assertViewIs('auth.application');
    }
}
