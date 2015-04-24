<?php
/**
 * ----------------------
 * TemplateSms.class.php
 * 
 * User: jian0307@icloud.com
 * Date: 2015/4/17
 * Time: 17:52
 * ----------------------
 */

namespace Lib\SmsLib\Sms189;

/**
 * Class TemplateSms
 * 天翼开放平台-模板短信套餐
 *
 * @see http://open.189.cn/index.php?m=api&c=index&a=show&id=858
 * @see http://open.189.cn/index.php?m=api&c=index&a=show&id=873
 * @package Lib\SmsLib\Sms189
 */
class TemplateSms extends BaseSms189 {

    /**
     * 短信模板ID，到短信模板申请页面查看
     * @var
     */
    private $template_id;

    /**
     * 验证码模板
     * @var
     */
    private $template_param_tpl;

    public function __construct() {
        parent::__construct();

        $this->grant_type = GrantType::CLIENT_CREDENTIALS;
        $this->send_url = 'http://api.189.cn/v2/emp/templateSms/sendSms';
    }

    /**
     * 设置配置
     * @param $config
     * @example
     * //平台设置
     * array(
     *       //类型为天翼模板方式
     *       'smsType'   => 'sms189_template',
     *       //App Id
     *       'app_id'    => '815014150000040983',
     *       //App Secret
     *       'app_secret'=> 'a0ea9692c603631b05f3a18362ec85e4',
     *       //模板ID
     *       'template_id' => xxx,
     *       //短信模板
     *       'template_param_tpl' => {"code":"%s","timeout":"%s"}
     *   )
     */
    public function setConf($config) {
        parent::setConf($config);

        $this->template_id = $config['template_id'];
        $this->template_param_tpl = $config['template_param_tpl'];
    }

    /**
     * 下发短信
     * @param $mobile
     * @param null $message
     * @param int $sceneType
     * @return string
     */
    public function send($mobile,$message = null,$sceneType = 1) {
        parent::send($mobile,$message,$sceneType);

        $time = date('Y-m-d H:i:s',$this->timestamp);
        $access_token = $this->getAccessKey();
        $template_param = sprintf($this->template_param_tpl,$message,'2');
        $post_data = array(
            'app_id'            => $this->app_id,
            'access_token'      => $access_token,
            'template_id'       => $this->template_id,
            'template_param'    => $template_param,
            'acceptor_tel'      => $this->mobile,
            'timestamp'         => $time
        );
        $require_str = urldecode(http_build_query($post_data));
        $response_str = $this->curl_post($this->send_url,$require_str);
        $res = json_decode($response_str);
        //{"res_code": "0","res_message": "Success","identifier": "000000001"}
        $this->response->code = $res->res_code;
        $this->response->message = in_array($res->res_code,ErrorCode::$_ERROR_NO_) ? ErrorCode::$_ERROR_NO_[$res->res_code] : '未知错误';
        $this->response->data = $res;
        return $this->response;
    }
}