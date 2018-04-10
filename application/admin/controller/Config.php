<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\admin\controller;

use controller\BasicAdmin;
use service\LogService;

/**
 * 后台参数配置控制器
 * Class Config
 * @package app\admin\controller
 */
class Config extends BasicAdmin
{

    /**
     * 当前默认数据模型
     * @var string
     */
    public $table = 'SystemConfig';

    /**
     * 当前页面标题
     * @var string
     */
    public $title = '网站参数配置';

    /**
     * 显示系统常规配置
     * @return \think\response\View
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        if (!$this->request->isPost()) {
            return view('', ['title' => $this->title]);
        }
        foreach ($this->request->post() as $key => $vo) {
            sysconf($key, $vo);
        }
        LogService::write('系统管理', '系统参数配置成功');
        $this->success('系统参数配置成功！', '');
    }

    /**
     * 文件存储配置
     * @return \think\response\View
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function file()
    {
        $alert = [
            'type'    => 'danger',
            'title'   => '操作安全警告（默认使用本地服务存储）',
            'content' => '请根据实际情况配置存储引擎，合理做好站点下载分流。建议尽量使用云存储服务，同时保证文件访问协议与网站访问协议一致！'
        ];
        $this->title = '文件存储配置';
        $this->assign('alert', $alert);
        return $this->index();
    }

}
