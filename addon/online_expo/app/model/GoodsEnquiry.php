<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/18 23:57
// +----------------------------------------------------------------------

namespace addon\online_expo\app\model;

use addon\saler_tools\app\common\BaseModel;
use addon\saler_tools\app\model\Shop;
use app\model\sys\SysUser;

/**
 *
 * Class GoodsEnquiry
 * @package addon\online_expo\app\model
 */
class GoodsEnquiry extends BaseModel
{

    protected $name = 'online_expo_goods_enquiry';

    protected $pk = 'id';

    protected $readonly = [
        'site_id', 're_site_id'
    ];


    public function createNames()
    {
        return $this->hasOne(SysUser::class, 'uid', 'uid')->bind(['create_name' => 'real_name']);
    }

    public function reCreateNames()
    {
        return $this->hasOne(SysUser::class, 'uid', 're_uid')->bind(['create_name' => 'real_name']);
    }

    public function reShopNames()
    {
        return $this->hasOne(Shop::class, 'site_id', 're_site_id')->bind(['shop_name'=>'shop_name', 'shop_logo' => 'logo']);
    }

    public function shopNames()
    {
        return $this->hasOne(Shop::class, 'site_id', 'site_id')->bind(['shop_name', 'shop_logo' => 'logo']);
    }

}
