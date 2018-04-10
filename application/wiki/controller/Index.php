<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\wiki\controller;

use app\model\ApiFields;
use app\model\ApiGroup;
use app\model\ApiList;
use app\util\DataType;
use app\util\ReturnCode;

/**
 * 接口文档首页
 * Class Index
 * @package app\wiki\controller
 */
class Index extends Base
{

    /**
     * 默认检查文档用户在线状态
     * @var bool
     */
    public $inline = true;

    /**
     * 接口文档首页
     * @return mixed
     */
    public function index()
    {
        try {
            // 查询所有接口组
            $apiGroup = ApiGroup::all(function ($query) {
                $query->where(['status' => 1, 'is_deleted' => 0])
                    ->order(['sort' => 'asc','id' => 'desc']);
            });
        } catch (\Exception $e) {
            $this->error('数据查询错误!' . $e);
        }
        foreach ($apiGroup as &$vo) {
            // 所属组接口数量统计 like统计一个接口属于多个分组的
            $vo['apiNum'] = (new ApiList())
                ->where(['is_deleted' => 0])
                ->where('gid', 'like', '%' . $vo['id'] . '%')
                ->count();
        }

        $this->assign([
            'title'     => '接口文档',
            'api_url'   => $this->http_type . $this->api_url,
            'apiGroup'  => $apiGroup,
        ]);
        return $this->fetch();
    }

    /**
     * 接口列表&详细
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function detail()
    {
        $gid = $this->request->route('gid');
        $hash = $this->request->route('hash');
        $apiList = ApiList::all(function ($query) {
            $query->where(['is_deleted' => 0])
                ->where('gid', 'like', '%' . $this->request->route('gid') . '%')
                ->order(['sort' => 'asc','id' => 'desc']);
        });
        try {
            // 更新接口热度
            (new ApiGroup())->where('id', $gid)->setInc('hot_num', 1);
        } catch (\Exception $e) {
            $this->error('数据更新错误!' . $e);
        }
        try {
            // 获取分组信息
            $group = (new ApiGroup())->where('id', $gid)->find();
        } catch (\Exception $e) {
            $this->error('数据查询失败!' . $e);
        }
        // 根据路由参数判断
        if ($hash) {
            $detail = ApiList::get(['hash' => $hash]);
        } else {
            $detail = $apiList[0];
            $hash = $detail['hash'];
        }
        $request = ApiFields::all(['hash' => $hash, 'type' => 0]);
        $response = ApiFields::all(['hash' => $hash, 'type' => 1]);
        $dataType = array(
            DataType::TYPE_INTEGER => 'Integer',
            DataType::TYPE_STRING => 'String',
            DataType::TYPE_BOOLEAN => 'Boolean',
            DataType::TYPE_ENUM => 'Enum',
            DataType::TYPE_FLOAT => 'Float',
            DataType::TYPE_FILE => 'File',
            DataType::TYPE_ARRAY => 'Array',
            DataType::TYPE_OBJECT => 'Object',
            DataType::TYPE_MOBILE => 'Mobile'
        );

        $this->assign([
            'title' => '在线接口列表',
            'request' => $request,
            'response' => $response,
            'dataType' => $dataType,
            'apiList' => $apiList,
            'detail' => $detail,
            'hash' => $hash,
            'gid' => $gid,
            'group' => $group,
            'http_type' => $this->http_type,
            'api_url' => $this->api_url,
            'url' => $this->http_type . $this->api_url . $hash,
        ]);

        return $this->fetch();
    }

    /**
     * 算法说明
     * @return mixed
     */
    public function calculation()
    {
        $this->assign(['title' => '算法说明',]);

        return $this->fetch();
    }

    /**
     * 错误码说明
     * @return mixed
     */
    public function errorCode()
    {

        // 与ReturnCode类错误码对应
        $codeArr = ReturnCode::getConstants();
        $errorInfo = array(
            ReturnCode::SUCCESS => '请求成功',
            ReturnCode::INVALID => '非法操作',
            ReturnCode::DB_SAVE_ERROR => '数据存储失败',
            ReturnCode::DB_READ_ERROR => '数据读取失败',
            ReturnCode::CACHE_SAVE_ERROR => '缓存存储失败',
            ReturnCode::CACHE_READ_ERROR => '缓存读取失败',
            ReturnCode::FILE_SAVE_ERROR => '文件读取失败',
            ReturnCode::LOGIN_ERROR => '登录失败',
            ReturnCode::NOT_EXISTS => '不存在',
            ReturnCode::JSON_PARSE_FAIL => 'JSON数据格式错误',
            ReturnCode::TYPE_ERROR => '类型错误',
            ReturnCode::NUMBER_MATCH_ERROR => '数字匹配失败',
            ReturnCode::EMPTY_PARAMS => '丢失必要数据',
            ReturnCode::DATA_EXISTS => '数据已经存在',
            ReturnCode::AUTH_ERROR => '权限认证失败',
            ReturnCode::OTHER_LOGIN => '别的终端登录',
            ReturnCode::VERSION_INVALID => 'API版本非法',
            ReturnCode::PARAM_INVALID => '数据类型非法,参数无效',
            ReturnCode::ACCESS_TOKEN_TIMEOUT => '身份令牌过期',
            ReturnCode::SESSION_TIMEOUT => 'SESSION过期',
            ReturnCode::UNKNOWN => '未知错误',
            ReturnCode::EXCEPTION => '系统异常',
            ReturnCode::CURL_ERROR => 'CURL操作异常',
            ReturnCode::RECORD_NOT_FOUND => '记录未找到',
            ReturnCode::DELETE_FAILED => '删除失败',
            ReturnCode::ADD_FAILED => '添加记录失败',
            ReturnCode::UPDATE_FAILED => '更新记录失败',
            ReturnCode::VCODE_GET_FAILED => '验证码获取失败',
            ReturnCode::VCODE_ERROR => '验证码错误',
            ReturnCode::RES_PWD_ERROR => '密码重置失败',
        );

        $this->assign([
            'title'     => '错误码说明',
            'errorInfo' => $errorInfo,
            'codeArr'   => $codeArr,
        ]);

        return $this->fetch();
    }

}