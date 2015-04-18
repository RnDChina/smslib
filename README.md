# smslib
短信验证码库 for ThinkPHP

##使用方法

1、将Lib放在Application/Common目录中，作为公共模块库
2、在Application/Common/Conf/config.php中添加

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
    'content_tpl' => "您正在注册华友汇，本次验证码为:%s，两分钟内有效！【%s】",
    //短信后缀签名，需要中文输入法的左右括号，签名字数要在3-8个字 例：【公司名称】 短信内容 = 短信正文+短信签名
    'suffix' => '华友汇'
),
```

3、调用
```php
$sms = SmsProxy::getInstance();
$sms->setConf(C('SMS_189CUSTOM_CONFIG'));
//不传验证码，会默认生成6位随机数字作为验证码
$obj = $sms->send('手机号');
```
