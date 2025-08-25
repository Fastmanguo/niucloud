<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/6/1 18:50
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;

/**
 * 商品日志
 * Class GoodsLog
 * @package addon\saler_tools\app\model
 */
class GoodsLog extends BaseModel
{

    protected $name = 'saler_tools_goods_log';

    protected $pk = 'log_id';

    protected $type = [
        'option_data' => 'json'
    ];

}
