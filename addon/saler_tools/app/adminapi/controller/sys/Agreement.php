<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/13 20:51
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\sys;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\sys\AgreementService;

/**
 *
 * Class Agreement
 * @package addon\saler_tools\app\adminapi\controller\sys
 */
class Agreement extends BaseAdminController
{

    public function index($key)
    {
        return app(AgreementService::class)->getAgreement($key);
    }

}
