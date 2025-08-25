<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/25 19:54
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\goodsPool\GoodsPoolService;

/**
 *
 * Class GoodsPool
 * @package addon\saler_tools\app\adminapi\controller
 */
class GoodsPool extends BaseAdminController
{

    public function lists()
    {
        $data = $this->_vali([
            'brand_id.query'  => '',
            'series_id.query' => '',
            'model_id.query'  => '',
            'search.query'    => '',
        ]);

        return app(GoodsPoolService::class)->lists($data);
    }


    public function detail($id)
    {
        return app(GoodsPoolService::class)->detail($id);
    }

}
