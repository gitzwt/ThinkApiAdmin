<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

/**
 * Api接口路由
 */

// 命令行生成路由缓存 optimize:route
// php think optimize:route

// 方法前置
$afterBehavior = ['\app\api\behavior\ApiAuth', '\app\api\behavior\ApiPermission', '\app\api\behavior\RequestFilter',];

// 路由控制器路径必须以api打头!!!
return [
    '[api]' => [
        // 欢迎使用ThinkApiAdmin v4.0 5a570d64428ca
        '5a570d64428ca' => [
            'api/Index/index',
            ['method' => 'get', 'after_behavior' => $afterBehavior]
        ],
        // 获取AccessToken 5a60c77b79875
        '5a60c77b79875' => [
            'api/Buildtoken/getAccessToken',
            ['method' => 'post', 'after_behavior' => $afterBehavior]
        ],
        // 图片上传接口(单文件,支持云存储)base64加密传输 5a64a01c9ed67
        '5a64a01c9ed67' => [
            'api/Tool/uploadImage',
            ['method' => 'post', 'after_behavior' => $afterBehavior]
        ],
        // 文件上传接口(单文件,云存储)模拟HTTP的Post请求方式 5a63444c41b45
        '5a63444c41b45' => [
            'api/Tool/uploadFile',
            ['method' => 'post', 'after_behavior' => $afterBehavior]
        ],
        // 文件上传接口(支持多文件文件,本地存储)模拟HTTP的Post请求方式 5acce4ff98111
        '5acce4ff98111' => [
            'api/Tool/uploadFiles',
            ['method' => 'post', 'after_behavior' => $afterBehavior]
        ],

        // 接口Hash异常跳转
        '__miss__' => ['api/Miss/index'],
    ],
];