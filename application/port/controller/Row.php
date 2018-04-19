<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\port\controller;

use app\model\ApiList;
use app\util\DataType;
use controller\BasicAdmin;
use service\DataService;
use service\LogService;
use think\Db;

/**
 * Class Row
 * @package app\port\controller
 */
class Row extends BasicAdmin
{

    /**
     * 指定当前数据表
     * @var string
     */
    public $table_api_list = 'ApiList';    //api接口列表
    public $table_api_group = 'ApiGroup';    //api接口组
    public $table_admin = 'SystemUser';      //系统用户表
    public $table_api_fields = 'ApiFields';  //接口参数表
    protected $dataType = [
        DataType::TYPE_INTEGER => 'Integer',
        DataType::TYPE_STRING => 'String',
        DataType::TYPE_BOOLEAN => 'Boolean',
        DataType::TYPE_ENUM => 'Enum',
        DataType::TYPE_FLOAT => 'Float',
        DataType::TYPE_FILE => 'File',
        DataType::TYPE_MOBILE => 'Mobile',
        DataType::TYPE_OBJECT => 'Object',
        DataType::TYPE_ARRAY => 'Array'
    ];  // 接口参数数据类型

    /**
     * 接口列表
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $this->title = '接口列表';
        $get = $this->request->get();
        $db = Db::name($this->table_api_list)
            ->where('is_deleted', '0')
            ->order(['sort' => 'asc', 'id' => 'desc']);
        foreach (['apiName', 'hash', 'info'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%{$get[$key]}%");
            }
        }
        foreach (['handler', 'status'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, $get[$key]);
            }
        }
        return parent::_list($db);
    }

    /**
     * 列表数据处理
     * @param $list
     */
    protected function _index_data_filter(&$list)
    {
        $alert = [
            'type' => 'danger',
            'title' => '操作安全警告（谨慎刷新接口路由）',
            'content' => '新增或编辑接口后必须刷新接口路由才能正常访问！请根据实际情况刷新路由!'
        ];
        $tags = Db::name($this->table_api_group)->column('id,name');
        $handlers = Db::name($this->table_admin)->column('id,username');
        foreach ($list as &$vo) {
            $vo['tags_list'] = [];
            $vo['handler_name'] = Db::name($this->table_admin)->where('id', $vo['handler'])->value('username');
            foreach (explode(',', $vo['gid']) as $tag) {
                if ($tag !== '' && isset($tags[$tag])) {
                    $vo['tags_list'][$tag] = $tags[$tag];
                } elseif ($tag !== '') {
                    $vo['tags_list'][$tag] = $tag;
                }
            }
        }
        $this->assign(['handlers' => $handlers, 'alert' => $alert, 'tags' => $tags]);
    }

