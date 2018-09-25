<?php
declare(strict_types=1);

namespace Tests\Feature\Api;

use Tests\TestCase;

class PostTest extends TestCase
{
    /**
     * @test
     */
    public function get_with_parameters()
    {
        $response = $this->call('GET', '/api/get', [
            'class' => 'motogp',
            'no'    => '99'
        ]);

        $response->assertSuccessful();
        $expected = ['query' => 'class=motogp&no=99'];
        $response->assertJson($expected);
    }

    /**
     * @test
     */
    public function get_with_query()
    {
        $response = $this->call('GET', '/api/get?class=motogp&no=99');

        $response->assertStatus(200);
        $response->assertSuccessful();
        $expected = ['query' => 'class=motogp&no=99'];
        $response->assertJson($expected);
    }

    /**
     * @test
     */
    public function post_()
    {
        $response = $this->call('POST', '/api/post', [
            'email'    => 'a@example.com',
            'password' => 'secret-password',
        ]);

        $response->assertSuccessful();
        $expected = [
            'email'    => 'a@example.com',
            'password' => 'secret-password',
        ];
        $response->assertJson($expected);
    }
}
