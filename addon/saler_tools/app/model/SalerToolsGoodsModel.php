<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/12 3:47
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;
use think\db\Query;

/**
 *
 * Class SalerToolsGoodsModel
 * @package addon\saler_tools\app\model
 */
class SalerToolsGoodsModel extends BaseModel
{

    protected $pk = 'model_id';

    protected $name = 'saler_tools_goods_model';

    protected $json = ['attr_data'];

    protected $jsonAssoc = true;



    public function searchReadSiteIdAttr(Query $query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('site_id', $value)->whereOr('site_id', 0);
    }

    public function searchBrandIdAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('brand_id', $value);
    }


    public function searchSeriesIdAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('series_id', $value);
    }



    public function brand()
    {
        return $this->hasOne(SalerToolsGoodsBrand::class, 'brand_id', 'brand_id')->bind(['brand_name','brand_en','logo']);
    }


    public function series()
    {
        return $this->hasOne(SalerToolsGoodsSeries::class, 'series_id', 'series_id')->bind(['series_name']);
    }

}
