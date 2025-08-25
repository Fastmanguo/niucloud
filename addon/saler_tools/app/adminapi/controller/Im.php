<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/3/30 0:20
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\im\CoreImService;

/**
 *
 * Class Im
 * @package addon\saler_tools\app\adminapi\controller
 */
class Im extends BaseAdminController
{

    public function login()
    {
        $token = (new CoreImService())->userOnline();
        return success($token);
    }

}
