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
3. 方法
```php
/**
     * Notes:[成功通知]
     * Desc: 这个方法，必须传递 data 数据，msg 可以自己进行控制
     * User: Back
     * Date: 2021/6/4
     * Time: 18:19
     * @param  array  $data
     * @param  null  $msg
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respond($data = null, $msg = null)
    {
        return ApiResponse::asSuccess()->withData($data)->withMessage($msg)->build();
    }

    /**
     * Notes:[自定义成功消息通知]
     * Desc: 这个方法，默认返回成功。data 数据默认为 null，msg 信息自行控制
     * User: Back
     * Date: 2021/6/4
     * Time: 18:19
     * @param $msg
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respondWithMessage($msg)
    {
        return ApiResponse::asSuccess()->withMessage($msg)->build();
    }

    /**
     * Notes:[错误请求]
     * Desc: 错误方法，可以传递 api_code，系统根据 api_code 自行查找对应的文字说明，具体看你是否配置了文字对应关系
     * User: Back
     * Date: 2021/6/4
     * Time: 18:20
     * @param $api_code
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respondBadRequest($api_code)
    {
        return $this->respondWithError($api_code, 400);
    }

    /**
     * Notes:[错误提示]
     * Desc: 错误方法，可以传递 api_code，系统根据 api_code 自行查找对应的文字说明，可以控制 http_code 必传。比如 人脸识别失败，我要返回 200 状态码。
     * User: Back
     * Date: 2021/6/4
     * Time: 18:20
     * @param $api_code
     * @param $http_code
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respondWithError($api_code, $http_code)
    {
        return ApiResponse::asError($api_code)->withHttpCode($http_code)->build();
    }

    /**
     * Notes:[账号密码验证失败]
     * Desc: 账号密码错误，或者登录失效都可以是用此方法
     * User: Back
     * Date: 2021/6/4
     * Time: 18:20
     * @param $api_code
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respondUnAuthorizedRequest($api_code)
    {
        return $this->respondWithError($api_code, 401);
    }

    /**
     * Notes:[数据不存在]
     * Desc: 数据不存在时可以使用此方法
     * User: Back
     * Date: 2021/6/4
     * Time: 18:20
     * @param $api_code
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respondNotFound($api_code)
    {
        return $this->respondWithError($api_code, 404);
    }
```

4.接管异常处理，修改`app/Exceptions/Handler.php`

1. 引入
```php
use MarcinOrlowski\ResponseBuilder\ExceptionHandlerHelper;
```

2. 如果没有`render`方法，那么就增加这个方法。如果有那么修改此方法为下面内容
```PHP
public function render($request, Throwable $e)
{
    return ExceptionHandlerHelper::render($request, $e);
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