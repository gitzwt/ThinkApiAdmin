<?php

// +----------------------------------------------------------------------
// | ThinkApiAdmin
// +----------------------------------------------------------------------

namespace app\api\controller;

use mailer\tp5\Mailer;
use think\Db;
use app\util\Strs;
use service\FileService;
use think\Cache;
use app\util\ReturnCode;
use controller\BasicApi;
use service\ToolsService;

/**
 * 公用工具接口类
 * Class Tool
 * @package app\api\controller
 */
class Tool extends BasicApi
{
    /**
     * 指定当前数据表
     * @var string
     */
    public $table_region = 'DataRegion';   //基础地区数据表


    /**
     * 根据手机号获取验证码 (短信签名)
     * 验证码cache名: 手机号_vcode  value:验证码
     * 接口为测试模式时发送失败也设置验证码并接口返回显示
     * 验证cache('1377****9387_vcode') $mobile.'_vcode'
     * @return \think\response\Json
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function getVcodeByMobile()
    {
        $mobile = $this->request->get('mobile');
        $signame = $this->request->get('signame', '');
        if (empty($mobile)) {
            return $this->buildFailed(ReturnCode::EMPTY_PARAMS, '缺少手机号');
        }
        $v_code = mt_rand(100000, 999999);
        $code = config('api.V_CODE'); //验证码
        $param = ['code' => $v_code];
        $res = ToolsService::dayuSms($code, $param, $mobile, $signame);
        $this->debug([
            'Time' => date('Y-m-d H:i:s'),
            'SmsCode' => $res['code'],
            'SmsStatus' => $res['msg'],
        ]);
        if ($res['code'] == 200) {
            // 验证码发送成功设置cache
            Cache::set($mobile . '_vcode', $v_code, config('api.V_CODE_TIME'));
            return $this->buildSuccess([
                'SmsMsg' => '验证码获取成功,请注意查收',
            ]);
        }
        if ($this->test) {
            $this->debug([ 'Vcode' => $v_code]);
            // 验证码发送失败设置cache
            Cache::set($mobile . '_vcode', $v_code, config('api.V_CODE_TIME'));
        }
        return $this->buildFailed(ReturnCode::VCODE_GET_FAILED, '验证码获取失败');
    }

    /**
     * 根据Email获取验证码
     * 接口为测试模式时发送失败也设置验证码并接口返回显示
     * 验证cache('zwt0706@gmail.com_vcode') $email.'_vcode'
     * @return \think\response\Json
     * @throws \mailer\lib\Exception
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function getVcodeByEmail()
    {
        $email = $this->request->get('email');
        if (empty($email)) {
            return $this->buildFailed(ReturnCode::EMPTY_PARAMS, '缺少邮件地址');
        }
        $result = $this->validate(
            ['email' => $email],
            ['email|收件人' => 'require|email']
        );
        if ($result !== true) {
            return $this->buildFailed(ReturnCode::TYPE_ERROR, $result);
        }
        $v_code = mt_rand(100000, 999999);
        $mailer = Mailer::instance();
        $res = $mailer->to($email)
            ->subject('邮件验证码')
            ->line('尊敬的用户:')
            ->line('        您好!您本次的验证码为:{vcode}', ['vcode' => $v_code])
            ->line('        打死也不要告诉别人哦!')
            ->line(sysconf('site_name'))
            ->line(date('Y-m-d H:i:s'))
            ->send();
        // view('邮件模板') 更多使用参阅tp-mailer readme.md
        $this->debug([
            'Time' => date('Y-m-d H:i:s'),
            'EmailCode' => $res,
        ]);
        if ($res == 0) {
            if ($this->test) {
                $this->debug([ 'Vcode' => $v_code]);
                // 验证码发送失败设置cache
                Cache::set($email . '_vcode', $v_code, config('api.V_CODE_TIME'));
            }
            return $this->buildFailed(ReturnCode::VCODE_GET_FAILED, $mailer->getError());
        } else {
            // 验证码发送成功设置cache
            Cache::set($email . '_vcode', $v_code, config('api.V_CODE_TIME'));
            return $this->buildSuccess([
                'EmailMsg' => '验证码获取成功,请注意查收',
            ]);
        }
    }

    /**
     * 文件上传接口(单文件,支持云存储)模拟HTTP的Post请求方式
     * @return \think\response\Json
     * @throws \OSS\Core\OssException
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function uploadFile()
    {
        $file = $this->request->file('file');
        $ext = pathinfo($file->getInfo('name'), 4);
        if (empty($file)) {
            return $this->buildFailed(ReturnCode::EMPTY_PARAMS, '缺少文件');
        }
        $folder = Cache::has('folder_name') ? Cache::get('folder_name') : Cache::set('folder_name', Strs::randString(16, 3, '0123456789'), 86400);
        $filename = uniqid() . ".{$ext}";
        $result = FileService::save($folder . '/' . $filename, file_get_contents($file->getInfo('tmp_name')));
        if ($result === null) {
            return $this->buildFailed(ReturnCode::DB_SAVE_ERROR, '图片上传失败');
        }
        $url = FileService::getFileUrl($folder . '/' . $filename);
        $this->debug([
            'Time' => date('Y-m-d H:i:s'),
            'UploadStatus' => '文件上传成功',
        ]);
        return $this->buildSuccess([
            'Url' => $url,
        ]);
    }

    /**
     * 文件上传接口(支持多文件文件,本地存储)模拟HTTP的Post请求方式
     * @return \think\response\Json
     * @throws \OSS\Core\OssException
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function uploadFiles()
    {
        $files = $this->request->file('file');
        $folder = Cache::has('folder_name') ? Cache::get('folder_name') : Cache::set('folder_name', Strs::randString(16, 3, '0123456789'), 86400);
        foreach ($files as $key => $file) {
            $ext = pathinfo($file->getInfo('name'), 4);
            $filename = uniqid() . ".{$ext}";
            $result = FileService::save($folder . '/' . $filename, file_get_contents($file->getInfo('tmp_name')));
            if ($result === null) {
                $info[$key] = ['code' => -1, 'error' => $file->getError(), 'file_name' => $file->getInfo('name')];
            } else {
                $url = FileService::getFileUrl($folder . '/' . $filename);
                $info[$key] = ['code' => 1, 'url' => $url, 'file_name' => $file->getInfo('name')];
            }
        }
        $this->debug([
            'Time' => date('Y-m-d H:i:s'),
        ]);
        return $this->buildSuccess([
            'info' => $info,
        ]);
    }

    /**
     * 图片上传接口(单文件,支持云存储)base64加密传输
     * @return \think\response\Json
     * @throws \OSS\Core\OssException
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function uploadImage()
    {
        $param = $this->request->param();
        if (empty($param['img'])) {
            return $this->buildFailed(ReturnCode::EMPTY_PARAMS, '缺少图片');
        }
        // base64解码后的图片字符串
        $img = base64_decode(param['img']);
        // 获取上传存储类型
        $uptype = in_array($param['uptype'], ['local', 'qiniu', 'oss']) ? $param['uptype'] : sysconf('storage_type');
        $folder = Cache::has('folder_name') ? Cache::get('folder_name') : Cache::set('folder_name', Strs::randString(16, 3, '0123456789'), 86400);
        $filename = uniqid() . '.png';
        $res = FileService::save($folder . '/' . $filename, $img, $uptype);
        if ($res === null) {
            return $this->buildFailed(ReturnCode::DB_SAVE_ERROR, '图片上传失败');
        }
        $url = FileService::getFileUrl($folder . '/' . $filename, $uptype);
        $this->debug([
            'Time' => date('Y-m-d H:i:s'),
            'UploadStatus' => '图片上传成功',
        ]);
        return $this->buildSuccess([
            'Url' => $url,
        ]);
    }

    /**
     * 获取省份列表
     * @return \think\response\Json
     */
    public function getProvinceList()
    {
        $this->debug(['Time' => date('Y-m-d H:i:s'),]);
        try {
            $p_list = Db::name($this->table_region)
                ->where('type', 1)
                ->select();
        } catch (\Exception $e) {
            return $this->buildFailed(ReturnCode::DB_READ_ERROR, '省份列表获取失败', $data = ['Error' => $e . '请重试']);
        }
        if ($p_list) {
            return $this->buildSuccess([
                'Result' => $p_list,
            ]);
        }
        return $this->buildFailed(ReturnCode::DB_READ_ERROR, '省份列表获取失败', $data = ['Error' => '数据不存在']);
    }