    /**
     * 新增接口
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add()
    {
        if ($this->request->isGet()) {
            return parent::_form(null, 'form', 'id');
        }
        $param = $this->request->param();
        empty($param['apiName']) && $this->error('请填写接口名称!');
        empty($param['hash']) && $this->error('Hash为空请重新添加!');
        if (isset($param['method']) && $param['method'] === '') $this->error('请选择请求类型!');
        if (isset($param['isTest']) && $param['isTest'] === '') $this->error('请选择测试模式!');
        if (isset($param['accessToken']) && $param['accessToken'] === '') $this->error('请选择Token验证模式!');
        if (isset($param['needLogin']) && $param['needLogin'] === '') $this->error('请选择用户登录验证模式!');
        empty($param['info']) && $this->error('请填写接口描述!');
        $api_name = Db::name($this->table_api_list)->where('apiName', $param['apiName'])->find();
        if (!empty($api_name)) {
            $this->error('该接口已存在,请勿重复添加!');
        }
        $data = [
            'apiName' => $param['apiName'],
            'hash' => $param['hash'],
            'method' => $param['method'],
            'isTest' => $param['isTest'],
            'accessToken' => $param['accessToken'],
            'needLogin' => $param['needLogin'],
            'info' => $param['info'],
            'handler' => session('user.id')
        ];
        if (false !== DataService::save($this->table_api_list, $data)) {
            LogService::write('API接口管理', '添加接口成功');
            $this->success('添加接口成功!', '');
        }
        LogService::write('API接口管理', '添加接口失败');
        $this->error('添加接口失败, 请稍后再试!');
    }

    /**
     * 编辑接口
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit()
    {
        if ($this->request->isGet()) {
            return parent::_form($this->table_api_list, 'form', 'id');
        }
        $param = $this->request->param();
        empty($param['apiName']) && $this->error('请填写接口名称!');
        empty($param['hash']) && $this->error('Hash为空请重新添加!');
        if (isset($param['method']) && $param['method'] === '') $this->error('请选择请求类型!');
        if (isset($param['isTest']) && $param['isTest'] === '') $this->error('请选择测试模式!');
        if (isset($param['accessToken']) && $param['accessToken'] === '') $this->error('请选择Token验证模式!');
        if (isset($param['needLogin']) && $param['needLogin'] === '') $this->error('请选择用户登录验证模式!');
        empty($param['info']) && $this->error('请填写接口描述!');
        $data = [
            'id' => $param['id'],
            'apiName' => $param['apiName'],
            'hash' => $param['hash'],
            'method' => $param['method'],
            'isTest' => $param['isTest'],
            'accessToken' => $param['accessToken'],
            'needLogin' => $param['needLogin'],
            'info' => $param['info'],
            'handler' => session('user.id')
        ];
        if (false !== DataService::save($this->table_api_list, $data, 'id')) {
            LogService::write('API接口管理', '编辑接口成功');
            $this->success('编辑接口成功!', '');
        }
        LogService::write('API接口管理', '编辑接口失败');
        $this->error('编辑接口失败, 请稍后再试!');
    }

    /**
     * 接口回收站
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function recycle()
    {
        $this->title = '接口回收站';
        $get = $this->request->get();
        $db = Db::name($this->table_api_list)
            ->where('is_deleted', '1')
            ->order(['sort' => 'asc', 'id' => 'desc']);
        foreach (['apiName', 'hash', 'info'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%{$get[$key]}%");
            }
        }
        foreach (['handler', 'status'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, $get[$key]);
            }
        }
        return parent::_list($db);
    }

    /**
     * 列表数据处理
     * @param $list
     */
    protected function _recycle_data_filter(&$list)
    {
        $tags = Db::name($this->table_api_group)->column('id,name');
        $handlers = Db::name($this->table_admin)->column('id,username');
        foreach ($list as &$vo) {
            $vo['tags_list'] = [];
            $vo['handler_name'] = Db::name($this->table_admin)->where('id', $vo['handler'])->value('username');
            foreach (explode(',', $vo['gid']) as $tag) {
                if ($tag !== '' && isset($tags[$tag])) {
                    $vo['tags_list'][$tag] = $tags[$tag];
                } elseif ($tag !== '') {
                    $vo['tags_list'][$tag] = $tag;
                }
            }
        }
        $this->assign(['handlers' => $handlers, 'tags' => $tags]);
    }

