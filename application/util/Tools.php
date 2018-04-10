<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\util;

/**
 * 工具类
 * Class Tools
 * @package app\util
 */
class Tools
{

    /**
     * 与当前时间比对
     * @param $timestamp
     * @return string
     */
    public static function getDate($timestamp)
    {
        $now = time();
        $diff = $now - $timestamp;
        if ($diff <= 60) {
            return $diff . '秒前';
        } elseif ($diff <= 3600) {
            return floor($diff / 60) . '分钟前';
        } elseif ($diff <= 86400) {
            return floor($diff / 3600) . '小时前';
        } elseif ($diff <= 2592000) {
            return floor($diff / 86400) . '天前';
        } else {
            return '一个月前';
        }
    }

    /**
     * 根据key对字符串进行MD5多重加密
     * @param $str
     * @param string $auth_key
     * @return string
     */
    public static function userMd5($str, $auth_key = '')
    {
        if (!$auth_key) {
            $auth_key = config('api.AUTH_KEY');
        }
        return '' === $str ? '' : md5(sha1($str) . $auth_key);
    }

}