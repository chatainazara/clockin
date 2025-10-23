<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Work;
use Carbon\Carbon;

class AdminAttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_admin_attendance_list_check()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 17, 9, 3, 19));
        // ユーザーと勤怠作成
        $users = User::factory()->count(2)->create(['role' => 'user']);
        foreach($users as $user){
            Work::create([
                'user_id' => $user->id,
                'work_date' => Carbon::today(),
                'clock_in_at' => Carbon::now()->subMinutes(2),
                'clock_out_at' => Carbon::now()->subMinutes(1),
            ]);
        }
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get("/admin/attendance/list");
        $works = Work::all();
        foreach($works as $work){
            $user = User::find($work->user_id);
            $response->assertSeeInOrder([
                $user->name,
                Carbon::parse($work->clock_in_at)->format('H:i'),
                Carbon::parse($work->clock_out_at)->format('H:i'),
            ]);
        }
    }

    public function test_admin_attendance_list_date()
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
        $response = $this->actingAs($admin)->get("/admin/attendance/list");
        $response->assertSeeText(Carbon::parse($work->work_date)->format('Y年m月d日'));
    }

    public function test_admin_attendance_list_subday()
    {
        $now = Carbon::create(2025, 10, 17, 9, 3, 19);
        Carbon::setTestNow($now);
        // ユーザーと勤怠作成
        $user = User::factory()->create(['role' => 'user']);
        $work = Work::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->subDay(),
            'clock_in_at' => Carbon::now()->subDay()->subMinutes(2),
            'clock_out_at' => Carbon::now()->subDay()->subMinutes(1),
        ]);
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get("/admin/attendance/list/?date={$now->copy()->subDay()}");
        $response->assertSeeInOrder([
            $user->name,
            Carbon::parse($work->clock_in_at)->format('H:i'),
            Carbon::parse($work->clock_out_at)->format('H:i'),
        ]);
    }

    public function test_admin_attendance_list_addday()
    {
        $now = Carbon::create(2025, 10, 17, 9, 3, 19);
        Carbon::setTestNow($now);
        // ユーザーと勤怠作成
        $user = User::factory()->create(['role' => 'user']);
        $work = Work::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->addDay(),
            'clock_in_at' => Carbon::now()->addDay()->subMinutes(2),
            'clock_out_at' => Carbon::now()->addDay()->subMinutes(1),
        ]);
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get("/admin/attendance/list/?date={$now->copy()->addDay()}");
        $response->assertSeeInOrder([
            $user->name,
            Carbon::parse($work->clock_in_at)->format('H:i'),
            Carbon::parse($work->clock_out_at)->format('H:i'),
        ]);
    }
}
