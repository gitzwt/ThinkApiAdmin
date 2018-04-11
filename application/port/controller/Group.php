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
 * api组管理控制器
 * Class Group
 * @package app\port\controller
 */
class Group extends BasicAdmin
{
    /**
     * 指定当前数据表
     * @var string
     */
    public $table_api_group = 'ApiGroup';    //api接口组
    public $table_admin = 'SystemUser';      //系统用户表

    /**
     * 接口组列表
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $this->title = '接口组列表';
        $get = $this->request->get();
        $db = Db::name($this->table_api_group)
            ->where('is_deleted', '0')
            ->order(['sort' => 'asc', 'id' => 'desc']);
        foreach (['name'] as $key) {
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
     */
    protected function _index_data_filter(&$list)
    {
        $handlers = Db::name($this->table_admin)->column('id,username');
        foreach ($list as &$vo) {
            $vo['handler_name'] = Db::name($this->table_admin)->where('id', $vo['handler'])->value('username');
        }
        $this->assign(['handlers' => $handlers]);
    }

    /**
     * 接口组回收站列表
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function recycle()
    {
        $this->title = '接口组回收站';
        $get = $this->request->get();
        $db = Db::name($this->table_api_group)
            ->where('is_deleted', '1')
            ->order('id', 'DESC');
        // 应用搜索条件
        foreach (['name'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%{$get[$key]}%");
            }
        }
        return parent::_list($db);
    }

    /**
     * 列表数据处理
     * @param $list
     */
    protected function _recycle_data_filter(&$list)
    {
        $handlers = Db::name($this->table_admin)->column('id,username');
        foreach ($list as &$vo) {
            $vo['handler_name'] = Db::name($this->table_admin)->where('id', $vo['handler'])->value('username');
        }
        $this->assign(['handlers' => $handlers]);
    }

    /**
     * 添加接口组
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        if ($this->request->isGet()) {
            return parent::_form(null, 'form', 'id');
        }
        // 接收提交的数据
        list($id, $name, $img, $description, $detail,) = [
            $this->request->post('id', ''),
            $this->request->post('name', ''),
            $this->request->post('img', ''),
            $this->request->post('description', ''),
            $this->request->post('detail', ''),
        ];
        empty($name) && $this->error('请输入分组名称!');
        empty($description) && $this->error('请输入分组描述!');
        $group_name = Db::name($this->table_api_group)->where('name', $name)->where('id', '<>', $id)->find();
        if (!empty($group_name)) {
            $this->error('该分类名称已存在,请勿重复添加!');
        }
        $data = [
            'id' => $id,
            'name' => $name,
            'img' => $img,
            'description' => $description,
            'detail' => $detail,
            'handler' => session('user.id'),
        ];
        if (false !== DataService::save($this->table_api_group, $data, 'id')) {
            LogService::write('API接口管理', '添加API分组成功');
            $this->success('添加API分组成功!', '');
        }
        LogService::write('API接口管理', '添加API分组失败');
        $this->error('添加API分组失败, 请稍后再试!');
    }

    /**
     * 编辑素材分类
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
            return parent::_form($this->table_api_group, 'form', 'id');
        }
        // 接收提交的数据
        list($id, $name, $img, $description, $detail,) = [
            $this->request->post('id', ''),
            $this->request->post('name', ''),
            $this->request->post('img', ''),
            $this->request->post('description', ''),
            $this->request->post('detail', ''),
        ];
        empty($name) && $this->error('请输入分组名称!');
        empty($description) && $this->error('请输入分组描述!');
        $info = Db::name($this->table_api_group)->where('id', $id)->find();
        $group_name = Db::name($this->table_api_group)->where('name', $name)->where('id', '<>', $id)->find();
        if ($info['name'] == $name && $info['description'] == $description && $info['img'] == $img && $info['detail'] == $detail) {
            $this->error('数据没有改变, 无需修改!');
        }
        if (!empty($group_name)) {
            $this->error('该分组名称已存在,请勿重复添加!');
        }
        $data = [
            'id' => $id,
            'name' => $name,
            'img' => $img,
            'description' => $description,
            'detail' => $detail,
            'handler' => session('user.id'),
        ];
        if (false !== DataService::save($this->table_api_group, $data, 'id')) {
            LogService::write('API接口管理', '编辑API分组成功');
            $this->success('编辑API分组成功!', '');
        }
        LogService::write('API接口管理', '编辑API分组失败');
        $this->error('编辑API分组失败, 请稍后再试!');
    }

    /**
     * 禁用分组
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        if (DataService::update($this->table_api_group)) {
            LogService::write('API接口管理', '功能禁用成功');
            $this->success("功能禁用成功!", '');
        }
        LogService::write('API接口管理', '功能禁用失败');
        $this->error("功能禁用失败, 请稍候再试!");
    }

    /**
     * 启用分组
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        if (DataService::update($this->table_api_group)) {
            LogService::write('API接口管理', '功能启用成功');
            $this->success("功能启用成功!", '');
        }
        LogService::write('API接口管理', '功能启用失败');
        $this->error("功能启用失败, 请稍候再试!");
    }


    /**
     * 删除接口分组
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del()
    {
        if (DataService::update($this->table_api_group)) {
            LogService::write('API接口管理', 'API接口分组删除成功');
            $this->success("API接口分组删除成功!", '');
        }
        LogService::write('API接口管理', 'API接口分组删除失败');
        $this->error("API接口分组删除失败, 请稍候再试!");
    }

    /**
     * 还原分组删除
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function restore()
    {
        if (DataService::update($this->table_api_group)) {
            LogService::write('API接口管理', '还原接口分组成功');
            $this->success("还原接口分组成功!", '');
        }
        LogService::write('API接口管理', '还原接口分组失败');
        $this->error("还原接口分组失败, 请稍候失败!");
    }

    /**
     * 彻底删除分组
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delete()
    {
        if (DataService::update($this->table_api_group)) {
            LogService::write('API接口管理', '彻底删除接口分组成功');
            $this->success("彻底删除接口分组成功!", '');
        }
        LogService::write('API接口管理', '彻底删除接口分组失败');
        $this->error("彻底删除接口分组失败, 请稍候失败!");
    }

}