<?php

namespace Back\ApiResponse;

use Illuminate\Support\Facades\Config;
use MarcinOrlowski\ResponseBuilder\MessageManager;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;

class ApiResponse extends ResponseBuilder
{
    protected function buildResponse(
        bool $success,
        int $api_code,
        $message_or_api_code,
        ?array $lang_args = null,
        $data = null,
        ?array $debug_data = null
    ): array {
        $response = $this->buildResponseCover(
            $success, $api_code, $message_or_api_code, $lang_args, $data, $debug_data
        );
        $response['timestamp'] = time();

        return $response;
    }

    /** Preserve the original data structure instead of applying upstream converters. */
    protected function buildResponseCover(
        bool $success,
        int $api_code,
        $msg_or_api_code,
        ?array $placeholders = null,
        $data = null,
        ?array $debug_data = null
    ): array {
        if (is_int($msg_or_api_code)) {
            // Older builders resolve messages on the builder itself.
            $message = class_exists(MessageManager::class)
                ? MessageManager::get($success, $msg_or_api_code, $placeholders)
                : $this->getMessageForApiCode($success, $msg_or_api_code, $placeholders);
        } else {
            if (!is_string($msg_or_api_code)) {
                throw new \InvalidArgumentException('The response message must be a string or an API code.');
            }
            $message = $msg_or_api_code;
        }

        $response = [
            self::KEY_SUCCESS => $success,
            self::KEY_CODE => $api_code,
            self::KEY_LOCALE => app()->getLocale(),
            self::KEY_MESSAGE => $message,
            self::KEY_DATA => $data,
        ];

        if ($debug_data !== null) {
            $response[Config::get(self::CONF_KEY_DEBUG_DEBUG_KEY, self::KEY_DEBUG)] = $debug_data;
        }

        return $response;
    }
}