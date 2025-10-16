<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/13 20:13
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseController;
use addon\saler_tools\app\service\user\SourcingService;

/**
 * 定金找货
 * Class Sourcing
 * @package addon\saler_tools\app\adminapi\controller
 */
class Sourcing extends BaseController
{
    /**
     * 采购单添加
     * @return \think\Response
     */
    public function sourcingAdd()
    {
       $data = $this->_vali([
            'uid.require'   => "请输入当前登录用户id",
            'requirement.require'   => "请输入顾客要求",
            'mobile.default'   => "",
            'deposit_price.require'   => "请输入定金金额",
            'sale_uids.require'   => "请输入销售人员id",
            'delivery_time.default'   => "",
            'payment_images.default'   => "",
            'remarks.default'   => "",
        ]);
        if($data['delivery_time']){
            $data['delivery_time'] = strtotime($data['delivery_time']);
        }
        return (new SourcingService())->sourcingAdd($data);
    }

    /**
     * 采购单列表
     * @return \think\Response
     */
    public function sourcingLists()
    {
        $data = $this->_vali([
            'uid.require'   => "请输入当前登录用户id",
            'page.require'   => "请输入当前页码",
            'limit.require'   => "请输入每页显示数量",
            'search.default'   => "",
            'status.default'   => "",
            "create_time.default"   => "",
            "deposit_price.default"   => "",
            "delivery_time.default"   => "",
        ]);
        return (new SourcingService())->sourcingLists($data);
    }

    /**
     * 采购单详情
     * @return \think\Response
     */
    public function sourcingDetails()
    {
        $data = $this->_vali([
            'id.require'   => "请输入id",
        ]);
        return (new SourcingService())->sourcingDetails($data);
    }

    /**
     * 采购单商品添加
     * @return \think\Response
     */
    public function sourcingGoodsAdd()
    {
        $data = $this->_vali([
            'id.require'   => "请输入采购单id",
            'goods_id.require'   => "请输入商品id",
        ]);
        return (new SourcingService())->sourcingGoodsAdd($data);
    }

    /**
     * 采购单结束
     * @return \think\Response
     */
    public function sourcingEnd()
    {
        $data = $this->_vali([
            'id.require'   => "请输入采购单id",
        ]);
        return (new SourcingService())->sourcingEnd($data);
    }

    /**
     * 采购单重新找货
     * @return \think\Response
     */
    public function sourcingAgain()
    {
        $data = $this->_vali([
            'id.require'   => "请输入采购单id",
        ]);
        return (new SourcingService())->sourcingAgain($data);
    }

    /**
     * 采购单删除
     * @return \think\Response
     */
    public function sourcingDel()
    {
        $data = $this->_vali([
            'id.require'   => "请输入采购单id",
        ]);
        return (new SourcingService())->sourcingDel($data);
    }

    /**
     * 采购单编辑
     * @return \think\Response
     */
    public function sourcingEdit()
    {
        $data = $this->_vali([
            'id.require'   => "请输入采购单id",
            'requirement.require'   => "请输入顾客要求",
            'mobile.require'   => "请输入顾客手机号",
            'deposit_price.require'   => "请输入定金金额",
            'sale_uids.require'   => "请输入销售人员id",
            'payment_images.require'   => "请输入收款凭证",
            'remarks.require'   => "请输入备注",
            'balance_price.default'   => "",
            'price.default'   => "",
            'customer_type.default'   => "",
        ]);
        $data['update_time'] = time();
        return (new SourcingService())->sourcingEdit($data);
    }

    /**
     * 开单
     * @return \think\Response
     */
    public function sourcingBilling()
    {
        $data = $this->_vali([
            'id.require'   => "请输入采购单id",
            'goods_id.require'   => "请输入商品id",
            'order_id.require'   => "请输入订单id",
            'mobile.require'   => "请输入顾客手机号",
            'deposit_price.require'   => "请输入定金金额",
            'balance_price.require'   => "请输入尾款金额",
            'customer_type.require'   => "请输入客户类型",
            'delivery_time.default'   => "",
            'payment_images.require'   => "请输入收款凭证",
            'remarks.default'   => "",
        ]);
        $data['billing_time'] = time();
        $data['price'] = $data['deposit_price'] + $data['balance_price'];
        if($data['delivery_time']){
            $data['delivery_time'] = strtotime($data['delivery_time']);
        }
        return (new SourcingService())->sourcingBilling($data);
    }

    /**
     * 采购单统计
     * @return \think\Response
     */
    public function sourcingCount()
    {
        $data = $this->_vali([
            'uid.require'   => "请输入当前登录用户id",
        ]);
        return (new SourcingService())->sourcingCount($data);
    }
}
