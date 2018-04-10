<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\api\controller;

use app\util\ReturnCode;
use controller\BasicApi;

/**
 * 接口Hash异常跳转类
 * Class Miss
 * @package app\api\controller
 */
class Miss extends BasicApi
{
    public function index()
    {
        return $this->buildFailed(ReturnCode::NOT_EXISTS, '接口请求异常:' . date('Y-m-d H:i:s'));
    }
}