    /**
     * 接口打分组标签
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function api_tagset()
    {
        $tags = $this->request->post('tags', '');
        $api_id = $this->request->post('api_id', '');
        $api = Db::name($this->table_api_list)->where('id', $api_id)->find();
        empty($api) && $this->error('需要操作的数据不存在!');
        if (false !== Db::name($this->table_api_list)->where('id', $api_id)->setField('gid', $tags)) {
            LogService::write('API接口管理', '接口分组设置成功');
            $this->success('接口分组设置成功!', '');
        }
        LogService::write('API接口管理', '接口分组设置失败');
        $this->error('接口分组设置失败, 请稍候再试!');
    }

    /**
     * 禁用接口
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        if (DataService::update($this->table_api_list)) {
            LogService::write('API接口管理', '功能禁用成功');
            $this->success("功能禁用成功!", '');
        }
        LogService::write('API接口管理', '功能禁用失败');
        $this->error("功能禁用失败, 请稍候再试!");
    }

    /**
     * 启用接口
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        if (DataService::update($this->table_api_list)) {
            LogService::write('API接口管理', '功能启用成功');
            $this->success("功能启用成功!", '');
        }
        LogService::write('API接口管理', '功能启用失败');
        $this->error("功能启用失败, 请稍候再试!");
    }

    /**
     * 删除接口
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del()
    {
        if (DataService::update($this->table_api_list)) {
            LogService::write('API接口管理', 'API接口删除成功');
            $this->success("API接口删除成功!", '');
        }
        LogService::write('API接口管理', 'API接口删除失败');
        $this->error("API接口删除失败, 请稍候再试!");
    }

    /**
     * 还原接口删除
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function restore()
    {
        if (DataService::update($this->table_api_list)) {
            LogService::write('API接口管理', '还原接口删除成功');
            $this->success("还原接口删除成功!", '');
        }
        LogService::write('API接口管理', '还原接口删除失败');
        $this->error("还原接口删除失败, 请稍候失败!");
    }

    /**
     * 彻底删除接口 删除对应的接口参数信息
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function delete()
    {
        $id = $this->request->post('id');
        $hash = $this->request->post('value');
        if (Db::name($this->table_api_list)->delete($id)) {
            Db::name($this->table_api_fields)->where('hash', $hash)->delete();
            LogService::write('API接口管理', '彻底删除接口成功');
            $this->success("彻底删除接口成功!", '');
        }
        LogService::write('API接口管理', '彻底删除接口失败');
        $this->error("彻底删除接口失败, 请稍候失败!");
        $this->error($hash);
    }

    /**
     * 刷新接口路由  保持固定格式
     * @throws \think\exception\DbException
     */
    public function refresh()
    {
        $apiRoutePath = ROOT_PATH . 'application/apiRoute.php';
        $tplPath = ROOT_PATH . 'application/util/apiRoute.tpl';
        $methodArr = ['get|post', 'post', 'get'];

        $tplStr = file_get_contents($tplPath);
        $listInfo = ApiList::all(['is_deleted' => 0]);
        // 保持固定格式
        $foot = PHP_EOL . '        // 接口Hash异常跳转' . PHP_EOL . '        \'__miss__\' => [\'api/Miss/index\'],' . PHP_EOL . '    ],' . PHP_EOL . '];';
        foreach ($listInfo as $value) {
            $tplStr .= '        // ' . $value->info . ' ' . $value->hash . PHP_EOL . '        \'' . $value->hash . '\' => [' . PHP_EOL . '            \'api/' . $value->apiName . '\',' . PHP_EOL . '            [\'method\' => \'' . $methodArr[$value->method] . '\', \'after_behavior\' => $afterBehavior]' . PHP_EOL . '        ],' . PHP_EOL;
        }
        file_put_contents($apiRoutePath, $tplStr . $foot);
        LogService::write('API接口管理', '刷新接口路由成功');
        $this->success('刷新接口路由成功!', '');
    }

    /**
     * 接口参数列表
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function param()
    {
        $this->title = '接口参数列表';
        $get = $this->request->get();
        $db = Db::name($this->table_api_fields);
        foreach (['fieldName', 'hash'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, 'like', "%{$get[$key]}%");
            }
        }
        foreach (['handler', 'type'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $db->where($key, $get[$key]);
            }
        }
        return parent::_list($db);
    }

    /**
     * 列表数据处理
     * @param $list
     */
    protected function _param_data_filter(&$list)
    {
        $handlers = Db::name($this->table_admin)->column('id,username');
        foreach ($list as &$vo) {
            $vo['handler_name'] = Db::name($this->table_admin)->where('id', $vo['handler'])->value('username');
        }
        $this->assign(['dataType' => $this->dataType, 'handlers' => $handlers]);
    }

