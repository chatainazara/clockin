<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AdminLoginTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => 'adminadmin',
            'role' => 'admin',
        ]);
    }

    public function test_user_email_validation()
    {
        // ログインページを開く
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
        $response = $this->get('/no_route');
        $response->assertStatus(404);
        // メールアドレスを入力せずに他の必須項目を入力する
        $loginData = [
            'email' => '',
            'password' => 'adminadmin',
        ];
        // ボタンを押す
        $response = $this->post('/login', $loginData);
        // バリデーションを期待
        $response->assertSessionHasErrors('email');
        $this->assertEquals('メールアドレスを入力してください', session('errors')->first('email'));
    }

    public function test_user_password_validation()
    {
        // ログインページを開く
        $response = $this->get('/admin/login');
        $user = User::first();
        // パスワードを入力せずに他の必須項目を入力する
        $loginData = [
            'email' => $user->email,
            'password' => '',
        ];
        // ボタンを押す
        $response = $this->post('/login', $loginData);
        // バリデーションを期待
        $response->assertSessionHasErrors('password');
        $this->assertEquals('パスワードを入力してください', session('errors')->first('password'));
    }

        public function test_incorrect_data_validation()
    {
        // ログインページを開く
        $response = $this->get('/admin/login');

    // 登録されていないメールアドレスを入力し他の必須項目を入力する
        $user = User::first();
        $loginData = [
            'email' => 'not'.$user->email,
            'password' => 'adminadmin',
        ];
        // ボタンを押す
        $response = $this->post('/login', $loginData);
        // バリデーションを期待
        $this->assertGuest();
        $response->assertSessionHasErrors('email');
        $this->assertEquals('ログイン情報が登録されていません', session('errors')->first('email'));

    // 登録されていないパスワードを入力し他の必須項目を入力する
        $loginData = [
            'email' => $user->email,
            'password' => 'notadminadmin',
        ];
        // ボタンを押す
        $response = $this->post('/login', $loginData);
        // バリデーションを期待
        $response->assertSessionHasErrors('password');
        $this->assertEquals('ログイン情報が登録されていません', session('errors')->first('password'));
    }
}
