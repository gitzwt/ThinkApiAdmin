<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\index\controller;

use think\Controller;

/**
 * 网站入口控制器
 * Class Index
 * @package app\index\controller
 */
class Index extends Controller
{

    /**
     * 网站入口
     */
    public function index()
    {
        $this->redirect('@admin');
    }

    /**
     * @throws \Exception
     */
    public function qrc()
    {
        $wechat = load_wechat('Extends');
        for ($i = 10; $i < 90; $i++) {
            $qrc = $wechat->getQRCode($i, 1);
            print_r($qrc);
        }

    }

}
