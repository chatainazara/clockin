<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Work;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_attendance_detail()
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
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get("/admin/attendance/{$work->id}");
        $response->assertSeeInOrder([
            $user->name,
            Carbon::parse($work->work_date)->format('m月d日'),
            Carbon::parse($work->clock_in_at)->format('H:i'),
            Carbon::parse($work->clock_out_at)->format('H:i'),
        ]);
    }

    public function test_attendance_detail_clock_out_before_in()
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
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get("/admin/attendance/{$work->id}");
        $response = $this->actingAs($admin)->post("/admin/attendance/{$work->id}",[
            'work_fix' => [
                'clock_in_at' => '09:00',
                'clock_out_at' => '08:00',
                'remark' => 'テスト',
            ]
        ]);
        $response->assertSessionHasErrors([
            'work_fix.clock_in_at',
        ]);
        $this->assertEquals('出勤時間もしくは退勤時間が不適切な値です', session('errors')->first('work_fix.clock_in_at'));
    }

    public function test_attendance_detail_clock_out_before_rest_start()
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
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get("/admin/attendance/{$work->id}");
        $response = $this->actingAs($admin)->post("/admin/attendance/{$work->id}",[
            'work_fix' => [
                'clock_in_at' => '08:00',
                'clock_out_at' => '16:00',
                'remark' => 'テスト',
            ],
            'rest_fixes' => [
                [
                    'rest_start_at' => '17:00',
                    'rest_end_at' => '17:30',
                ]
            ]
        ]);
        $response->assertSessionHasErrors([
            'rest_fixes.0.rest_start_at',
        ]);
        $this->assertEquals('休憩時間が不適切な値です', session('errors')->first('rest_fixes.0.rest_start_at'));
    }

    public function test_attendance_detail_clock_out_before_rest_end()
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
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get("/admin/attendance/{$work->id}");
        $response = $this->actingAs($admin)->post("/admin/attendance/{$work->id}",[
            'work_fix' => [
                'clock_in_at' => '08:00',
                'clock_out_at' => '16:00',
                'remark' => 'テスト',
            ],
            'rest_fixes' => [
                [
                    'rest_start_at' => '15:00',
                    'rest_end_at' => '16:30',
                ]
            ]
        ]);
        $response->assertSessionHasErrors([
            'rest_fixes.0.rest_end_at',
        ]);
        $this->assertEquals('休憩時間もしくは退勤時間が不適切な値です', session('errors')->first('rest_fixes.0.rest_end_at'));
    }

    public function test_attendance_detail_remark_empty()
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
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get("/admin/attendance/{$work->id}");
        $response = $this->actingAs($admin)->post("/admin/attendance/{$work->id}",[
            'work_fix' => [
                'clock_in_at' => '08:00',
                'clock_out_at' => '16:00',
                'remark' => NULL,
            ],
        ]);
        $response->assertSessionHasErrors([
            'work_fix.remark',
        ]);
        $this->assertEquals('備考を記入してください', session('errors')->first('work_fix.remark'));
    }
}
