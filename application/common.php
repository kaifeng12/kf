<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件


/**
 * 打印输出数据到文件
 * @param mixed $data 输出的数据
 * @param bool $force 强制替换
 * @param string|null $file
 */
function p($data, $force = false, $file = null)
{
    is_null($file) && $file = env('runtime_path') . date('Ymd') . '.txt';
    $str = (is_string($data) ? $data : (is_array($data) || is_object($data)) ? print_r($data, true) : var_export($data, true)) . PHP_EOL;
    $force ? file_put_contents($file, $str) : file_put_contents($file, $str, FILE_APPEND);
}

/**
 * 日期格式标准输出
 * @param string $datetime 输入日期
 * @param string $format 输出格式
 * @return false|string
 */
function format_datetime($datetime, $format = 'Y年m月d日 H:i:s')
{
    return date($format, strtotime($datetime));
}

/**
 * UTF8字符串加密
 * @param string $string
 * @return string
 */
function encode($string)
{
    list($chars, $length) = ['', strlen($string = iconv('utf-8', 'gbk', $string))];
    for ($i = 0; $i < $length; $i++) {
        $chars .= str_pad(base_convert(ord($string[$i]), 10, 36), 2, 0, 0);
    }
    return $chars;
}

/**
 * UTF8字符串解密
 * @param string $string
 * @return string
 */
function decode($string)
{
    $chars = '';
    foreach (str_split($string, 2) as $char) {
        $chars .= chr(intval(base_convert($char, 36, 10)));
    }
    return iconv('gbk', 'utf-8', $chars);
}

/**
 * 把数组内时间戳修改为'Y-m-d'等模式
 * @param array $arr    二维数组
 * @param string $key   数组内时间字段名
 * @param number $type  0(默认)修改为'Y-m-d',1则修改为'Y-m-d H:i:s'
 * @return array        返回修改后的数组
 */
function changDate(&$arr,$key,$type=0){
    if(!is_array($arr)) return false;
    if(isset($arr[$key])){
        $type==0 && $arr[$key]=date('Y-m-d',$arr[$key]);
        $type==1 && $arr[$key]=date('Y-m-d H:i:s',$arr[$key]);
        return ;
    }
    foreach ($arr as &$v){
        if($type==0){
            $v[$key]=date('Y-m-d',$v[$key]);
        }elseif ($type==1){
            $v[$key]=date('Y-m-d H:i:s',$v[$key]);
        }
    }
    //return $arr;
}

//获取系统配置
function sys_config($name,$type){
    return Db::name('config')->where(['name'=>$name,'type'=>$type])->value('value');
}

/**
 * curl获取接口数据
 * @param string $url
 * @param string $type
 * @param string $res
 * @param string $arr
 */
function curl($url,$type='get',$res='json',$arr=''){
    $ch=curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if ($type=='post'){
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
    }
    
    $output=curl_exec($ch);
    
    if($res='json'){
        if(curl_errno($ch)){
            //出错
            return curl_errno($ch);
        }else{
            curl_close($ch);
            return json_decode($output,true);
        }
    }
}

/**
 * Emoji原形转换为String
 * @param string $content
 * @return string
 */
function emojiEncode($content)
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
function emojiDecode($content)
{
    return json_decode(preg_replace_callback('/\\\\\\\\/i', function () {
        return '\\';
    }, json_encode($content)));
}
