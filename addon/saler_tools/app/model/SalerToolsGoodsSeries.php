<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/13 2:29
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;
use think\db\Query;

/**
 *
 * Class SalerToolsGoodsSeries
 * @package addon\saler_tools\app\model
 */
class SalerToolsGoodsSeries extends BaseModel
{

    protected $pk = 'series_id';

    protected $name = 'saler_tools_goods_series';

    protected $readonly = ['site_id'];


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


    public function searchSeriesNameAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->whereLike('series_name', '%' . $value . '%');
    }

    public function searchCategoryKeyAttr(Query $query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->whereLike('category_key', '%,' . $value . ',%');
    }


}
