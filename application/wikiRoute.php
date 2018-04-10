<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

/**
 * Wiki路由
 */


return [
    // 登录
    'wiki/login' => [
        'wiki/Login/index',
        ['method' => 'get|post']
    ],
    // 错误码
    'wiki/errorcode' => [
        'wiki/Index/errorcode',
        ['method' => 'get']
    ],
    // 算法介绍
    'wiki/calculation' => [
        'wiki/Index/calculation',
        ['method' => 'get']
    ],
    // 接口列表
    'wiki/:gid/[:hash]' =>[
        'wiki/Index/detail',
        ['method' => 'get'],
    ],
];
