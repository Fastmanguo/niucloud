<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/11 3:41
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;

/**
 *
 * Class SalerToolsGoodsCategory
 * @package addon\saler_tools\app\model
 */
class SalerToolsGoodsCategory extends BaseModel
{

    protected $pk = 'category_id';

    protected $name = 'saler_tools_goods_category';

    public function template()
    {
        return $this->hasOne(SalerToolsGoodsTemplate::class,'category_id','category_id')
            ->bind(['template_id']);
    }

    public function templateData()
    {
        return $this->hasOne(SalerToolsGoodsTemplate::class,'category_id','category_id')
            ->bind(['template_data']);
    }

}
