<?php

return [
    'min_code' => 100,
    'max_code' => 1024,

    // YOUR_API_CODE => 'language.message_key'
    'map' => [],

    'exception_handler' => [
        \Illuminate\Auth\AuthenticationException::class => [
            'handler' => \Back\ApiResponse\ExceptionHandlers\AuthenticationExceptionHandler::class,
            'pri' => -100,
            'config' => [],
        ],
        \Illuminate\Validation\ValidationException::class => [
            'handler' => \Back\ApiResponse\ExceptionHandlers\ValidationExceptionHandler::class,
            'pri' => -100,
            'config' => [],
        ],
        \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class => [
            'handler' => \Back\ApiResponse\ExceptionHandlers\MethodNotAllowedHttpExceptionHandler::class,
            'pri' => -100,
            'config' => [],
        ],
        \Symfony\Component\HttpKernel\Exception\HttpException::class => [
            'handler' => \MarcinOrlowski\ResponseBuilder\ExceptionHandlers\HttpExceptionHandler::class,
            'pri' => -100,
            'config' => [],
        ],
    ],

    'encoding_options' => JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE,

    'debug' => [
        'debug_key' => 'debug',
        'exception_handler' => [
            'trace_key' => 'trace',
            'trace_enabled' => env('APP_DEBUG', false),
        ],
        'converter' => ['debug_enabled' => env('RB_CONVERTER_DEBUG', false)],
    ],
];