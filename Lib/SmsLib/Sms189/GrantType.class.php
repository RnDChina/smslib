<?php
/**
 * ----------------------
 * GrantType.class.php
 * 
 * User: jian0307@icloud.com
 * Date: 2015/4/19
 * Time: 0:00
 * ----------------------
 */

namespace Lib\SmsLib\Sms189;

/**
 * 授权类型常量
 *
 * @see http://open.189.cn/index.php?m=content&c=index&a=lists&catid=62
 * @package Lib\SmsLib\Sms189
 */
class GrantType {
    /**
     * AC授权模式
     */
    const AUTHORIZATION_CODE = 'authorization_code';
    /**
     * CC授权模式
     */
    const CLIENT_CREDENTIALS = 'client_credentials';
    /**
     * 更新令牌
     */
    const REFRESH_TOKEN = 'refresh_token';
}