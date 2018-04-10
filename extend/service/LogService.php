<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace service;

use think\Db;
use think\Request;

/**
 * 操作日志服务
 * Class LogService
 * @package service
 */
class LogService
{

    /**
     * 获取后台数据操作对象
     * @return \think\db\Query
     */
    protected static function db()
    {
        return Db::name('SystemLog');
    }

    /**
     * 写入操作日志
     * @param string $action
     * @param string $content
     * @return bool
     */
    public static function write($action = '行为', $content = "内容描述")
    {
        $request = Request::instance();
        $node = strtolower(join('/', [$request->module(), $request->controller(), $request->action()]));
        $data = ['ip' => $request->ip(), 'node' => $node, 'username' => session('user.username') . '', 'action' => $action, 'content' => $content];
        return self::db()->insert($data) !== false;
    }

}
