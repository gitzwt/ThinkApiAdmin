<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\wechat\controller;

use controller\BasicAdmin;
use service\DataService;
use think\Db;

/**
 * 微信文章管理
 * Class Article
 * @package app\wechat\controller
 */
class Keys extends BasicAdmin
{

    /**
     * 指定当前数据表
     * @var string
     */
    public $table = 'WechatKeys';

    /**
     * 显示关键字列表
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $this->assign('title', '微信关键字');
        $db = Db::name($this->table)->where('keys', 'not in', ['subscribe', 'default']);
        return $this->_list($db);
    }

    /**
     * 列表数据处理
     * @param $data
     * @throws \Exception
     */
    protected function _index_data_filter(&$data)
    {
        $types = [
            'keys'  => '关键字', 'image' => '图片', 'news' => '图文',
            'music' => '音乐', 'text' => '文字', 'video' => '视频', 'voice' => '语音'
        ];
        $wechat = load_wechat('Extends');
        foreach ($data as &$vo) {
            $result = $wechat->getQRCode($vo['keys'], 1);
            $vo['qrc'] = $wechat->getQRUrl($result['ticket']);
            $vo['type'] = isset($types[$vo['type']]) ? $types[$vo['type']] : $vo['type'];
        }
    }

    /**
     * 添加关键字
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        $this->title = '添加关键字规则';
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑关键字
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        $this->title = '编辑关键字规则';
        return $this->_form($this->table, 'form');
    }


    /**
     * 表单处理
     * @param $data
     */
    protected function _form_filter($data)
    {
        if ($this->request->isPost() && isset($data['keys'])) {
            $db = Db::name($this->table)->where('keys', $data['keys']);
            !empty($data['id']) && $db->where('id', 'neq', $data['id']);
            $db->count() > 0 && $this->error('关键字已经存在，请使用其它关键字！');
        }
    }

    /**
     * 删除关键字
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del()
    {
        if (DataService::update($this->table)) {
            $this->success("关键字删除成功！", '');
        }
        $this->error("关键字删除失败，请稍候再试！");
    }


    /**
     * 关键字禁用
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        if (DataService::update($this->table)) {
            $this->success("关键字禁用成功！", '');
        }
        $this->error("关键字禁用失败，请稍候再试！");
    }

    /**
     * 关键字禁用
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        if (DataService::update($this->table)) {
            $this->success("关键字启用成功！", '');
        }
        $this->error("关键字启用失败，请稍候再试！");
    }

    /**
     * 关注默认回复
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function subscribe()
    {
        $this->assign('title', '编辑默认回复');
        return $this->_form($this->table, 'form');
    }

    /**
     * 关注默认回复表单处理
     * @param $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _subscribe_form_filter(&$data)
    {
        if ($this->request->isGet()) {
            $data = Db::name($this->table)->where('keys', 'subscribe')->find();
        }
        $data['keys'] = 'subscribe';
    }


    /**
     * 无配置默认回复
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function defaults()
    {
        $this->assign('title', '编辑无配置默认回复');
        return $this->_form($this->table, 'form');
    }


    /**
     * 无配置默认回复表单处理
     * @param $data
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function _defaults_form_filter(&$data)
    {
        if ($this->request->isGet()) {
            $data = Db::name($this->table)->where('keys', 'default')->find();
        }
        $data['keys'] = 'default';
    }
}
