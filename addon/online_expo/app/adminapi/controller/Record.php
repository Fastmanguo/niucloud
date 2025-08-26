<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/20 1:57
// +----------------------------------------------------------------------

namespace addon\online_expo\app\adminapi\controller;

use addon\online_expo\app\service\RecordService;
use addon\saler_tools\app\common\BaseAdminController;

/**
 *
 * Class Record
 * @package addon\online_expo\app\adminapi\controller
 */
class Record extends BaseAdminController
{

    public function lists()
    {
        $data = $this->_vali([
            'type.default' => 1
        ]);
        return app(RecordService::class)->lists($data);
    }

}
