<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/11 4:18
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;
use app\model\sys\SysUser;
use think\model\concern\SoftDelete;

/**
 *
 * Class SalerToolsStore
 * @package addon\saler_tools\app\model
 */
class SalerToolsStore extends BaseModel
{

    use SoftDelete;

    protected $pk = 'store_id';

    protected $name = 'saler_tools_store';

    protected $readonly = ['site_id'];

    protected $deleteTime = 'deleted_time';

    protected $autoWriteTimestamp = false;

    protected $defaultSoftDelete = 0;

    protected $type = [
        'deleted_time' => 'int'
    ];

    public function createBy()
    {
        return $this->hasOne(SysUser::class, 'uid', 'create_uid')->bind(['create_name' => 'real_name']);
    }

}
