<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/29 23:17
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;
use app\model\sys\SysUser;
use app\model\sys\SysUserRole;

/**
 *
 * Class Shop
 * @package addon\saler_tools\app\model
 */
class Shop extends BaseModel
{

    protected $pk = 'site_id';

    protected $name = 'saler_tools_shop';

    protected $type = [
        'banner_list'        => 'array',
        'certificate_images' => 'array',
    ];


    public function createInfo()
    {
        return $this->hasOne(SysUser::class, 'uid', 'uid');
    }


    public function country()
    {
        return $this->hasOne(Country::class, 'country_code', 'country_code');
    }

}
