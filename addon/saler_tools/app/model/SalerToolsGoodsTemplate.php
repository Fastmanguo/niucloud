<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/11 3:36
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;

/**
 * 商品模板
 * Class SalerToolsGoodsTemplate
 * @package addon\saler_tools\app\model
 */
class SalerToolsGoodsTemplate extends BaseModel
{

    protected $pk = 'template_id';

    protected $name = 'saler_tools_goods_template';

    protected $jsonAssoc = true;

    protected $type = [
        'template_data' => 'array'
    ];

    public function category()
    {
        return $this->hasOne(SalerToolsGoodsCategory::class, 'category_id', 'category_id')
            ->bind(['category_name']);
    }

}
