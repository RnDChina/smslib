<?php
/**
 * ----------------------
 * EdmSms.class.php
 * 
 * User: jian0307@icloud.com
 * Date: 2015/4/18
 * Time: 10:02
 * ----------------------
 */

namespace Lib\SmsLib\SmsEdmcn;


use Lib\SmsLib\BaseSms;

/**
 * 美橙短信平台
 * @package Lib\SmsLib\SmsEdmcn
 */
class EdmSms extends BaseSms {

    /**
     * 短信系统平台用户名即管理名称
     * @var string
     */
    private $username = '';

    /**
     * 短信系统平台用户登录密码
     * @var string
     */
    private $password = '';

    /**
     * 认证密钥
     * @var string
     */
    private $secret_key = '';

    /**
     * 短信模板
     * @var string
     */
    private $content_tpl = '';

    /**
     * 短信后缀签名，需要中文输入法的左右括号，签名字数要在3-8个字 例：【公司名称】 短信内容 = 短信正文+短信签名
     * @var string
     */
    private $suffix = '';

    /**
     * 接口地址
     * @var string
     */
    private $send_url = 'http://sms.edmcn.cn/api/cm/trigger_mobile.php';


    /**
     * 配置
     * @param $config
     * @example
     * array(
     *  'username' => 'xxx',
     *  'password' => 'xxx',
     *  'secret_key' => 'xxx',
     *  'content_tpl'  => "您正在注册华友汇，本次验证码为:%s，两分钟内有效！【%s】",
     *  'suffix' => '华友汇'
     * );
     */
    public function setConf($config) {
        parent::setConf($config);

        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->secret_key = $config['secret_key'];
        $this->content_tpl = $config['content_tpl'];
        $this->suffix = $config['suffix'];
    }

    /**
     * 发送验证码
     * @param $mobile
     * @param $message
     * @return string
     */
    public function send($mobile,$message = null) {
        parent::send($mobile,$message);

        $content = sprintf($this->content_tpl,$message,$this->suffix);
        $content = urlencode($content);
        //当前格林尼治时间戳
        $time = $this->timestamp - 8 * 3600;
        $authKey = md5($this->username.$time.md5($this->password).$this->secret_key);

        $options = array(
            'username' => $this->username,
            'time' => $time,
            'mobile' => $this->mobile,
            'content' => $content,
            'authkey' => $authKey
        );

        return $this->triggerSms($options);
    }

    /**
     * 触发下发验证码
     * @param array $data
     * @return string
     */
    private function triggerSms(Array $data = array()) {
        /*反馈信息
		$msg_arr = array(1 => '发送成功',2 => '参数不正确',3 => '验证失败',4 => '用户名或密码错误',
						 5 => '数据库操作失败',6 => '余额不足',7 => '内容不符合格式',8 => '频率超限',
						 9 => '接口超时',10 => '后缀签名长度超过限制');
		*/
        $row = parse_url($this->send_url);
        $host = $row['host'];
        $port = isset($row['port']) ? $row['port']:80;
        $file = $row['path'];
        $post = '';
        while (list($k,$v) = each($data)) $post .= $k."=".$v."&";
        $post = substr( $post , 0 , -1 );
        $len = strlen($post);
        $fp = @fsockopen($host ,$port, $errno, $errstr, 10);
        if(!$fp) return "connect error";
        $receive = '';
        $out = "POST $file HTTP/1.0\r\n";
        $out .= "Host: $host\r\n";
        $out .= "Content-type: application/x-www-form-urlencoded\r\n";
        $out .= "Connection: Close\r\n";
        $out .= "Content-Length: $len\r\n\r\n";
        $out .= $post;
        fwrite($fp, $out);
        while (!feof($fp)) {
            $receive .= fgets($fp, 128);
        }
        fclose($fp);
        $receive = explode("\r\n\r\n",$receive);
        unset($receive[0]);
        return implode("",$receive);
    }
}