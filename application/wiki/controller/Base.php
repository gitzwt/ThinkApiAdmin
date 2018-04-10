<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\wiki\controller;

use think\Controller;
use think\Cookie;

/**
 * Wiki工程基类
 * Class Base
 * @package app\wiki\controller
 */
class Base extends Controller
{

    /**
     * 默认检查文档用户在线状态
     * @var bool
     */
    public $inline = true;

    /**
     * http类型
     * @var string
     */
    public $http_type;

    /**
     * api_url地址
     * @var string
     */
    public $api_url;

    public function _initialize()
    {
        // 判断文档用户登录在线
        if ($this->inline) {
            if (!Cookie::has('doc')) {
                $this->redirect('@wiki/login');
            }
        }

        // 判断http类型
        $this->http_type = is_https() ? 'https://' : 'http://';
        // api_url地址,根据实际服务器环境更改
        $this->api_url = $this->request->host() . url('@api/', '', false);
    }

}