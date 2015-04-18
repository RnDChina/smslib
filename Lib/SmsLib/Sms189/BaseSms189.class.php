<?php
/**
 * ----------------------
 * BaseSms189.class.php
 * 
 * User: jian0307@icloud.com
 * Date: 2015/4/18
 * Time: 23:32
 * ----------------------
 */

namespace Lib\SmsLib\Sms189;


use Lib\SmsLib\BaseSms;

/**
 * 天翼短信平台基类
 *
 * @package Lib\SmsLib\Sms189
 */
class BaseSms189 extends BaseSms {

    /**
     * 应用ID
     * @var string
     */
    protected $app_id = '';

    /**
     * 应用的密钥
     * @var string
     */
    protected $app_secret = '';

    /**
     * 时间戳，格式为：yyyy-MM-dd hh:mm:ss
     * @var string
     */
    protected $timestamp='';

    /**
     * 请求地址
     * @var string
     */
    protected $send_url = '';

    /**
     * 授权模式
     * @var string
     */
    protected $grant_type='';

    /**
     * 获取访问令牌的URL
     * @var string
     */
    protected $access_token_url = 'https://oauth.api.189.cn/emp/oauth2/v3/access_token';

    /**
     * 配置
     * @param $config
     */
    public function setConf($config) {
        parent::setConf($config);

        $this->app_id = $config['app_id'];
        $this->app_secret = $config['app_secret'];
    }

    /**
     * 下发验证码
     * @param $mobile
     * @param null $message
     * @return string
     */
    public function send( $mobile,$message = null) {
        parent::send($mobile,$message);
    }

    /**
     * 获取访问令牌
     *
     * @see http://open.189.cn/index.php?m=content&c=index&a=lists&catid=62
     * @return string
     */
    protected function getAccessKey() {
        $param['grant_type']= "grant_type=".$this->grant_type;
        $param['app_id'] = "app_id=".$this->app_id;
        $param['app_secret'] = "app_secret=".$this->app_secret;
        $plaintext = implode("&",$param);
        $result = $this->curl_post($this->access_token_url,$plaintext);
        $res = json_decode($result);
        /*{
            //获取到的访问令牌（AT或UIAT）
            "access_token":"USER_INDEPENDENT_ACCESS_TOKEN",
            //访问令牌的有效期（以秒为单位）
            "expires_in":9999,
            //标准返回码。返回0表示成功；其他表示调用出错或异常。
            “res_code”:0,
            //返回码描述信息
            “res_message”:”Success”
        }*/
        return $res->access_token;
    }

    /**
     * GET请求
     * @param string $url
     * @return mixed
     */
    protected function curl_get($url=''){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * POST请求
     * @param string $url
     * @param string $postdata
     * @return mixed
     */
    protected function curl_post($url='', $postdata=''){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  FALSE);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}