    /**
     * 接口参数添加
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function add_param()
    {
        if ($this->request->isGet()) {
            $this->assign(['dataType' => $this->dataType]);
            return parent::_form(null, 'paramform', 'id');
        }
        $param = $this->request->param();
        empty($param['showName']) && $this->error('请填写参数字段名称!');
        if (isset($param['dataType']) && $param['dataType'] === '') $this->error('请选择参数数据类型!');
        $fields_name = Db::name($this->table_api_fields)->where('showName', $param['showName'])->find();
        if (!empty($fields_name)) {
            $this->error('该参数字段已存在,请勿重复添加!');
        }
        $data = [
            'fieldName' => $param['showName'],
            'hash' => $param['hash'],
            'dataType' => $param['dataType'],
            'default' => $param['default'],
            'isMust' => $param['isMust'],
            'range' => $param['range'],
            'info' => $param['info'],
            'type' => $param['type'],
            'showName' => $param['showName'],
            'handler' => session('user.id')
        ];
        if (false !== DataService::save($this->table_api_fields, $data)) {
            LogService::write('API接口管理', '添加接口参数成功');
            $this->success('添加接口参数成功!', '');
        }
        LogService::write('API接口管理', '添加接口参数失败');
        $this->error('添加接口参数失败, 请稍后再试!');
    }

    /**
     * 编辑接口参数
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit_param()
    {
        if ($this->request->isGet()) {
            $this->assign(['dataType' => $this->dataType]);
            return parent::_form($this->table_api_fields, 'paramform', 'id');
        }
        $param = $this->request->param();
        empty($param['showName']) && $this->error('请填写参数字段名称!');
        if (isset($param['dataType']) && $param['dataType'] === '') $this->error('请选择参数数据类型!');
        $data = [
            'id' => $param['id'],
            'fieldName' => $param['showName'],
            'hash' => $param['hash'],
            'dataType' => $param['dataType'],
            'default' => $param['default'],
            'isMust' => $param['isMust'],
            'range' => $param['range'],
            'info' => $param['info'],
            'type' => $param['type'],
            'showName' => $param['showName'],
            'handler' => session('user.id')
        ];
        if (false !== DataService::save($this->table_api_fields, $data)) {
            LogService::write('API接口管理', '编辑接口参数成功');
            $this->success('编辑接口参数成功!', '');
        }
        LogService::write('API接口管理', '编辑接口参数失败');
        $this->error('编辑接口参数失败, 请稍后再试!');
    }

    /**
     * 删除接口参数
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del_param()
    {
        if (DataService::update($this->table_api_fields)) {
            LogService::write('API接口管理', 'API接口参数删除成功');
            $this->success("API接口参数删除成功!", '');
        }
        LogService::write('API接口管理', 'API接口参数删除失败');
        $this->error("API接口参数删除失败, 请稍候再试!");
    }

    /**
     * 请求参数列表
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function ask()
    {
        $get = $this->request->get();
        $this->title = $get['hash'] . ' 的请求参数列表';
        $db = Db::name($this->table_api_fields);
        $db->where('type', 0);
        if (!empty($get['hash'])) {
            $db->where('hash', $get['hash']);
        }
        return parent::_list($db);
    }

    /**
     * 列表数据处理
     * @param $list
     */
    protected function _ask_data_filter(&$list)
    {
        foreach ($list as &$vo) {
            $vo['handler_name'] = Db::name($this->table_admin)->where('id', $vo['handler'])->value('username');
        }
        $this->assign(['dataType' => $this->dataType, 'type' => 0]);
    }

    /**
     * 响应参数列表
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function res()
    {
        $get = $this->request->get();
        $this->title = $get['hash'] . ' 的响应参数列表';
        $db = Db::name($this->table_api_fields);
        $db->where('type', 1);
        if (!empty($get['hash'])) {
            $db->where('hash', $get['hash']);
        }
        return parent::_list($db);
    }

    /**
     * 列表数据处理
     * @param $list
     */
    protected function _res_data_filter(&$list)
    {
        foreach ($list as &$vo) {
            $vo['handler_name'] = Db::name($this->table_admin)->where('id', $vo['handler'])->value('username');
        }
        $this->assign(['dataType' => $this->dataType, 'type' => 1]);
    }

