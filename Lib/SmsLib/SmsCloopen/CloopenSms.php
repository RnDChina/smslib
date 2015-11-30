<?php
namespace Lib\SmsLib\SmsCloopen;

/**
 * ----------------------
 * CloopenSms.php
 * 
 * User: jian0307@icloud.com
 * Date: 2015/6/9
 * Time: 15:27
 * ----------------------
 */
use Lib\SmsLib\BaseSms;

/**
 * 容联*云通讯模板短信
 * http://www.yuntongxun.com
 * @see http://docs.yuntongxun.com/index.php/%E5%BC%80%E5%8F%91%E6%8C%87%E5%8D%97:%E7%9F%AD%E4%BF%A1%E9%AA%8C%E8%AF%81%E7%A0%81/%E9%80%9A%E7%9F%A5
 * @see http://docs.yuntongxun.com/index.php/%E6%A8%A1%E6%9D%BF%E7%9F%AD%E4%BF%A1
 * Class CloopenSms
 */
class CloopenSms extends BaseCloopen
{
    //短信模板ID，到短信模板申请页面查看
    private $template_id;

    /**
     * 配置
     *
     * @param $config
     * array(
     *      'account_sid' => '8a48b5514dd25566014dd776124a0429',
     *      'account_token' => '4eb09d93e6a346128dfb59670cae009c',
     *      'app_id' => '8a48b5514dd25566014dd7765524042b',
     *      'is_sandbox' => true,
     *      'template_id' => 1
     *   )
     */
    public function setConf($config)
    {
        parent::setConf($config);

        $this->template_id = $config['template_id'];
    }

    /**
     * 发送模板短信
     * @param string $mobile 单个手机号或者多个手机号以逗号分割
     * @param null $message 数组，一条信息或者多条信息,约束一下格式：[‘验证码’，‘超时时间’]
     * @param int $sceneType 场景ID
     * @return stdClass|mixed|\SimpleXMLElement
     */
    public function send($mobile, $message = null, $sceneType = 1)
    {
        parent::send($mobile, $message, $sceneType);

        //主帐号鉴权信息验证，对必选参数进行判空。
        $response = $this->accAuth();
        if (!empty($response)) {
            return $response;
        }
        // 拼接请求包体
        if ($this->body_type == "json") {
            $data="";
            for ($i=0; $i<count($message); $i++) {
                $data = $data. "'".$message[$i]."',";
            }
            $body= "{'to':'$mobile','templateId':'$this->template_id','appId':'$this->app_id','datas':[".$data."]}";
        } else {
            $data="";
            for ($i=0; $i<count($message); $i++) {
                $data = $data. "<data>".$message[$i]."</data>";
            }
            $body="<TemplateSMS>
                    <to>$mobile</to>
                    <appId>$this->app_id</appId>
                    <templateId>$this->template_id</templateId>
                    <datas>".$data."</datas>
                  </TemplateSMS>";
        }
        //$this->showlog("request body = ".$body);
        // 大写的sig参数
        $sig =  strtoupper(md5($this->account_sid . $this->account_token . $this->timestamp_));
        // 生成请求URL
        $url="https://$this->server_ip:$this->server_port/$this->soft_version/Accounts/$this->account_sid/SMS/TemplateSMS?sig=$sig";
        //$this->showlog("request url = ".$url);
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->account_sid . ":" . $this->timestamp_);
        // 生成包头
        $header = array("Accept:application/$this->body_type","Content-Type:application/$this->body_type;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = $this->curlPost($url, $body, $header);
        //$this->showlog("response body = ".$result);
        if ($this->body_type == "json") {//JSON格式
            $datas = json_decode($result);
        } else { //xml格式
            $datas = simplexml_load_string(trim($result, " \t\n\r"));
        }
        //重新装填数据
        if ($datas->statusCode == '000000') {
            $this->response->code = 0;
            $this->response->message = ErrorCode::$_ERROR_NO_[(string)$datas->statusCode];
            $this->response->data = $datas->templateSMS;
        } else {
            $this->response->code = (string) $datas->statusCode;
            $this->response->message = isset(ErrorCode::$_ERROR_NO_[$this->response->code]) ? ErrorCode::$_ERROR_NO_[$this->response->code] : '未知错误';
            $this->response->data = $datas->templateSMS;
        }
        return $this->response;
    }

    /**
     * 获取验证码内容
     * @return mixed
     */
    public function getSmsCode()
    {
        return $this->message[0];
    }
}
