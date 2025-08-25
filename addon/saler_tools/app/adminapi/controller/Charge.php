<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/21 23:43
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\charge\ChargeService;

/**
 *
 * Class Charge
 * @package addon\saler_tools\app\adminapi\controller
 */
class Charge extends BaseAdminController
{

    public function list($charge_type)
    {
        return app(ChargeService::class)->getPriceList($charge_type);
    }

}
