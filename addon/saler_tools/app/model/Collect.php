<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/6 1:44
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;

/**
 *
 * Class Collect
 * @package addon\saler_tools\app\model
 */
class Collect extends BaseModel
{

    protected $name = 'saler_tools_collect';


    protected $pk = 'collect_id';


    protected $readonly = ['uid','site_id'];


    /**
     * 关联商品信息
     */
    public function goodsInfo()
    {
        return $this->hasOne(Goods::class, 'goods_id', 'relate_id')->bind(['goods_cover','goods_name','peer_price','currency_code']);
    }

}
