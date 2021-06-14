<?php

namespace Back\ApiResponse\Exceptions;

use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\ExceptionHandlerHelper;
use MarcinOrlowski\ResponseBuilder\ExceptionHandlers\DefaultExceptionHandler;
use MarcinOrlowski\ResponseBuilder\ExceptionHandlers\HttpExceptionHandler;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use MarcinOrlowski\ResponseBuilder\Util;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Throwable;

class BackExceptionHandler extends ExceptionHandlerHelper
{
    /**
     * Process single error and produce valid API response.
     *
     * @param  \Throwable  $ex  Exception to be handled.
     * @param  integer  $api_code
     * @param  integer  $http_code
     * @param  string  $error_message
     *
     * @return HttpResponse
     */
    protected static function error(
        Throwable $ex,
        int $api_code,
        int $http_code = null,
        string $error_message = null
    ): HttpResponse {
        $ex_http_code = ($ex instanceof HttpException) ? $ex->getStatusCode() : $ex->getCode();
        $http_code = $http_code ?? $ex_http_code;
        $error_message = $error_message ?? '';

        // Check if we now have valid HTTP error code for this case or need to make one up.
        // We cannot throw any exception if codes are invalid because we are in Exception Handler already.
        if ($http_code < RB::ERROR_HTTP_CODE_MIN) {
            // Not a valid code, let's try to get the exception status.
            $http_code = $ex_http_code;
        }
        // Can it be considered a valid HTTP error code?
        if ($http_code < RB::ERROR_HTTP_CODE_MIN) {
            // We now handle uncaught exception, so we cannot throw another one if there's
            // something wrong with the configuration, so we try to recover and use built-in
            // codes instead.
            // FIXME: We should log this event as (warning or error?)
            $http_code = RB::DEFAULT_HTTP_CODE_ERROR;
        }

        // If we have trace data debugging enabled, let's gather some debug info and add to the response.
        $debug_data = null;
        if (Config::get(RB::CONF_KEY_DEBUG_EX_TRACE_ENABLED, false)) {
            $debug_data = [
                Config::get(RB::CONF_KEY_DEBUG_EX_TRACE_KEY, RB::KEY_TRACE) => [
                    RB::KEY_CLASS => \get_class($ex),
                    RB::KEY_FILE => $ex->getFile(),
                    RB::KEY_LINE => $ex->getLine(),
                ],
            ];
        }

        // If this is ValidationException, add all the messages from MessageBag to the data node.
        $data = null;
        if ($ex instanceof ValidationException) {
            /** @var ValidationException $ex */
            // $data = [RB::KEY_MESSAGES => $ex->validator->errors()->messages()];
            $errors = $ex->validator->errors()->messages();
            $error_message = current($errors[key($errors)]);
        }

        return RB::asError($api_code)
            ->withMessage($error_message)
            ->withHttpCode($http_code)
            ->withData($data)
            ->withDebugData($debug_data)
            ->build();
    }

    /**
     * Returns name of exception handler class, configured to process specified exception class or @null if no
     * exception handler can be determined.
     *
     * @param  string  $cls  Name of exception class to handle
     *
     * @return array|null
     */
    protected static function getHandler(\Throwable $ex): ?array
    {
        $result = null;

        $cls = \get_class($ex);
        if (\is_string($cls)) {
            $cfg = static::getExceptionHandlerConfig();

            // check for exact class name match...
            if (\array_key_exists($cls, $cfg)) {
                $result = $cfg[$cls];
            } else {
                // no exact match, then lets try with `instanceof`
                // Config entries are already sorted by priority.
                foreach (\array_keys($cfg) as $class_name) {
                    if ($ex instanceof $class_name) {
                        $result = $cfg[$class_name];
                        break;
                    }
                }
            }
        }

        return $result;
    }


    /**
     * Returns ExceptionHandlerHelper configration array with user configuration merged into built-in defaults.
     *
     * @return array
     */
    protected static function getExceptionHandlerConfig(): array
    {
        $default_config = [
            HttpException::class => [
                'handler' => HttpExceptionHandler::class,
                'pri' => -100,
                'config' => [
                    // used by unauthenticated() to obtain api and http code for the exception
                    HttpResponse::HTTP_UNAUTHORIZED => [
                        RB::KEY_API_CODE => BaseApiCodes::EX_AUTHENTICATION_EXCEPTION(),
                    ],
                    // Required by ValidationException handler
                    HttpResponse::HTTP_UNPROCESSABLE_ENTITY => [
                        RB::KEY_API_CODE => BaseApiCodes::EX_VALIDATION_EXCEPTION(),
                    ],

                    RB::KEY_DEFAULT => [
                        RB::KEY_API_CODE => BaseApiCodes::EX_UNCAUGHT_EXCEPTION(),
                        RB::KEY_HTTP_CODE => HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
                    ],
                ],
                // default config is built into handler.
            ],

            // default handler is mandatory. `default` entry MUST have both `api_code` and `http_code` set.
            RB::KEY_DEFAULT => [
                'handler' => DefaultExceptionHandler::class,
                'pri' => -127,
                'config' => [
                    RB::KEY_API_CODE => BaseApiCodes::EX_UNCAUGHT_EXCEPTION(),
                    RB::KEY_HTTP_CODE => HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
                ],
            ],
        ];
        $cfg = Util::mergeConfig($default_config,
            Config::get(RB::CONF_KEY_EXCEPTION_HANDLER, []));
        Util::sortArrayByPri($cfg);

        return $cfg;
    }
}