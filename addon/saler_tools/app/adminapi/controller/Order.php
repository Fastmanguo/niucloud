<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/20 19:41
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\order\OrderService;

/**
 * 订单
 * Class Order
 * @package addon\saler_tools\app\adminapi\controller
 */
class Order extends BaseAdminController
{


    public function lists()
    {
        $data = $this->_vali([
            'search.query'       => '',
            'order_id.query'     => '',
            'order_status.query' => '',
            'is_paid.query'      => '',
            'start_time.query'   => '',
            'end_time.query'     => '',
            'is_delivery.query'  => '',
            'type.default'       => 'sale',
        ]);

        // 如果是查询发货相关过滤掉无效订单
        if (isset($data['is_delivery'])) {
            $data['order_status'] = [OrderService::ADD_ORDER, OrderService::FINISH_ORDER];
        }

        // 订单编号转换 格式 #202501200000000001
        if (isset($data['search']) && preg_match('/^#\d/', $data['search'])) {
            $data['order_no'] = str_replace('#', '', $data['search']);
            unset($data['search']);
        }
        $order = $this->_order(['create_time'], [], ['order_id' => 'desc']);

        return app(OrderService::class)->lists($data,$order);
    }


    /**
     * 开单
     */
    public function add()
    {

        $data = $this->_vali([
            'goods_id.default'              => '',
            'goods_cover.default'           => '',
            'goods_name.default'            => '',
            'goods_code.default'            => '',
            'goods_image.default'           => [],
            'goods_num.default'             => '',
            'goods_price.default'           => 0,
            'money.default'                 => 0,
            'payment_receipt.default'       => 0,
            'deposit.default'               => 0,
            'exp_trans_price.default'       => 0,
            'lock_remark.default'           => '',
            'lock_receipt.default'          => [],
            'sale_uids.default'             => [],
            'total_cost.default'            => 0,
            'initial_cost.default'          => 0,
            'additional_cost.default'       => [],
            'additional_total_cost.default' => 0,
            'order_type.default'            => '',
            'currency_code.default'         => '',
            'address_info.default'          => '',
            'after_sales_service.default'   => [],
            'remark.default'                => '',
            'paid_receipt.default'          => [],
            'paid_remark.default'           => '',
            'transaction_time.query'        => '',// 销售时间
        ]);


        return app(OrderService::class)->add($data);
    }


    /**
     * 订单编辑
     */
    public function edit()
    {

        $data = $this->_vali([
            'order_id.require'            => 'please_select_order',
            'goods_cover.query'           => '',
            'goods_name.query'            => '',
            'goods_code.query'            => '',
            'goods_image.query'           => '',
            'goods_price.query'           => '',
            'money.query'                 => '',
            'payment_receipt.query'       => '',
            'deposit.query'               => '',
            'exp_trans_price.query'       => '',
            'lock_remark.query'           => '',
            'lock_receipt.query'          => '',
            'sale_uids.query'             => '',
            'total_cost.query'            => '',
            'initial_cost.query'          => '',
            'additional_cost.query'       => '',
            'additional_total_cost.query' => '',
            'order_type.query'            => '',
            'currency_code.query'         => '',
            'address_info.query'          => '',
            'after_sales_service.query'   => '',
            'remark.query'                => '',
            'paid_receipt.default'        => [],
            'paid_remark.default'         => '',
            'transaction_time.query'      => '',// 销售时间
            'order_status.query'          => '',
        ]);


        return app(OrderService::class)->edit($data);
    }


    public function detail($order_id)
    {
        $data = $this->_vali([
            'option.query' => '',
        ]);

        $data = array_merge($data, ['order_id' => $order_id]);

        return app(OrderService::class)->detail($data);
    }

    public function close()
    {
        $data = $this->_vali([
            'order_id.require'     => 'please_select_order',
            'close_remark.default' => '',
        ]);
        return app(OrderService::class)->close($data);
    }

    /**
     * 获取预开单信息
     */
    public function preAdd()
    {
        $data = $this->_vali([
            'goods_id.require' => '商品ID不能为空',
        ]);

        return app(OrderService::class)->preAdd($data);
    }


    /**
     * 解除锁单
     */
    public function unLock()
    {
        $data = $this->_vali([
            'order_id.require'       => 'please_select_order',
            'un_lock_remark.require' => '订单号不能为空',
        ]);
        return app(OrderService::class)->unLock($data);
    }

    public function lock()
    {
        $data = $this->_vali([
            'goods_id.require'        => 'please_select_goods',
            'lock_remark.default'     => '',
            'deposit.default'         => 0,
            'exp_trans_price.default' => 0,
            'address_info.default'    => '',
            'sale_uids.default'       => [],
            'goods_num.min:1'         => 'order_goods_num_min',
        ]);
        return app(OrderService::class)->lock($data);
    }


    /**
     * 结款结款
     */
    public function paid()
    {
        $data = $this->_vali([
            'order_id.require'     => 'please_select_order',
            'paid_receipt.default' => [],
            'paid_remark.default'  => '',
        ]);

        return app(OrderService::class)->paid($data);

    }


    /**
     * 退款
     */
    public function refund()
    {
        $data = $this->_vali([
            'order_id.require' => 'please_select_order',
        ]);

        return app(OrderService::class)->refund($data);
    }

    /**
     * 订单退货
     */
    public function refundGoods()
    {
        $data = $this->_vali([
            'order_id.require' => 'please_select_order',
        ]);

        return app(OrderService::class)->returnGoods($data);
    }


    /**
     * 获取锁单统计
     */
    public function lockStat()
    {
        $data = $this->_vali([
            'search.query'       => '',
            'order_id.query'     => '',
            'order_status.query' => '',
            'is_paid.query'      => '',
            'start_time.query'   => '',
            'end_time.query'     => '',
        ]);


        return app(OrderService::class)->lockStat($data);

    }


    /**
     * 订单发货
     */
    public function send()
    {
        $data = $this->_vali([
            'order_id.require'        => 'please_select_order',
            'delivery_remark.require' => '发货备注不能为空',
        ]);

        return app(OrderService::class)->send($data);
    }

    /**
     * 删除订单
     */
    public function deleted()
    {
        $data = $this->_vali([
            'order_id.require' => 'please_select_order',
        ]);

        return app(OrderService::class)->deleted($data);
    }


    /**
     * 赎回质押商品
     */
    public function ransom()
    {
        $data = $this->_vali([
            'goods_id.require' => 'please_select_goods',
            'money.require'    => 'please_input_money',
        ]);

        return app(OrderService::class)->ransom($data);
    }


}
