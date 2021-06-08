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
2. 在`app/Http/Controllers/Controller.php`
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

3.接管异常处理，修改`app/Exceptions/Handler.php`

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