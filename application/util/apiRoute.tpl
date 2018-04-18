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
$afterBehavior = ['\app\api\behavior\ApiAuth', '\app\api\behavior\RequestFilter',];

// 路由控制器路径必须以api打头!!!
return [
    '[api]' => [
