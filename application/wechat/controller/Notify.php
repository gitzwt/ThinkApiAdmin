<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\wechat\controller;

use think\Controller;
use think\Db;
use think\Log;

/**
 * 微信支付通知处理控制器
 * Class Notify
 * @package app\wechat\controller
 */
class Notify extends Controller
{

    /**
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        // 实例支付接口
        $pay = load_wechat('Pay');

        // 获取支付通知
        $notifyInfo = $pay->getNotify();

        // 支付通知数据获取失败
        if ($notifyInfo === false) {
            // 接口失败的处理
            Log::error("微信支付通知消息验证失败，{$pay->errCode}[{$pay->errCode}]");
            return $pay->errMsg;
        } else {
            //支付通知数据获取成功
            if ($notifyInfo['result_code'] == 'SUCCESS' && $notifyInfo['return_code'] == 'SUCCESS') {
                // 记录支付通知数据
                if (!Db::name('WechatPayNotify')->insert($notifyInfo)) {
                    $pay->replyXml(['return_code' => 'ERROR', 'return_msg' => '系统记录微信通知时发生异常！']);
                }
                $prepayMap = ['out_trade_no' => $notifyInfo['out_trade_no']];
                $prepayData = Db::name('WechatPayPrepayid')->where($prepayMap)->find();
                if (empty($prepayData)) {
                    $pay->replyXml(['return_code' => 'ERROR', 'return_msg' => '系统中未发现对应的预支付记录！']);
                }
                $prepayUpdateData = ['transaction_id' => $notifyInfo['transaction_id'], 'is_pay' => 1, 'pay_at' => date('Y-m-d H:i:s')];
                if (false === Db::name('WechatPayPrepayid')->where($prepayMap)->update($prepayUpdateData)) {
                    $pay->replyXml(['return_code' => 'ERROR', 'return_msg' => '更新系统预支付记录失败！']);
                }
                // 支付状态完全成功，可以更新订单的支付状态了
                // @todo 这里去完成你的订单状态修改操作
                // 回复xml，replyXml方法是终态方法
                $pay->replyXml(['return_code' => 'SUCCESS', 'return_msg' => '系统业务处理成功！']);
            }
        }
    }

}
