<?php
/**
 * ----------------------
 * CloopenVoiceSms.php
 *
 * User: jian0307@icloud.com
 * Date: 2015/7/2
 * Time: 15:27
 * ----------------------
 */
namespace Lib\SmsLib\SmsCloopen;

use Lib\SmsLib\BaseSms;

/**
 * 容联*云通讯语音标准验证码接口
 * http://www.yuntongxun.com
 * @see http://www.yuntongxun.com/activity/smsIdentifying#tiyan
 * Class CloopenVoiceSms
 */
class CloopenVoiceSms extends BaseCloopen
{
    //播放次数，1－3次
    private $playTimes = 3;
    //语音验证码状态通知回调地址，云通讯平台将向该Url地址发送呼叫结果通知
    private $respUrl;
    //语言类型。取值en（英文）、zh（中文），默认值zh
    private $lang = 'zh';
    //第三方私有数据
    private $userData;
    //欢迎提示音，在播放验证码语音前播放此内容（语音文件格式为wav）
    private $welcomePrompt;
    //语音验证码的内容全部播放此节点下的全部语音文件
    private $playVerifyCode;
    //显示主叫号码，显示权限由服务侧控制。
    private $displayNum;

    public function setConf($config)
    {
        parent::setConf($config);

        $this->playTimes = isset($config['playTimes']) ? $config['playTimes'] : 3;
        $this->respUrl = isset($config['respUrl']) ? $config['respUrl'] : null;
        $this->lang = isset($config['lang']) ? $config['lang'] : 'zh';
        $this->userData = isset($config['userData']) ? $config['userData'] : null;
        $this->welcomePrompt = isset($config['welcomePrompt']) ? $config['welcomePrompt'] : null;
        $this->playVerifyCode = isset($config['playVerifyCode']) ? $config['playVerifyCode'] : null;
    }

    /**
     * 发送语音验证码
     * @param string $mobile 接收号码
     * @param null $message 验证码内容，为数字和英文字母，不区分大小写，长度4-8位
     * @param int $sceneType 场景类型
     * @return mixed|null|\SimpleXMLElement
     */
    public function send($mobile, $message = null, $sceneType = 1)
    {
        //主帐号鉴权信息验证，对必选参数进行判空。
        $auth = $this->accAuth();
        if ($auth != "") {
            return $auth;
        }
        // 拼接请求包体
        if ($this->body_type == "json") {
            $bodyAry = array(
                'appId'=>$this->app_id,
                'verifyCode'=>$message,
                'playTimes'=>$this->playTimes,
                'to'=>$mobile,
                'respUrl'=>$this->respUrl,
                'displayNum'=>$this->displayNum,
                'lang'=>$this->lang,
                'userData'=>$this->userData,
                'welcomePrompt'=>$this->welcomePrompt,
                'playVerifyCode' => $this->playVerifyCode
            );
            $body = json_encode($bodyAry);
        } else {
            $body="<VoiceVerify>
                    <appId>$this->app_id</appId>
                    <verifyCode>$message</verifyCode>
                    <playTimes>$this->playTimes</playTimes>
                    <to>$mobile</to>
                    <respUrl>$this->respUrl</respUrl>
                    <displayNum>$this->displayNum</displayNum>
                    <lang>$this->lang</lang>
                    <userData>$this->userData</userData>
					<welcomePrompt>$this->welcomePrompt</welcomePrompt>
					<playVerifyCode>$this->playVerifyCode</playVerifyCode>
                  </VoiceVerify>";
        }
        // 大写的sig参数
        $sig =  strtoupper(md5($this->account_sid . $this->account_token . $this->timestamp_));
        // 生成请求URL
        $url="https://$this->server_ip:$this->server_port/$this->soft_version/Accounts/$this->account_sid/Calls/VoiceVerify?sig=$sig";
        // 生成授权：主帐户Id + 英文冒号 + 时间戳。
        $authen = base64_encode($this->account_sid . ":" . $this->timestamp_);
        // 生成包头
        $header = array("Accept:application/$this->body_type","Content-Type:application/$this->body_type;charset=utf-8","Authorization:$authen");
        // 发送请求
        $result = $this->curl_post($url, $body, $header);
        if ($this->body_type=="json") {//JSON格式
            $datas=json_decode($result);
        } else { //xml格式
            $datas = simplexml_load_string(trim($result, " \t\n\r"));
        }
        return $datas;
    }
}
