<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\api\behavior;

use app\model\ApiList;
use app\util\ApiLog;
use app\util\ReturnCode;
use think\Request;

/**
 * 处理Api接入认证
 * Class ApiAuth
 * @package app\api\behavior
 */
class ApiAuth
{

    /**
     * @var Request
     */
    private $request;
    private $apiInfo;

    /**
     * 默认行为函数
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function run()
    {
        $this->request = Request::instance();
        $hash = $this->request->routeInfo();
        if (isset($hash['rule'][1])) {
            $hash = $hash['rule'][1];
            $this->apiInfo = ApiList::get(['hash' => $hash]);
            if ($this->apiInfo) {
                $this->apiInfo = $this->apiInfo->toArray();
            } else {
                return json(['code' => ReturnCode::DB_READ_ERROR, 'msg' => '获取接口配置数据失败', 'data' => ['Error' => '该hash接口不存在...']]);
            }
            // 检测接口状态,禁用状态禁止访问
            if (!$this->apiInfo['status']) {
                return json(['code' => ReturnCode::INVALID, 'msg' => '当前接口不可用', 'data' => ['Error' => '接口维护中...']]);
            }
            // 检测accesstoken,测试状态跳过
            if ($this->apiInfo['accessToken'] && !$this->apiInfo['isTest']) {
                $accessRes = $this->checkAccessToken();
                if ($accessRes) {
                    return $accessRes;
                }
            }
            // 检测版本,测试状态跳过
            if (!$this->apiInfo['isTest']) {
                $versionRes = $this->checkVersion();
                if ($versionRes) {
                    return $versionRes;
                }
            }
            // 检测登录
            $loginRes = $this->checkLogin();
            if ($loginRes) {
                return $loginRes;
            }

            ApiLog::setApiInfo($this->apiInfo);
        }
    }

    /**
     * Api接口合法性检测 access-token
     * @return \think\response\Json
     */
    private function checkAccessToken()
    {
        $access_token = $this->request->header('access-token');
        if (!isset($access_token) || !$access_token) {
            return json(['code' => ReturnCode::ACCESS_TOKEN_TIMEOUT, 'msg' => '缺少参数access-token', 'data' => []]);
        } else {
            $appInfo = cache('AccessToken:' . $access_token);
            if (!$appInfo) {
                return json(['code' => ReturnCode::ACCESS_TOKEN_TIMEOUT, 'msg' => 'access-token已过期', 'data' => []]);
            }
            ApiLog::setAppInfo($appInfo);
        }
    }

    /**
     * Api版本参数校验 version
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    private function checkVersion()
    {
        $version = $this->request->header('version');
        if (!isset($version) || !$version) {
            return json(['code' => ReturnCode::EMPTY_PARAMS, 'msg' => '缺少参数version', 'data' => []]);
        } else {
            if ($version != sysconf('app_version')) {
                return json(['code' => ReturnCode::VERSION_INVALID, 'msg' => 'API版本不匹配', 'data' => []]);
            }
        }
    }

    /**
     * 检测接口用户登录情况  开启登录检测必须满足以下条件
     * 用户注册/登录成功 set cache name: userId:用户userId value:盐值加密的用户id
     * @todo 根据APP或微信公众号/小程序 用户登录
     */
    private function checkLogin()
    {
        $userToken = $this->request->header('user-token', '');
        if ($this->apiInfo['needLogin']) {
            if (!$userToken) {
                return json(['code' => ReturnCode::AUTH_ERROR, 'msg' => '缺少user-token', 'data' => []]);
            }
        }
        if ($userToken) {
            $userInfo = cache('userId:' . $userToken);
            if (!$userInfo) {
                return json(['code' => ReturnCode::AUTH_ERROR, 'msg' => 'user-token不匹配', 'data' => ['LoginStatus' => '请先登录']]);
            }
            ApiLog::setUserInfo($userInfo);
        }
    }

}
