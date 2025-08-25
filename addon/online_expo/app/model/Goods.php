<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/7 1:23
// +----------------------------------------------------------------------

namespace addon\online_expo\app\model;

use addon\saler_tools\app\model\Collect;
use addon\saler_tools\app\model\Goods as BaseGoodsModel;

/**
 *
 * Class Goods
 * @package addon\online_expo\app\model
 */
class Goods extends BaseGoodsModel
{

    /** 关联上收藏标识 */
    public function collect()
    {
        return $this->hasOne(Collect::class, 'relate_id', 'goods_id');
    }


    public function searchSiteIdAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('site_id', $value);
    }

}
