<?php
declare(strict_types=1);

namespace Tests\Feature\Api;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class AuthWithoutMiddlewareTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    /**
     * @test
     */
    public function guard_api()
    {
        $user = factory(User::class)->create([
            'name'      => 'Mike',
            'api_token' => 'token1'
        ]);

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertStatus(200);
        $response->assertJson([
            'name' => 'Mike',
        ]);
    }
}
