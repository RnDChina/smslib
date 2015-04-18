<?php
/**
 * ----------------------
 * SmsProxy.class.php
 * 
 * User: jian0307@icloud.com
 * Date: 2015/4/17
 * Time: 17:28
 * ----------------------
 */

namespace Lib;
use Lib\SmsLib\ISms;
use Lib\SmsLib\Sms189\CustomSms;
use Lib\SmsLib\Sms189\TemplateSms;
use Lib\SmsLib\SmsEdmcn\EdmSms;
use Think\Exception;

/**
 * 短信平台代理
 *
 * @package Lib
 */
class SmsProxy implements ISms {
    //---------------平台类型--------------------
    /**
     * 天翼自定义验证码
     */
    const _SMS189_CUSTOM_ = 'sms189_custom';

    /**
     * 天翼模板短信
     */
    const _SMS189_TEMPLATE_ = 'sms189_template';

    /**
     * 美橙短信验证码
     */
    const _SMSEMD_= 'smsemd';

    /**
     * 短信平台配置
     * @var null
     */
    private $smsConfig = null;//

    /**
     * 短信平台实例
     * @var null
     */
    private $smsEntity = null;

    static private $_instance;

    private function __construct() {
    }

    public static function getInstance(){
        if(!(self::$_instance instanceof self)){
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * 配置
     * @param $config
     * @throws Exception
     * @return null
     */
    public function setConf($config) {
        $this->smsConfig = $config;
        $smsType = $this->smsConfig['smsType'];
        switch($smsType) {
            case self::_SMS189_CUSTOM_:
                /*  array(
                        //类型为天翼自定义验证码方式
                        'smsType'   => 'sms189_custom',
                        //App Id
                        'app_id'    => 'xxx',
                        //App Secret
                        'app_secret'=> 'xxxxxx'
                    )
                */
                $this->smsEntity = new CustomSms();
                $this->smsEntity->setConf($config);
                break;
            case self::_SMS189_TEMPLATE_:
                /*  array(
                       //类型为天翼模板短信方式
                       'smsType'   => 'sms189_template',
                       //App Id
                       'app_id'    => 'xxx',
                       //App Secret
                       'app_secret'=> 'xxxxxx',
                       //模板ID
                       'template_id' => xxx,
                       //短信模板
                       'template_param_tpl' => {"code":"%s","timeout":"%s"}
                   )
               */
                $this->smsEntity = new TemplateSms();
                $this->smsEntity->setConf($config);
                break;
            case self::_SMSEMD_:
                /*  array(
                        //短信系统平台用户名即管理名称
                        'username' => 'xxx',
                        //短信系统平台用户登录密码
                        'password' => 'xxxxxx',
                        //在接口触发页面可以获取
                        'secret_key' => 'xxxxxxxx',
                        //短信正文模板
                        'content_tpl' => "您正在注册华友汇，本次验证码为:%s，两分钟内有效！【%s】",
                        //短信后缀签名，需要中文输入法的左右括号，签名字数要在3-8个字 例：【公司名称】 短信内容 = 短信正文+短信签名
                        'suffix' => '华友汇'
                    )
                */
                $this->smsEntity = new EdmSms();
                $this->smsEntity->setConf($config);
                break;
            default:
                throw new Exception("不存在的短信平台类型");
                break;
        }
    }

    /**
     * 发送短信验证码
     * @param $mobile
     * @param $message
     * @return null
     */
    public function send($mobile,$message = null) {
        return $this->smsEntity->send($mobile,$message);
    }

    /**
     * 生成短信验证码
     * @param int $len
     * @return null
     */
    public function createSmsCode( $len = 6 ) {
        return $this->smsEntity->createSmsCode( $len );
    }

    /**
     * 获取验证码
     * @return string
     */
    public function getSmsCode() {
        return $this->smsEntity->getSmsCode();
    }

    private function __clone(){}
}