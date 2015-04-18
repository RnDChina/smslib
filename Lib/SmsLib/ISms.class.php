<?php
namespace Lib\SmsLib;

/**
 * ----------------------
 * ISms.class.php
 * 
 * User: jian0307@icloud.com
 * Date: 2015/4/17
 * Time: 17:35
 * ----------------------
 */

/**
 * 短信接口
 */
interface ISms{
    /**
     * 短信平台配置信息
     * @param $config
     */
    public function setConf($config);

    /**
     * 发送短信
     * @param $mobile
     * @param $message
     * @return mixed
     */
    public function send($mobile,$message = null);

    /**
     * 生成验证码
     * @param int $len
     * @return mixed
     */
    public function createSmsCode( $len = 6 );

    /**
     * 获取验证码
     * @return string
     */
    public function getSmsCode();
}