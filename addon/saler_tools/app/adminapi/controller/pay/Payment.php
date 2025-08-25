<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/5/14 22:50
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\pay;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\pay\PaymentService;

/**
 * 支付
 * Class Payment
 * @package addon\saler_tools\app\adminapi\controller
 */
class Payment extends BaseAdminController
{

    /**
     * 获取订单信息
     */
    public function info()
    {
        $data = $this->_vali([
            'trade_id.query' => '',
            'trade_no.query' => ''
        ]);

        if (empty($data)) return fail();

        return app(PaymentService::class)->info($data);

    }

    /**
     * 对订单进行支付
     */
    public function pay()
    {
        $data = $this->_vali([
            'trade_id.query' => '',
            'trade_no.query' => '',
            'type.require'   => 'pay.notHavePayType'
        ]);

        return app(PaymentService::class)->pay($data);
    }


    /**
     * 获取支付方式
     */
    public function payType()
    {

    }

}
