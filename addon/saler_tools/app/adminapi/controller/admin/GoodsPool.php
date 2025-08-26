<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/25 20:05
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\goodsPool\GoodsPoolService;

/**
 *
 * Class GoodsPool
 * @package addon\saler_tools\app\adminapi\controller\admin
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


    public function save()
    {
        $data = $this->_vali([
            'id.query'                   => '',
            'goods_name.require'         => '商品名称不能为空',
            'goods_image.require'        => '商品图片不能为空',
            'brand_id.default'           => 0,
            'series_id.default'          => 0,
            'model_id.default'           => 0,
            'model_no.default'           => '',
            'price.default'              => 0,
            'deal_avg_price.default'     => 0,
            'deal_avg_price_max.default' => 0,
            'deal_avg_price_min.default' => 0,
            'attr_data.query'            => '',
            'status.default'             => 1
        ]);

        $data['goods_cover'] = empty($data['goods_image']) ? '' : $data['goods_image'][0];

        return app(GoodsPoolService::class)->save($data);
    }

    public function del()
    {
        $data = $this->_vali([
            'id.query' => '',
            'ids.query' => ''
        ],'get');
        return app(GoodsPoolService::class)->del($data);
    }

}
