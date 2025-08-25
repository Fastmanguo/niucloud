<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/6 1:45
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\CollectService;

/**
 *
 * Class Collect
 * @package addon\saler_tools\app\adminapi\controller
 */
class Collect extends BaseAdminController
{

    public function lists()
    {
        $data = request()->get();
        return app(CollectService::class)->lists($data);
    }


    public function check()
    {
        $data = $this->_vali([
            'relate_id.require' => '',
            'type_code.default' => ''
        ]);

        return app(CollectService::class)->check($data);

    }


    public function modify()
    {
        $data = $this->_vali([
            'relate_id.require' => '',
            'type_code.default' => '',
            'status.default'    => 0,
        ]);

        return app(CollectService::class)->modify($data);
    }


}
