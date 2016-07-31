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
use Exception;
use Lib\SmsLib\ISms;
use Lib\SmsLib\ResponseData;
use Lib\SmsLib\Sms189\CustomSms;
use Lib\SmsLib\Sms189\TemplateSms;
use Lib\SmsLib\SmsCloopen\CloopenSms;
use Lib\SmsLib\SmsCloopen\CloopenVoiceSms;
use Lib\SmsLib\SmsEdmcn\EdmSms;
use Lib\SmsLib\SmsEntinfo\EntinfoSms;

/**
 * 短信平台代理
 *
 * @package Lib
 */
class SmsProxy implements ISms
{
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
     * 漫道科技短信
     */
    const _SMSENTINFO_ = 'smsentinfo';

    /**
     * 容联云通讯
     */
    const _SMSCLOOPEN_ = 'sms_cloopen';

    /**
     * 容联云通讯语音验证码
     */
    const _SMSVOICECLOOPEN_ = 'sms_voice_cloopen';

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
    static public function createInstance()
    {
        return new SmsProxy();
    }

    /**
     * 配置
     * @param $config
     * @throws Exception
     * @return null
     */
    public function setConf($config)
    {
        $this->smsConfig = $config;
        $this->smsType = $this->smsConfig['smsType'];
        switch($this->smsType) {
            case self::_SMS189_CUSTOM_:
                $this->smsEntity = new CustomSms();
                $this->smsEntity->setConf($config);
                break;
            case self::_SMS189_TEMPLATE_:
                $this->smsEntity = new TemplateSms();
                $this->smsEntity->setConf($config);
                break;
            case self::_SMSEMD_:
                $this->smsEntity = new EdmSms();
                $this->smsEntity->setConf($config);
                break;
            case self::_SMSENTINFO_:
                $this->smsEntity = new EntinfoSms();
                $this->smsEntity->setConf($config);
                break;
            case self::_SMSCLOOPEN_:
                $this->smsEntity = new CloopenSms();
                $this->smsEntity->setConf($config);
                break;
            case self::_SMSVOICECLOOPEN_:
                $this->smsEntity = new CloopenVoiceSms();
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
    public function send($mobile,$message = null,$sceneType = 0)
    {
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
                'mobile' => $this->getMobile(),
                'message' => $this->getSmsCode(),
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
    public function createSmsCode( $len = 6 )
    {
        return $this->smsEntity->createSmsCode( $len );
    }

    /**
     * 获取手机号
     * @return string
     */
    public function getMobile()
    {
        return $this->smsEntity->getMobile();
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
    public function getSendTimestamp()
    {
        return $this->smsEntity->getSendTimestamp();
    }

    /**
     * 获取场景ID
     * @return int
     */
    public function getSceneType()
    {
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
    public function chkSmsVerify($mobile,$message,$sceneType,$timeout = 120)
    {
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
