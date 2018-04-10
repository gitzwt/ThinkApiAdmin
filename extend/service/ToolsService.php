<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace service;

use think\Request;
use Flc\Dysms\Client;
use Flc\Dysms\Request\SendSms;
use Hashids\Hashids;

/**
 * 系统工具服务
 * Class ToolsService
 * @package service
 */
class ToolsService
{

    /**
     * Cors Options 授权处理
     */
    public static function corsOptionsHandler()
    {
        if (request()->isOptions()) {
            header('Access-Control-Allow-Origin:*');
            header('Access-Control-Allow-Headers:Accept,Referer,Host,Keep-Alive,User-Agent,X-Requested-With,Cache-Control,Content-Type,Cookie,token');
            header('Access-Control-Allow-Credentials:true');
            header('Access-Control-Allow-Methods:GET,POST,OPTIONS');
            header('Access-Control-Max-Age:1728000');
            header('Content-Type:text/plain charset=UTF-8');
            header('Content-Length: 0', true);
            header('status: 204');
            header('HTTP/1.0 204 No Content');
            exit;
        }
    }

    /**
     * Cors Request Header信息
     * @return array
     */
    public static function corsRequestHander()
    {
        return [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Credentials' => true,
            'Access-Control-Allow-Methods' => 'GET,POST,OPTIONS',
            'Access-Defined-X-Support' => 'example@example.com',
            'Access-Defined-X-Servers' => 'Example Technology Co. Ltd',
        ];
    }

