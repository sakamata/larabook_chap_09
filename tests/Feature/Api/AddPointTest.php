<?php
declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Eloquent\EloquentCustomer;
use App\Eloquent\EloquentCustomerPoint;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddPointTest extends TestCase
{
    use RefreshDatabase; // <---(1)

    const CUSTOMER_ID = 1;

    protected function setUp()
    {
        parent::setUp();

        Carbon::setTestNow();

        // (2) テストに必要なレコードを登録
        factory(EloquentCustomer::class)->create([
            'id' => self::CUSTOMER_ID,
        ]);
        factory(EloquentCustomerPoint::class)->create([
            'customer_id' => self::CUSTOMER_ID,
            'point'       => 100,
        ]);
    }

    /**
     * @test
     */
    public function put_add_point()
    {
        // (3) API実行
        $response = $this->putJson('/api/customers/add_point', [
            'customer_id' => self::CUSTOMER_ID,
            'add_point'   => 10,
        ]);

        // (4) HTTPレスポンスアサーション
        $response->assertStatus(200);
        $expected = ['customer_point' => 110];
        $response->assertExactJson($expected);

        // (5) データベースアサーション
        $this->assertDatabaseHas('customer_points', [
            'customer_id' => self::CUSTOMER_ID,
            'point'       => 110,
        ]);
        $this->assertDatabaseHas('customer_point_events', [
            'customer_id' => self::CUSTOMER_ID,
            'event'       => 'ADD_POINT',
            'point'       => 10,
            'created_at'  => Carbon::now(),
        ]);
    }

    /**
     * @test
     */
    public function put_add_point_バリデーションエラー()
    {
        // (2) API実行
        $response = $this->putJson('/api/customers/add_point', [
        ]);

        // (3) HTTPレスポンスアサーション
        $response->assertStatus(422);
        $expected = [
            'message' => 'The given data was invalid.',
            'errors'  => [
                'customer_id' => [
                    'The customer id field is required.',
                ],
                'add_point'   => [
                    'The add point field is required.',
                ],
            ],
        ];
        $response->assertExactJson($expected);
    }

    /**
     * @test
     */
    public function put_add_point_バリデーションエラー_errorsのみ検証()
    {
        $response = $this->putJson('/api/customers/add_point', [
        ]);

        $response->assertStatus(422);

        // errorsキーのみ検証
        $expected = [
            'errors' => [
                'customer_id' => [
                    'The customer id field is required.',
                ],
                'add_point'   => [
                    'The add point field is required.',
                ],
            ],
        ];
        $response->assertJson($expected);
    }

    /**
     * @test
     */
    public function put_add_point_バリデーションエラー_キーのみ検証()
    {
        $response = $this->putJson('/api/customers/add_point', [
        ]);

        $response->assertStatus(422);

        // レスポンスボディJSONを配列に変換して検証
        $jsonValues = $response->json();

        $this->assertArrayHasKey('errors', $jsonValues);

        $errors = $jsonValues['errors'];
        $this->assertArrayHasKey('customer_id', $errors);
        $this->assertArrayHasKey('add_point', $errors);
    }

    /**
     * @test
     */
    public function put_add_point_バリデーションエラー_customer_id()
    {
        // (2) API実行
        $response = $this->putJson('/api/customers/add_point', [
            'customer_id' => 'a',
            'add_point'   => 10,
        ]);

        // (3) HTTPレスポンスアサーション
        $response->assertStatus(422);
        $expected = [
            'message' => 'The given data was invalid.',
            'errors'  => [
                'customer_id' => [
                    'The customer id must be an integer.',
                ],
            ],
        ];
        $response->assertExactJson($expected);
    }

    /**
     * @test
     */
    public function put_add_point_customer_id事前条件エラー()
    {
        // (1) API実行
        $response = $this->putJson('/api/customers/add_point', [
            'customer_id' => 999,
            'add_point'   => 10,
        ]);

        // (2) HTTPレスポンスアサーション
        $response->assertStatus(400);
        $expected = [
            'message' => 'customer_id:999 does not exists',
        ];
        $response->assertExactJson($expected);
    }

    /**
     * @test
     * @dataProvider dataProvider_put_add_point_add_point事前条件エラー
     */
    public function put_add_point_add_point事前条件エラー(int $addPoint)
    {
        // (1) API実行
        $response = $this->putJson('/api/customers/add_point', [
            'customer_id' => self::CUSTOMER_ID,
            'add_point'   => $addPoint,
        ]);

        // (2) HTTPレスポンスアサーション
        $response->assertStatus(400);
        $expected = [
            'message' => 'add_point should be equals or greater than 1',
        ];
        $response->assertExactJson($expected);
    }

    public function dataProvider_put_add_point_add_point事前条件エラー()
    {
        return [
            [0],
            [-1],
        ];
    }
}
