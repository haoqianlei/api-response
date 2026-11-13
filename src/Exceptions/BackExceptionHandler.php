<?php

namespace Back\ApiResponse\Exceptions;

use Back\ApiResponse\ApiResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Validation\ValidationException;
use MarcinOrlowski\ResponseBuilder\ExceptionHandlerHelper;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class BackExceptionHandler extends ExceptionHandlerHelper
{
    protected static function getExceptionHandlerConfig(): array
    {
        $defaults = [
            \Symfony\Component\HttpKernel\Exception\HttpException::class => [
                'handler' => \MarcinOrlowski\ResponseBuilder\ExceptionHandlers\HttpExceptionHandler::class,
                'pri' => -100,
                'config' => [],
            ],
            RB::KEY_DEFAULT => [
                'handler' => \MarcinOrlowski\ResponseBuilder\ExceptionHandlers\DefaultExceptionHandler::class,
                'pri' => -127,
                'config' => [
                    'api_code' => \MarcinOrlowski\ResponseBuilder\BaseApiCodes::EX_UNCAUGHT_EXCEPTION(),
                    'http_code' => 500,
                ],
            ],
        ];
        $config = \MarcinOrlowski\ResponseBuilder\Util::mergeConfig($defaults, Config::get(RB::CONF_KEY_EXCEPTION_HANDLER, []));
        \MarcinOrlowski\ResponseBuilder\Util::sortArrayByPri($config);
        return $config;
    }

    protected static function error(
        Throwable $ex,
        int $api_code,
        ?int $http_code = null,
        ?string $error_message = null
    ): Response {
        $exceptionStatus = $ex instanceof HttpExceptionInterface ? $ex->getStatusCode() : 500;
        $http_code = $http_code ?? $exceptionStatus;
        if ($http_code < 400 || $http_code > 599) {
            $http_code = $exceptionStatus;
        }

        $map = Config::get(RB::CONF_KEY_MAP, []);
        if (is_array($map) && array_key_exists($api_code, $map)) {
            $error_message = Lang::get($map[$api_code], ['api_code' => $api_code]);
        }

        $data = null;
        if ($ex instanceof ValidationException) {
            $error_message = $ex->validator->errors()->first() ?: $error_message;
            $data = [RB::KEY_MESSAGES => $ex->errors()];
        }

        $debug = null;
        if (Config::get(RB::CONF_KEY_DEBUG_EX_TRACE_ENABLED, false)) {
            $debug = [Config::get(RB::CONF_KEY_DEBUG_EX_TRACE_KEY, RB::KEY_TRACE) => [
                RB::KEY_CLASS => get_class($ex),
                RB::KEY_FILE => $ex->getFile(),
                RB::KEY_LINE => $ex->getLine(),
            ]];
        }

        $response = ApiResponse::asError($api_code)
            ->withMessage(is_array($error_message) ? implode('', $error_message) : $error_message)
            ->withHttpCode($http_code)
            ->withData($data)
            ->withDebugData($debug)
            ->build();

        // Preserve protocol headers such as Allow and Retry-After.
        if ($ex instanceof HttpExceptionInterface) {
            $response->headers->add($ex->getHeaders());
        }

        return $response;
    }
}
