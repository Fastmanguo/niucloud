<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/6 1:53
// +----------------------------------------------------------------------

namespace addon\online_expo\app\adminapi\controller;

use addon\online_expo\app\service\GoodsService;
use addon\online_expo\app\service\ShopService;
use addon\saler_tools\app\common\BaseAdminController;

/**
 *
 * Class Goods
 * @package addon\online_expo\app\adminapi\controller
 */
class Goods extends BaseAdminController
{

    /**
     * TODO：需要将商品搜索和店铺搜索分开
     */
    public function lists()
    {
        $data = $this->_vali([
            'store_id.query'      => '',
            'site_id.query'       => '',
            'is_sale.query'       => '',
            'recycling_uid.query' => '',
            'appraiser_uid.query' => '',
            'create_uid.query'    => '',
            'category_id.query'   => '',
            'brand_id.query'      => '',  // 新增品牌ID参数
            'search.query'        => '',
            'query_type.default'  => 'goods',
            'country_code.query'  => '',
            'is_all.default'      => 0
        ]);

        $order = $this->_order(['create_time', 'peer_price'], ['update_time' => 'desc'], ['goods_id' => 'desc']);

        if ($data['query_type'] == 'goods') {
            return app(GoodsService::class)->lists($data,$order);
        } else {
            return app(ShopService::class)->lists($data);
        }


    }


    public function detail($goods_id)
    {
        return app(GoodsService::class)->detail($goods_id);
    }


}
