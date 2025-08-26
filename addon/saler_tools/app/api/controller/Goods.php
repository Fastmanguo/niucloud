<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/6 0:12
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\api\controller;

use addon\saler_tools\app\api\service\GoodsService;
use addon\saler_tools\app\common\BaseApiController;

/**
 *
 * Class Goods
 * @package addon\saler_tools\app\api\controller
 */
class Goods extends BaseApiController
{


    /**
     * 商品列表
     */
    public function lists()
    {
        $data = $this->_vali([
            'store_id.query'      => '',
            'recycling_uid.query' => '',
            'appraiser_uid.query' => '',
            'create_uid.query'    => '',
            'category_id.query'   => '',
            'search.query'        => ''
        ]);
        return app(GoodsService::class)->lists($data);
    }

    /**
     * 商品详情
     */
    public function detail($goods_id)
    {
        return app(GoodsService::class)->detail($goods_id);
    }


}
