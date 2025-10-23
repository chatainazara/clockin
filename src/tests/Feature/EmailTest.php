<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\URL;
    use Illuminate\Support\Facades\Notification;
    use Illuminate\Auth\Notifications\VerifyEmail;

class EmailTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_verification_email_sent()
    {
        Notification::fake();
        $formData = [
            'name' => 'Test User',
            'email' => 'test'.uniqid().'@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        $this->post('/register', $formData);
        $user = User::where('email', $formData['email'])->first();
        // ユーザーが作成されていることを確認
        $this->assertNotNull($user);
        // 認証メールが送信されたことを確認
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_verification_page_show()
    {
        // Guzzle用のモックハンドラを作成、ダミーのHTTPレスポンスを返すように設定
        $mock = new MockHandler([
            new Response(200, [], '{"items":[{"Content":{"Body":"Test email body"}}]}')
        ]);
        // 処理の流れを作成
        $handlerStack = HandlerStack::create($mock);
        // クライアントを作成
        $client = new Client(['handler' => $handlerStack]);
        // ユーザー登録データ
        $formData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        // 登録
        $this->post('/register', $formData)
            ->assertRedirect('/email/verify');
        // mailhog用クライアント作成
        $client = new Client([
            'base_uri' => 'http://mailhog:8025',
            'timeout'  => 5.0,
        ]);
        try {
            // MailHog のトップページにアクセス
            $response = $client->get('/');
            // ステータスコードが 200 であることを確認
            $this->assertEquals(200, $response->getStatusCode(), 'MailHogページにアクセスできません');
            // ページ本文に MailHog の文字列が含まれているか確認
            $body = (string) $response->getBody();
            $this->assertStringContainsString('MailHog', $body, 'MailHogの画面が表示されていません');
        } catch (RequestException $e) {
            $this->fail('MailHogへのアクセスに失敗しました: ' . $e->getMessage());
        }
    }

    public function test_verification_finish()
    {
        // ユーザーを DB に作成（未認証状態）
        $user = User::factory()->create([
            'email_verified_at' => null,
            'password' => bcrypt('password'),
        ]);
        // ユーザー登録フォームを POST（実際にメールが送信される）
        $formData = [
            'name' => $user->name,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
        $this->post('/register', $formData)->assertRedirect();
        // MailHog からメールを取得して送信確認だけ
        $mailResponse = file_get_contents('http://mailhog:8025/api/v2/messages');
        $messages = json_decode($mailResponse, true);
        // テスト用に署名付き URL を生成（これで 403 を回避）
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',          // ルート名（routes/web.php にあるもの）
            now()->addMinutes(60),          // 有効期限
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );
        // 認証リンクを踏む（actingAs でログイン状態）
        $response = $this->actingAs($user)
            ->get(parse_url($verificationUrl, PHP_URL_PATH) . '?' . parse_url($verificationUrl, PHP_URL_QUERY));
        // 正しく /attendance にリダイレクトされることを確認
        $response->assertRedirect('/attendance');
    }
}
