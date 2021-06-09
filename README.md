# Api 统一响应处理

[![Latest Version on Packagist](https://img.shields.io/packagist/v/back/api-response.svg?style=flat-square)](https://packagist.org/packages/back/api-response)
[![Total Downloads](https://img.shields.io/packagist/dt/back/api-response.svg?style=flat-square)](https://packagist.org/packages/back/api-response)

团队内部使用的`API`响应处理包

## 安装

你可以通过`composer`进行安装

```bash
composer require back/api-response
```

## 使用

1. 生成配置文件

```SHELL
 php artisan vendor:publish --provider="MarcinOrlowski\ResponseBuilder\ResponseBuilderServiceProvider"
```
2. 修改`app/Http/Controllers/Controller.php`

```PHP
namespace App\Http\Controllers;

use Back\ApiResponse\ResponseHandler; // 引入
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    // 引入 ResponseHandler Trait
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, ResponseHandler;
}
```
3. 在控制器中使用得方法，前提是必须拿`app/Http/Controllers/Controller.php`作为父类，代码编写方式请自行查看`你的项目/vendor/back/api-response/ResponseHandler.php`
```php
// 成功通知
// 这个方法， data，msg 可以自己进行控制可选
$this->respond($data, $msg);

// 自定义成功消息通知
// 这个方法，默认返回成功。data 数据默认为 null，msg 信息自行控制
$this->respondWithMessage($msg);

// 错误方法
// 错误方法，可以传递 api_code，系统根据 api_code 自行查找对应的文字说明，具体看你是否配置了文字对应关系
$this->respondBadRequest($api_code);

// 自定义错误消息通知
// 这个方法，默认返回成功。$api_code 数据必传，msg 信息自行控制，如果不传默认去找 $api_code 对应得消息通知
$this->respondWithErrorMessage($api_code, $msg);

// 错误提示
// 错误方法，可以传递 api_code，系统根据 api_code 必传自行查找对应的文字说明，可以控制 http_code 必传。比如 人脸识别失败，我要返回 200 状态码。    
$this->respondWithError($api_code, $http_code);

// 身份验证失败
// 如果账号密码错误，或者登录失效都可以是用此方法，参数必传
$this->respondUnAuthorizedRequest($api_code); 

// 数据不存在
// 数据不存在时可以使用此方法，参数必传
$this->respondNotFound($api_code);
```

4.接管异常处理，修改`app/Exceptions/Handler.php`

1. 引入
```php
use Back\ApiResponse\Exceptions\BackExceptionHandler;
```

2. 如果没有`render`方法，那么就增加这个方法。如果有那么修改此方法为下面内容
```PHP
public function render($request, Throwable $e)
{
    return BackExceptionHandler::render($request, $e);
}
```

### 修改日志

请查看[CHANGELOG](CHANGELOG.md)来观看最近发生的变化

## 贡献

有关详细信息请观看[CONTRIBUTING](CONTRIBUTING.md)

### 安全

如果您发现任何与安全相关的问题，请发送电子邮件`1300657068@qq.com`而不是使用`issues`。

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.