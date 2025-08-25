<?php
// +----------------------------------------------------------------------
// | niushop-saas-dev
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/5/8 11:32
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\common;

use think\facade\Cache;
use think\File;
use think\Response;

class Utils
{

    /**
     * 创建不重复的编号(抵抗高并发)
     * @param $key string 缓存标识
     * @param $prefix string|int 前缀
     * @param $unique string|int 唯一值
     * @param $type string 前缀类型
     */
    public static function createno(string $key = "", $prefix = "", $unique = "", $type = 'ymdHi')
    {

        $time_str = date($type);
        $max_no   = Cache::get($key . "_" . $time_str);
        if (!isset($max_no) || empty($max_no)) {
            $max_no = 1;
        } else {
            $max_no = $max_no + 1;
        }
        $no = $prefix . $time_str . $unique . sprintf("%05d", $max_no);
        Cache::tag('CREATE_NO')->set($key . "_" . $time_str, $max_no, 60);
        return $no;
    }

    /**
     * 创建64位的编号
     * @param string $filename
     * @param string $name
     * @param bool $content
     * @param int $expire
     * @return mixed
     */
    public static function createnoEx(string $key = "", $prefix = "", $unique = "", $type = 'ymdHi')
    {

        $time_str = date($type);
        $max_no   = Cache::get($key . "_" . $time_str);
        if (!isset($max_no) || empty($max_no)) {
            $max_no = 1;
        } else {
            $max_no = $max_no + 1;
        }
        $no = $prefix . dechex($time_str . sprintf("%05d", $max_no) . $unique);
        Cache::tag('CREATE_NO')->set($key . "_" . $time_str, $max_no, 60);
        return $no;
    }

    public static function download(string $filename, string $name = '', bool $content = false, int $expire = 180)
    {

        header('Access-Control-Expose-Headers: Content-Disposition');
        // 不要缓存
        header('Cache-Control: no-cache');
        header('Pragma: no-cache');

        return Response::create($filename, 'file')->name($name)->isContent($content)->expire($expire);
    }



}
