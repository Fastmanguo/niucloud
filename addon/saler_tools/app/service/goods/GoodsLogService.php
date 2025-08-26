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

/**
 * 商品日志
 * Class GoodsLogService
 * @package addon\saler_tools\app\service\goods
 */
class GoodsLogService extends BaseAdminService
{

    public static function setLog($site_id, $goods_id, $num, $type, $option_data = [])
    {
        (new GoodsLog())->create([
            'site_id'     => $site_id,
            'goods_id'    => $goods_id,
            'num'         => $num,
            'type'        => $type,
            'option_data' => $option_data,
            'create_time' => time()
        ]);
    }


}
