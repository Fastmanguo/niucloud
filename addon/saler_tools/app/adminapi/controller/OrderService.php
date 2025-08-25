<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/20 2:42
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\order\OrderServiceService;

/**
 * 服务订单
 * Class OrderService
 * @package addon\saler_tools\app\adminapi\controller
 */
class OrderService extends BaseAdminController
{

    public function lists()
    {
        $data = $this->_vali([
            'status.query'      => '',
            'search.query'      => '',
            'create_uid.query'  => '',
            'sales_uid.query'   => '',
            'service_uid.query' => '',
        ]);
        return app(OrderServiceService::class)->lists($data);
    }


    public function stat()
    {
        return success([
            'order_count' => 0,
            'total_cost'  => 0,
            'total_money' => 0
        ]);
    }


    public function add()
    {
        $data                = $this->_vali([
            'goods_name.require'      => '商品名称不能为空',
            'goods_code.default'      => '',
            'goods_image.require'     => '商品照片不能为空',
            'expect_cost.default'     => 0,
            'additional_cost.default' => [],
            'expect_money.default'    => 0,
            'create_uid.default'      => 0,
            'sales_uid.default'       => 0,
            'service_uid.default'     => 0,
            'remark.default'          => '',
            'address_info.default'    => '',
        ]);
        $data['goods_cover'] = $data['goods_image'][0] ?? [];

        return app(OrderServiceService::class)->add($data);

    }


    public function edit()
    {
        $data = $this->_vali([
            'service_id.require'      => '商品名称不能为空',
            'goods_code.default'      => '',
            'goods_image.require'     => '商品照片不能为空',
            'expect_cost.default'     => 0,
            'additional_cost.default' => [],
            'expect_money.default'    => 0,
            'create_uid.default'      => 0,
            'sales_uid.default'       => 0,
            'service_uid.default'     => 0,
            'remark.default'          => '',
            'address_info.default'    => '',
        ]);

        $data['goods_cover'] = $data['goods_image'][0] ?? [];

        return app(OrderServiceService::class)->edit($data);

    }


    public function operate()
    {
        $data = $this->_vali([
            'service_id.require' => '订单ID不能为空',
            'status.require'     => '状态不能为空',
            'money.query'        => '',
            'paid_receipt.query' => '',
            'cost.query'         => '',
        ]);

        return app(OrderServiceService::class)->operate($data);
    }


    public function detail($service_id)
    {
        return app(OrderServiceService::class)->detail($service_id);
    }


}