    /**
     * 通过省份id获取城市列表
     * @return \think\response\Json
     */
    public function getCityListByPid()
    {
        $pid = $this->request->get('pid');
        $this->debug(['Time' => date('Y-m-d H:i:s'),]);
        try {
            $c_list = Db::name($this->table_region)
                ->where(['type' => 2, 'parent_id' => $pid])
                ->select();
        } catch (\Exception $e) {
            return $this->buildFailed(ReturnCode::DB_READ_ERROR, '城市列表获取失败', $data = ['Error' => $e . '请重试']);
        }
        if ($c_list) {
            return $this->buildSuccess([
                'Result' => $c_list,
            ]);
        }
        return $this->buildFailed(ReturnCode::DB_READ_ERROR, '城市列表获取失败', $data = ['Error' => '数据不存在']);
    }

    /**
     * 通过城市id获取县区列表
     * @return \think\response\Json
     */
    public function getDistrictListByCid()
    {
        $cid = $this->request->get('cid');
        $this->debug(['Time' => date('Y-m-d H:i:s'),]);
        try {
            $d_list = Db::name($this->table_region)
                ->where(['type' => 3, 'parent_id' => $cid])
                ->select();
        } catch (\Exception $e) {
            return $this->buildFailed(ReturnCode::DB_READ_ERROR, '县区列表获取失败', $data = ['Error' => $e . '请重试']);
        }
        if ($d_list) {
            return $this->buildSuccess([
                'Result' => $d_list,
            ]);
        }
        return $this->buildFailed(ReturnCode::DB_READ_ERROR, '县区列表获取失败', $data = ['Error' => '数据不存在']);
    }

}