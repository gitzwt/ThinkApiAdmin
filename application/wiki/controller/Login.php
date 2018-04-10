<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\wiki\controller;

use app\util\Tools;
use think\Db;
use think\Cookie;

/**
 * 文档访问登录
 * Class Login
 * @package app\wiki\controller
 */
class Login extends Base
{

    /**
     * 默认检查文档用户在线状态
     * @var bool
     */
    public $inline = false;

    /**
     * 文档登录
     * @return array|mixed
     */
    public function index()
    {
        if ($this->request->isGet()) {
            return $this->fetch('', ['title' => '登录在线接口文档']);
        } else {
            $key = $this->request->post('key', '', 'trim');
            // 访问key验证
            try{
                $doc = Db::name('ApiDocument')->where('key', $key)->find();
            } catch (\Exception $e){
                return ['code'=>500,'msg'=>'密钥数据查询错误'];
            }
            if (empty($doc)){
                return ['code'=>301,'msg'=>'访问key不存在，请重新输入!'];
            } elseif (empty($doc['status'])){
                return ['code'=>302,'msg'=>'该key已经被禁用，请联系管理员!'];
            } elseif ($doc['endTime']<time()) {
                return ['code'=>303,'msg'=>'该key'.Tools::getDate($doc['endTime']).'已经过期，请联系管理员!'];
            }
            // 更新登录信息
            $data = ['lastTime' => time(), 'lastIp' => $this->request->ip()];
            try{
                Db::name('ApiDocument')->where('id', $doc['id'])->update($data);
                Db::name('ApiDocument')->where('id', $doc['id'])->setInc('times');
            } catch (\Exception $e){
                return ['code'=>500,'msg'=>'数据更新错误'];
            }
            Cookie::set('doc',encode($doc['key']),43200);
            return ['code'=>200,'msg'=>'登录成功，正在展示接口文档...','url'=>url('/wiki')];
        }
    }
}