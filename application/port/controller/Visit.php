<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\port\controller;

use controller\BasicAdmin;
use service\DataService;
use service\LogService;
use app\util\Strs;
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

    /**
     * 添加接口应用
     * @return \think\response\View
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        if ($this->request->isGet()) {
            $vo['app_id'] = Strs::randString(8, 1);
            $vo['app_secret'] = Strs::randString(32);
            return view('appform', ['vo' => $vo]);
        }
        $param = $this->request->param();
        empty($param['app_name']) && $this->error('请填写应用名称!');
        empty($param['app_info']) && $this->error('请填写应用说明!');
        $api_name = Db::name($this->table_api_app)->where('app_name', $param['app_name'])->find();
        if (!empty($api_name)) {
            $this->error('该应用名已存在,请勿重复添加!');
        }
        $data = [
            'app_id' => $param['app_id'],
            'app_secret' => $param['app_secret'],
            'app_name' => $param['app_name'],
            'app_info' => $param['app_info'],
            'handler' => session('user.id')
        ];
        if (false !== DataService::save($this->table_api_app, $data)) {
            LogService::write('API接口管理', '添加接口应用成功');
            $this->success('添加接口应用成功!', '');
        }
        LogService::write('API接口管理', '添加接口应用失败');
        $this->error('添加接口应用失败, 请稍后再试!');
    }

    /**
     * 编辑接口应用
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        if ($this->request->isGet()) {
            return parent::_form($this->table_api_app, 'appform', 'id');
        }
        $param = $this->request->param();
        empty($param['app_name']) && $this->error('请填写应用名称!');
        empty($param['app_info']) && $this->error('请填写应用说明!');
        $data = [
            'id' => $param['id'],
            'app_id' => $param['app_id'],
            'app_secret' => $param['app_secret'],
            'app_name' => $param['app_name'],
            'app_info' => $param['app_info'],
            'handler' => session('user.id')
        ];
        if (false !== DataService::save($this->table_api_app, $data)) {
            LogService::write('API接口管理', '编辑接口应用成功');
            $this->success('编辑接口应用成功!', '');
        }
        LogService::write('API接口管理', '编辑接口应用失败');
        $this->error('编辑接口应用失败, 请稍后再试!');
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

    /**
     * 添加接口文档访问密钥
     * @return \think\response\View
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add_doc()
    {
        if ($this->request->isGet()) {
            $vo['key'] = Strs::randString(20);
            return view('docform', ['vo' => $vo]);
        }
        $param = $this->request->param();
        empty($param['key']) && $this->error('请填写访问密钥!');
        empty($param['endTime']) && $this->error('请选择密钥过期时间!');
        $key = Db::name($this->table_api_document)->where('key', $param['key'])->find();
        if (!empty($key)) {
            $this->error('该访问密钥已存在,请勿重复添加!');
        }
        $data = [
            'key' => $param['key'],
            'endTime' => strtotime($param['endTime']),
            'createTime' => time(),
            'handler' => session('user.id')
        ];
        if (false !== DataService::save($this->table_api_document, $data)) {
            LogService::write('API接口管理', '添加接口文档访问密钥成功');
            $this->success('添加接口文档访问密钥成功!', '');
        }
        LogService::write('API接口管理', '添加接口文档访问密钥失败');
        $this->error('添加接口文档访问密钥失败, 请稍后再试!');
    }

    /**
     * 编辑接口文档访问密钥
     * @return \think\response\View
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit_doc()
    {
        if ($this->request->isGet()) {
            $id = $this->request->get('id');
            $vo = Db::name($this->table_api_document)->where('id',$id)->find();
            $vo['endTime'] = date('Y-m-d',$vo['endTime']);
            return view('docform', ['vo' => $vo]);
        }
        $param = $this->request->param();
        empty($param['key']) && $this->error('请填写访问密钥!');
        empty($param['endTime']) && $this->error('请选择密钥过期时间!');
        $data = [
            'id' => $param['id'],
            'key' => $param['key'],
            'endTime' => strtotime($param['endTime']),
            'createTime' => time(),
            'handler' => session('user.id')
        ];
        if (false !== DataService::save($this->table_api_document, $data)) {
            LogService::write('API接口管理', '编辑接口文档访问密钥成功');
            $this->success('编辑接口文档访问密钥成功!', '');
        }
        LogService::write('API接口管理', '编辑接口文档访问密钥失败');
        $this->error('编辑接口文档访问密钥失败, 请稍后再试!');
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