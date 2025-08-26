<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/28 5:40
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;
use app\model\sys\SysUser;
use think\model\concern\SoftDelete;

/**
 * 联系人
 * Class Contact
 * @package addon\saler_tools\app\model
 */
class Contact extends BaseModel
{

    use SoftDelete;

    protected $name = 'saler_tools_contact';

    protected $pk = 'contact_id';

    protected $deleteTime = 'deleted_time';

    protected $defaultSoftDelete = 0;

    public function byNames()
    {
        return $this->hasOne(SysUser::class,'uid','by_uid')->bind(['by_name' => 'real_name']);
    }

}
