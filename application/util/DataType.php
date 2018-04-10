<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\util;

/**
 * 数据类型维护
 * 特别注意：这里的数据类型包含但不限于常规数据类型，可能会存在系统自己定义的数据类型
 * Class DataType
 * @package app\util
 */
class DataType {

    const TYPE_INTEGER = 1;
    const TYPE_STRING = 2;
    const TYPE_ARRAY = 3;
    const TYPE_FLOAT = 4;
    const TYPE_BOOLEAN = 5;
    const TYPE_FILE = 6;
    const TYPE_ENUM = 7;
    const TYPE_MOBILE = 8;
    const TYPE_OBJECT = 9;

    //JPush推送消息类型
    const PUSH_SYSTEM_DATA = 1;
    const PUSH_ACTIVITY_DATA = 2;

}