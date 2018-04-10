<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\wechat\controller;

use controller\BasicAdmin;
use service\LogService;
use service\ToolsService;
use service\WechatService;
use think\Db;

/**
 * 微信粉丝管理
 * Class Fans
 * @package app\wechat\controller
 */
class Fans extends BasicAdmin
{

    /**
     * 定义当前默认数据表
     * @var string
     */
    public $table = 'WechatFans';

    /**
     * 显示粉丝列表
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $this->title = '微信粉丝管理';
        $get = $this->request->get();
        $db = Db::name($this->table)->where('is_back', '0')->order('subscribe_time desc');
        (isset($get['sex']) && $get['sex'] !== '') && $db->where('sex', $get['sex']);
        foreach (['nickname', 'country', 'province', 'city'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, 'like', "%{$get[$key]}%");
        }
        if (isset($get['tag']) && $get['tag'] !== '') {
            $db->where("concat(',',tagid_list,',') like :tag", ['tag' => "%,{$get['tag']},%"]);
        }
        return parent::_list($db);
    }

    /**
     * 列表数据处理
     * @param $list
     */
    protected function _data_filter(&$list)
    {
        $tags = Db::name('WechatFansTags')->column('id,name');
        foreach ($list as &$vo) {
            list($vo['tags_list'], $vo['nickname']) = [[], ToolsService::emojiDecode($vo['nickname'])];
            foreach (explode(',', $vo['tagid_list']) as $tag) {
                if ($tag !== '' && isset($tags[$tag])) {
                    $vo['tags_list'][$tag] = $tags[$tag];
                } elseif ($tag !== '') {
                    $vo['tags_list'][$tag] = $tag;
                }
            }
        }
        $this->assign('tags', $tags);
    }

    /**
     * 黑名单列表
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function back()
    {
        $this->title = '微信粉丝黑名单管理';
        $get = $this->request->get();
        $db = Db::name($this->table)->where('is_back', '1')->order('subscribe_time desc');
        (isset($get['sex']) && $get['sex'] !== '') && $db->where('sex', $get['sex']);
        foreach (['nickname', 'country', 'province', 'city'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->where($key, 'like', "%{$get[$key]}%");
        }
        if (isset($get['tag']) && $get['tag'] !== '') {
            $db->where("concat(',',tagid_list,',') like :tag", ['tag' => "%,{$get['tag']},%"]);
        }
        return parent::_list($db);
    }

    /**
     * 设置黑名单
     * @throws \Exception
     */
    public function backadd()
    {
        $wechat = load_wechat('User');
        $openids = $this->_getActionOpenids();
        if (false !== $wechat->addBacklist($openids)) {
            Db::name($this->table)->where('openid', 'in', $openids)->setField('is_back', '1');
            $this->success("已成功将 " . count($openids) . " 名粉丝移到黑名单!", '');
        }
        $this->error("设备黑名单失败，请稍候再试！{$wechat->errMsg}[{$wechat->errCode}]");
    }

    /**
     * 标签选择
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function tagset()
    {
        $tags = $this->request->post('tags', '');
        $fans_id = $this->request->post('fans_id', '');
        $fans = Db::name('WechatFans')->where('id', $fans_id)->find();
        empty($fans) && $this->error('需要操作的数据不存在!');
        $wechat = load_wechat('User');
        foreach (explode(',', $fans['tagid_list']) as $tagid) {
            is_numeric($tagid) && $wechat->batchDeleteUserTag($tagid, [$fans['openid']]);
        }
        foreach (explode(',', $tags) as $tagid) {
            is_numeric($tagid) && $wechat->batchAddUserTag($tagid, [$fans['openid']]);
        }
        if (false !== Db::name('WechatFans')->where('id', $fans_id)->setField('tagid_list', $tags)) {
            $this->success('粉丝标签成功!', '');
        }
        $this->error('粉丝标签设置失败, 请稍候再试!');
    }

    /**
     * 取消黑名单
     * @throws \Exception
     */
    public function backdel()
    {
        $wechat = load_wechat('User');
        $openids = $this->_getActionOpenids();
        if (false !== $wechat->delBacklist($openids)) {
            Db::name($this->table)->where('openid', 'in', $openids)->setField('is_back', '0');
            $this->success("已成功将 " . count($openids) . " 名粉丝从黑名单中移除!", '');
        }
        $this->error("设备黑名单失败，请稍候再试！{$wechat->errMsg}[{$wechat->errCode}]");
    }

    /**
     * 给粉丝打标签
     * @throws \Exception
     */
    public function tagadd()
    {
        $tagid = $this->request->post('tag_id', 0);
        empty($tagid) && $this->error('没有可能操作的标签ID');
        $openids = $this->_getActionOpenids();
        $wechat = load_wechat('User');
        if (false !== $wechat->batchAddUserTag($tagid, $openids)) {
            $this->success('设置粉丝标签成功!', '');
        }
        $this->error("设置粉丝标签失败, 请稍候再试! {$wechat->errMsg}[{$wechat->errCode}]");
    }

    /**
     * 移除粉丝标签
     * @throws \Exception
     */
    public function tagdel()
    {
        $tagid = $this->request->post('tag_id', 0);
        empty($tagid) && $this->error('没有可能操作的标签ID');
        $openids = $this->_getActionOpenids();
        $wechat = load_wechat('User');
        if (false !== $wechat->batchDeleteUserTag($tagid, $openids)) {
            $this->success('删除粉丝标签成功!', '');
        }
        $this->error("删除粉丝标签失败, 请稍候再试! {$wechat->errMsg}[{$wechat->errCode}]");
    }

    /**
     * 获取当前操作用户openid数组
     * @return array
     */
    private function _getActionOpenids()
    {
        $ids = $this->request->post('id', '');
        empty($ids) && $this->error('没有需要操作的数据!');
        $openids = Db::name($this->table)->where('id', 'in', explode(',', $ids))->column('openid');
        empty($openids) && $this->error('没有需要操作的数据!');
        return $openids;
    }

    /**
     * 同步粉丝列表
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function sync()
    {
        Db::name($this->table)->where('1=1')->delete();
        if (WechatService::syncAllFans('')) {
            WechatService::syncBlackFans('');
            LogService::write('微信管理', '同步全部微信粉丝成功');
            $this->success('同步获取所有粉丝成功！', '');
        }
        $this->error('同步获取粉丝失败，请稍候再试！');
    }

}
