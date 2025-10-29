<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/6/1 18:54
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\goods;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\GoodsLog;
use think\facade\Db;

/**
 * 商品日志
 * Class GoodsLogService
 * @package addon\saler_tools\app\service\goods
 */
class GoodsLogService extends BaseAdminService
{

    public static function setLog($site_id, $goods_id, $num, $type, $option_data = [], $uid)
    {
        // 将 option_data 转换为 JSON 字符串
        $option_data_json = !empty($option_data) ? json_encode($option_data, JSON_UNESCAPED_UNICODE) : '';
        
        // 使用原生 SQL 插入数据
        $sql = "INSERT INTO `saler_tools_goods_log` (`site_id`, `goods_id`, `num`, `type`, `option_data`, `create_time`, `create_uid`) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        Db::execute($sql, [
            $site_id,
            $goods_id,
            $num,
            $type,
            $option_data_json,
            time(),
            $uid
        ]);
    }


}
