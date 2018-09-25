# PHPフレームワーク Laravel Webアプリケーション開発 - chapter09 サンプルコード

## 対応

### 9-1 ユニットテスト

* app/Services/CalculatePointService.php
* app/Exceptions/PreConditionException.php
* tests/Unit/CalculatePointServiceTest.php
* phpunit.xml

### 9-2 データベーステスト

* app/Services/AddPointService.php
* app/Model/PointEvent.php
* app/Eloquent/EloquentCustomerPointEvent.php
* app/Eloquent/EloquentCustomerPoint.php
* app/Eloquent/EloquentCustomer.php
* phpunit.xml
* database/factories/CustomerFactory.php
* database/factories/CustomerPointFactory.php
* tests/Unit/AddPoint/EloquentCustomerPointEventTest.php
* tests/Unit/AddPoint/EloquentCustomerPointTest.php
* tests/Unit/AddPointServiceTest.php
* tests/Unit/AddPointServiceWithMockTest.php
 
### 9-3 WebAPIテスト

* routes/api.php
* tests/Feature/Api/PingTest.php
* app/Http/Actions/AddPointAction.php
* app/Http/Requests/AddPointRequest.php
* app/Providers/RouteServiceProvider.php
* app/UseCases/AddPointUseCase.php
* app/Exceptions/PreConditionException.php
* app/Exceptions/Handler.php
* tests/Feature/Api/AddPointTest.php
* tests/Feature/Api/AuthTest.php
* tests/Feature/Api/AuthWithoutMiddlewareTest.php
* tests/Feature/Api/MailTest.php
* tests/Feature/Api/MiddlewareTest.php
* tests/Feature/Api/PingTest.php
* tests/Feature/Api/PostTest.php
* tests/Feature/Api/WithoutMiddlewareTest.php
* phpunit.xml

## Usage

* 本章サンプルコードは、Homestead で動作します。
* 実行する際は、Homestead のインストールを行った後に下記の手順を実行して下さい。

```
$ git clone https://github.com/laravel-socym/chapter09.git
$ cd chapter09
$ composer install
$ ./vendor/bin/homestead make
$ vagrant up

$ vagrant ssh
vagrant@chapter09:~$ cd /vagrant
vagrant@chapter09:~$ make
vagrant@chapter09:~$ ./vendor/bin/phpunit
```
