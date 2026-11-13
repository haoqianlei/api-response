# API 统一响应处理

`back/api-response` 是用于 Laravel 的 API JSON 响应扩展包，基于 `marcin-orlowski/laravel-api-response-builder` 封装。通过统一的响应字段和控制器方法，让前端以相同方式处理接口结果。

支持成功响应、业务错误、业务码消息翻译和异常响应；普通响应与异常响应均包含 Unix 时间戳。

## 目录

- [版本要求](#版本要求)
- [安装与配置](#安装与配置)
- [快速开始](#快速开始)
- [响应格式](#响应格式)
- [控制器方法](#控制器方法)
- [直接使用构建器](#直接使用构建器)
- [业务码与消息配置](#业务码与消息配置)
- [接入异常处理](#接入异常处理)
- [验证失败与调试信息](#验证失败与调试信息)
- [从旧版本升级](#从旧版本升级)
- [常见问题](#常见问题)
- [开发与测试](#开发与测试)

## 版本要求

| Laravel | PHP 最低版本 | 自动匹配的 Response Builder |
| --- | --- | --- |
| 10.x | 8.1 | 10.x |
| 11.x | 8.2 | 11.x |
| 12.x | 8.2 | 12.x |
| 13.2 及以上的 13.x | 8.3 | 13.x |

Composer 根据应用的 Laravel 和 PHP 版本选择依赖。Laravel 13.0、13.1 不在支持范围内，因为上游 Response Builder 13 要求 Laravel `^13.2`。

本仓库开发用的 `composer.lock` 锁定一套测试依赖。应用安装本包时，Composer 使用应用自身的依赖约束解析版本，不会套用本包的锁文件。

## 安装与配置

在 Laravel 应用根目录执行：

```sh
composer require back/api-response
```

Laravel 默认通过包自动发现注册服务提供者和 `ApiResponse` 别名。

如需自定义业务码、消息映射或调试设置，发布配置文件：

```sh
php artisan vendor:publish --provider="Back\ApiResponse\ApiResponseServiceProvider" --tag=api-response-config
```

生成文件为 `config/response_builder.php`，读取配置时使用 `response_builder`，例如：

```php
config('response_builder.map');
```

已有同名配置文件时，先检查并合并差异，保留应用原有的业务配置。修改配置后，若应用启用了配置缓存，需要重新生成：

```sh
php artisan config:cache
```

## 快速开始

在基础控制器中引入 `ResponseHandler` Trait。保留应用原有父类和其他 Trait，只增加本包的 Trait 即可。

```php
<?php

namespace App\Http\Controllers;

use Back\ApiResponse\ResponseHandler;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use ResponseHandler;
}
```

业务控制器继承该控制器后即可调用：

```php
<?php

namespace App\Http\Controllers;

class UserController extends Controller
{
    public function show()
    {
        return $this->respond(['id' => 1, 'name' => '张三'], '查询成功');
    }
}
```

这些方法返回响应对象，控制器中需要使用 `return` 返回。

## 响应格式

成功响应示例，HTTP 状态码为 `200`：

```json
{
  "success": true,
  "code": 0,
  "locale": "zh_CN",
  "message": "查询成功",
  "data": {
    "id": 1,
    "name": "张三"
  },
  "timestamp": 1700000000
}
```

失败响应示例，HTTP 状态码为 `400`：

```json
{
  "success": false,
  "code": 1001,
  "locale": "zh_CN",
  "message": "参数错误",
  "data": null,
  "timestamp": 1700000000
}
```

| 字段 | 含义 |
| --- | --- |
| `success` | 业务操作是否成功 |
| `code` | 业务码；默认成功码为 `0`，与 HTTP 状态码分别设置 |
| `locale` | Laravel 应用当前语言，示例中的 `zh_CN` 不代表包默认语言 |
| `message` | 显式传入的消息，或根据业务码解析的消息 |
| `data` | 业务数据；未传入时为 `null` |
| `timestamp` | 构建响应时的 Unix 时间戳，单位为秒 |
| `debug` | 可选的调试信息；异常响应是否包含此字段由配置决定 |

`data` 保留传入数据的结构，不使用上游转换器将数组强制转换为对象。空数组返回 `[]`，列表数组返回 JSON 数组，对象按自身的 JSON 序列化规则输出。

## 控制器方法

| 方法 | 业务状态 | HTTP 状态码 | 用途 |
| --- | --- | --- | --- |
| `respond($data = null, $msg = null)` | 成功 | 200 | 返回业务数据，可选消息 |
| `respondWithMessage($msg)` | 成功 | 200 | 仅返回成功消息，`data` 为 `null` |
| `respondWithErrorMessage($api_code, $msg = null)` | 失败 | 400 | 返回业务码及可选错误消息 |
| `respondWithError($api_code, $http_code, $msg = null)` | 失败 | 调用方指定 | 分别控制业务码和 HTTP 状态码 |
| `respondBadRequest($api_code)` | 失败 | 400 | 请求参数错误 |
| `respondUnAuthorizedRequest($api_code)` | 失败 | 401 | 未登录或认证失败 |
| `respondNotFound($api_code)` | 失败 | 404 | 数据不存在 |

```php
// 返回数据。
return $this->respond(['id' => 1], '创建成功');

// 仅返回成功消息。
return $this->respondWithMessage('保存成功');

// 返回自定义错误消息。
return $this->respondWithErrorMessage(1001, '参数错误');

// 从配置中查找业务码对应的消息。
return $this->respondNotFound(1002);

// 业务失败，同时按接口约定使用 HTTP 200。
return $this->respondWithError(1001, 200, '业务校验失败');
```

最后一种情况的 `success` 仍为 `false`，前端需要结合 HTTP 状态码与响应体中的 `success` 判断结果。

## 直接使用构建器

不使用控制器 Trait 时，可以直接引用本包的构建器：

```php
use Back\ApiResponse\ApiResponse;

return ApiResponse::asSuccess()
    ->withData(['id' => 1])
    ->withMessage('创建成功')
    ->withHttpCode(201)
    ->build();
```

失败响应也可以携带业务数据：

```php
return ApiResponse::asError(1001)
    ->withMessage('库存不足')
    ->withData(['remaining' => 0])
    ->withHttpCode(409)
    ->build();
```

直接使用上游构建接口时，成功响应的 HTTP 状态码限于 2xx，失败响应限于 4xx/5xx。需要“业务失败但 HTTP 200”时，使用控制器的 `respondWithError()` 方法。

也可显式引用 Facade：

```php
use Back\ApiResponse\ApiResponseFacade;

return ApiResponseFacade::asSuccess()->withData(['id' => 1])->build();
```

## 业务码与消息配置

在 `config/response_builder.php` 中配置业务码范围和语言键映射：

```php
'min_code' => 100,
'max_code' => 1024,

'map' => [
    1001 => 'api.invalid_parameters',
    1002 => 'api.user_not_found',
],
```

默认成功码 `0` 为特殊有效值。上游内部异常码使用从 `min_code` 起的保留偏移区域，建议应用使用 `1001` 这类独立业务码，避免覆盖内置异常码。若使用更大的业务码，需要同时增大 `max_code`。

在应用语言目录创建 `api.php`，例如 `lang/zh_CN/api.php`：

```php
<?php

return [
    'invalid_parameters' => '参数错误，业务码：:api_code',
    'user_not_found' => '用户不存在',
];
```

语言目录名称应与应用当前语言一致。若应用自定义了语言目录，请放到实际语言目录中。

```php
return $this->respondWithErrorMessage(1001);
// message：参数错误，业务码：1001

return $this->respondWithErrorMessage(1001, '手机号格式错误');
// message：手机号格式错误
```

普通响应中，显式传入消息会覆盖业务码对应的消息；未传消息且没有配置映射时，使用上游默认提示。异常处理会优先使用已配置的业务码映射，验证异常则以第一条验证错误作为消息。

## 接入异常处理

控制器 Trait 只负责主动返回响应。需要统一认证失败、验证失败及其他异常的 JSON 格式时，还需要接入 `BackExceptionHandler`。

下面的示例仅处理 `api/*` 路径或期望 JSON 的请求；可按应用的 API 路径调整条件。

### Laravel 10

在 `app/Exceptions/Handler.php` 的异常处理类中加入或合并以下方法：

```php
public function render($request, \Throwable $e)
{
    if ($request->is('api/*') || $request->expectsJson()) {
        return \Back\ApiResponse\Exceptions\BackExceptionHandler::render($request, $e);
    }

    return parent::render($request, $e);
}
```

### Laravel 11、12、13

使用新应用结构时，在 `bootstrap/app.php` 现有的 `withExceptions()` 中添加回调，保留应用已有配置：

```php
->withExceptions(function (\Illuminate\Foundation\Configuration\Exceptions $exceptions): void {
    $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
        if ($request->is('api/*') || $request->expectsJson()) {
            return \Back\ApiResponse\Exceptions\BackExceptionHandler::render($request, $e);
        }

        return null;
    });
})
```

从旧版本升级、仍使用 `app/Exceptions/Handler.php` 的应用，可按上一节的方式接入。

默认异常处理结果如下，应用可通过 `exception_handler` 配置覆盖对应业务码或 HTTP 状态码：

| 异常 | HTTP 状态码 |
| --- | --- |
| 认证失败 `AuthenticationException` | 401 |
| 请求方法不允许 `MethodNotAllowedHttpException` | 405 |
| 验证失败 `ValidationException` | 422 |
| 其他 HTTP 异常 | 保留异常状态码，如 404、429 |
| 其他未处理异常 | 500 |

异常响应同样包含 `timestamp`。HTTP 异常携带的 `Allow`、`Retry-After` 等响应头也会保留。

## 验证失败与调试信息

验证失败时，`message` 使用第一条验证错误，`data.messages` 包含所有字段错误。例如 HTTP `422` 响应的数据部分：

```json
{
  "message": "邮箱不能为空",
  "data": {
    "messages": {
      "email": ["邮箱不能为空"],
      "name": ["姓名不能为空"]
    }
  }
}
```

以上仅展示部分字段；实际响应还包含 `success`、`code`、`locale` 和 `timestamp`。验证消息的具体语言取决于应用的验证翻译配置。

异常调试开关位于 `config/response_builder.php`：

```php
'debug' => [
    'debug_key' => 'debug',
    'exception_handler' => [
        'trace_key' => 'trace',
        'trace_enabled' => env('APP_DEBUG', false),
    ],
    'converter' => [
        'debug_enabled' => env('RB_CONVERTER_DEBUG', false),
    ],
],
```

启用异常调试后，响应会增加异常类、文件和行号等信息。生产环境应关闭 `APP_DEBUG`，同时确认没有在配置中单独启用 `trace_enabled`。

## 从旧版本升级

1. 确认 Laravel 和 PHP 满足上面的版本要求；本包不再支持 PHP 7.x 和 Laravel 9 及更早版本。
2. 更新本包及所需依赖，让 Composer 根据应用的 Laravel 版本重新解析。只有在包含本次改动的版本发布后，常规 Composer 更新才会获取这些改动。
3. 对照本仓库的 `config/response_builder.php` 合并已发布配置，保留应用自定义业务码和消息映射。
4. 补充 `AuthenticationException` 处理配置；移除旧配置中错误嵌套于 `HttpException` 项内部的 `default` 配置。自定义全局默认处理器应放在 `exception_handler.default`。
5. 按应用结构接入异常处理，并检查前端对响应字段的使用。
6. 如启用了配置缓存，重新执行 `php artisan config:cache`。

需要关注的响应变化：

- 异常响应增加 `timestamp`。
- 请求方法不允许默认返回 HTTP `405`。
- 验证失败的 `data` 从 `null` 变为包含 `messages` 的对象。
- 业务码映射的异常消息会进行语言解析。

## 常见问题

### 为什么安装 Laravel 13 时出现依赖冲突？

本包支持 Laravel `13.2+`，并要求 PHP `8.3+`。检查应用是否仍锁定 Laravel 13.0/13.1，或其他包是否限制了框架版本。不要通过修改 `vendor` 中的文件解决依赖冲突。

### 为什么消息显示语言键，或者显示默认提示？

检查 `response_builder.map`、应用当前语言和语言文件路径是否一致，并确认业务码在配置范围内。配置了不存在的语言键时，翻译结果可能直接返回该键。

### 为什么调用了方法却没有返回接口响应？

控制器需要写 `return $this->respond(...)`。也要确认业务控制器继承了引入 `ResponseHandler` 的基础控制器。

### 为什么验证错误或 404 仍不是统一格式？

确认已接入异常处理，且请求符合回调中的 API 路径或 JSON 条件。客户端可发送 `Accept: application/json`；应用原有的异常回调也可能优先处理某些异常。

### 可以返回空数组和列表吗？

可以。`respond([])` 的 `data` 为 `[]`，`respond([['id' => 1]])` 的 `data` 为列表，`respond()` 的 `data` 为 `null`。

## 开发与测试

在本包源码目录执行：

```sh
composer install
composer test
```

测试覆盖响应结构、默认消息、业务码翻译、Facade 状态隔离、HTTP 状态码、异常响应头、验证错误和调试配置。

本地已在 PHP 8.4 环境下验证 Laravel 10、11、12、13 四个主版本。仓库的 GitHub Actions 配置会分别使用 PHP 8.1/8.2/8.2/8.3 检查对应 Laravel 版本；实际执行结果以 CI 记录为准。

## License

本项目使用 [MIT License](LICENSE.md)。