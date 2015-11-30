<?php
/**
 * ----------------------
 * BaseCloopen.php
 * 
 * User: jian0307@icloud.com
 * Date: 2015/7/2
 * Time: 12:52
 * ----------------------
 */
namespace Lib\SmsLib\SmsCloopen;

use Lib\SmsLib\BaseSms;

class BaseCloopen extends BaseSms
{
    //主账户Id,登陆云通讯网站后，可在“控制台-应用”中看到开发者主账号ACCOUNT SID。
    protected $account_sid;
    //主账号Token，登陆云通讯网站后，可在控制台-应用中看到开发者主账号AUTH TOKEN
    protected $account_token;
    //服务网关ip或url
    protected $server_ip;
    //服务网关端口
    protected $server_port;
    //应用Id，如果是在沙盒环境开发，请配置“控制台-应用-测试DEMO”中的APPID。如切换到生产环境，请使用自己创建应用的APPID。
    protected $app_id;
    //版本
    protected $soft_version = '2013-12-26';
    //时间戳
    protected $timestamp_;
    //包体格式，可填值：json 、xml
    protected $body_type = "json";
    //短信模板ID，到短信模板申请页面查看
    //protected $template_id;
    //是否是沙盒模式
    protected $is_sandbox = false;
    //沙盒环境（用于应用开发调试）
    protected $sandbox_url = 'sandboxapp.cloopen.com';
    //生产环境（用户应用上线使用）
    protected $product_url = 'app.cloopen.com';

    public function setConf($config)
    {
        parent::setConf($config);

        //设置主帐号
        $this->account_sid = $config['account_sid'];
        $this->account_token = $config['account_token'];
        $this->app_id = $config['app_id'];

        $this->timestamp_ = date("YmdHis");
        $this->is_sandbox = $config['is_sandbox'];
        //$this->template_id = $config['template_id'];
        $this->server_ip = $this->is_sandbox ? $this->sandbox_url : $this->product_url;
        $this->server_port = isset($config['server_port']) ? $config['server_port'] : '8883';
        $this->soft_version = isset($config['soft_version']) ? $config['soft_version'] : '2013-12-26';
        $this->body_type = isset($config['body_type']) ? $config['body_type'] : 'json';
    }

    public function send($mobile, $message = null, $sceneType = 1)
    {
        parent::send($mobile, $message, $sceneType);
    }

    /**
     * 主帐号鉴权
     */
    protected function accAuth()
    {
        if (empty($this->server_ip)) {
            $this->response->code = 172004;
            $this->response->message = ErrorCode::$_ERROR_NO_['172004'];
            $this->response->data = null;
            return $this->response;
        }
        if (empty($this->server_port)) {
            $this->response->code = 172005;
            $this->response->message = ErrorCode::$_ERROR_NO_['172005'];
            $this->response->data = null;
            return $this->response;
        }
        if (empty($this->soft_version)) {
            $this->response->code = 172013;
            $this->response->message = ErrorCode::$_ERROR_NO_['172013'];
            $this->response->data = null;
            return $this->response;
        }
        if (empty($this->account_sid)) {
            $this->response->code = 172006;
            $this->response->message = ErrorCode::$_ERROR_NO_['172006'];
            $this->response->data = null;
            return $this->response;
        }
        if (empty($this->account_token)) {
            $this->response->code = 172007;
            $this->response->message = ErrorCode::$_ERROR_NO_['172007'];
            $this->response->data = null;
            return $this->response;
        }
        if (empty($this->app_id)) {
            $this->response->code = 172012;
            $this->response->message = ErrorCode::$_ERROR_NO_['172012'];
            $this->response->data = null;
            return $this->response;
        }
    }

    /**
     * 发起HTTPS请求
     */
    protected function curlPost($url, $data, $header, $post = 1)
    {
        //初始化curl
        $ch = curl_init();
        //参数设置
        $res= curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, $post);
        if ($post) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        //连接失败
        if ($result == false) {
            if ($this->body_type == 'json') {
                $result = "{\"statusCode\":\"172001\",\"statusMsg\":\"网络错误\"}";
            } else {
                $result = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?><Response><statusCode>172001</statusCode><statusMsg>网络错误</statusMsg></Response>";
            }
        }
        curl_close($ch);
        return $result;
    }
}
