<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/10 21:31
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;
use app\model\sys\SysUser;
use think\db\Query;
use think\model\concern\SoftDelete;

/**
 * 商品管理
 * Class Goods
 * @package addon\saler_tools\app\model
 */
class Goods extends BaseModel
{

    use SoftDelete;

    protected $name = 'saler_tools_goods';

    protected $pk = 'goods_id';

    protected $readonly = ['site_id'];

    protected $jsonAssoc = true;

    protected $deleteTime = 'deleted_time';

    protected $defaultSoftDelete = 0;

    protected $autoWriteTimestamp = 'datetime';


    protected $type = [
        'goods_image'           => 'array',
        'goods_tag'             => 'array',
        'goods_tips'            => 'array',
        'goods_attachment'      => 'array',
        'recycling_image'       => 'array',
        'remark_image'          => 'array',
        'warranty_card_image'   => 'array',


        // 钱相关
        'price'                 => 'float',
        'guide_price'           => 'float',
        'peer_price'            => 'float',
        'agent_price'           => 'float',
        'total_cost'            => 'float',
        'additional_total_cost' => 'float',
        'initial_cost'          => 'float',
        // 库存
        'stock'                 => 'int',
        'is_online_expo'        => 'int',
    ];


    public function searchSearchAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->whereLike('goods_name|goods_desc', '%' . $value . '%');
    }

    public function searchGoodsNameAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('goods_name', $value);
    }

    public function searchIsSaleAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('is_sale', $value);
    }

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


    public function searchGoodsIdAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        if (is_string($value) && str_contains($value, ',')) {
            $query->whereIn('goods_id', explode(',', $value));
        } else if (is_array($value)) {
            $query->whereIn('goods_id', $value);
        } else {
            $query->where('goods_id', $value);
        }
    }


    public function searchCategoryIdAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('category_id', $value);
    }


    public function searchAppraiserUidAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('appraiser_uid', $value);
    }


    public function searchCreateUidAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('create_uid', $value);
    }


    public function searchRecyclingUidAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        if (is_string($value) && str_contains($value, ',')) {
            $query->whereIn('recycling_uid', explode(',', $value));
        } else if (is_array($value)) {
            $query->whereIn('recycling_uid', $value);
        } else {
            $query->where('recycling_uid', $value);
        }
    }

    public function searchStoreIdAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        if (is_string($value) && str_contains($value, ',')) {
            $query->whereIn('store_id', explode(',', $value));
        } else if (is_array($value)) {
            $query->whereIn('store_id', $value);
        } else {
            $query->where('store_id', $value);
        }
    }

    public function searchWatchLocationAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        if (is_array($value)) {
            $query->whereIn('watch_location', $value);
        } else {
            $query->where('watch_location', $value);
        }
    }

    public function searchTargetAudienceAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        if (is_string($value) && str_contains($value, ',')) {
            $query->whereIn('target_audience', explode(',', $value));
        } else if (is_array($value)) {
            $query->whereIn('target_audience', $value);
        } else {
            $query->where('target_audience', $value);
        }
    }

    public function searchGoodsTagAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        if (is_array($value)) {
            $query->whereIn('goods_tag', $value);
        } else {
            $query->where('goods_tag', $value);
        }
    }

    public function searchGoodsAttributeAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        if (is_array($value)) {
            $query->whereIn('goods_attribute', $value);
        } else {
            $query->where('goods_attribute', $value);
        }
    }

    public function searchRecyclingTimeAttr(Query $query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        if (is_array($value)) {
            if (!empty($value[0]) && !empty($value[1])) {
                $query->whereBetween('recycling_time', [$value[0], $value[1]]);
            } elseif (!empty($value[0])) {
                $query->where('recycling_time', '>=', $value[0]);
            } elseif (!empty($value[1])) {
                $query->where('recycling_time', '<=', $value[1]);
            }
        }
    }

    /***************************************** 操作事件 ****************************************/

    public static function onBeforeInsert($model)
    {

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


    public function store()
    {
        return $this->hasOne(SalerToolsStore::class, 'store_id', 'store_id')->bind(['store_name']);
    }


    public function category()
    {
        return $this->hasOne(SalerToolsGoodsCategory::class, 'category_id', 'category_id')->bind(['category_name']);
    }


    public function appraiserName()
    {
        return $this->hasOne(SysUser::class, 'uid', 'appraiser_uid')->bind(['appraiser_name' => 'real_name']);
    }


    public function createName()
    {
        return $this->hasOne(SysUser::class, 'uid', 'create_uid')->bind(['create_name' => 'real_name']);

    }

    public function updateName()
    {
        return $this->hasOne(SysUser::class, 'uid', 'update_uid')->bind(['create_name' => 'update_name']);
    }


    public function recyclingName()
    {
        return $this->hasOne(SysUser::class, 'uid', 'recycling_uid')->bind(['recycling_name' => 'real_name']);
    }


    public function goodsCost()
    {
        return $this->hasMany(SalerToolsGoodsCost::class, 'goods_id', 'goods_id');
    }


    public function goodsAttr()
    {
        return $this->hasMany(SalerToolsGoodsAttr::class, 'goods_id', 'goods_id')->order('sort', 'desc');
    }


    /**
     * 关联订单金额
     */
    public function orderMoneys()
    {
        return $this->hasOne(Order::class, 'goods_id', 'goods_id')->bind(['order_money' => 'money']);
    }
}
