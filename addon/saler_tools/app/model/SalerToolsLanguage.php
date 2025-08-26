<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/7 19:17
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;

/**
 *
 * Class SalerToolsLanguage
 * @package addon\saler_tools\app\model
 */
class SalerToolsLanguage extends BaseModel
{
    protected $pk = 'id';

    protected $name = 'saler_tools_language';

    protected $type = [
        'version' => 'int'
    ];

}
