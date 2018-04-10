<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

/**
 * api 常量设置
 */

return [
    'USER_ADMINISTRATOR' => [1, 2],
    'AUTH_KEY'           => 'I&TC{pft>L,C`wFQ>&#ROW>k{Kxlt1>ryW(>r<#R',
    'HASHIDS'            => '8nh7SMdtkiNJgkS',

    //跨域配置
    'CROSS_DOMAIN' => [
        'Access-Control-Allow-Origin'      => '*',
        'Access-Control-Allow-Methods'     => 'POST,PUT,GET,DELETE',
        'Access-Control-Allow-Headers'     => 'ApiAuth, User-Agent, Keep-Alive, Origin, No-Cache, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type, X-E4M-With',
        'Access-Control-Allow-Credentials' => 'true'
    ],

    //后台登录状态维持时间[目前只有登录和解锁会重置登录时间]
    'ONLINE_TIME'  => 7200,
    //AccessToken失效时间
    'ACCESS_TOKEN_TIME_OUT'  => 7200,

    //阿里大于短信配置
    'SIGN_NAME' => '签名',    //短信模板签名
    'V_CODE_TIME' => 1800,  //验证码有效时间 半小时
    //短信模板代码
    'V_CODE' => '', //验证码
    'PASSWORD' => '', //重置密码
];


