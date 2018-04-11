<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\port\controller;

use controller\BasicAdmin;
use service\DataService;
use service\LogService;
use think\Db;

/**
 * api访问控制
 * Class Visit
 * @package app\port\controller
 */
class Visit extends BasicAdmin
{
    /**
     * 指定当前数据表
     * @var string
     */
    public $table_api_app = 'ApiApp';    //api应用列表
    public $table_api_document = 'ApiDocument';    //api文档密钥列表
    public $table_admin = 'SystemUser';      //系统用户表

    /**
     * 应用列表
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function app()
    {
        $this->title = '应用列表';
        $get = $this->request->get();
        $db = Db::name($this->table_api_app)
            ->order(['id' => 'desc']);
        foreach (['app_name', 'app_id'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%{$get[$key]}%");
            }
        }
        foreach (['handler', 'app_status'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, $get[$key]);
            }
        }
        return parent::_list($db);
    }

    /**
     * 列表数据处理
     * @param $list
     */
    protected function _app_data_filter(&$list)
    {
        $handlers = Db::name($this->table_admin)->column('id,username');
        foreach ($list as &$vo) {
            $vo['handler_name'] = Db::name($this->table_admin)->where('id', $vo['handler'])->value('username');
        }
        $this->assign(['handlers' => $handlers]);
    }

    public function add()
    {

    }

    public function edit()
    {

    }

    /**
     * 禁用应用
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        if (DataService::update($this->table_api_app)) {
            LogService::write('API接口管理', '应用禁用成功');
            $this->success("应用禁用成功!", '');
        }
        LogService::write('API接口管理', '应用禁用失败');
        $this->error("应用禁用失败, 请稍候再试!");
    }

    /**
     * 启用应用
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        if (DataService::update($this->table_api_app)) {
            LogService::write('API接口管理', '应用启用成功');
            $this->success("应用启用成功!", '');
        }
        LogService::write('API接口管理', '应用启用失败');
        $this->error("应用启用失败, 请稍候再试!");
    }


    /**
     * 删除接口应用
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del()
    {
        if (DataService::update($this->table_api_app)) {
            LogService::write('API接口管理', 'API接口应用删除成功');
            $this->success("API接口应用删除成功!", '');
        }
        LogService::write('API接口管理', 'API接口应用删除失败');
        $this->error("API接口应用删除失败, 请稍候再试!");
    }

    /**
     * 文档密钥
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function doc()
    {
        $this->title = '文档密钥';
        $get = $this->request->get();
        $db = Db::name($this->table_api_document)
            ->order(['id' => 'desc']);
        foreach (['key'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%{$get[$key]}%");
            }
        }
        foreach (['handler', 'status'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, $get[$key]);
            }
        }
        return parent::_list($db);
    }

    /**
     * 列表数据处理
     * @param $list
     * @throws \Exception
     */
    protected function _doc_data_filter(&$list)
    {
        // ip转网络地理位置
        $ip = new \Ip2Region();
        $handlers = Db::name($this->table_admin)->column('id,username');
        foreach ($list as &$vo) {
            $result = $ip->btreeSearch($vo['lastIp']);
            $vo['isp'] = isset($result['region']) ? $result['region'] : '';
            $vo['isp'] = str_replace(['|0|0|0|0', '0', '|'], ['', '', ' '], $vo['isp']);
            $vo['handler_name'] = Db::name($this->table_admin)->where('id', $vo['handler'])->value('username');
        }
        $this->assign(['handlers' => $handlers]);
    }

    public function add_doc()
    {

    }

    public function edit_doc()
    {

    }

    /**
     * 禁用文档密钥
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid_doc()
    {
        if (DataService::update($this->table_api_document)) {
            LogService::write('API接口管理', '文档密钥禁用成功');
            $this->success("文档密钥禁用成功!", '');
        }
        LogService::write('API接口管理', '文档密钥禁用失败');
        $this->error("文档密钥禁用失败, 请稍候再试!");
    }

    /**
     * 启用文档密钥
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume_doc()
    {
        if (DataService::update($this->table_api_document)) {
            LogService::write('API接口管理', '文档密钥启用成功');
            $this->success("文档密钥启用成功!", '');
        }
        LogService::write('API接口管理', '文档密钥启用失败');
        $this->error("文档密钥启用失败, 请稍候再试!");
    }

    /**
     * 删除文档密钥
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del_doc()
    {
        if (DataService::update($this->table_api_document)) {
            LogService::write('API接口管理', 'API文档密钥删除成功');
            $this->success("API文档密钥删除成功!", '');
        }
        LogService::write('API接口管理', 'API文档密钥删除失败');
        $this->error("API文档密钥删除失败, 请稍候再试!");
    }

}