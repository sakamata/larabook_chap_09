<?php
declare(strict_types=1);

namespace Tests\Unit\AddPoint;

use App\Eloquent\EloquentCustomer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentCustomerTest extends TestCase
{
    use RefreshDatabase; // <---(1)

    /**
     * @test
     */
    public function factory()
    {
        // (1) テストデータ登録
        factory(EloquentCustomer::class)->create();
        // (2) テストデータ登録（プロパティを指定）
        factory(EloquentCustomer::class)->create([
            'name' => 'Mike',
        ]);

        $this->assertTrue(true); // ダミー
    }
}
