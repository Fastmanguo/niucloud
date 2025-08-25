<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/23 20:19
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;

/**
 *
 * Class Captcha
 * @package addon\saler_tools\app\model
 */
class Captcha extends BaseModel
{

    protected $autoWriteTimestamp = false;

    protected $pk = 'id';

    protected $name = 'saler_tools_captcha';


}
