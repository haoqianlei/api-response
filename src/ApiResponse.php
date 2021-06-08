<?php

namespace Back\ApiResponse;


use DateTime;
use Illuminate\Support\Facades\Config;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;
use MarcinOrlowski\ResponseBuilder\Validator;

class ApiResponse extends \MarcinOrlowski\ResponseBuilder\ResponseBuilder
{
    /**
     * Notes:[自定义增加返回的数据字段值]
     * User: Back
     * Date: 2021/1/26
     * Time: 23:35
     * @param  bool  $success
     * @param  int  $api_code
     * @param  int|string  $message_or_api_code
     * @param  array|null  $lang_args
     * @param  null  $data
     * @param  array|null  $debug_data
     * @return array
     */
    protected function buildResponse(
        bool $success,
        int $api_code,
        $message_or_api_code,
        array $lang_args = null,
        $data = null,
        array $debug_data = null
    ): array {
        // tell ResponseBuilder to do all the heavy lifting first
        $response = $this->buildResponseCover($success, $api_code, $message_or_api_code, $lang_args, $data,
            $debug_data);

        // then do all the tweaks you need
        $date = new DateTime();
        $response['timestamp'] = $date->getTimestamp();

        // finally, return what $response holds
        return $response;
    }

    /**
     * Notes:[重写方法]
     * User: COJOY_10
     * Date: 2021/6/8
     * Time: 23:16
     * @param  bool  $success
     * @param  int  $api_code
     * @param $msg_or_api_code
     * @param  array|null  $placeholders
     * @param  null  $data
     * @param  array|null  $debug_data
     * @return array
     */
    protected function buildResponseCover(
        bool $success,
        int $api_code,
        $msg_or_api_code,
        array $placeholders = null,
        $data = null,
        array $debug_data = null
    ): array {
        // get human readable message for API code or use message string (if given instead of API code)
        if (\is_int($msg_or_api_code)) {
            $message = $this->getMessageForApiCode($success, $msg_or_api_code, $placeholders);
        } else {
            Validator::assertIsString('message', $msg_or_api_code);
            $message = $msg_or_api_code;
        }

        /** @noinspection PhpUndefinedClassInspection */
        $response = [
            RB::KEY_SUCCESS => $success,
            RB::KEY_CODE => $api_code,
            RB::KEY_LOCALE => \App::getLocale(),
            RB::KEY_MESSAGE => $message,
            RB::KEY_DATA => $data,
        ];

        if ($debug_data !== null) {
            $debug_key = Config::get(RB::CONF_KEY_DEBUG_DEBUG_KEY, RB::KEY_DEBUG);
            $response[$debug_key] = $debug_data;
        }

        return $response;
    }


}
