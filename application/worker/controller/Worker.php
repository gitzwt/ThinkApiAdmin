<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\worker\controller;


use think\worker\Server;

/**
 * Class Worker
 * @package app\worker\controller
 */
class Worker extends Server
{
    /**
     * 定义服务端socket端口号
     * @var string
     */
    protected $socket = 'http://127.0.0.1:5946';
    // 修改服务器端php.ini找到disable_functions一项，将stream_socket_server禁用项删掉

    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $data)
    {
        $connection->send('我收到你的信息了');
    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {
    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection)
    {
    }

    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg)
    {
        echo "error $code $msg" . PHP_EOL;
    }

    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker)
    {
    }
}