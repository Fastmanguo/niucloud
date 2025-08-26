<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/21 22:29
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\charge\ChargeService;

/**
 *
 * Class SiteCharge
 * @package addon\saler_tools\app\adminapi\controller\admin
 */
class SiteCharge extends BaseAdminController
{

    public function type()
    {
        return success(app(ChargeService::class)->getTypeList());
    }

    public function list()
    {
        $data = $this->_vali([
            'charge_type.require' => '请选择套餐类型'
        ]);
        return app(ChargeService::class)->list($data);
    }


    public function add()
    {
        $data = $this->_vali([

        ]);

    }


    public function edit()
    {

    }


}
