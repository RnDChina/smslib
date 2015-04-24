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
use Lib\SmsLib\ResponseData;
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
     * 平台类型
     * @var
     */
    private $smsType;

    /**
     * 使用场景类型 1、用户注册 2、找回密码
     * @var
     */
    private $sceneType;

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

    /**
     * 缓存前缀，缓存到文件用来教研验证码，和控制频繁发送
     * @var
     */
    private $cacheSmsPrefix = 'cache_sms_';

    /**
     * 生成实例
     */
    static public function createInstance() {
        return new SmsProxy();
    }

    /**
     * 配置
     * @param $config
     * @throws Exception
     * @return null
     */
    public function setConf($config) {
        $this->smsConfig = $config;
        $this->smsType = $this->smsConfig['smsType'];
        switch($this->smsType) {
            case self::_SMS189_CUSTOM_:
                /*  array(
                        //类型为天翼自定义验证码方式
                        'smsType'   => 'sms189_custom',
                        //App Id
                        'app_id'    => '815014150000040983',
                        //App Secret
                        'app_secret'=> 'a0ea9692c603631b05f3a18362ec85e4'
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
                       'app_id'    => '815014150000040983',
                       //App Secret
                       'app_secret'=> 'a0ea9692c603631b05f3a18362ec85e4',
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
                        'username' => 'sms135006',
                        //短信系统平台用户登录密码
                        'password' => 'G95400SS',
                        //在接口触发页面可以获取
                        'secret_key' => 'c138e82cb43592b21e5877d3135ab227',
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
     * @param string $mobile 手机号
     * @param string $message 验证码
     * @param int $sceneType 场景ID
     * @return ResponseData 返回ResponseData格式数据
     */
    public function send($mobile,$message = null,$sceneType = 0) {
        $response = new ResponseData();
        //验证手机格式
        if (!preg_match('/^1[\d]{10}$/', $mobile)){
            $response->code = ResponseData::__MOBILE_ERROR__;
            $response->message = "手机格式错误";
            $response->data = null;
            return $response;
        }
        $cacheId = $this->cacheSmsPrefix.$mobile.'_'.$sceneType;
        //短信频繁度验证避免浪费短信包同一号码30秒只能发1次
        $verifySms = json_decode(S($cacheId));
        if( $verifySms && $verifySms->ctime > time() - 30) {
            $response->code = ResponseData::__REQUEST_ERROR__;
            $response->message = "请求太频繁";
            $response->data = null;
            return $response;
        }
        //dump($mobile.$message.$sceneType);exit;
        $response = $this->smsEntity->send($mobile,$message,$sceneType);
        if( $response && $response->code == 0 ) {
            $jsonAry = array(
                'mobile' => $mobile,
                'message' => $message,
                'sceneType' => $this->getSceneType(),
                'ctime' => $this->getSendTimestamp(),
                'ip' => get_client_ip()
            );
            //发送成功缓存验证码信息，只缓存5分钟
            S($cacheId,json_encode($jsonAry),300);
        }
        return $response;
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

    /**
     * 获取生成时间戳
     * @return int
     */
    public function getSendTimestamp() {
        return $this->smsEntity->getSendTimestamp();
    }

    /**
     * 获取场景ID
     * @return int
     */
    public function getSceneType() {
        return $this->smsEntity->getSceneType();
    }

    /**
     * 检查短信验证码是否有效
     * @param string $mobile   手机号
     * @param string $message  验证码
     * @param int $sceneType 场景类型 1 注册 2 找回密码
     * @param int $timeout 超时时间，单位秒，默认120秒（2分钟）
     * @return bool
     */
    public function chkSmsVerify($mobile,$message,$sceneType,$timeout = 120) {
        $cacheId = $this->cacheSmsPrefix.$mobile.'_'.$sceneType;
        $verifySms = json_decode(S($cacheId));
        if( $verifySms ) {
            if( $message == $verifySms->message ) {
                //验证码超时
                if($verifySms->ctime + $timeout < time()) {
                    S($cacheId,null);
                    return false;
                }
                //验证通过，销毁缓存的验证码
                else {
                    S($cacheId,null);
                    return true;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}