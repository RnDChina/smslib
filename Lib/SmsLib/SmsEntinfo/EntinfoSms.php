<?php
/**
 * ----------------------
 * EntinfoSms.php
 *
 * User: jian0307@icloud.com
 * Date: 2015/6/8
 * Time: 17:10
 * ----------------------
 */

namespace Lib\SmsLib\SmsEntinfo;

use Lib\SmsLib\BaseSms;

class EntinfoSms extends BaseSms
{
    //替换成您自己的序列号
    private $_sn = '';
    //密码
    private $_password = '';
    //扩展码
    private $_ext = '';
    //应用名，做后缀
    private $_appName = '';
    //唯一标识，最长18位，默认空 如果空返回系统生成的标识串 如果传值保证值唯一 成功则返回传入的值
    private $_rrid = '';
    //定时时间 格式为2011-6-29 11:09:21
    private $_stime = '';

    /**
     * 配置
     * @param $config
     * @throws \Lib\SmsLib\Exception
     * @example
     * array(
     *  'sn' => 'xxx',
     *  'password' => 'xxx',
     *  'ext' => '',
     *  'rrid' => '',
     *  'stime' => '',
     *  'appName' => '多美淘'
     * );
     */
    public function setConf($config)
    {
        parent::setConf($config);

        $this->_sn = $config['sn'];
        $this->_password = $config['password'];
        $this->_ext = $config['ext'];
        $this->_appName = $config['appName'];
        $this->_rrid = $config['rrid'];
        $this->_stime = $config['stime'];
    }

    public function send($mobile, $message = null, $sceneType = 1)
    {
        parent::send($mobile, $message, $sceneType);

        $flag = 0;
        $argv = array (
            //替换成您自己的序列号
            'sn'=>$this->_sn,
            //此处密码需要加密 加密方式为 md5(sn+password) 32位大写
            'pwd'=>strtoupper(md5($this->_sn.$this->_password)),
            //手机号 多个用英文的逗号隔开 post理论没有长度限制.推荐群发一次小于等于10000个手机号
            'mobile'=>"{$mobile}",
            //短信内容
            'content'=>urlencode("{$this->message}[$this->_appName]"),
            //扩展码
            'ext'=>$this->_ext,
            //唯一标识，默认空 如果空返回系统生成的标识串,如果传值保证值唯一,成功则返回传入的值
            'rrid'=>$this->_rrid,
            //定时时间 格式为2011-6-29 11:09:21
            'stime'=>$this->_stime
        );
        $params = '';
        foreach ($argv as $key => $value) {
            if ($flag != 0) {
                $params .= "&";
                $flag = 1;
            }
            $params.= $key."=";
            $params.= urlencode($value);
            $flag = 1;
        }
        $length = strlen($params);
        //创建socket连接
        $fp = fsockopen("sdk2.entinfo.cn", 8060, $errno, $errstr, 10);
        if (!$fp) {
            return false;
        }
        //构造post请求的头
        $header = "POST /webservice.asmx/mdSmsSend_u HTTP/1.1\r\n";
        $header .= "Host:sdk2.entinfo.cn\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: ".$length."\r\n";
        $header .= "Connection: Close\r\n\r\n";
        //添加post的字符串
        $header .= $params."\r\n";
        //发送post的数据
        fputs($fp, $header);
        $inheader = 1;
        while (!feof($fp)) {
            $line = fgets($fp, 1024); //去除请求包的头只显示页面的返回数据
            if ($inheader && ($line == "\n" || $line == "\r\n")) {
                $inheader = 0;
            }
            if ($inheader == 0) {
                // echo $line;
            }
        }
        $line = str_replace("<string xmlns=\"http://tempuri.org/\">", "", $line);
        $line = str_replace("</string>", "", $line);
        //$result = explode("-",$line);
        $this->response->code =  $line > 1 ? 0 : $line;
        $this->response->message = $this->response->code == 0 ? '发送成功' : ErrorCode::$_ERROR_NO_[$this->response->code];
        $this->response->data = null;
        return $this->response;
    }
}
