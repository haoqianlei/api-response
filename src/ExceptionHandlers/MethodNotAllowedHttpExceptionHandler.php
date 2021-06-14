<?php

namespace Back\ApiResponse\ExceptionHandlers;

use MarcinOrlowski\ResponseBuilder\BaseApiCodes;
use MarcinOrlowski\ResponseBuilder\Contracts\ExceptionHandlerContract;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

final class MethodNotAllowedHttpExceptionHandler implements ExceptionHandlerContract
{
    public function handle(array $user_config, /** @scrutinizer ignore-unused */ \Throwable $ex): ?array
    {
        $defaults = [
            RB::KEY_API_CODE => BaseApiCodes::EX_UNCAUGHT_EXCEPTION(),
            RB::KEY_HTTP_CODE => HttpResponse::HTTP_INTERNAL_SERVER_ERROR,
        ];

        return \array_replace($defaults, $user_config);
    }
}