    /**
     * 接口返回json上传格式化
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function upload_res()
    {
        if ($this->request->isGet()) {
            return parent::_form(null, 'uploadform', 'id');
        }
        $param = $this->request->param();
        empty($param['jsonStr']) && $this->error('请填写接口返回的JSON字符串!');
        $jsonStr = html_entity_decode($param['jsonStr']);
        $returnStr = json_decode($jsonStr, true);
        $update_res = Db::name($this->table_api_list)->where('hash', $param['hash'])->setField('returnStr', json_encode($returnStr));
        $this->handle($returnStr['data'], $dataArr);
        $old = Db::name($this->table_api_fields)->where(['hash' => $param['hash'], 'type' => $param['type']])->select();
        $oldArr = array_column($old, 'showName');
        $newArr = array_column($dataArr, 'showName');
        $addArr = array_diff($newArr, $oldArr);
        $delArr = array_diff($oldArr, $newArr);
        if ($delArr) {
            Db::name($this->table_api_fields)->where('showName', 'in', $delArr)->delete();
        }
        if ($addArr) {
            $addData = [];
            foreach ($dataArr as $item) {
                if (in_array($item['showName'], $addArr)) {
                    $addData[] = $item;
                }
            }
            $insert_res = Db::name($this->table_api_fields)->insertAll($addData);
        }
        if ($update_res || $insert_res) {
            LogService::write('API接口管理', 'API接口响应参数上传成功');
            $this->success("API接口返回json示例上传成功!", '');
        }
        LogService::write('API接口管理', 'API接口响应参数上传失败');
        $this->success("API接口返回json示例上传失败!", '');
    }

    /**
     * 响应参数json data数组规整化
     * @param $data
     * @param $dataArr
     * @param string $prefix
     * @param string $index
     */
    private function handle($data, &$dataArr, $prefix = 'data', $index = 'data')
    {
        $param = $this->request->param();
        if (!$this->isAssoc($data)) {
            $addArr = array(
                'fieldName' => $index,
                'showName' => $prefix,
                'hash' => $param['hash'],
                'isMust' => 1,
                'dataType' => DataType::TYPE_ARRAY,
                'type' => $param['type'],
                'handler' => session('user.id')
            );
            $dataArr[] = $addArr;
            $prefix .= '[]';
            if (is_array($data[0])) {
                $this->handle($data[0], $dataArr, $prefix);
            }
        } else {
            $addArr = array(
                'fieldName' => $index,
                'showName' => $prefix,
                'hash' => $param['hash'],
                'isMust' => 1,
                'dataType' => DataType::TYPE_OBJECT,
                'type' => $param['type'],
                'handler' => session('user.id')
            );
            $dataArr[] = $addArr;
            $prefix .= '{}';
            foreach ($data as $index => $datum) {
                $myPre = $prefix . $index;
                $addArr = array(
                    'fieldName' => $index,
                    'showName' => $myPre,
                    'hash' => $param['hash'],
                    'isMust' => 1,
                    'dataType' => DataType::TYPE_STRING,
                    'type' => $param['type'],
                    'handler' => session('user.id')
                );
                if (is_numeric($datum)) {
                    if (preg_match('/^\d*$/', $datum)) {
                        $addArr['dataType'] = DataType::TYPE_INTEGER;
                    } else {
                        $addArr['dataType'] = DataType::TYPE_FLOAT;
                    }
                    $dataArr[] = $addArr;
                } elseif (is_array($datum)) {
                    $this->handle($datum, $dataArr, $myPre, $index);
                } else {
                    $addArr['dataType'] = DataType::TYPE_STRING;
                    $dataArr[] = $addArr;
                }
            }
        }
    }

    /**
     * 判断是否是关联数组（true表示是关联数组）
     * @param array $arr
     * @return bool
     */
    private function isAssoc(array $arr)
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

}