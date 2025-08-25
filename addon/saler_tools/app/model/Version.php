<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/19 18:28
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;

/**
 * 版本管理
 * Class Version
 * @package addon\saler_tools\app\model
 */
class Version extends BaseModel
{
    protected $pk = 'id';

    protected $name = 'saler_tools_version';

}
