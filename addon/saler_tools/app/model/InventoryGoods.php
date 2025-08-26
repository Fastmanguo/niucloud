<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/25 7:36
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;
use think\db\Query;

/**
 *
 * Class InventoryGoods
 * @package addon\saler_tools\app\model
 */
class InventoryGoods extends BaseModel
{

    protected $pk = ['goods_id', 'inventory_id'];


    protected $name = 'saler_tools_inventory_goods';


    public function searchStatusAttr(Query $query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        if (is_string($value) && str_contains($value, ',')) {
            $query->whereIn($query->getName() . '.status', explode(',', $value));
        } else if (is_array($value)) {
            $query->whereIn($query->getName() . '.status', $value);
        } else {
            $query->where($query->getName() . '.status', $value);
        }
    }

    public function searchInventoryIdAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('inventory_id', $value);
    }


    public function goods()
    {
        return $this->hasOne(Goods::class, 'goods_id', 'goods_id')
            ->bind(['goods_cover', 'goods_name', 'goods_tag', 'goods_code', 'condition', 'watch_location', 'create_time']);
    }

}
