<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\util;

/**
 * 错误码统一维护
 * Class ReturnCode
 * @package app\util
 */
class ReturnCode {

    // 与wiki errorCode对应
    const SUCCESS = 1;  // 请求成功
    const INVALID = -1; // 非法操作
    const DB_SAVE_ERROR = -2; // 数据存储失败
    const DB_READ_ERROR = -3;  // 数据读取失败
    const CACHE_SAVE_ERROR = -4;  // 缓存存储失败
    const CACHE_READ_ERROR = -5;  // 缓存读取失败
    const FILE_SAVE_ERROR = -6;  // 文件读取失败
    const LOGIN_ERROR = -7;  // 登录失败
    const NOT_EXISTS = -8;  // 不存在
    const JSON_PARSE_FAIL = -9;  // JSON数据格式错误
    const TYPE_ERROR = -10;  // 类型错误
    const NUMBER_MATCH_ERROR = -11;  // 数字匹配失败
    const EMPTY_PARAMS = -12;  // 丢失必要数据
    const DATA_EXISTS = -13;  // 数据已经存在
    const AUTH_ERROR = -14;  // 权限认证失败
    const OTHER_LOGIN = -16;  // 别的终端登录
    const VERSION_INVALID = -17;  // API版本非法
    const CURL_ERROR = -18;  // CURL操作异常
    const RECORD_NOT_FOUND = -19; // 记录未找到
    const DELETE_FAILED = -20; // 删除失败
    const ADD_FAILED = -21; // 添加记录失败
    const UPDATE_FAILED = -22; // 更新记录失败
    const VCODE_GET_FAILED = -23; // 验证码获取失败
    const VCODE_ERROR = -24; // 验证码错误
    const RES_PWD_ERROR = -25; // 密码重置失败
    const PARAM_INVALID = -995; // 数据类型非法,参数无效
    const ACCESS_TOKEN_TIMEOUT = -996;  // 身份令牌过期
    const SESSION_TIMEOUT = -997;  // SESSION过期
    const UNKNOWN = -998;  // 未知错误
    const EXCEPTION = -999;  // 系统异常

    static public function getConstants() {
        $oClass = new \ReflectionClass(__CLASS__);
        return $oClass->getConstants();
    }

}