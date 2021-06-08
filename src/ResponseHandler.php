<?php


namespace Back\ApiResponse;

trait ResponseHandler
{
    /**
     * Notes:[成功通知]
     * Desc: 这个方法，必须传递 data 数据，msg 可以自己进行控制
     * User: COJOY_10
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
     * User: COJOY_10
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
     * User: COJOY_10
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
     * User: COJOY_10
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
     * User: COJOY_10
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
     * User: COJOY_10
     * Date: 2021/6/4
     * Time: 18:20
     * @param $api_code
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function respondNotFound($api_code)
    {
        return $this->respondWithError($api_code, 404);
    }
}