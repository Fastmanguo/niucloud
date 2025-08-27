<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/17 20:35
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\goods\GoodsService;

/**
 * 商品管理
 * Class Goods
 * @package addon\saler_tools\app\adminapi\controller
 */
class Goods extends BaseAdminController
{


    public function lists()
    {
        $data = $this->_vali([
            'store_id.query'         => '',
            'is_sale.query'          => '',
            'recycling_uid.query'    => '',
            'appraiser_uid.query'    => '',
            'create_uid.query'       => '',
            'category_id.query'      => '',
            'search.query'           => '',
            'watch_location.query'   => '',
            'goods_attribute.query'  => '',
            'goods_tag.query'        => '',
            'target_audience.query'  => '',
            'recycling_time.query'   => '',
            'query_type.query'       => '',
            '__order__.query'        => '',
            // 价格查询
            'query_price_type.query' => '',
            'query_price_min.query'  => '',
            'query_price_max.query'  => '',
        ]);

        $order = $this->_order(['update_time', 'create_time', 'price'], ['update_time' => 'desc'], ['goods_id' => 'desc']);

        return app(GoodsService::class)->lists($data, $order);
    }


    public function detail($goods_id)
    {
        return app(GoodsService::class)->detail($goods_id);
    }


    /**
     * 发布商品
     */
    public function add()
    {
        $data = $this->_vali([
            'category_id.require'           => 'category_id_require',
            'goods_cover.default'           => '',
            'goods_video.default'           => '',
            'goods_image.require'           => 'goods_image_be_empty',
            'detail_image.default'          => [],
            'goods_name.require'            => 'goods_name_require',
            'goods_desc.require'            => '商品描述不能为空',
            'goods_attachment.default'      => '', // 商品属性 全新 二手？
            'goods_attribute.default'       => [], // 商品附件
            'goods_tag.default'             => [],
            'goods_tips.default'            => [],
            'goods_sku.default'             => '',
            'goods_code.default'            => '',
            'brand_id.default'              => 0,
            'condition.default'             => '',
            'template_id.default'           => '',
            'series_id.default'             => '',
            'watch_location.default'        => '',
            'store_id.default'              => 0,
            'model_id.default'              => '',
            'price.default'                 => 0,
            'guide_price.default'           => 0,
            'peer_price.default'            => 0,
            'agent_price.default'           => '',
            // 商品成本
            'total_cost.default'            => 0,
            'additional_total_cost.default' => 0,
            'initial_cost.default'          => 0,
            // 库存
            'stock.default'                 => 1,
            // 回收信息
            'recycle_type.default'          => '',
            'recycling_uid.default'         => 0,
            'recycling_time.query'          => '',
            'recycling_image.default'       => [],
            'appraiser_uid.default'         => 0,
            // 商品备注
            'remark.default'                => '',
            'remark_image.default'          => [],
            'is_sale.default'               => 0,

            'target_audience.default'       => 0,
            'product_warranty_card.default' => 0,
            'warranty_card_image.default'   => 0,
            // 商品参数
            'goods_attr.default'            => [],
            'goods_cost.default'            => [],
            'attachment_list.default'       => [],
            'currency_code.default'         => 'CNY',
            'is_online_expo.default'        => 1,

            // 质押商品属性
            'contact_entrusted.query'       => '', // 委托联系方式（质押/寄卖都与此项关联）
            'expire_time.query'             => '',
        ]);

        return app(GoodsService::class)->add($data);

    }


