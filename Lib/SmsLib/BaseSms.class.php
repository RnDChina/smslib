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
     * @param $mobile
     * @param $message
     * @return string
     */
    public function send($mobile,$message = null) {
        if (!preg_match('/^1[\d]{10}$/', $mobile)){
            echo json_encode(array('status'=>'0','error'=>'手机号码格式错误'));
            exit;
        }
        $this->mobile = $mobile;
        $this->message = $message ? $message : $this->createSmsCode();
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
}