<?php
declare(strict_types=1);

namespace Tests\Feature\Api;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function guard_api()
    {
        factory(User::class)->create([
            'name'      => 'Mike',
            'api_token' => 'token1'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer token1'
        ])->getJson('/api/user');

        $response->assertStatus(200);
        $response->assertJson([
            'name' => 'Mike',
        ]);
    }

    /**
     * @test
     */
    public function actingAsで認証ユーザ設定()
    {
        $user = factory(User::class)->create([
            'name'      => 'Mike',
            'api_token' => 'token1'
        ]);

        // ミドルウェアを無効にして、認証ユーザを設定
        $response = $this->withoutMiddleware()
            ->actingAs($user)
            ->getJson('/api/user');

        $response->assertStatus(200);
        $response->assertJson([
            'name' => 'Mike',
        ]);
    }
}
