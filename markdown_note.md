# PHPフレームワーク Laravel Webアプリケーション開発 輪読会資料
# Chapter 9 テスト
## テストコード実装の基礎と実践

### 目次
- 9-1　ユニットテスト
  - 9-1-1　テスト対象クラス
  - 9-1-2　テストクラスの生成
  - 9-1-3　テストメソッドの実装
  - 9-1-4　データプロバイダの活用
  - 9-1-5　例外のテスト
  - 9-1-6　テストの前処理・後処理
  - 9-1-7　テストの設定
- 9-2　データベーステスト
  - 9-2-1　テスト対象のテーブルとクラス
  - 9-2-2　データベーステストの基礎
  - 9-2-3　Eloquentクラスのテスト
  - 9-2-4　サービスクラスのテスト
  - 9-2-5　モックによるテスト（サービスクラス）
- 9-3　WebAPIテスト
  - 9-3-1　WebAPIテスト機能
  - 9-3-2　テスト対象のAPI
  - 9-3-3　APIテストの実装
  - 9-3-4　WebAPIテストに便利な機能

<br>

## 9-1　ユニットテスト

Lravelでサポートされるテスト機能   
- ユニットテスト  (モジュール単位　クラス、メソッド)   
   PHPのデファクトスタンダートの[PHPUnit]をLaravel用に拡張されたものがある
- フィーチャーテスト  (Webページ、API)   

<br>

## 余談
サンプルコードの冒頭の奴は、厳密な型チェックモードに設定している   
`declare(strict_types=1);`   を記述することによって型指定をして、間違った値が入るとエラーを出します。


### 9-1-1　テスト対象クラス
ポイント算出のメソッドを元にtestを行う   
app\Services\CalculatePointService.php   
```php
<?php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\PreConditionException;

final class CalculatePointService
{
    /**
     * @param int $amount
     * @return int
     * @throws PreConditionException
     */
    public static function calcPoint(int $amount): int
    {
        if ($amount < 0) {
            throw new PreConditionException('購入金額が負の数');
        }

        if ($amount < 1000) {
            return 0;
        }

        if ($amount < 10000) {
            $basePoint = 1;
        } else {
            $basePoint = 2;
        }

        return intval($amount / 100) * $basePoint;
    }
}
```

要約すると以下の処理をするメソッド   
ポイント算出を以下のルールで行う

| 購入金額 | ポイント |
| :-- | :-- |
| 0~999|ポイント無し |
| 1,000~9,999 | 100円につき1ポイント |
| 10,000以上 | 100円につき2ポイント |

<br>

### 9-1-2　テストクラスの生成
ユニットテストを記述するテストクラスは以下のコマンドで生成する   
```
$ php artisan make:test CalculatePointServiceTest --unit
Test created successfully.
```
以下のパスにファイルが生成される   
`tests\Unit\CalculatePointServiceTest.php`   

#### リスト9.1.2.2 testsディレクトリの構成
```
tests
├─Feature                                // フィーチャ機能テストのディレクトリ
│  └─ExampleTest.php
├─Unit                                   // ユニットテストのディレクトリ
│  ├─CalculatePointServiceTest.php      // 生成されたテストクラス(これ以外はデフォルト)
│  └─ExampleTest.php
├──CreatesApplication.php
└──TestCase.php                         // テスト基底クラス
```
テストクラスを実装する際は Tests\TestCase クラスを継承することが多いが、フレームワークの機能を使わない場合は PHPUnit\Framework\TestCase クラスを直接敬称しても問題ない。

9.1.2.3 テストクラスのクラス図
@startuml
    title テストクラスのクラス図
    PHPUnit\Framwork\TestCase <|-- Test\TestCase
    Test\TestCase <|-- Tests\Unit\YouTest
@enduml

生成されたtestClassには以下の様な test... で始まるメソッドが作られる   
test... から始まるメソッドがテストメソッドとして実行される。   
9.1.2.4　抜粋　生成された CalculatePointServiceTest クラス
```php
    public function testExample()
    {
        $this->assertTrue(true);
    }
```

