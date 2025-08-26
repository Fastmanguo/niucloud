<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/12 18:36
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;

/**
 *
 * Class SalerToolsGoodsAttr
 * @package addon\saler_tools\app\model
 */
class SalerToolsGoodsAttr extends BaseModel
{

    protected $name = 'saler_tools_goods_attr';

    protected $pk = 'attr_id';

    protected $readonly = 'site_id';

}
