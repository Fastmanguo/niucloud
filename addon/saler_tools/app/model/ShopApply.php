<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/14 0:29
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;
use app\model\sys\SysUser;
use app\model\sys\SysUserRole;

/**
 * 店铺申请管理
 * Class ShopApply
 * @package addon\saler_tools\app\model
 */
class ShopApply extends BaseModel
{

    protected $name = 'saler_tools_shop_apply';

    protected $pk = 'id';

    protected $autoWriteTimestamp = false;


    public function createInfo()
    {
        return $this->hasOne(SysUser::class, 'uid', 'uid')->field('uid,real_name,last_ip');
    }

}
