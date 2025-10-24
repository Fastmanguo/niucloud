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

    /**
     * @return \think\Response
     * 我的订单 发货-待发货统计
     */
    public function goodsOrderCount()
    {
        $data = $this->request->params([
            ['site_id',""],
            ['create_uid',""],
        ]);
        return app(OrderService::class)->goodsOrderCount($data['site_id'],$data['create_uid']);
    }

    /**
     * @return \think\Response
     * 订单统计
     */
    public function getCount()
    {
         $data = $this->_vali([
            'start_time.default'   => '',
            'end_time.default'     => '',
            'search.default'       => '',
            'site_id.require'     => '店铺id不能为空',
        ]);
        
        if($data['start_time']){
            $all_data['start_time'] = $data['start_time'];
            $all_data['end_time'] = $data['end_time'];
        }
        if($data['search']){
            $all_data['search'] = $data['search'];
        }

        $all_data['type'] = "sale";
        $all = app(OrderService::class)->lists($all_data,['order_id'=>'desc'],1,$data['site_id']);

        $d_all_data = $all_data;
        $d_all_data['is_delivery'] = 0;
        $d_all_data['is_paid'] = 1;
        $d_all = app(OrderService::class)->lists($d_all_data,['order_id'=>'desc'],1,$data['site_id']);

        $yfh_all_data = $all_data;
        $yfh_all_data['is_delivery'] = 1;
        $yfh_all_data['is_paid'] = 1;
        $yfh_all = app(OrderService::class)->lists($yfh_all_data,['order_id'=>'desc'],1,$data['site_id']);

        $y_all_data = $all_data;
        $y_all_data['order_status'] = "CANCEL_ORDER";
        $y_all = app(OrderService::class)->lists($y_all_data,['order_id'=>'desc'],1,$data['site_id']);

        $djk_all_data = $all_data;
        $djk_all_data['is_paid'] = 0;
        // $djk_all_data['order_status'] = ['<>', 'CANCEL_ORDER'];
        $djk_all = app(OrderService::class)->lists($djk_all_data,['order_id'=>'desc'],1,$data['site_id']);


        return success([
            'all' => $all,
            'd_shipped' => $d_all,
            'y_cancel' => $y_all,
            'yfh_all' => $yfh_all,
            'djk_all' => $djk_all,
        ]);
    }



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
            'goods_attribute.default'        => '',
            'customer_id.default'           => '',
            'category_id.default'           => '',
            "goods_attr_list.default"       => '',

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
            'shipment_type.require' => '请输入发货方式',
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
            
            'goods_attribute.default'        => '',
            'customer_id.default'           => '',
            'category_id.default'           => '',
            "goods_attr_list.default"       => '',
            'shipment_type.require' => '请输入发货方式',
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

    /**
     * 取消锁单
     */
    public function lockCancel(){
        $data = $this->_vali([
            'order_id.require'       => '请选择锁单订单id',
        ]);
        return app(OrderService::class)->lockCancel($data);
    }


    /**
     * 编辑锁单数据
     */
    public function lockEdit(){
        $data = $this->_vali([
            'sale_uids.default'       => [],
            'deposit.default'         => 0,
            'goods_attr_list.require' => '请选择商品规格',
            'payment_receipt.default' => "",
            'exp_trans_price.default' => 0,
            'address_info.default'    => '',
            'lock_remark.default'     => '',
            'order_id.require'        => '请选择锁单订单id',
            // 'goods_num.min:1'         => 'order_goods_num_min',
        ]);
        $goods_num_list = [];
        foreach($data['goods_attr_list'] as $key => $val){
            $goods_num_list[] = $val['lock_goods_num'];
        }
        $data['goods_num'] = array_sum($goods_num_list);
        $data['update_time'] = date('Y-m-d H:i:s');
        $data['goods_attr_list'] = json_encode($data['goods_attr_list'], JSON_UNESCAPED_UNICODE);
        if($data['payment_receipt']){
            $data['payment_receipt'] = json_encode($data['payment_receipt'], JSON_UNESCAPED_UNICODE);
        }
        // return success($data);
        return app(OrderService::class)->lockEdit($data);
    }

    public function lock()
    {
        $data = $this->_vali([
            'goods_id.require'        => '请选择锁单商品id',
            'uid.require'             => '请输入锁单人id',
            'site_id.require'         => '请输入站点id',
            'sale_uids.default'       => [],
            'deposit.default'         => 0,
            'goods_attr_list.require' => '请选择商品规格',
            'payment_receipt.default' => [],
            'exp_trans_price.default' => 0,
            'address_info.default'    => '',
            'lock_remark.default'     => '',
            // 'goods_num.min:1'         => 'order_goods_num_min',
        ]);
        return success($data);
        $goods_num_list = [];
        foreach($data['goods_attr_list'] as $key => $val){
            $goods_num_list[] = $val['lock_goods_num'];
        }
        $data['goods_num'] = array_sum($goods_num_list);
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

            'paid_type.require' => '请输入结款方式',
            'shipment_type.require' => '请输入发货方式',
            'logistics_code.default' => '',
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
            'logistics_code.default' => '',
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

    /**
     * 锁单金额统计
     */
    public function lockStatistics()
    {
        $data = $this->_vali([
            'site_id.require' => '请输入站点id',
            'uid.default'     => "",
        ]);
        return app(OrderService::class)->lockStatistics($data);
    }

    /**
     * 锁单比例统计
     */
    public function lockProportion()
    {
        $data = $this->_vali([
            'site_id.require' => '请输入站点id',
            'uid.default'     => "",
        ]);
        return app(OrderService::class)->lockProportion($data);
    }

    /**
     * 获取店铺员工信息
     */
    public function getPersonInfo()
    {
        $data = $this->_vali([
            'site_id.require' => '请输入站点id',
        ]);
        return app(OrderService::class)->getPersonInfo($data);
    }

    /**
     * 记账类型添加
     */
    public function addType()
    {
        $data = $this->_vali([
            'type_name.require' => '请输入记账类型名称',
            "status.default" => 1,
            'uid.require'     => "请输入用户id",
        ]);
        $data['create_time'] = time();
        return app(OrderService::class)->addType($data);
    }

    /**
     * 记账类型删除
     */
    public function delType()
    {
        $data = $this->_vali([
            'type_id.require' => '请输入记账类型id',
        ]);
        return app(OrderService::class)->delType($data);
    }

    /**
     * 记账类型列表
     */
    public function typeList()
    {   
        $data = $this->_vali([
            "status.default" => 1,
            'uid.require'     => "请输入用户id",
        ]);
        return app(OrderService::class)->typeList($data['status'],$data['uid']);
    }


}
