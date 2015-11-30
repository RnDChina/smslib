# smslib
短信验证码库 for ThinkPHP

##使用方法

* 1、将Lib放在Application/Common目录中，作为公共模块库

* 2、在Application/Common/Conf/config.php中添加

```php
//注册新的命名空间，存放自定义类库
'AUTOLOAD_NAMESPACE' => array(
    'Lib'     => APP_PATH.'/Common/Lib',
),

//天翼平台自定义验证码套餐配置
'SMS_189CUSTOM_CONFIG' => array(
    //类型为天翼自定义验证码方式
    'smsType'   => 'sms189_custom',
    //App Id
    'app_id'    => 'xxx',
    //App Secret
    'app_secret'=> 'xxxxxxx'
),

//天翼平台模板短信套餐配置
'SMS_189TEMPLATE_CONFIG' => array(
    //类型为天翼自定义验证码方式
    'smsType'   => 'sms189_template',
    //App Id
    'app_id'    => 'xxx',
    //App Secret
    'app_secret'=> 'xxxxxxx',
    //短信模板ID
    'template_id' => 1,
    //短信模板
    'template_param_tpl' => '{"code":"%s","timeout":"%s"}'
),

//美橙短信验证码配置
'SMS_EDMCN_CONFIG' => array(
    //类型为美橙短信
    'smsType' => 'smsemd',
    //短信系统平台用户名即管理名称
    'username' => 'xxxxxxx',
    //短信系统平台用户登录密码
    'password' => 'xxxxxxx',
    //在接口触发页面可以获取
    'secret_key' => 'xxxxxxx',
    //短信正文模板
    'content_tpl' => "您正在注册XXX，本次验证码为:%s，两分钟内有效！【%s】",
    //短信后缀签名，需要中文输入法的左右括号，签名字数要在3-8个字 例：【公司名称】 短信内容 = 短信正文+短信签名
    'suffix' => 'XXX'
),

//漫道科技短信验证码配置
'SMS_ENTINFO_CONFIG' => array(
    'smsType'   => 'smsentinfo',
    'sn' => 'xxx-xxx-xxx-xxxxx',
    'password' => 'xxxxxx',
    'ext' => '',
    'rrid' => '',
    'stime' => '',
    'appName' => 'xxx'
),

//容联云通讯短信验证码配置
'SMS_CLOOPEN_CONFIG' => array(
  'account_sid' => 'XXXXXXXXXXXXXXXXXXXXXXXXXX',
  'account_token' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
  'app_id' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXx',
  'is_sandbox' => true,
  'template_id' => 1
),

//容联云通讯语音短信验证码配置
'SMS_VOICECLOOPEN_CONFIG' => array(
     'account_sid' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
     'account_token' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
     'app_id' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
     'is_sandbox' => true,
 )
```

* 3、调用

```php
$stringUtil = new \Org\Util\String();
$sms = SmsProxy::createInstance();
//天翼自定义验证码套餐
//$sms->setConf(C('SMS_189CUSTOM_CONFIG'));
//天翼模板短信套餐
//$sms->setConf(C('SMS_189TEMPLATE_CONFIG'));
//美橙短信验证码
$this->sms->setConf(C('SMS_EDMCN_CONFIG'));
//漫道科技
$this->sms->setConf(C('SMS_ENTINFO_CONFIG'));
//容联云通讯
$this->sms->setConf(C('SMS_CLOOPEN_CONFIG'));
//容联云通讯语音
$this->sms->setConf(C('SMS_VOICECLOOPEN_CONFIG'));

$response = $this->sms->send($mobile,$stringUtil->randString(6,1),1);
if( !$response ){
    echo "send sms code failed!";
    exit;
}
echo json_encode($response);
```

* 4、验证

```php
$code = I("param.code");
$mobile = I('param.mobile');
if (!preg_match('/^1[\d]{10}$/', $mobile)){
    echo "手机号格式不正确！";
    exit;
}
$res = $this->sms->chkSmsVerify($mobile,$code,1);
if( $res ) {
    echo "Success!";
} else {
    echo "Failed!";
}
```
