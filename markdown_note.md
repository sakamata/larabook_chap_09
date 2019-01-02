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
```
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
```
    public function testExample()
    {
        $this->assertTrue(true);
    }
```

別の方法でコメントに @test アノテーションを付ける方法もある。作者はこっちをお勧めしてる   
参考: [アノテーションについて](https://phpunit.readthedocs.io/ja/latest/annotations.html)   
9.1.2.5 @testアノテーション
```
    /**
    * @test
    */
    public function Example()
    {
        $this->assertTrue(true);
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


<br>

### 9-1-4　データプロバイダの活用

<br>

### 9-1-5　例外のテスト

<br>

### 9-1-6　テストの前処理・後処理

<br>

### 9-1-7　テストの設定

<br>

## 9-2　データベーステスト

<br>

### 9-2-1　テスト対象のテーブルとクラス

<br>

### 9-2-2　データベーステストの基礎

<br>

### 9-2-3　Eloquentクラスのテスト

<br>

### 9-2-4　サービスクラスのテスト

<br>

### 9-2-5　モックによるテスト（サービスクラス）

<br>

## 9-3　WebAPIテスト

<br>

### 9-3-1　WebAPIテスト機能

<br>

### 9-3-2　テスト対象のAPI

<br>

### 9-3-3　APIテストの実装

<br>

### 9-3-4　WebAPIテストに便利な機能