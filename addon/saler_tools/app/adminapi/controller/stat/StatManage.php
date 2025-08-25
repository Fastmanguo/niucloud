<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/6/1 15:46
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\stat;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\stat\StatManageService;

/**
 * 店铺经营报表
 * Class StatManage
 * @package addon\saler_tools\app\adminapi\controller\stat
 */
class StatManage extends BaseAdminController
{

    public function getStat()
    {
        $data = $this->_vali([
            'month_time.default' => ''
        ]);
        $data['site_id'] = $this->site_id;

        if (empty($data['month_time'])) $data['month_time'] = strtotime('first day of this month');

        return success(app(StatManageService::class)->getStat($data));
    }

}
