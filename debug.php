<?php
/**
 * Created by PhpStorm.
 * Author: zwt
 * Date: 2018/1/19
 * Time: 21:10
 * Email: zwt0706@gmail.com
 */

//debug 运行测试文件


//获取签名signature   be25d988b2156d1889d65b9010a37b29
//echo md5("app_id=69920173&app_secret=SVRZKjiXaLnaIGaqCqjsCloazWRfOUdl&device_id=668668&rand_str=CJbHN4oIRT2L&timestamp=1514736000");

// 图片转base64
//$img = file_get_contents("./tim.png");
//$img_base64 = base64_encode($img);
//echo $img_base64;

//广告位删除前判断下有无广告
//$can = '';
//$cannot = '';
//$ids = explode(',', '1,2,3');
//$apids = ['1','3','4','5','6'];
//foreach ($ids as &$v) {
//    if (in_array($v, $apids)) {
//        // 拼接不能删除的
//        $cannot .= $v . ',';
//    } else {
//        // 拼接可以删除的
//        $can .= $v . ',';
//    }
//}
//var_dump(array_filter(explode(',', $can)));

//$str='aaaaabbbbbbbccccc';
//$match = substr($str,strpos($str,',')+1);
//var_dump($match);