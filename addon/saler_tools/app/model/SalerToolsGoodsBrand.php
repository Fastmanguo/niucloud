<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/11 3:37
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;
use think\db\Query;
use think\model\concern\SoftDelete;

/**
 *
 * Class SalerToolsGoodsBrand
 * @package addon\saler_tools\app\model
 */
class SalerToolsGoodsBrand extends BaseModel
{

    use SoftDelete;

    protected $deleteTime = 'delete_time';

    protected $defaultSoftDelete = 0;

    protected $pk = 'brand_id';

    protected $name = 'saler_tools_goods_brand';

    protected $readonly = ['site_id'];


    public function searchReadSiteIdAttr(Query $query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('site_id', $value)->whereOr('site_id', 0);
    }

    public function searchBrandNameAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->whereLike('brand_name', '%' . $this->handelSpecialCharacter($value) . '%');
    }


    public function series()
    {
        return $this->hasMany(SalerToolsGoodsSeries::class, 'brand_id', 'brand_id');
    }

    public function brandModel()
    {
        return $this->hasMany(SalerToolsGoodsModel::class, 'brand_id', 'brand_id');
    }


}
