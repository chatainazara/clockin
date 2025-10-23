<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use App\Models\User;

class TimeTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_attendance_time_correct()
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);
        $this->actingAs($user);
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $nowTime=Carbon::now()->format('H:i');
        $response->assertSee($nowTime);
    }
}
