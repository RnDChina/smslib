<?php
/**
 * ----------------------
 * ErrorCode.php
 * 
 * User: jian0307@icloud.com
 * Date: 2015/6/9
 * Time: 14:50
 * ----------------------
 */
namespace Lib\SmsLib\SmsEntinfo;

class ErrorCode
{
    /**
     * Entinfo 短信平台错误码定义
     * @var array
     */
    public static $_ERROR_NO_ = array (
        '1'     => '没有需要取得的数据',
        '-2'    => '帐号或密码不正确',
        '-4'    => '余额不足',
        '-5'    => '数据格式错误',
        '-6'    => '参数有误',
        '-7'    => '权限受限',
        '-8'    => '流量控制错误',
        '-9'    => '扩展码权限错误',
        '-10'   => '内容长度长',
        '-11'   => '内部数据库错误',
        '-12'   => '序列号状态错误'
    );
}
