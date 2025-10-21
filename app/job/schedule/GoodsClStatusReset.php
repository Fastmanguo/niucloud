<?php
// +----------------------------------------------------------------------
// | Niucloud-admin 企业快速开发的saas管理平台
// +----------------------------------------------------------------------
// | 官方网址：https://www.niucloud.com
// +----------------------------------------------------------------------
// | niucloud团队 版权所有 开源版本可自由商用
// +----------------------------------------------------------------------
// | Author: Niucloud Team
// +----------------------------------------------------------------------

namespace app\job\schedule;

use core\base\BaseJob;
use think\facade\Log;
use think\facade\Db;

/**
 * 每天凌晨0点重置所有商品的cl_status为0
 */
class GoodsClStatusReset extends BaseJob
{
    public function doJob()
    {
        try {
            // 使用原生SQL更新所有商品的cl_status为0
            $update_result = Db::execute("UPDATE goods SET cl_status = 0 WHERE 1=1");
            
            // 记录日志
            Log::write('商品cl_status重置计划任务执行成功，更新了' . $update_result . '条记录 ' . date('Y-m-d H:i:s'));
            
            return true;
        } catch (\Throwable $e) {
            // 记录错误日志
            Log::error('商品cl_status重置计划任务执行失败，错误原因：' . $e->getMessage() . ' ' . date('Y-m-d H:i:s'));
            return false;
        }
    }
}