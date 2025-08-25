<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/5/16 6:50
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\pay;

use Yansongda\Pay\Pay;

/**
 * 支付宝支付
 * Class AlipayService
 * @package addon\saler_tools\app\service\pay
 */
class AlipayService
{

    /**
     * @param $data
     */
    public function pay($data)
    {
        return Pay::alipay()->app([
            'out_trade_no' => $data['out_trade_no'],
            'total_amount' => $data['money'],
            'subject'      => $data['body'],//用户付款中途退出返回商户网站的地址, 一般是商品详情页或购物车页
        ])->toArray();
    }

    public function app($data)
    {
        $orderInfo = Pay::alipay()->app([
            'out_trade_no' => $data['out_trade_no'],
            'total_amount' => $data['money'],
            'subject'      => $data['body'],//用户付款中途退出返回商户网站的地址, 一般是商品详情页或购物车页
        ])->getBody()->getContents();

        return [
            'provider'  => 'alipay',
            'orderInfo' => $orderInfo,
        ];

    }

    public function notify()
    {

    }

}
