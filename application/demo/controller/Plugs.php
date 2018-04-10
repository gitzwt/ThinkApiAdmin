<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\demo\controller;

use think\Controller;

/**
 * 系统权限管理控制器
 * Class Plugs
 * @package app\demo\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/07/10 18:13
 */
class Plugs extends Controller
{


    /**
     * 文件上传
     * @return \think\response\View
     */
    public function file()
    {
        return view('', ['title' => '文件上传']);
    }

    /**
     * 省市区插件
     * @return \think\response\View
     */
    public function region()
    {
        return view('', ['title' => '省市区插件']);
    }

    /**
     * 富文本编辑器
     * @return \think\response\View
     */
    public function editor()
    {
        return view('', ['title' => '富文本编辑器']);
    }
}
