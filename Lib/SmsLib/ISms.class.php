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
     * @param string $mobile 手机号
     * @param string $message 验证码
     * @param int $sceneType 场景类型 1注册 2找回密码
     * @return mixed
     */
    public function send($mobile,$message = null,$sceneType = 1);

    /**
     * 生成验证码
     * @param int $len 长度
     * @return mixed
     */
    public function createSmsCode( $len = 6 );

    /**
     * 获取验证码
     * @return string 验证码
     */
    public function getSmsCode();

    /**
     * 获取发送时间戳
     * @return int 时间戳
     */
    public function getSendTimestamp();

    /**
     * 获取场景ID
     * @return int 场景ID
     */
    public function getSceneType();
}