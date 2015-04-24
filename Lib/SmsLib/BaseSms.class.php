<?php
/**
 * ----------------------
 * AbsSms.class.php
 * 
 * User: jian0307@icloud.com
 * Date: 2015/4/18
 * Time: 10:11
 * ----------------------
 */

namespace Lib\SmsLib;

/**
 * Class BaseSms
 * @package Lib\SmsLib
 */
class BaseSms implements ISms {

    /**
     * 手机号
     * @var
     */
    protected $mobile;

    /**
     * 验证码
     * @var
     */
    protected $message;

    /**
     * 时间戳
     * @var
     */
    protected $timestamp;

    /**
     * 场景类型ID 1 注册 2 找回密码
     * @var
     */
    protected $sceneType;

    /**
     * 响应数据
     * @var
     */
    protected $response = null;

    public function __construct() {
        date_default_timezone_set('Asia/Shanghai');//设置时区
        $this->timestamp = time();
    }

    /**
     * 配置，子类重写
     * @param $config
     * @throws Exception
     */
    public function setConf($config) {
        if(empty($config)) {
            throw new Exception("配置错误");
        }
    }

    /**
     * 发送验证码，子类重写
     * @param string $mobile 手机号
     * @param string $message 验证码
     * @param int $sceneType 场景类型
     * @return string
     */
    public function send($mobile,$message = null,$sceneType = 1) {
        $this->mobile = $mobile;
        $this->message = $message ? $message : $this->createSmsCode();
        $this->sceneType = $sceneType;
        $this->response = new ResponseData();
    }

    /**
     * 生成数字验证码（子类可按需求重写）
     * @param int $len 默认6位
     * @return string
     */
    public function createSmsCode($len = 6) {
        $chars = "0123456789";
        $str = "";
        for ($i = 0; $i < $len; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * 获取验证码
     * @return string
     */
    public function getSmsCode() {
        return $this->message;
    }

    /**
     * 获取发送时间戳
     * $return int
     */
    public function getSendTimestamp() {
        return $this->timestamp;
    }

    /**
     * 获取场景ID
     * @return int 场景ID
     */
    public function getSceneType(){
        return $this->sceneType;
    }
}