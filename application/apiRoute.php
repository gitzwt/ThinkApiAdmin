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
        // 根据手机号获取验证码 (短信签名) 5acd9f48bb275
        '5acd9f48bb275' => [
            'api/Tool/getVcodeByMobile',
            ['method' => 'get', 'after_behavior' => $afterBehavior]
        ],
        // 根据Email获取验证码 5acdaf03ae160
        '5acdaf03ae160' => [
            'api/Tool/getVcodeByEmail',
            ['method' => 'get', 'after_behavior' => $afterBehavior]
        ],
        // 获取省份名称和ID 5a6ad33cd8b30
        '5a6ad33cd8b30' => [
            'api/Tool/getProvinceList',
            ['method' => 'get', 'after_behavior' => $afterBehavior]
        ],
        // 获取城市名称和ID通过省份ID 5a6ad3bc9c27e
        '5a6ad3bc9c27e' => [
            'api/Tool/getCityListByPid',
            ['method' => 'get', 'after_behavior' => $afterBehavior]
        ],
        // 获取县区名称和ID通过城市ID 5a6ad401210c7
        '5a6ad401210c7' => [
            'api/Tool/getDistrictListByCid',
            ['method' => 'get', 'after_behavior' => $afterBehavior]
        ],

        // 接口Hash异常跳转
        '__miss__' => ['api/Miss/index'],
    ],
];