<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\api\controller;

use controller\BasicApi;

/**
 * API index类
 * Class Index
 * @package app\api\controller
 */
class Index extends BasicApi
{
    /**
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        $this->debug([
            'ThinkPHPVersion' => THINK_VERSION,
            'PHPVersion' => PHP_VERSION
        ]);

        return $this->buildSuccess([
            'Product' => sysconf('site_name'),
            'Version' => sysconf('app_version'),
            'Company' => sysconf('site_copy'),
            'ToYou' => "I'm glad to meet you（终于等到你！）"
        ]);
    }
}
