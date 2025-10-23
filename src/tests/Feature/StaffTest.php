<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Work;
use Carbon\Carbon;

class StaffTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use DatabaseMigrations;

    protected function setUp(): void
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 17, 9, 3, 19));
        parent::setUp();
        $users = User::factory()->count(2)->create();
        foreach($users as $user){
            Work::create([
                'user_id' => $user->id,
                'work_date' => Carbon::today(),
                'clock_in_at' => Carbon::now()->subMinutes(2),
                'clock_out_at' => Carbon::now()->subMinutes(1),
            ]);
        }
    }

    public function test_staff_list()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get("/admin/staff/list");
        $users = User::all();
        foreach($users as $user){
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
    }

    public function test_staff_detail()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $users = User::where('role','user')->get();
        foreach($users as $user){
            $work = Work::where('user_id',$user->id)->first();
            $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$user->id}");
            $response->assertSee($user->name);
            $response->assertSeeInOrder([
                Carbon::parse($work->work_date)->format('m/d'),
                Carbon::parse($work->clock_in_at)->format('H:i'),
                Carbon::parse($work->clock_out_at)->format('H:i'),
            ]);
        }
    }

    public function test_staff_detail_submonth()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 17, 9, 3, 19));
        $admin = User::factory()->create(['role' => 'admin']);
        $users = User::where('role','user')->get();
        foreach($users as $user){
            Work::create([
                'user_id' => $user->id,
                'work_date' => Carbon::today()->subMonth(),
                'clock_in_at' => Carbon::now()->subMonth()->subMinutes(4),
                'clock_out_at' => Carbon::now()->subMonth()->subMinutes(2),
            ]);
        }
        foreach($users as $user){
            $work = Work::where('user_id',$user->id)->where('work_date',Carbon::today()->subMonth())->first();
            $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$user->id}?month=".Carbon::today()->subMonth()->format('Y-m') );
            $response->assertSeeInOrder([
                Carbon::parse($work->work_date)->format('m/d'),
                Carbon::parse($work->clock_in_at)->format('H:i'),
                Carbon::parse($work->clock_out_at)->format('H:i'),
            ]);
        }
    }

    public function test_staff_detail_addmonth()
    {
        Carbon::setTestNow(Carbon::create(2025, 10, 17, 9, 3, 19));
        $admin = User::factory()->create(['role' => 'admin']);
        $users = User::where('role','user')->get();
        foreach($users as $user){
            Work::create([
                'user_id' => $user->id,
                'work_date' => Carbon::today()->addMonth(),
                'clock_in_at' => Carbon::now()->addMonth()->subMinutes(4),
                'clock_out_at' => Carbon::now()->addMonth()->subMinutes(2),
            ]);
        }
        foreach($users as $user){
            $work = Work::where('user_id',$user->id)->where('work_date',Carbon::today()->addMonth())->first();
            $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$user->id}?month=".Carbon::today()->addMonth()->format('Y-m') );
            $response->assertSeeInOrder([
                Carbon::parse($work->work_date)->format('m/d'),
                Carbon::parse($work->clock_in_at)->format('H:i'),
                Carbon::parse($work->clock_out_at)->format('H:i'),
            ]);
        }
    }

    public function test_staff_detail_detail(){
        Carbon::setTestNow(Carbon::create(2025, 10, 17, 9, 3, 19));
        $work = Work::first();
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get("/admin/attendance/{$work->id}");
        $response->assertViewIs('admin.attendance_detail');
        $now = Carbon::now()->format('Y年m月d日');
        $response->assertSee($now);
    }
}
