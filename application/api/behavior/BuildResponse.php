<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\api\behavior;

use app\model\ApiFields;
use app\util\ApiLog;
use app\util\DataType;
use think\cache;
use think\Request;
use think\Config;

/**
 * 输出结果规整 RESTful风格
 * Class BuildResponse
 * @package app\api\behavior
 */
class BuildResponse {

    /**
     * 返回参数过滤（主要是将返回参数的数据类型给规范）
     * @param $response \think\Response
     * @throws \think\exception\DbException
     */
    public function run($response) {
        $data = $response->getData();
        $request = Request::instance();
        $header = Config::get('api.CROSS_DOMAIN');
        $response->header($header);
        $hash = $request->routeInfo();
        if (isset($hash['rule'][1])) {
            $hash = $hash['rule'][1];

            $has = Cache::has('ResponseFieldsRule:' . $hash);
            if ($has) {
                $rule = cache('ResponseFieldsRule:' . $hash);
            } else {
                $rule = ApiFields::all(['hash' => $hash, 'type' => 1]);
                cache('ResponseFieldsRule:' . $hash, $rule);
            }

            if ($rule) {
                $rule = json_decode(json_encode($rule), true);
                $newRule = array_column($rule, 'dataType', 'showName');
                if (is_array($data)) {
                    $this->handle($data['data'], $newRule);
                } elseif (empty($data)) {
                    if ($newRule['data'] == DataType::TYPE_OBJECT) {
                        $data = (object)[];
                    } elseif ($newRule['data'] == DataType::TYPE_ARRAY) {
                        $data = [];
                    }
                }
                $response->data($data);
            }
        }
        ApiLog::setResponse($data);
        ApiLog::save();
    }

    /**
     * 返回header头参数
     * @param $data
     * @param $rule
     * @param string $prefix
     */
    private function handle(&$data, $rule, $prefix = 'data') {
        if (empty($data)) {
            if ($rule[$prefix] == DataType::TYPE_OBJECT) {
                $data = (object)[];
            }
        } else {
            if ($rule[$prefix] == DataType::TYPE_OBJECT) {
                $prefix .= '{}';
                foreach ($data as $index => &$datum) {
                    $myPre = $prefix . $index;
                    if (isset($rule[$myPre])) {
                        switch ($rule[$myPre]) {
                            case DataType::TYPE_INTEGER:
                                $datum = intval($datum);
                                break;
                            case DataType::TYPE_FLOAT:
                                $datum = floatval($datum);
                                break;
                            case DataType::TYPE_STRING:
                                $datum = strval($datum);
                                break;
                            default:
                                $this->handle($datum, $rule, $myPre);
                                break;
                        }
                    }
                }
            } else {
                $prefix .= '[]';
                if (is_array($data[0])) {
                    foreach ($data as &$datum) {
                        $this->handle($datum, $rule, $prefix);
                    }
                }
            }
        }
    }

}