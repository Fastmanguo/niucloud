<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/24 17:45
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;

/**
 * 鉴定师鉴定记录
 * Class IdentifyLog
 * @package addon\saler_tools\app\model
 */
class IdentifyLog extends BaseModel
{

    protected $pk = 'log_id';

    protected $name = 'saler_tools_identify_log';

    public function identifyInfo()
    {
        return $this->hasOne(Identify::class, 'id', 'identify_id');
    }

    public function identifyUserInfo()
    {
        return $this->hasOne(IdentifyUser::class, 'uid', 'uid')->bind(['nickname','mobile']);
    }

}
