<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/20 23:39
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;

/**
 *
 * Class NotifyTemplateService
 * @package addon\saler_tools\app\model
 */
class NotifyTemplate extends BaseModel
{

    protected $pk = 'id';

    protected $name = 'saler_tools_notify_template';

    protected $json = ['email'];

    protected $jsonAssoc = true;

    protected $type = [
        'is_use' => 'int'
    ];

    public function language()
    {
        return $this->hasOne(SalerToolsLanguage::class, 'key', 'lang')->bind(['lang_name' => 'name']);
    }

}