別の方法でコメントに @test アノテーションを付ける方法もある。作者はこっちをお勧めしてる   
参考: [アノテーションについて](https://phpunit.readthedocs.io/ja/latest/annotations.html)   
9.1.2.5 @testアノテーション
```php
    /**
    * @test
    */
    public function Example()
    {
        $this->assertTrue(true);
    }
```
さらにアノテーションと、メソッド名を日本語にすると（え!?） test結果の判別が付けやすくなる
```php
    /**
    * @test
    */
    public function divide_除数がゼロなら例外を投げる()
    {
        // (略)
    }
```

9.1.2.6 テストの実行例   
phpunitコマンドに続けてテストクラスファイルを指定する
```
$ ./vendor/bin/phpunit tests/Unit/CalculatePointServiceTest.php
PHPUnit 6.5.9 by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: 1.94 seconds, Memory: 10.00MB

OK (1 test, 1 assertion)
```

9.1.2.8 テストの失敗例
```
vagrant@homestead:~/larabook/chapter09$ ./vendor/bin/phpunit tests/Unit/CalculatePointServiceTest.php
PHPUnit 6.5.9 by Sebastian Bergmann and contributors.

F                                                                   1 / 1 (100%)

Time: 1.04 seconds, Memory: 10.00MB

There was 1 failure:

1) Tests\Unit\CalculatePointServiceTest::Example
Failed asserting that false is true.

/home/vagrant/larabook/chapter09/tests/Unit/CalculatePointServiceTest.php:19

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.
```
コマンドの際 phpunit コマンドの引数を省略すると 全テストが実行される。よく使うので覚えておくと良い   

9.1.2.9　全てのテストの実行例
```
$ ./vendor/bin/phpunit
```

<br>

### 9-1-3　テストメソッドの実装
ポイント付与のサンプルコードを元にテストメソッドを実装する   
ここでは、ポイント算出ルールの境界値に沿ってテストを記述する   
まず、購入金額が0円のパターンを検査する   
0円の場合ポイントが0になるか?

9.1.3.2 主なアサーションメソッド(抜粋)   
| メソッド | 内容 |
| :-- | :-- |
| assertSame | 型も含めて期待値と値が一致するかを検証 |
| assertTrue | 値がtrueかどうかを検証 |
| assertReqExp | 値が正規表現にマッチするかどうかを検証 |
| assertArrayHasKey | 値が配列の場合、指定したキーが存在するかを検証 |


`tests\Unit\CalculatePointServiceTest.php`に以下のメソッドを追加
```php
    /**
     * @test
     */
    public function calcPoint_購入金額が0ならポイントは0()
    {
        $result = CalculatePointService::calcPoint(0);
        $this->assertSame(0, $result); // $result が0である事を検証
    }

```

テストを実行すると2つのtestが通る事を確認できる
```
vagrant@homestead:~/larabook/chapter09$ ./vendor/bin/phpunit tests/Unit/CalculatePointServiceTest
PHPUnit 6.5.9 by Sebastian Bergmann and contributors.

..                                                                  2 / 2 (100%)

Time: 4.43 seconds, Memory: 10.00MB

OK (2 tests, 2 assertions)
```

同様に今度は購入金額1000円でのテストを書く
```php
    /**
     * @test
     */
    public function calcPoint_購入金額が1000ならポイントは10()
    {
        $result = CalculatePointService::calcPoint(1000);
        $this->assertSame(10, $result); // $result が10である事を検証
    }
```
同様にテストが3つ通る事を検証
```
vagrant@homestead:~/larabook/chapter09$ ./vendor/bin/phpunit tests/Unit/CalculatePointServiceTest
PHPUnit 6.5.9 by Sebastian Bergmann and contributors.

...                                                                 3 / 3 (100%)

Time: 1.03 seconds, Memory: 10.00MB

OK (3 tests, 3 assertions)
```

こんな感じでテスト項目を増やすって事らしい

<br>

### 9-1-4　データプロバイダの活用

テストメソッドは引数と戻り値の組み合わせのみだが、データプロバイダは同じ処理に対して、異なるパラメータや引数を渡してテストする事ができて便利。   
テストメソッドに渡すパラメータを指定するメソッドを用意する   
データプロバイダメソッドは `public` にする必要がある   

9.1.4.1 データプロバイダメソッドの例
```php
    public function dataProvider_for_calcPoint(): array
    {
        return [
            '購入金額が0なら0ポイント'       => [0, 0],
            '購入金額が999なら0ポイント'     => [0, 999],
            '購入金額が1000なら10ポイント'   => [10, 1000],
        ];
    }
```

データプロバイダを利用するには、テストメソッドに `@dataProvider`アノテーションを指定する。

9.1.4.2 データプロバイダを利用したテストメソッド
```php
    /**
     * @test
     * @dataProvider dataProvider_for_calcPoint
     */
    public function calcPoint(int $expected, int $amount)
    {
        $result = CalculatePointService::calcPoint($amount);
        $this->assertSame($expected, $result); 
    }
```

仮に以下の値をあえて間違った上でテストを実行してみる
`'購入金額が1000なら10ポイント'   => [0, 1000],`

```
vagrant@homestead:~/larabook/chapter09$ ./vendor/bin/phpunit tests/Unit/CalculatePointServiceTest
PHPUnit 6.5.9 by Sebastian Bergmann and contributors.

.....F                                                              6 / 6 (100%)

Time: 1.34 seconds, Memory: 10.00MB

There was 1 failure:

1) Tests\Unit\CalculatePointServiceTest::calcPoint with data set "購入金額が1000なら10ポイント" (0, 1000)
Failed asserting that 10 is identical to 0.

/home/vagrant/larabook/chapter09/tests/Unit/CalculatePointServiceTest.php:59

FAILURES!
Tests: 6, Assertions: 6, Failures: 1.
```
1エラーとなりエラーとなった配列のキー（日本語）と、配列の組み合わせ（0,1000）が出力されるので、エラー詳細が明快になる。   

より多くのテストパターンを実装、数値の境界部分の値を追加している   
9.1.4.6 データプロバイダメソッドに要素を追加
```php
    public function dataProvider_for_calcPoint() : array
    {
        return [
            '購入金額が0なら0ポイント' => [0, 0],
            '購入金額が999なら0ポイント' => [0, 999],
            '購入金額が1000なら10ポイント' => [10, 1000],
            '購入金額が9999なら99ポイント' => [99, 9999],
            '購入金額が10000なら200ポイント' => [200, 10000],
        ];
    }
```
こんな感じでデータプロバイダメソッドでは複数の値、パラメータでテストが行える。   

<br>

### 9-1-5　例外のテスト

throw で投げられた例外処理をテストするには、以下の利用方法がある

- try/catch の利用
- expectException メソッド
- @expectedExcepsion アノテーション
作者が薦めるのは3つめのアノテーション

- 例外がスローされるか？   
- スローされた例外が意図したものであるか？   
を検証する

以下3つの例はこのコードのみで完結する自己循環型のテスト例で、`CalculatePointService`をテストしている訳では無い   

#### try/catchの利用
通常のPHPコードと同様にテスト対象を tryで囲む奴を使う
```php
    /**
     * @test
     */
    public function exception_try_catch()
    {
        try {
            throw new \InvalidArgumentException('message', 200);
            $this->fail(); // （1）例外がスローされない時はテストを失敗させる
        } catch (\Throwable $e) {
            // 指定した例外クラスがスローされているか
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            // スローされた例外のコードを検証
            $this->assertSame(200, $e->getCode());
            // スローされた例外のメッセージを検証
            $this->assertSame('message', $e->getMessage());
        }
    }
```
`InvalidArgumentException`  引数の型が期待する型と一致しなかった場合にスローされる例外。   

#### expectException メソッドの利用

```php
    /**
     * @test
     */
    public function exception_expectedException_method()
    {
        // 指定した例外クラスがスローされているか
        $this->expectException(\InvalidArgumentException::class);
        // スローされた例外のコードを検証
        $this->expectExceptionCode(200);
        // スローされた例外のメッセージを検証
        $this->expectExceptionMessage('message');

        throw new \InvalidArgumentException('message', 200);
    }
```


#### @expectedExcepsion アノテーションの利用

作者押しの例外テスト方法   
アノテーション（コメント）部分に必要事項を書いてしまう

```php
    /**
     * @test
     * @expectedException \InvalidArgumentException
     * @expectedExceptionCode 200
     * @expectedExceptionMessage message
     */
    public function exception_expectedException_annotation()
    {
        throw new \InvalidArgumentException('message', 200);
    }
```

9.1.5.4 購入金額が負数の場合のテスト

テスト例となる `CalculatePointService` classのメソッドに以下の様な throw 処理がある、これをテストする   

```php
        if ($amount < 0) {
            throw new PreConditionException('購入金額が負の数');
        }
```

例外テストの具体例
```php
    /**
     * @test
     * @expectedException \App\Exceptions\PreConditionException
     * @expectedExceptionMessage 購入金額が負の数
     */
    public function calcPoint_購入金額が負の数なら例外をスロー()
    {
        CalculatePointService::calcPoint(-1);
    }
```
マイナスの値が`calcPoint`に投げられた際を検証するために
アノテーションで ’@expectedException'で使用される例外処理先`\App\Exceptions\PreConditionException`を指定する
ちなみにこのテストでは値の検証のみなので`@expectedExceptionMessage`の行は無くても良さげ

<br>

### 9-1-6　テストの前処理・後処理
テスト前にDBに必要なの値の仕込みや、テスト後に値を削除変更する必要がある場合、テストメソッドに書くと煩雑になるので、別途専用にメソッドが用意されている。`PHPUnit\Framework\TestClass`にあるテンプレートメソッド。   
- 前処理   setUp メソッド   
- テスト中 testメソッド 
- 後処理   tearDown メソッド
の順で呼ばれて処理される   

また、テストクラス毎に呼ばれる以下もある
- setUpBeforeClass メソッド
- tearDownAfterClass メソッド
これらはテストメソッドが属するテストクラス毎に1回だけ呼ばれる   

9.1.6.1 テンプレートメソッドの動きを見るテスト
どんな順番で処理が実行されるかを確かめるメソッド群
```php
<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CalculatePointService;
use Tests\TestCase;

class TemplateMethodTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        echo __METHOD__, PHP_EOL;
    }

    protected function setUp()
    {
        parent::setUp();

        echo __METHOD__, PHP_EOL;
    }

    /**
     * @test
     */
    public function テストメソッド1()
    {
        echo __METHOD__, PHP_EOL;
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function テストメソッド2()
    {
        echo __METHOD__, PHP_EOL;
        $this->assertTrue(true);
    }

    protected function tearDown()
    {
        parent::tearDown();

        echo __METHOD__, PHP_EOL;
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        echo __METHOD__, PHP_EOL;
    }
}
```
上記のテストを実行すると以下の様な順序でテストが行われる事が確認できる
```
vagrant@homestead:~/larabook/chapter09$ ./vendor/bin/phpunit tests/Unit/TemplateMethodTest.php
PHPUnit 6.5.9 by Sebastian Bergmann and contributors.

Tests\Unit\TemplateMethodTest::setUpBeforeClass
.Tests\Unit\TemplateMethodTest::setUp
Tests\Unit\TemplateMethodTest::テストメソッド1
Tests\Unit\TemplateMethodTest::tearDown
.                                                                  2 / 2 (100%)Tests\Unit\TemplateMethodTest::setUp
Tests\Unit\TemplateMethodTest::テストメソッド2
Tests\Unit\TemplateMethodTest::tearDown
Tests\Unit\TemplateMethodTest::tearDownAfterClass


Time: 1.1 seconds, Memory: 10.00MB

OK (2 tests, 2 assertions)
```
テンプレートメソッドを使う際は`parent`で継承元メソッドを呼ぶことを忘れないようにすること   
`parent::setUp()`

<br>

### 9-1-7　テストの設定
Laravelのルートディレクトリにあう`phpunit.xml`ファイルで以下の設定項目の編集が可能   
- PHPUnitに関する設定の変更が可能
- また、テストディレクトリやテスト対象ファイルの追加や変更を設定可能
- テスト実行時にPHPの設定を変更する
- 環境変数によってアプリケーション設定を変更する

詳細な設定に関しては [PHPUnit](https://phpunit.readthedocs.io/ja/latest/) の公式マニュアルを参照のこと   

<br>

## 9-2　データベーステスト
**データベースを利用したテストコードの実装**

DBを利用するテストでは以下の様な手間がかかる作業が多い
- テスト用DBの設定
- テスト用レコードの登録
- 対象クラスの処理後のレコード検証

本節はこれらを解説する

<br>

### 9-2-1　テスト対象のテーブルとクラス

本節で使うDBを確認する。会員のポイントを加算する処理を例に解説する   

#### テーブル構成

9.2.1.1 テーブル構成を示すER図

@startuml
    entity "customers" {
        +id[PK]
        --
        name
        created_at
        updated_at
    }
    entity "customer_points" {
        +customer_id[PK]
        --
        point (保有ポイント int)
        created_at
        updated_at
    }
    entity "customer_point_events" {
        +id[PK]
        --
        customer_id[FK]
        event (発生イベント名 var)
        point (変化ポイント int)
        created_at
    }
    customers -- customer_points
    customers --o{ customer_point_events
@enduml

#### 処理シナリオ
- customer_point_events テーブルで加算イベント追加
- customer_points テーブルが保持するポイントを加算
- 1. 2. を同一トランザクションで実施
- 処理失敗の際はロールバック

#### 実装クラス
9.2.1.5 ポイント加算処理のクラス構成

@startuml
    AddPointSErvice ..> PointEvent
    AddPointSErvice ..> EloquentCustomerPointEvent
    AddPointSErvice ..> EloquentCustomerPoint
    EloquentCustomerPointEvent ..> PointEvent

    AddPointSErvice : add(PointEvent $pointEvent)
    EloquentCustomerPointEvent : register(PointEvent $e)
    EloquentCustomerPoint : addPoint(int $customerid, int $addPoint)
@enduml

以下、各classのコードの記述があるが省略、詳しくは書籍、もしくはリンク先GitHubのコードを参照の事   

[app\Services\AddPointService.php](https://github.com/laravel-socym/chapter09/blob/master/app/Services/AddPointService.php)  
add メソッドで、複数のtableにtransaction付きでデータを書き込む複数メソッドの実行が記述されている   

[app\Eloquent\EloquentCustomerPointEvent.php](https://github.com/laravel-socym/chapter09/blob/master/app/Eloquent/EloquentCustomerPointEvent.php)   
`customer_point_events`tableと関連付けし、registerメソッドで各カラム(cuntomer_id, event, point, created_at)にデータをsaveしている   

[app\Eloquent\EloquentCustomerPoint.php](https://github.com/laravel-socym/chapter09/blob/master/app/Eloquent/EloquentCustomerPointEvent.php)   
addPointメソッドで`customer_point`テーブルの該当 customer_id のpointを更新している   

[app\Model\PointEvent.php](https://github.com/laravel-socym/chapter09/blob/master/app/Model/PointEvent.php)   
処理に必要な個別の項目の定義（DBカラム）と、呼び出しをしている   

<br>

### 9-2-2　データベーステストの基礎
テスト実行前にDBの状態を整える必要がある。テストに影響を与える環境を常に同じ状態に整えてからテストを実行することが重要。   

#### テスト用データベースを利用
テスト用のDBを用意する
9.2.2.1 テスト用DBの作成例（MySQL）

```
$ mysqladmin create app_test
# これでcreate database できるの知らんかった…
```

テスト時はこちらを使うようphpunit.xmlで設定する。

9.2.2.2 テスト用DBの設定(phpunit.xml 抜粋)
```xml
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="QUEUE_DRIVER" value="sync"/>
        <!-- ここでテスト用データベースを指定 -->
        <env name="DB_DATABASE" value="app_test"/>
        <env name="MAIL_DRIVER" value="log"/>
    </php>
```

<br>

#### テスト用トレイトの利用

**RefreshDatabaseトレイトについて**   
テスト実行時に自動でマイグレーションを行わせるには、RefreshDatabaseトレイトを使う。使用には以下の様に use させる   
```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class EloquentCustomerPointEventTest extends TestCase
{
 use RefreshDatabase;  // 自動でマイグレーション実行
 // （省略）
}
```
現在のDBをテスト開始時に自動で migrate:refresh してくれる。   
自動で（入れ子の）トランザクションがされるので、テスト時の内容は全てロールバックされ元に戻る   

他にもDB制御するテスト用トレイトはある。詳細は書籍を見てください。   

<br>

#### Factoryでテスト用レコードの準備
テストに必要なレコードを登録するにはFactoryが便利   
利用は以下のコマンド make:factory を使用する   
引数は生成するFactory名を指定   
命名ルールは [Eloquentクラス名 + Factory] にすると良い。

9.2.2.4 make:factoryコマンドの実行例
```
$ php artisan make:factory EloquentCustomerFactory
Factory created successfully.
```
上記コマンドで以下にファイルが生成される   
`database\factories\EloquentCustomerFactory.php`

9.2.2.5 生成されたEloquentCustomerFactory   
```php
<?php

use Faker\Generator as Faker;

$factory->define(Model::class, function (Faker $faker) {
    return [
        //
    ];
});
```

9.2.2.6 EloquentCustomer用に変更したCuntomerFactory
```php
<?php
declare(strict_types=1);
// use で該当のEroquentクラスを呼ぶ
use App\Eloquent\EloquentCustomer;

// 5章.データベース 5-2-4で使用したFaker(ダミーデータライブラリ)を使用   
use Faker\Generator as Faker;

// define の第一引数に対象のEloquentクラス名を指定
$factory->define(EloquentCustomer::class, function (Faker $faker) {
    return [
        // Eroquantに設定するプロパティを連想配列で指定,Fakerでそれっぽい人名が入る
        'name' => $faker->name,
    ];
});
```

9.2.2.7 factory関数の利用例
```php
// cuntomerテーブルに1レコード登録
factory(EloquentCustomer::class)->create();

// cuntomerテーブルに1レコード登録（nameを指定）
factory(EloquentCustomer::class)->create([
    'name' => '太郎',
]);

// cuntomerテーブルに3レコード登録
factory(EloquentCustomer::class, 3)->create();
```

<br>

#### データベースのアサーション
テスト中に変更したDBのレコード検証には、DBのアサーションメソッドを利用すると良い。   
以下は実行例、使い方を見たまんまで書いてある   

9.2.2.8 データベースのアサーションメソッド例
```php
// customersテーブルにid=1のレコードが存在すれば成功
$this->assertDatabaseHas('customers', [
    'id' => 1,
]);

// customersテーブルにid=100のレコードが存在しなければ成功
$this->assertDatabaseHasMissing('customers', [
    'id' => 100,
]);
```

上記の様なレコードの有無で検証できないケース、例えばレコード数を測りたい場合はEloquentやクエリビルダを利用して検証する   

9.2.2.9 クエリビルダを利用したアサーション例
```php
//customerテーブルに5件のレコードがあれば成功
$this->assertSame(5, \DB::table('customers')->count());
```

<br>

### 9-2-3　Eloquentクラスのテスト
では実際にDBのテストを行って行く   
EloquentCustomerPointEventクラスのテストを行う。   
[app\Eloquent\EloquentCustomerPointEvent.php](https://github.com/laravel-socym/chapter09/blob/master/app/Eloquent/EloquentCustomerPointEvent.php)   
(`customer_point_events`tableと関連付けし、registerメソッドで各カラム(cuntomer_id, event, point, created_at)にデータをsaveしている)   

テスト対象は registerメソッド   
テストの際の事前条件と事後条件を想定する
- 事前条件: customersテーブルに対象レコードがある。
- 事後条件: customer_point_eventsにレコードが追加されている。

以下は実装したテストクラス

9.2.3.1 EloquentCustomerPointEventTestクラス（書籍は`EloquentCustomerPointTest`とあり校正ミス）
```php
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
        // これまで出てないが customers テーブルのモデル
        // name入れてないのはP405 9.2.2.6 factory側でFakerで指定されてる為っぽい
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
```

本には書いてないがテストを実施してみる
```
vagrant@homestead:~/larabook/chapter09$ ./vendor/bin/phpunit tests/Unit/AddPoint/EloquentCustomerPointEventTest.php
PHPUnit 6.5.9 by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: 8.45 seconds, Memory: 16.00MB

OK (1 test, 1 assertion)
```
8秒以上かかった。初回はテスト用のDB作成からmigrateも走るので無理もないかも。   

<br>

次にEloquentCustomerPointクラスのテストの実装

[app\Eloquent\EloquentCustomerPoint.php](https://github.com/laravel-socym/chapter09/blob/master/app/Eloquent/EloquentCustomerPointEvent.php)   
addPointメソッドで`customer_point`テーブルの該当 customer_id のpointを更新している   

addPointメソッドは下記の事前条件と事後条件を想定している
- 事前条件1: customersテーブルに対象のレコードがある
- 事前条件2: customer_pointテーブルに対象のレコードがある
- 事後条件: customer_pointテーブルの対象レコードにpointが加算されている

これも一つ前のテストとほとんど同じ構成となっている。違いとしては addPointで元の 100ポイントから、10ポイント加算で110ポイントになっているかをテストしている。   

9.2.3.2 EloquentCustomerPointTestクラス
```php
<?php
declare(strict_types=1);

namespace Tests\Unit\AddPoint;

use App\Eloquent\EloquentCustomer;
use App\Eloquent\EloquentCustomerPoint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EloquentCustomerPointTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function addPoint()
    {
        // (1) テストに必要なレコードを登録
        $customerId = 1;
        factory(EloquentCustomer::class)->create([
            'id' => $customerId,
        ]);
        factory(EloquentCustomerPoint::class)->create([
            'customer_id' => $customerId,
            'point'       => 100,
        ]);

        // (2) テスト対象メソッドの実行
        $eloquent = new EloquentCustomerPoint();
        $result = $eloquent->addPoint($customerId, 10);

        // (3) テスト結果のアサーション
        $this->assertTrue($result);

        $this->assertDatabaseHas('customer_points', [
            'customer_id' => $customerId,
            'point'       => 110,
        ]);
    }
}
```

同様にテストを実施してみた。今度は2秒程度。テスト用のtableが出来た状態からのテストなので早かった。      
```
vagrant@homestead:~/larabook/chapter09$ ./vendor/bin/phpunit tests/Unit/AddPoint/EloquentCustomerPointTest.php
PHPUnit 6.5.9 by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: 2.38 seconds, Memory: 16.00MB

OK (1 test, 2 assertions)
```

このようにDBを利用するEloquentクラスのテストもFactoryやデータベースアサーションを利用することで手軽なテストができる。   

<br>

### 9-2-4　サービスクラスのテスト

AddPointServiceクラスのテスト方法について   

[app\Services\AddPointService.php](https://github.com/laravel-socym/chapter09/blob/master/app/Services/AddPointService.php)  
add メソッドで、複数のtableにtransaction付きでデータを書き込む複数メソッドの実行が記述されている   

addメソッドの事前条件と事後条件の想定は以下の通り   

- 事前条件1: customersテーブルに対象レコードがある
- 事前条件2: customer_pointテーブルに対象レコードがある
- 事後条件1: customer_point_eventsテーブルに対象レコードがある
- 事後条件2: customer_pointsテーブルの対象レコードにpointが加算されている

9.2.4.1 AddPointServiceTestクラス
```php
<?php
declare(strict_types=1);

namespace Tests\Unit\AddPoint;

use App\Eloquent\EloquentCustomer;
use App\Eloquent\EloquentCustomerPoint;
use App\Model\PointEvent;
use App\Services\AddPointService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AddPointServiceTest extends TestCase
{
    use RefreshDatabase;

    const CUSTOMER_ID = 1;

    protected function setUp()
    {
        parent::setUp();

        // (1) テストに必要なレコードを登録
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
     * @throws \Throwable
     */
    public function add()
    {
        // (2) テスト対象メソッドの実行
        $event = new PointEvent(
            self::CUSTOMER_ID,
            '加算イベント',
            10,
            Carbon::create(2018, 8, 4, 12, 34, 56)
        );
        /** @var AddPointService $service */
        $service = app()->make(AddPointService::class);
        $service->add($event);

        // (3) テスト結果のアサーション
        $this->assertDatabaseHas('customer_point_events', [
            'customer_id' => self::CUSTOMER_ID,
            'event'       => $event->getEvent(),
            'point'       => $event->getPoint(),
            'created_at'  => $event->getCreatedAt(),
        ]);
        $this->assertDatabaseHas('customer_points', [
            'customer_id' => self::CUSTOMER_ID,
            'point'       => 110,
        ]);
    }
}
```

テンプレートメソッドを使う際は`parent`で継承元メソッドを呼ぶことを忘れないようにする
`parent::setUp()`

テストメソッド内に前提条件を書いても良いが、不明瞭になるので、setUpメソッドに事前条件を書くのが望ましい。

ここで、サービスクラスのインスタンス化をしてる?（この書き方良くわかってない）
```php
/** @var AddPointService $service */
$service = app()->make(AddPointService::class);
```

テスト実行結果
```
vagrant@homestead:~/larabook/chapter09$ ./vendor/bin/phpunit tests/Unit/AddPointServiceTest.php
PHPUnit 6.5.9 by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: 2.37 seconds, Memory: 16.00MB

OK (1 test, 2 assertions)
```

<br>

### 9-2-5　モックによるテスト（サービスクラス）

一つ前の 9-2-4 と別で、Eloquentクラスをモックにして**DBにアクセスせずに**、サービスクラスの処理のみをテストする方法もある。

無名クラスを使って、テスト対象のクラスが読み込む子供のクラスをその場で使い捨て利用する感じ。   

9.5.5.1 モックを利用したAddPointSErviceクラスのテスト
```php
<?php
declare(strict_types=1);

namespace Tests\Unit;

use App\Eloquent\EloquentCustomerPoint;
use App\Eloquent\EloquentCustomerPointEvent;
use App\Model\PointEvent;
use App\Services\AddPointService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AddPointServiceWithMockTest extends TestCase
{
    use RefreshDatabase;

    private $customerPointEventMock;
    private $customerPointMock;

    protected function setUp()
    {
        parent::setUp();

        // (1) Eloquentクラスのモック化
        $this->customerPointEventMock = new class extends EloquentCustomerPointEvent
        {
            /** @var PointEvent */
            public $pointEvent;

            public function register(PointEvent $event)
            {
                $this->pointEvent = $event;
            }
        };

        $this->customerPointMock = new class extends EloquentCustomerPoint
        {
            /** @var int */
            public $customerId;

            /** @var int */
            public $point;

            public function addPoint(int $customerId, int $point): bool
            {
                $this->customerId = $customerId;
                $this->point = $point;

                return true;
            }
        };
    }

    /**
     * @test
     * @throws \Throwable
     */
    public function add()
    {
        // (2) テスト対象メソッドの実行
        $customerId = 1;
        $event = new PointEvent(
            $customerId,
            '加算イベント',
            10,
            Carbon::create(2018, 8, 4, 12, 34, 56)
        );
        $service = new AddPointService(
            $this->customerPointEventMock,
            $this->customerPointMock
        );
        $service->add($event);

        // (3) テスト結果のアサーション
        $this->assertEquals($event, $this->customerPointEventMock->pointEvent);
        $this->assertSame($customerId, $this->customerPointMock->customerId);
        $this->assertSame(10, $this->customerPointMock->point);
    }
}
```

**個人的な解釈**   
テスト対象の`AddPointService.php`の一部はこうなっている
```php
    public function __construct(
        // 2人の子供を起こして使える様に設計されているので...
        EloquentCustomerPointEvent $eloquentCustomerPointEvent,
        EloquentCustomerPoint $eloquentCustomerPoint
    ) {
        $this->eloquentCustomerPointEvent = $eloquentCustomerPointEvent;
        $this->eloquentCustomerPoint = $eloquentCustomerPoint;
        $this->db = $eloquentCustomerPointEvent->getConnection();
    }
```

テストコードは**無名クラス**としてDBにアクセスする`EloquentCustomerPointEvent`と`EloquentCustomerPoint`を`setUp()`メソッド内でnewして privateプロパティにしている。   

`AddPointServiceWithMockTest.php`抜粋
```php
private $customerPointEventMock;
private $customerPointMock;

protected function setUp()
{
        // 省略
    $this->customerPointEventMock = new class extends EloquentCustomerPointEvent
    {
        // 省略
    }

    $this->customerPointMock = new class extends EloquentCustomerPoint
    {
        // 省略
    }
}
```

これを`add()`メソッド内で、`AddPointService`をnewさせる際に、コンストラクタインジェクションの引数に読み込んで利用している。   
`AddPointServiceWithMockTest.php`抜粋
```php
        $service = new AddPointService(
            $this->customerPointEventMock,
            $this->customerPointMock
        );
```

なんとか理解はできたが、自分で書いてサクサク利用できるようになるには、時間がかかりそう。   

<br>

## 9-3　WebAPIテスト
**WebAPIを検証するテストコードの実装**

ここではよりユニットテストより粒度の高い大きいフィーチャーテストの解説となる   
LaravelではHTTPリクエストをシミュレートしてテストする機能が用意されている、この機能を利用した例の説明となる。   

<br>

### 9-3-1　WebAPIテスト機能

まず下記にあるような単純なAPIを扱う   
`routes\api.php`抜粋
```php
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});
```
`/api/ping` にアクセスすると json [message => 'pong'] が得られるごく単純なAPIとなっている
```
$ curl  http://larabook.test/api/ping
{"message":"pong"}
```

<br>

#### テストクラスの生成

テストクラスの生成は `artisan make:test`コマンドを使う。コマンドにクラス名指定で、 `test/Feature/` 以下にテストクラスファイルが生成される。 `--unit` オプションは使わないので注意

9.3.1.3 フィーチャーテストクラスの生成例
```
$ php artisan make:test Api/PingTest
Test created successfully.
```

生成されたファイル `tests\Feature\Api\PingTest.php`コマンドでパスを切った場合はディレクトリもよろしく作られる
9.3.1.4 生成されたフィーチャーテストクラスの名前空間
```php
<?php
// 名前空間の生成もフィーチャーテストとしてよろしくやってくれる
namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

// 省略
```

<br>

#### HTTPリクエストの送信

HTTPリクエストのテスト実施は、疑似的なHTTPリクエストを送信するメソッドを利用する。  `call`メソッドはHTTPリクエストを疑似的に送信してくれる。

9.3.1.5 callメソッドの定義
```php
public function call {
                        // 以下使用の際の引数の順序となっている?
    $method,            // HTTPメソッド
    $uri,               // URI
    $parameters = [],   // 送信パラメータ
    $cookies = [],      // cookie
    $files = [],        // アップロードファイル
    $server = [],       // サーバパラメータ
    $content = null     // RAWリクエストボディ
}
```

9.3.1.6 callメソッドの実行例
```php
// GETリクエスト クエリストリング
$response = $this->call('GET', '/api/get?class=motogp&no=99');

// GETリクエスト $parameters
$response = $this->call('GET', '/api/get', [
    'class' => 'motogp',
    'no'    => '99'
]);

// POSTリクエスト
$response = $this->call('POST', '/api/post', [
    'email'    => 'a@example.com',
    'password' => 'secret-password',
]);
```

また、jsonの場合は以下の定義となる

9.3.1.7 jsonメソッドの定義
```php
public function json($method, $uri, array $data = [], array $headers = [])
```
第3引数のリクエストヘッダは自動でも設定される   

call,jsonメソッドにはHTTPメソッド毎のラッパー関数が用意されている。
通常はラッパーメソッドを利用し、細かなパラメーター指定が必要な場合にcall,jsonメソッドを利用すると良い。   

| メソッド | 送信する疑似HTTPリクエスト |
| :-- | :-- |
| get(\$uri, /$headers = []) | GETリクエスト |
| getJson(\$uri, \$headers = []) | GETリクエスト(JSON) |
| post(\$uri, \$data, \$headers = []) | POSTリクエスト |
| postJson(\$uri, \$data, \$headers = []) | POSTリクエスト(JSON) |
| put(\$uri, \$data, \$headers = []) | PUTリクエスト |
| putJson(\$uri, \$data, \$headers = []) | PUTリクエスト(JSON) |
| patch(\$uri, \$data, \$headers = []) | PATCHリクエスト |
| patchJson(\$uri, \$data, \$headers = []) | PATCHリクエスト(JSON) |
| delete(\$uri, \$data, \$headers = []) | DELETEリクエスト |
| deleteJson(\$uri, \$data, \$headers = []) | DELETEリクエスト(JSON) |

<br>

#### HTTPレスポンスのアサーション

送信したHTTPリクエストの結果検証をするアサーションメソッドがある。
Tests\TestCaseクラスにもあるが、
Illuminate\Foundation\Testing\TestResponceクラスの方が特化したアサーションの記述が容易なので作者はこちらを薦めている。

Illuminate\Foundation\Testing\TestResponceクラスの主なアサーションメソッドは以下
9.3.1.10 レスポンスをアサーションする主なメソッド(抜粋)   

| メソッド | 内容 |
| :-- | :-- |
| assertStatus(\$status) | HTTPステータスコードが引数と一致していれば成功 |
| assertSuccessful() | HTTPステータスコードが2XXなら成功 |
| assertRedirect(\$url = null) | 次のいずれか 201,301,302,303,307,308 かつ、ヘッダのuriの値が app('uri)->to(\$uri) の値と一致すれば成功 |
| assertHeader(\$headerName, \$value = null) | レスポンスヘッダが存在(\$valueがnullの場合）もしくは該当ヘッダの値が$valueと一致すれば成功) |
| assertExactJson(array \$data, \$strict = false) | レスポンスボディのJSONをデコードした配列が$dataと一致すれば成功 |
| assertJson(array \$data, \$strict = false) | レスポンスボディのJSONをデコードした配列に$dataが含まれていれば成功 |

以上を反映した`/api/ping`のAPIテストの例は以下の通り   

9.3.1.11 ping APIのテストクラス
```php
<?php
declare(strict_types=1);

namespace Tests\Feature\Api;

use Tests\TestCase;

class PingTest extends TestCase
{
    /**
     * @test
     */
    public function get_ping()
    {
        $response = $this->get('/api/ping');
        // HTTPステータスコードを検証
        $response->assertStatus(200);
        // レスポンスボディのJSONを検証
        $response->assertExactJson(['message' => 'pong']);
    }
}
```

テストしてみる
```
$ ./vendor/bin/phpunit tests/Feature/Api/PingTest.php
PHPUnit 6.5.9 by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: 1.26 seconds, Memory: 12.00MB

OK (1 test, 2 assertions)
```

このようにWebAPIのテストでは疑似的なリクエスト送信をしてレスポンスが検証可能となっている。   

<br>

### 9-3-2　テスト対象のAPI

9-2 で行った「データベーステスト」のポイント加算処理を利用して、ポイント加算を行うWebAPIのテストを通じてフィーチャテストの解説。   

**WebAPIの概要**   
リクエストJSONに含まれるcustomer_idに該当する顧客に対して add_point で指定したポイントを加算する   

9.3.2.2 ポイント加算APIのクラス構成（左下4クラスはDBテストで使用したもの）
@startuml
    AddPointAction ..> AddPointUseCase
    AddPointUseCase ..> AddPointService
    AddPointService ..> PointEvent
    AddPointService ..> EloquentCustomerPointEvent
    AddPointService ..> EloquentCustomerPoint
    EloquentCustomerPointEvent ..> PointEvent
    AddPointUseCase ..> EloquentCustomerPoint
    AddPointUseCase ..> EloquentCustomer

    AddPointAction : __invoke(AddPointRequest $request)
    AddPointUseCase : run(int $customerid, int $addPoint, string $event, Carbon $now)
    AddPointService : add(PointEvent $pointEvent)
    EloquentCustomerPointEvent : register(PointEvent $e)
    EloquentCustomerPoint : addPoint(int $customerid, int $addPoint)
@enduml

各クラスの概要説明、ソースコードは**クラス名のリンク**をクリックで閲覧可能

[**AddPointActionクラス**](https://github.com/laravel-socym/chapter09/blob/master/app/Http/Actions/AddPointAction.php)
`app\Http\Actions\AddPointAction.php`
HTTPに関する処理とユースケースの実行を担う   
API処理の親ファイル バリデートを __invoke の引数にフォームリクエストを入れる事で行う。また、 AddPointUseCaseクラスをコンストラクトインジェクションして処理を引っ張ってきてる


[**AddPointRequestクラス**](https://github.com/laravel-socym/chapter09/blob/master/app/Http/Requests/AddPointRequest.php)
`app\Http\Requests\AddPointRequest.php`
要はバリデートの為のフォームリクエストファイル
`customer_id` と `add_point` を `'required|int'` のバリデートをしている


9.3.2.5 ルーティングの追加(routes/api.php)
```php
// 追加
use App\Http\Actions\AddPointAction;
// 中略
Route::put('/customers/add_point', AddPointAction::class);
```
AddPointActionクラスはのメソッドは `__invoke` しているので、クラス名のみの指定となっている

コントローラーやアクションクラスは標準設定だと `App\Http\Controller`以下に置くことが必要だが、以下の設定を変更することでカスタマイズできる。   

9.3.2.6 app\Providers\RouteServiceProvider の設定変更
```php
// before
// protected $namespace = 'App\Http\Controllers';

// after
protected $namespace = ''; // 空文字にする
```

[**AddPointUseCaseクラス**](https://github.com/laravel-socym/chapter09/blob/master/app/UseCases/AddPointUseCase.php)
`app\UseCases\AddPointUseCase.php`

配下の`AddPointService`, `EloquentCustomer`, `EloquentCustomerPoint`をコンストラクタインジェクションする。   
`run`メソッドで カスタマーの情報`(\$customerId, \$pointEvent, \$addPoint, \$now)`を引数に、ポイント加算処理を走らせ、返り値として保有した`cuntomer_point`tableの`point`を返す。   

[**PreConditionException**](https://github.com/laravel-socym/chapter09/blob/master/app/Exceptions/PreConditionException.php)

検証に失敗した際は、`app\Exceptions\PreConditionException`にtrow そちらに処理は書かれてないが、下記の`app\Exceptions\Handler`側で`PreConditionException`が例外instanceされた際ステータス400とエラーメッセージを出力させる。   

[**app\Exceptions\Handler**](https://github.com/laravel-socym/chapter09/blob/master/app/Exceptions/Handler.php)

9.3.2.9 app\Exceptions\Handlerクラスによるエラーレスポンスの設定(抜粋)
```php
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof PreConditionException) {
            return response()->json(['message' => trans($exception->getMessage())], Res::HTTP_BAD_REQUEST);
        }

        return parent::render($request, $exception);
    }
```

では以上のAPIを実際に使用してみる。
`http://larabook.test/api/customers/add_point`にcostomer_id = 1 が 10point Addするcurlコマンドを叩く

```
$ curl -v -H "Accept: application/json" -H "Content-type: application/json" -X PUT -d \
'{"customer_id":1,"add_point":10}' http://larabook.test/api/customers/add_point -w "\n"
```

9.3.2.10 curlコマンドによる実行例
```
vagrant@homestead:~/lalabook/chapter09$ curl -v -H "Accept: application/json" \
-H "Content-type: application/json" -X PUT -d \
'{"customer_id":1,"add_point":10}' \
 http://larabook.test/api/customers/add_point -w "\n"
*   Trying 192.168.10.10...
* TCP_NODELAY set
* Connected to larabook.test (192.168.10.10) port 80 (#0)
> PUT /api/customers/add_point HTTP/1.1
> Host: larabook.test
> User-Agent: curl/7.58.0
> Accept: application/json
> Content-type: application/json
> Content-Length: 32
>
* upload completely sent off: 32 out of 32 bytes
< HTTP/1.1 200 OK
< Server: nginx/1.14.0 (Ubuntu)
< Content-Type: application/json
< Transfer-Encoding: chunked
< Connection: keep-alive
< Cache-Control: no-cache, private
< Date: Tue, 08 Jan 2019 09:00:50 GMT
< X-RateLimit-Limit: 60
< X-RateLimit-Remaining: 59
<
* Connection #0 to host larabook.test left intact
{"customer_point":110}
```
ステータスは`HTTP/1.1 200 OK`となる。最後の行に`customer_point`が110に増加したjsonが返ってきているのが確認できる   
当然だが同じコマンドを叩く都度DBが更新され、`customer_point`が10増加する   

次にエラーとなる値で確認 add_point = 0 でPUTしてみる
```
vagrant@homestead:~/lalabook/chapter09$ curl -v -H "Accept: application/json" -H "Content-type: application/json" -X PUT -d '{"customer_id":1,"add_point":0}' http://larabook.test/api/customers/add_point -w "\n"
*   Trying 192.168.10.10...
* TCP_NODELAY set
* Connected to larabook.test (192.168.10.10) port 80 (#0)
> PUT /api/customers/add_point HTTP/1.1
> Host: larabook.test
> User-Agent: curl/7.58.0
> Accept: application/json
> Content-type: application/json
> Content-Length: 31
>
* upload completely sent off: 31 out of 31 bytes
< HTTP/1.1 400 Bad Request
< Server: nginx/1.14.0 (Ubuntu)
< Content-Type: application/json
< Transfer-Encoding: chunked
< Connection: keep-alive
< Cache-Control: no-cache, private
< Date: Tue, 08 Jan 2019 09:15:55 GMT
< X-RateLimit-Limit: 60
< X-RateLimit-Remaining: 59
<
* Connection #0 to host larabook.test left intact
{"message":"add_point should be equals or greater than 1"}
```
ステータスが`HTTP/1.1 400 Bad Request`となって、最後の行にエラーメッセージが返信されてきているのが確認できる。   

<br>

### 9-3-3　APIテストの実装

テストでは状況別で以下の４つのケースを実装する   

- ポイント加算が正常完了する
- バリテーションエラーになる
- add_pointが事前条件エラーになる
- customer_idが事前条件エラーになる

以下のクラスにテストを実装してゆく   
`tests\Feature\Api\AddPointTest`

9.3.3.1 tests\Feature\Api\AddPointTestクラス（準備部分を抜粋）
```php
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
        //(1-2)
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

    // 以下にテストを追加して行く

}
```
(1) DBを利用しているので `use RefreshDatabase;` をしている   
(1-2)`Carbon::setTestNow();`テスト用の日時を生成している   
[PHPで日付時刻の処理を書くなら Carbon がおすすめ](http://blog.asial.co.jp/1392) より引用
> setTestNow() はCarbonが基準とする現在の日時をモックで設定できる機能です。つまりロジック内でCarbonを適切に扱うことで時間に関するテストをユニットテストでも書けるようになります。   

(2)テストに必要な値は9.2.2.7同様 factoryを使用して準備する。


#### 正常ケース

APIで100point保持の会員に10point追加した際の動作を確認するシナリオ。   
ここまでくると、だいたいパッと見て何やってるか判断できる位にはなりたい所。   

9.3.3.1 tests\Feature\Api\AddPointTestクラス（正常ケースのテスト部分を抜粋）
```php
    /**
     * @test
     */
    // 正常ケースのテスト
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
```

<br>

#### バリテーションエラーとなるケース

9.3.3.2 バリテーションエラーのテストメソッド
```php
    /**
     * @test
     */
    public function put_add_point_バリデーションエラー()
    {
        // (2) API実行 パラメーター無しでアクセスしてる
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
```
(3) responce 422 はLaravelのバリテーションエラーのステータスコードとなっている。
`assertExactJson`メソッドで期待値と一致するかを検証）でエラーメッセージの検証をしているが、レスポンスボティ全体を検証できるメリットと、テストメソッドの可読性を上げることができる為に利用している。   

逆にランダムな値を含み、全体一致が難しい値等の検証は。`assertJson`メソッド(配列で指定した値が含まれているか検証)を使う   

9.3.3.3 assertJsonメソッドでレスポンスJSONの一部を検証
```php
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
```

また、JSONで帰って来た値の中に検証すべきメッセージが存在するかを確認したい場合、レスポンスのJSONを配列に変換して、key別に検証する以下の方法もある   
便利な方法なので覚えておくと良い   

9.3.3.4 レスポンスボディのJSONを配列に変換して検証
```php
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
```

<br>

#### add_pointが事前条件エラーとなるケース

add_pointが**0かマイナス値**の場合正常なエラーメッセージが返るかを検証するテストメソッド   
9-1-4 データプロバイダを使っている（配列で検証する値を複数用意して @dataProvider アノテーションで宣言《コメントにメソッド指定》）

復習: @dataProvider アノテーション 一見コメントだが、このメソッドの配列を使ってテストしろって指示出来る奴
```php
    /**
     * @test
     * @dataProvider dataProvider_put_add_point_add_point事前条件エラー
     */
```


9.3.3.4 add_pointの事前条件エラーを検証するメソッド
```php
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
```

<br>

#### customer_idが事前条件とエラーとなるケース

customer_idが存在しないid(999)だった場合に正常なエラーが返るかをテストするメソッド  
9.3.3.5 customer_idの事前条件エラーを検証するテストメソッド
```php
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
```

最後に該当ファイルのテストを実施してテストが正常に終わる事を確認  
```
vagrant@homestead:~/larabook/chapter09$ ./vendor/bin/phpunit tests/Feature/Api/AddPointTest.php
PHPUnit 6.5.9 by Sebastian Bergmann and contributors.

........                                                            8 / 8 (100%)

Time: 3.15 seconds, Memory: 20.00MB

OK (8 tests, 20 assertions)
```

無事正常なテストとして完了した   

<br>

### 9-3-4　WebAPIテストに便利な機能

最後にWebAPIテストに便利な機能の紹介   
`tests\Feature\Api\MiddlewareTest.php`にテストコードが記載されている   

<br>

#### ミドルウェアの無効化

HTTPリクエスト送信時にルーティング等で設定されているミドルウェアを無効にできる。  
withoutMiddleware メソッドに無効にしたいミドルウェアクラス名を指定すると実行されなくなる

9.3.4.1 ミドルウェアを無効にする
```php
    /**
     * @test
     */
    public function TeaPotMiddlewareを無効()
    {
        $response = $this->withoutMiddleware(TeaPotMiddleware::class)
            ->getJson('/api/live');

        $response->assertStatus(200);
    }
```

全ミドルウェアを無効にするには `withoutMiddleware`を引数無しで実行するか
`use Illuminate\Foundation\Testing\WithoutMiddleware;`を宣言する

9.3.4.2 全ミドルウェアを無効にする
```php
    /**
     * @test
     */
    public function 全てのミドルウェアを無効()
    {
        $response = $this->withoutMiddleware()
            ->getJson('/api/live');

        $response->assertStatus(200);
    }
```

9.3.4.3 全ミドルウェアを無効にする(WithoutMiddlewareトレイト)
```php
<?php
declare(strict_types=1);

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class WithoutMiddlewareTest extends TestCase
{
    use WithoutMiddleware;

    /**
     * @test
     */
    public function 全てのミドルウェアを無効()
    {
        $response = $this->getJson('/api/live');

        $response->assertStatus(200);
    }
}
```

<br>

#### 認証
認証が必要なAPIテストには以下の二つの方法がある   

- リクエストヘッダに認証に必要なトークン等を設定
- WithoutMiddlewareメソッド等で認証ミドルウェアを無効にする

`tests\Feature\Api\AuthTest.php`にサンプルコードがある   

テスト時は通常のリクエストと同様ミドルウェアで認証が実行される   

9.3.3.4 認証トークンを送信する例
```php
    /**
     * @test
     */
    public function guard_api()
    {
        // 認証ユーザーを事前に生成
        factory(User::class)->create([
            'name'      => 'Mike',
            'api_token' => 'token1'
        ]);

        // 認証トークンをリクエストヘッダに設定して送信
        $response = $this->withHeaders([
            'Authorization' => 'Bearer token1'
        ])->getJson('/api/user');

        $response->assertStatus(200);
        $response->assertJson([
            'name' => 'Mike',
        ]);
    }
```

9.3.4.5 actingAsメソッドによる認証ユーザー設定例
```php
    /**
     * @test
     */
    public function actingAsで認証ユーザ設定()
    {
        // 認証ユーザーを事前に生成
        $user = factory(User::class)->create([
            'name'      => 'Mike',
            'api_token' => 'token1'
        ]);

        // ミドルウェアを無効にして、actingAsメソッドに認証ユーザを設定
        $response = $this->withoutMiddleware()
            ->actingAs($user)
            ->getJson('/api/user');

        $response->assertStatus(200);
        $response->assertJson([
            'name' => 'Mike',
        ]);
    }
```
これだとテストを実行するとコントローラーやアクションでは`Auth::user()`等でユーザーを取得できるらしい   

<br>

#### コンポーネントのモック（Fake）

MailやNotification等が絡んだ機能でそのままテストをしてしまうと、例えばテストでMailが送信されてしまうが、このようなコンポーネントをモックにすれば、ミドルウェアに接続しないでテストが可能となる。ここではMailのモッククラスを紹介する。   

MailのモックはMailファサードのfakeメソッドを利用する。fakeメソッドを利用するとサービスコンテナに登録されているインスタンスがモッククラスに置き換わる   

**注意 :** アサーションメソッドを利用するにはsendメソッドでメールを送信する際に
`Illuminate\Contracts\Mail\Mailable`インターフェースを実装したクラスを引数に指定する必要がある   

9.3.4.6 MailFakeのアサーションメソッド
| メソッド | 内容 |
|:--|:--|
| assertSent | 指定されたメールが送信された事を検証 |
| assertNotSent | 指定されたメールが送信されてない事を検証 |
| assertNottingSent | メールが送信されてない事を検証 |
| assertQueued | 指定されたメールがメールキューに登録された事を検証 |
| assertNotQueued | 指定されたメールが送信されてない事を検証 |
| assertNottingQueued | メールキューに登録されてない事を検証 |


9.3.4.7 アサーションメソッドで送信内容が検証できる送信例 (`routes\api.php`)
```php
Route::post('/send-email', function (Request $request, Mailer $mailer) {
    // Mailableインターフェースを実装したクラス
    $mail = new \App\Mail\Sample();
    // sendメソッドにMailableインターフェースを実装したクラスを指定
    $mailer->to($request->get('to'))->send($mail);

    return response()->json('ok');
});
```



9.3.4.8 ファサードのfakeメソッドを利用した例 (`tests\Feature\Api\MailTest.php`抜粋)
```php
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
        // 第二引数が数値になると、送信された件数を検証する
        Mail::assertSent(Sample::class, 1);
        // (3) 送信した$mailableの値を検証
        // 第二引数をDIにしてメソッドを検証に使える、hasToは有無の検証なのでbool値が返る
        Mail::assertSent(Sample::class, function (Mailable $mailable) {
            return $mailable->hasTo('a@example.com');
        });
    }
```

別の方法で、モックを利用せずにメール送信を防ぐ方法もある。logを利用すると送信内容をログファイルに出力する。テスト実行時のみメールドライバを切り替えるには以下の行を追加する。   

9.3.4.9 MAIL_DRIVERをlogに設定 (`phpunit.xml`抜粋)
```php
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="QUEUE_DRIVER" value="sync"/>
        <env name="DB_DATABASE" value="app_test"/>
        <env name="MAIL_DRIVER" value="log"/> <!-- arrayを logにする -->
    </php>
```
**9章は以上となる**

## まとめ　感想

すげえ疲れた、そもそもテストをするにもそれに至るまで、元のアプリケーション実装を読み込む必要があった、しかし見慣れない書き方満載で、テストに行くまでに調べる事も多く理解するまでが大変だった。しかし理にかなった設計というものを垣間見た気がする。
おそらく筆者の方もテストの章を通じて、アプリケーションの設計思想を見せたかったのやもしれない。     
とはいえ、現状なんとかコードを読むことは出来るが、自分で書ける気はまだしない。しかし処理を分離するとテストもし易くなるというメリットはなんとなくつかめた。   
最初にフレームワークに触れた時も、当時のベタな書き方に対して、こんなの使いこなせる気がしない！と思ったものだが、やはりしばらくすれば慣れてくるのだろう。

この章の資料を作るにあたってコード以外の部分のスキルも身に付いた。
vagrantの環境追加に始まり開発環境でのsslをchromeでも閲覧可能にする方法の確立にはじまったが、エディタにvscodeを使うようになってツールに助けられた部分も大きい。複数のファイル間の呼び出し元クラスへ簡単に渡り歩けてコードを確認できり、ターミナルでシームレスにコマンドを叩いてテスト動作を確認できたりしたのはメリットだった。また、plantUMLを初めて使ってみて、書籍を同じクラス図等が作れるようになったり、自分のアプリケーションのコードと見比べたりも簡単に出来たので、理解の助けになった気がする。ツールはやはり良いものを使うべきだなぁ。という事を実感。
そしてこの3記事に渡るMarkdownのテキストも、全てまとめるとここが今2000行目！かなりのボリュームとなった（ソースコードは書籍GitHubのコピペが大半で申し訳ないが...）
今後は当初の目的であった自分のアプリのテストをゴリゴリ書いてデグレしにくい体制を早く作りたい。   

