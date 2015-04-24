<?php
/**
 * ----------------------
 * ResponseData.class.php
 * 
 * User: jian0307@icloud.com
 * Date: 2015/4/20
 * Time: 20:57
 * ----------------------
 */

namespace Lib\SmsLib;


class ResponseData {
    // 成功
    const __OK__ = 0;
    //手机格式错误
    const __MOBILE_ERROR__ = -1;
    //请求太频繁
    const __REQUEST_ERROR__ = -2;
    //发送失败
    const __SENDFAIL_ERROR__ = -3;

    /**
     * 状态码
     * @var
     */
    public $code;

    /**
     * 错误信息
     * @var
     */
    public $message;

    /**
     * 平台返回的数据，
     * 平台不同返回的数据不同，以平台协议为准
     * @var
     */
    public $data;
}