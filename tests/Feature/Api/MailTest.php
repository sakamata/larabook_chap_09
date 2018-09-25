<?php
declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Mail\Sample;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Testing\Fakes\MailFake;
use Tests\TestCase;

class MailTest extends TestCase
{
    /**
     * @test
     */
    public function post_send_mail()
    {
        $response = $this->postJson('/api/send-email', [
            'to' => 'a@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertExactJson(['ok']);
    }

    /**
     * @test
     */
    public function MailFakeを利用したテスト()
    {
        // (1) MailFakeでmailerを置き換える
        $fakerMailer = new MailFake();
        $this->app->singleton('mailer', function () use ($fakerMailer) {
            return $fakerMailer;
        });

        $response = $this->postJson('/api/send-email', [
            'to' => 'a@example.com',
        ]);

        $response->assertStatus(200);

        // (2) MakeFakeによるアサーション
        $fakerMailer->assertSent(Sample::class, 1);
        $fakerMailer->assertSent(Sample::class, function (Mailable $mailable) {
            return $mailable->hasTo('a@example.com');
        });
    }

    /**
     * @test
     */
    public function Mailファサードfakeを利用したテスト()
    {
        Mail::fake(); // <--- (1) MailFakeに置き換え

        $response = $this->postJson('/api/send-email-facade', [
            'to' => 'a@example.com',
        ]);

        $response->assertStatus(200);

        // (2) MailFakeを利用したアサーション
        Mail::assertSent(Sample::class, 1);
        // (3) 送信した$mailableの値を検証
        Mail::assertSent(Sample::class, function (Mailable $mailable) {
            return $mailable->hasTo('a@example.com');
        });
    }

    /**
     * @test
     */
    public function Mailファサードfakeを利用したテスト_DI()
    {
        Mail::fake(); // <--- (1) MailFakeに置き換え

        $response = $this->postJson('/api/send-email', [
            'to' => 'a@example.com',
        ]);

        $response->assertStatus(200);

        // (2) MailFakeを利用したアサーション
        Mail::assertSent(Sample::class, 1);
        // (3) 送信した$mailableの値を検証
        Mail::assertSent(Sample::class, function (Mailable $mailable) {
            return $mailable->hasTo('a@example.com');
        });
    }
}
