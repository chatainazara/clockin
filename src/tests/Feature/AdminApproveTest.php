<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Work;
use App\Models\WorkApplication;
use Carbon\Carbon;

class AdminApproveTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_application_pending_list()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 17, 9, 0, 0));
        // 一般ユーザーと勤怠を複数作成
        $users = User::factory()->count(2)->create(['role' => 'user']);
        foreach($users as $user){
            Work::factory()
            ->count(3)
            ->sequence(
                ['work_date' => Carbon::today()],
                ['work_date' => Carbon::yesterday()],
                ['work_date' => Carbon::today()->subDays(2)],
            )
            ->create(['user_id' => $user->id]);
        }
        $works = Work::all();
        // 各勤怠に対して WorkApplication を紐付けて作成
        foreach ($works as $work) {
            WorkApplication::factory()->create([
                'work_id' => $work->id,
                'approve_at' => NULL,
            ]);
        }
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get("/stamp_correction_request/list?status=pending");
        foreach($users as $user){
            $apps = WorkApplication::with('work')->whereHas('work', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->get();
            foreach($apps as $app){
                $response->assertSeeInOrder([
                    '承認待ち',
                    $user->name,
                    Carbon::parse($app->work->work_date)->format('Y/m/d'),
                    $app->reason,
                ]);
            }
        }
    }

    public function test_application_approved_list()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 17, 9, 0, 0));
        // 一般ユーザーと勤怠を複数作成
        $users = User::factory()->count(2)->create(['role' => 'user']);
        foreach($users as $user){
            Work::factory()
            ->count(3)
            ->sequence(
                ['work_date' => Carbon::today()],
                ['work_date' => Carbon::yesterday()],
                ['work_date' => Carbon::today()->subDays(2)],
            )
            ->create(['user_id' => $user->id]);
        }
        $works = Work::all();
        // 各勤怠に対して WorkApplication を紐付けて作成
        foreach ($works as $work) {
            WorkApplication::factory()->create([
                'work_id' => $work->id,
                'approve_at' => Carbon::now(),
            ]);
        }
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get("/stamp_correction_request/list?status=approved");
        foreach($users as $user){
            $apps = WorkApplication::with('work')->whereHas('work', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })->get();
            foreach($apps as $app){
                $response->assertSeeInOrder([
                    '承認済み',
                    $user->name,
                    Carbon::parse($app->work->work_date)->format('Y/m/d'),
                    $app->reason,
                ]);
            }
        }
    }

    public function test_application_detail()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 17, 9, 0, 0));
        // 一般ユーザーと勤怠を作成
        $user = User::factory()->create(['role' => 'user']);
        $work = Work::factory()->create(['user_id' => $user->id]);
        $app = WorkApplication::factory()->create([
            'work_id' => $work->id,
            'reason' => 'test',
            'approve_at' => NULL,
        ]);
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get("/stamp_correction_request/approve/$app->id");
        $response->assertSeeInOrder([
            $user->name,
            Carbon::parse($app->work->work_date)->format('m月d日'),
            Carbon::parse($app->clock_in_at)->format('H:i'),
            Carbon::parse($app->clock_out_at)->format('H:i'),
            $app->reason,
        ]);
    }

    public function test_application_approve()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 17, 9, 0, 0));
        // 一般ユーザーと勤怠および申請を作成
        $user = User::factory()->create(['role' => 'user']);
        $work = Work::factory()->create(['user_id' => $user->id]);
        $app = WorkApplication::factory()->create([
            'work_id' => $work->id,
            'reason' => 'test',
            'approve_at' => NULL,
        ]);
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get("/stamp_correction_request/approve/$app->id");
        $response = $this->actingAs($admin)->post("/work_applications/{$app->id}/approve");
        // データベースに approve_at が入ったか確認
        $this->assertNotNull($app->fresh()->approve_at);
        // worksテーブルが更新されたことを確認
        $updatedWork = $work->fresh();
        $this->assertEquals(Carbon::parse($app->clock_in_at), $updatedWork->clock_in_at);
        $this->assertEquals(Carbon::parse($app->clock_out_at), $updatedWork->clock_out_at);
    }
}
