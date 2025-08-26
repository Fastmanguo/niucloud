<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/12 4:04
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\GoodsSeriesService;

/**
 * 系列
 * Class Series
 * @package addon\saler_tools\app\adminapi\controller\admin
 */
class Series extends BaseAdminController
{

    public function lists()
    {
        $data = $this->_vali([
            'brand_id.query'    => '',
            'series_name.query' => ''
        ]);
        return app(GoodsSeriesService::class)->lists($data);
    }

}
