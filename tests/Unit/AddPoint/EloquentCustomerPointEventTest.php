<?php
declare(strict_types=1);

namespace Tests\Unit\AddPoint;

use App\Eloquent\EloquentCustomer;
use App\Eloquent\EloquentCustomerPointEvent;
use App\Model\PointEvent;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentCustomerPointEventTest extends TestCase
{
    use RefreshDatabase; // <---(1)

    /**
     * @test
     */
    public function register()
    {
        $customerId = 1;
        // (2) テストデータ登録
        // これまで出てないが customers テーブルのモデルで1レコード作成
        // name入れてないのはfactory側でFakerで自動生成される為っぽい
        factory(EloquentCustomer::class)->create([
            'id' => $customerId,
        ]);
        // テスト対象メソッドの実行
        // コンストラクタの引数（DBカラム名）をそのままの順番で入れてる
        $event = new PointEvent(
            $customerId,
            '加算イベント',
            100,
            Carbon::create(2018, 8, 4, 12, 34, 56)
        );
        // DBに登録
        $eloquet = new EloquentCustomerPointEvent();
        $eloquet->register($event);

        // (4) データベースレコードのアサーション
        // 今登録したレコードがあるかを確認
        $this->assertDatabaseHas('customer_point_events', [
            'customer_id' => $customerId,
            'event'       => $event->getEvent(),
            'point'       => $event->getPoint(),
            'created_at'  => $event->getCreatedAt(),
        ]);
    }
}
