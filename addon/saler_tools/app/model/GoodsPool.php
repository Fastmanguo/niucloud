<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/25 19:50
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;

/**
 *
 * Class GoodsPool
 * @package addon\saler_tools\app\model
 */
class GoodsPool extends BaseModel
{

    protected $pk = 'id';

    protected $name = 'saler_tools_goods_pool';

    protected $type = [
        'attr_data'   => 'json',
        'goods_image' => 'array',
    ];


    public function searchBrandIdAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        if (is_string($value) && str_contains($value, ',')) {
            $query->whereIn('brand_id', explode(',', $value));
        } else if (is_array($value)) {
            $query->whereIn('brand_id', $value);
        } else {
            $query->where('brand_id', $value);
        }
    }


    public function searchSeriesIdAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        if (is_string($value) && str_contains($value, ',')) {
            $query->whereIn('series_id', explode(',', $value));
        } else if (is_array($value)) {
            $query->whereIn('series_id', $value);
        } else {
            $query->where('series_id', $value);
        }
    }


    public function searchModelIdAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        if (is_string($value) && str_contains($value, ',')) {
            $query->whereIn('model_id', explode(',', $value));
        } else if (is_array($value)) {
            $query->whereIn('model_id', $value);
        } else {
            $query->where('model_id', $value);
        }
    }


    public function searchSearchAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->whereLike('goods_name', '%' . $this->handelSpecialCharacter($value) . '%');
    }

    /*****************************************  关联数据 ****************************************/
    public function brand()
    {
        return $this->hasOne(SalerToolsGoodsBrand::class, 'brand_id', 'brand_id')->bind(['brand_name']);
    }


    public function series()
    {
        return $this->hasOne(SalerToolsGoodsSeries::class, 'series_id', 'series_id')->bind(['series_name']);
    }


    public function model()
    {
        return $this->hasOne(SalerToolsGoodsModel::class, 'model_id', 'model_id')->bind(['model_name']);
    }


}
