<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/18 19:06
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;

/**
 *
 * Class SalerToolsGoodsCost
 * @package addon\saler_tools\app\model
 */
class SalerToolsGoodsCost extends BaseModel
{

    protected $pk = 'cost_id';

    protected $readonly = ['site_id'];

    protected $name = 'saler_tools_goods_cost';

    protected $type = [
        'money'  => 'float',
        'images' => 'array',
    ];


}
