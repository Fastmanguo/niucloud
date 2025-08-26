<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/28 5:07
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;
use app\model\sys\SysUser;
use think\model\concern\SoftDelete;

/**
 * 商家地址
 * Class Address
 * @package addon\saler_tools\app\model
 */
class Address extends BaseModel
{

    use SoftDelete;

    protected $deleteTime = 'deleted_time';

    protected $defaultSoftDelete = 0;


    protected $pk = 'address_id';

    protected $name = 'saler_tools_address';

    public function createNames()
    {
        return $this->hasOne(SysUser::class,'uid','uid')->bind(['create_name' => 'real_name']);
    }


}
