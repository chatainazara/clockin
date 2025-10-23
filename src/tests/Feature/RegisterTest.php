<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class RegisterTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use DatabaseMigrations;

    public function test_user_name_validation(){
        // 会員登録ページを開く
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response = $this->get('/no_route');
        $response->assertStatus(404);
        // 名前を入力せずに他の必須項目を入力する
        $user = User::factory()->make();
        $formData = [
            'name' => '',
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        // ボタンを押す
        $response = $this->post('/register', $formData);
        // バリデーションを期待
        $response->assertSessionHasErrors('name');
        $this->assertEquals('お名前を入力してください', session('errors')->first('name'));
    }

    public function test_user_email_validation()
    {
        // 会員登録ページを開く
        $response = $this->get('/register');
        // メールアドレスを入力せずに他の必須項目を入力する
        $user = User::factory()->make();
        $formData = [
            'name' => $user->name,
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        // ボタンを押す
        $response = $this->post('/register', $formData);
        // バリデーションを期待
        $response->assertSessionHasErrors('email');
        $this->assertEquals('メールアドレスを入力してください', session('errors')->first('email'));
    }

    public function test_user_password_min8_validation()
    {
        // 会員登録ページを開く
        $response = $this->get('/register');
        // パスワードを８文字以下で入力する
        $user = User::factory()->make();
        $formData = [
            'name' => $user['name'],
            'email' => $user['email'],
            'password' => 'passpas',
            'password_confirmation' => 'passpas',
        ];
        // ボタンを押す
        $response = $this->post('/register', $formData);
        // バリデーションを期待
        $response->assertSessionHasErrors('password');
        $this->assertEquals('パスワードは８文字以上で入力してください', session('errors')->first('password'));
    }

    public function test_user_password_confirm_validation()
    {
        // 会員登録ページを開く
        $response = $this->get('/register');
        // パスワードと異なる確認パスワードを入力する
        $user = User::factory()->make();
        $formData = [
            'name' => $user['name'],
            'email' => $user['email'],
            'password' => 'password',
            'password_confirmation' => 'notpassword',
        ];
        // ボタンを押す
        $response = $this->post('/register', $formData);
        // バリデーションを期待
        $response->assertSessionHasErrors('password');
        $this->assertEquals('パスワードと一致しません', session('errors')->first('password'));
    }

    public function test_user_password_empty_validation()
    {
        // 会員登録ページを開く
        $response = $this->get('/register');
        // パスワードを入力せずに他の必須項目を入力する
        $user = User::factory()->make();
        $formData = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => 'password',
        ];
        // ボタンを押す
        $response = $this->post('/register', $formData);
        // バリデーションを期待
        $response->assertSessionHasErrors('password');
            $this->assertEquals('パスワードを入力してください', session('errors')->first('password'));
    }

    public function test_user_can_register()
    {
        $formData = [
            'name' => 'TestUser',
            'email' => 'test' . uniqid() . '@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        // 登録実行
        $response = $this->post('/register', $formData);
        // リダイレクト先を確認
        $response->assertRedirect('/email/verify');
        // DB登録確認
        $this->assertDatabaseHas('users', [
            'email' => $formData['email'],
            'name'  => $formData['name'],
        ]);
        // パスワードがハッシュ化されているか確認
        $registeredUser = User::where('email', $formData['email'])->first();
        $this->assertTrue(Hash::check('password', $registeredUser->password));
    }
}
