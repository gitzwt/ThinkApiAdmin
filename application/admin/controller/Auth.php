<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\admin\controller;

use controller\BasicAdmin;
use service\DataService;
use service\NodeService;
use service\ToolsService;
use think\Db;

/**
 * 系统权限管理控制器
 * Class Auth
 * @package app\admin\controller
 */
class Auth extends BasicAdmin
{

    /**
     * 默认数据模型
     * @var string
     */
    public $table = 'SystemAuth';

    /**
     * 权限列表
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $this->title = '系统权限管理';
        return parent::_list($this->table);
    }

    /**
     * 权限授权
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function apply()
    {
        $auth_id = $this->request->get('id', '0');
        $method = '_apply_' . strtolower($this->request->get('action', '0'));
        if (method_exists($this, $method)) {
            return $this->$method($auth_id);
        }
        $this->assign('title', '节点授权');
        return $this->_form($this->table, 'apply');
    }

    /**
     * 读取授权节点
     * @param $auth_id
     */
    protected function _apply_getnode($auth_id)
    {
        $nodes = NodeService::get();
        $checked = Db::name('SystemAuthNode')->where(['auth' => $auth_id])->column('node');
        foreach ($nodes as &$node) {
            $node['checked'] = in_array($node['node'], $checked);
        }
        $all = $this->_apply_filter(ToolsService::arr2tree($nodes, 'node', 'pnode', '_sub_'));
        $this->success('获取节点成功！', '', $all);
    }

    /**
     * 保存授权节点
     * @param $auth_id
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    protected function _apply_save($auth_id)
    {
        list($data, $post) = [[], $this->request->post()];
        foreach (isset($post['nodes']) ? $post['nodes'] : [] as $node) {
            $data[] = ['auth' => $auth_id, 'node' => $node];
        }
        Db::name('SystemAuthNode')->where(['auth' => $auth_id])->delete();
        Db::name('SystemAuthNode')->insertAll($data);
        $this->success('节点授权更新成功！', '');
    }

    /**
     * 节点数据拼装
     * @param array $nodes
     * @param int $level
     * @return mixed
     */
    protected function _apply_filter($nodes, $level = 1)
    {
        foreach ($nodes as $key => &$node) {
            if (!empty($node['_sub_']) && is_array($node['_sub_'])) {
                $node['_sub_'] = $this->_apply_filter($node['_sub_'], $level + 1);
            }
        }
        return $nodes;
    }

    /**
     * 权限添加
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 权限编辑
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        return $this->_form($this->table, 'form');
    }

    /**
     * 权限禁用
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        if (DataService::update($this->table)) {
            $this->success("权限禁用成功！", '');
        }
        $this->error("权限禁用失败，请稍候再试！");
    }

    /**
     * 权限恢复
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        if (DataService::update($this->table)) {
            $this->success("权限启用成功！", '');
        }
        $this->error("权限启用失败，请稍候再试！");
    }

    /**
     * 权限删除
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $id = $this->request->post('id');
            Db::name('SystemAuthNode')->where(['auth' => $id])->delete();
            $this->success("权限删除成功！", '');
        }
        $this->error("权限删除失败，请稍候再试！");
    }

}
