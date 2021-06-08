<?php

namespace Back\ApiResponse;


use DateTime;

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
        $response = parent::buildResponse($success, $api_code, $message_or_api_code, $lang_args, $data, $debug_data);

        // then do all the tweaks you need
        $date = new DateTime();
        $response['timestamp'] = $date->getTimestamp();

        // finally, return what $response holds
        return $response;
    }
}
