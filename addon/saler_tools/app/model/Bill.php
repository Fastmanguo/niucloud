<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/23 2:01
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;
use app\model\sys\SysUser;
use think\model\concern\SoftDelete;

/**
 * 对账单
 * Class Bill
 * @package addon\saler_tools\app\model
 */
class Bill extends BaseModel
{

    use SoftDelete;

    protected $deleteTime = 'deleted_time';

    protected $defaultSoftDelete = 0;

    protected $autoWriteTimestamp = 'datetime';

    protected $pk = 'bill_id';

    protected $name = 'saler_tools_bill';

    protected $readonly = ['site_id', 'uid'];


    public function createNames()
    {
        return $this->hasOne(SysUser::class, 'uid', 'uid')->bind(['create_name' => 'real_name']);
    }

}
