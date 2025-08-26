<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/11 2:55
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;

/**
 *
 * Class SalerToolsAtte
 * @package addon\saler_tools\app\model
 */
class SalerToolsAttr extends BaseModel
{

    protected $pk = 'attr_id';

    protected $readonly = 'site_id';

    protected $name = 'saler_tools_attr';

}
