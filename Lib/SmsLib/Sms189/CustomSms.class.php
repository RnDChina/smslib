<?php
/**
 * ----------------------
 * CustomSms.class.php
 * 
 * User: jian0307@icloud.com
 * Date: 2015/4/17
 * Time: 17:51
 * ----------------------
 */

namespace Lib\SmsLib\Sms189;


use Think\Exception;

/**
 * Class CustomSms
 * 天翼开放平台-自定义验证码套餐
 *
 * @see http://open.189.cn/index.php?m=api&c=index&a=show&id=850
 * @package Lib\SmsLib\Sms189
 */
class CustomSms extends BaseSms189 {

    /**
     * 获取信任码地址
     * @var string
     */
    protected $randcode_token_url;

    /**
     * 验证码失效时间,单位分钟，默认是2分钟
     * @var int
     */
    protected $exp_time = 5;

    public function __construct() {
        parent::__construct();

        $this->grant_type = GrantType::CLIENT_CREDENTIALS;
        $this->send_url = 'http://api.189.cn/v2/dm/randcode/sendSms';
        $this->randcode_token_url = 'http://api.189.cn/v2/dm/randcode/token';
    }

    /**
     * 设置配置
     * @param $config
     * @throws Exception
     * @example
     * //短信验证码平台设置
     * array(
     *       //类型为天翼自定义验证码方式
     *       'smsType'   => 'sms189_custom',
     *       //App Id
     *       'app_id'    => '815014150000040983',
     *       //App Secret
     *       'app_secret'=> 'a0ea9692c603631b05f3a18362ec85e4'
     *   )
     */
    public function setConf($config) {
        parent::setConf($config);
    }

    /**
     * 发送消息
     * @param string $mobile
     * @param string $message
     * @param int $sceneType
     * @return string
     */
    public function send( $mobile,$message = null,$sceneType = 1) {
        parent::send($mobile,$message,$sceneType);

        $time = date('Y-m-d H:i:s',$this->timestamp);
        $access_token = $this->getAccessKey();
        $token = $this->getRandcodeToken($access_token);
        $post_data = array(
            'app_id'        => $this->app_id,
            'access_token'  => $access_token,
            'timestamp'     => $time,
            'token'         => $token,
            'phone'         => $this->mobile,
            'randcode'      => $this->message
        );
        if(!empty($this->exp_time)) {
            $post_data['exp_time'] = $this->exp_time;
        }
        ksort($post_data);
        $require_str = urldecode(http_build_query($post_data));
        $post_data['sign'] = rawurlencode(base64_encode(hash_hmac("sha1", $require_str, $this->app_secret, $raw_output=True)));
        ksort($post_data);
        $require_str = urldecode(http_build_query($post_data));
        $response_str = $this->curl_post($this->send_url,$require_str);
        $res = json_decode($response_str);
        //{"res_code":0,"identifier":"Vr1128\","create_at":"1429536601"}
        $this->response->code = $res->res_code;
        $this->response->message = in_array($res->res_code,ErrorCode::$_ERROR_NO_) ? ErrorCode::$_ERROR_NO_[$res->res_code] : '未知错误';
        $this->response->data = $res;
        return $this->response;
    }

    /**
     * 获取信任码
     * @param $access_token
     * @return mixed
     * @see http://open.189.cn/index.php?m=api&c=index&a=show&id=498
     */
    protected function getRandcodeToken($access_token) {
        $get_data = array(
            'app_id' => $this->app_id,
            'access_token' => $access_token,
            'timestamp' => date('Y-m-d H:i:s',$this->timestamp)
        );
        ksort($get_data);
        $request_str = urldecode(http_build_query($get_data));
        $get_data['sign'] = rawurlencode(base64_encode(hash_hmac("sha1", $request_str, $this->app_secret, $raw_output=True)));
        ksort($get_data);
        $request_str = urldecode(http_build_query($get_data));
        $this->randcode_token_url .= '?'.$request_str;
        $response = $this->curl_get($this->randcode_token_url);
        $resObj = json_decode($response);
        return $resObj->token;
    }
}