    /**
     * Emoji原形转换为String
     * @param string $content
     * @return string
     */
    public static function emojiEncode($content)
    {
        return json_decode(preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i", function ($str) {
            return addslashes($str[0]);
        }, json_encode($content)));
    }

    /**
     * Emoji字符串转换为原形
     * @param string $content
     * @return string
     */
    public static function emojiDecode($content)
    {
        return json_decode(preg_replace_callback('/\\\\\\\\/i', function () {
            return '\\';
        }, json_encode($content)));
    }

    /**
     * 一维数据数组生成数据树
     * @param array $list 数据列表
     * @param string $id 父ID Key
     * @param string $pid ID Key
     * @param string $son 定义子数据Key
     * @return array
     */
    public static function arr2tree($list, $id = 'id', $pid = 'pid', $son = 'sub')
    {
        list($tree, $map) = [[], []];
        foreach ($list as $item) {
            $map[$item[$id]] = $item;
        }
        foreach ($list as $item) {
            if (isset($item[$pid]) && isset($map[$item[$pid]])) {
                $map[$item[$pid]][$son][] = &$map[$item[$id]];
            } else {
                $tree[] = &$map[$item[$id]];
            }
        }
        unset($map);
        return $tree;
    }

    /**
     * 一维数据数组生成数据树
     * @param array $list 数据列表
     * @param string $id ID Key
     * @param string $pid 父ID Key
     * @param string $path
     * @param string $ppath
     * @return array
     */
    public static function arr2table(array $list, $id = 'id', $pid = 'pid', $path = 'path', $ppath = '')
    {
        $tree = [];
        foreach (self::arr2tree($list, $id, $pid) as $attr) {
            $attr[$path] = "{$ppath}-{$attr[$id]}";
            $attr['sub'] = isset($attr['sub']) ? $attr['sub'] : [];
            $attr['spl'] = str_repeat("&nbsp;&nbsp;&nbsp;├&nbsp;&nbsp;", substr_count($ppath, '-'));
            $sub = $attr['sub'];
            unset($attr['sub']);
            $tree[] = $attr;
            if (!empty($sub)) {
                $tree = array_merge($tree, (array)self::arr2table($sub, $id, $pid, $path, $attr[$path]));
            }
        }
        return $tree;
    }

    /**
     * 获取数据树子ID
     * @param array $list 数据列表
     * @param int $id 起始ID
     * @param string $key 子Key
     * @param string $pkey 父Key
     * @return array
     */
    public static function getArrSubIds($list, $id = 0, $key = 'id', $pkey = 'pid')
    {
        $ids = [intval($id)];
        foreach ($list as $vo) {
            if (intval($vo[$pkey]) > 0 && intval($vo[$pkey]) === intval($id)) {
                $ids = array_merge($ids, self::getArrSubIds($list, intval($vo[$key]), $key, $pkey));
            }
        }
        return $ids;
    }

    /**
     * 物流单查询 自动识别快递公司
     * @param string $code 快递单号
     * @return array
     */
    public static function express($code)
    {
        list($result, $client_ip) = [[], Request::instance()->ip()];
        $header = ['Host' => 'www.kuaidi100.com', 'CLIENT-IP' => $client_ip, 'X-FORWARDED-FOR' => $client_ip];
        $autoResult = HttpService::get("http://www.kuaidi100.com/autonumber/autoComNum?text={$code}", [], 30, $header);
        foreach (json_decode($autoResult)->auto as $vo) {
            $microtime = microtime(true);
            $location = "http://www.kuaidi100.com/query?type={$vo->comCode}&postid={$code}&id=1&valicode=&temp={$microtime}";
            $result[$vo->comCode] = json_decode(HttpService::get($location, [], 30, $header), true);
        }
        return $result;
    }

    /**
     * 查询物流信息 通过快递公司编号
     * @param string $express_code 快递公司编码
     * @param string $express_no 快递物流编号
     * @return array
     */
    public static function expressByCorp($express_code, $express_no)
    {
        list($microtime, $client_ip) = [microtime(true), Request::instance()->ip()];
        $header = ['Host' => 'www.kuaidi100.com', 'CLIENT-IP' => $client_ip, 'X-FORWARDED-FOR' => $client_ip];
        $location = "http://www.kuaidi100.com/query?type={$express_code}&postid={$express_no}&id=1&valicode=&temp={$microtime}";
        return json_decode(HttpService::get($location, [], 30, $header), true);
    }

    /**
     * 通用物流单查询
     * @param string $code 快递物流编号
     * @return array
     */
    public static function expressByAuto($code)
    {
        list($result, $client_ip) = [[], Request::instance()->ip()];
        $header = ['Host' => 'www.kuaidi100.com', 'CLIENT-IP' => $client_ip, 'X-FORWARDED-FOR' => $client_ip];
        $autoResult = HttpService::get("http://www.kuaidi100.com/autonumber/autoComNum?text={$code}", [], 30, $header);
        foreach (json_decode($autoResult)->auto as $vo) {
            $result[$vo->comCode] = self::expressByCorp($vo->comCode, $code);
        }
        return $result;
    }

    /**
     * * 阿里大于短信服务
     * @param string $code 短信模板code
     * @param array $param 短信内容(按模板对应数组排)
     * @param string $phone 手机号
     * @param string $signname 短信签名
     * @param string $outid 外部流水号
     * @return array code,状态值 msg,状态信息(ok-发送成功,msg_status_error-短信开关为关,other-其它)
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function dayuSms($code, $param, $phone, $signname, $outid = 'dev')
    {
        $result = [];
        if (sysconf('sms_status')) {
            $config = [
                'accessKeyId' => sysconf('sms_dayu_keyid'),
                'accessKeySecret' => sysconf('sms_dayu_secret'),
            ];
            $client = new Client($config);
            $sendSms = new SendSms;
            $sendSms->setTemplateCode($code);
            $sendSms->setTemplateParam($param);
            $sendSms->setPhoneNumbers($phone);
            $sendSms->setSignName($signname ?: config('api.SIGN_NAME'));
            $sendSms->setOutId($outid);
            $result['msg'] = ($client->execute($sendSms))->Message;
            $result['code'] = 200;
        } else {
            $result = ['code' => 500, 'msg' => 'msg_status_error'];
        }
        return $result;
    }

    /**
     * id盐值hash加密/字符串加密
     * @param string $id 要加密的id或字符串
     * @param int $length 加密后字符串长度
     * @return bool|string
     * @throws \Exception
     */
    public static function enhash($id, $length = 8)
    {
        $hashids = Hashids::instance($length, config('api.HASHIDS'));
        $encode_id = $hashids->encode($id); //加密
        return $encode_id;
    }

    /**
     * id盐值hash解密/字符串解密
     * @param string $id 要解密的id或字符串
     * @param int $length 解密前字符串长度
     * @return array|mixed
     * @throws \Exception
     */
    public static function dehash($id, $length = 8)
    {
        $hashids = Hashids::instance($length, config('api.HASHIDS'));
        $decode_id = $hashids->decode($id); //解密
        return $decode_id;
    }
}