    /**
     * 修改商品
     */
    public function edit()
    {
        $data = $this->_vali([
            'goods_id.require'              => 'goods_id_require',
            'category_id.require'           => 'category_id_require',
            'goods_cover.default'           => '',
            'goods_video.default'           => '',
            'goods_image.require'           => 'goods_image_be_empty',
            'detail_image.default'          => [],
            'goods_name.require'            => 'goods_name_require',
            'goods_desc.default'            => '',
            'goods_attachment.default'      => '', // 商品属性 全新 二手？
            'goods_attribute.default'       => [], // 商品附件
            'goods_tag.default'             => [],
            'goods_tips.default'            => [],
            'goods_sku.default'             => '',
            'goods_code.default'            => '',
            'brand_id.default'              => 0,
            'condition.default'             => '',
            'template_id.default'           => '',
            'series_id.default'             => '',
            'watch_location.default'        => '',
            'store_id.default'              => 0,
            'model_id.default'              => '',
            'price.default'                 => 0,
            'guide_price.default'           => 0,
            'peer_price.default'            => 0,
            'agent_price.default'           => '',
            // 商品成本
            'total_cost.default'            => 0,
            'additional_total_cost.default' => 0,
            'initial_cost.default'          => 0,
            // 库存
            'stock.default'                 => 1,
            // 回收信息
            'recycle_type.default'          => '',
            'recycling_uid.default'         => 0,
            'recycling_time.query'          => '',
            'recycling_image.default'       => [],
            'appraiser_uid.default'         => 0,
            // 商品备注
            'remark.default'                => '',
            'remark_image.default'          => [],
            'is_sale.default'               => 0,

            'target_audience.default'       => 0,
            'product_warranty_card.default' => 0,
            'warranty_card_image.default'   => 0,
            // 商品参数
            'goods_attr.default'            => [],
            'goods_cost.default'            => [],
            'attachment_list.default'       => [],
            'currency_code.default'         => 'CNY',
            'is_online_expo.query'          => '',

            // 质押商品属性
            'contact_entrusted.query'       => '', // 委托联系方式（质押/寄卖都与此项关联）
            'expire_time.query'             => '',
        ]);

        $data['goods_cover'] = $data['goods_image'][0] ?? '';

        return app(GoodsService::class)->edit($data);
    }


    /**
     * 商品下架
     */
    public function offSale()
    {
        $data = $this->_vali([
            'goods_id.require' => 'goods_id_require',
        ], 'put');

        return app(GoodsService::class)->offSale($data['goods_id']);
    }


    /**
     * 商品上架
     */
    public function onSale()
    {
        $data = $this->_vali([
            'goods_id.require' => 'goods_id_require',
        ], 'put');

        return app(GoodsService::class)->onSale($data['goods_id']);
    }


    /**
     * 修改成本
     */
    public function editCost()
    {

        $data = $this->_vali([
            'goods_id.require'              => 'goods_id_require',
            'total_cost.require'            => 'total_cost_require',
            'initial_cost.require'          => 'initial_cost_require',
            'additional_total_cost.require' => 'additional_total_cost_require',
            'goods_cost.default'            => []
        ]);

        $goods_id = $data['goods_id'];

        return app(GoodsService::class)->editCost($goods_id, $data);
    }


    /**
     * 修改商品鉴定人
     */
    public function editAppraiser()
    {

        $data = $this->_vali([
            'goods_id.require'      => 'goods_id_require',
            'appraiser_uid.default' => 0,
        ]);


        return app(GoodsService::class)->editAppraiser($data);
    }


    /**
     * 修改商品位置
     */
    public function editWatchLocation()
    {
        $data = $this->_vali([
            'goods_id.require'       => 'goods_id_require',
            'watch_location.default' => '',
        ], 'put');

        return app(GoodsService::class)->editWatchLocation($data);
    }


    /**
     * 商品存放情况
     */
    public function positionStatistics()
    {
        return app(GoodsService::class)->positionStatistics();
    }


    /**
     * 仓库统计
     */
    public function storeStatistics()
    {
        $data = $this->_vali([
            'store_id.query'        => '',
            'is_sale.query'         => '',
            'recycling_uid.query'   => '',
            'appraiser_uid.query'   => '',
            'create_uid.query'      => '',
            'category_id.query'     => '',
            'search.query'          => '',
            'watch_location.query'  => '',
            'goods_attribute.query' => '',
            'goods_tag.query'       => '',
            'target_audience.query' => '',
            'recycling_time.query'  => '',
            'query_type.query'      => '',
        ]);
        return app(GoodsService::class)->storeStatistics($data);
    }


    /**
     * 移动商品仓库
     */
    public function moveGoods()
    {
        $data = $this->_vali([
            'goods_id.require' => 'goods_id_require',
            'store_id.require' => 'store_id_require',
        ], 'put');
        return app(GoodsService::class)->moveGoods($data);
    }


    /**
     * 删除商品
     */
    public function del($goods_id)
    {
        return app(GoodsService::class)->del($goods_id);
    }


    /**
     * 批量删除
     */
    public function batchDel()
    {
        $data = $this->_vali([
            'goods_ids.require' => 'goods_id_require',
        ], 'delete');

        return app(GoodsService::class)->batchDel($data['goods_ids']);
    }


}
