<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/23 0:22
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\bill\ShopBillService;

/**
 * 店铺对账单
 * Class ShopBill
 * @package addon\saler_tools\app\adminapi\controller
 */
class ShopBill extends BaseAdminController
{

    public function lists()
    {
        $data = $this->_vali([
            'is_recycle.query' => '', // 查询回收站标识
        ]);
        return app(ShopBillService::class)->lists($data);
    }


    public function create()
    {
        $data = $this->_vali([
            "bill_name.require"               => "",
            "is_own_goods.default"            => 0,
            "is_pawned_goods.default"         => 0,
            "is_others_goods.default"         => 0,
            "init_own_goods_money.default"    => 0,
            "init_pawned_goods_money.default" => 0,
            "init_others_goods_money.default" => 0,
            "user_money.default"              => 0
        ]);
        
        // 三选一必填项校验：is_own_goods、is_pawned_goods、is_others_goods 必须有一个为1
        if ($data['is_own_goods'] != 1 && $data['is_pawned_goods'] != 1 && $data['is_others_goods'] != 1) {
            return fail('请选择商品类型：自有商品、质押商品、其他商品必须选择其中一项');
        }
        
        return app(ShopBillService::class)->create($data);
    }


    public function del($bill_id)
    {
        return app(ShopBillService::class)->deleted($bill_id);
    }

    /**
     * 查询账单详情
     * @param $bill_id
     */
    public function detail()
    {
        $data = $this->_vali([
            'bill_id.require'   => '请选择要查询的账单',
            'date_time.require' => '请选择要查询的账单时间'
        ]);
        return app(ShopBillService::class)->detail($data);
    }

    /**
     * 查询账单变动记录
     */
    public function recordLists()
    {
        $data = $this->_vali([
            'bill_id.require'   => '请选择要查询的账单',
            'date_time.require' => '请选择要查询的账单时间'
        ]);
        return app(ShopBillService::class)->recordLists($data);
    }


    public function stat()
    {

    }


    /**
     * 查询产品类型总金额
     */
    public function queryStat()
    {
        return app(ShopBillService::class)->queryStat();
    }


}
