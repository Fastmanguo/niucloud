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
 * 微信支付
 * Class WechatPayService
 * @package addon\saler_tools\app\service\pay
 */
class WechatPayService
{
    public function app($data)
    {
        $orderInfo = Pay::wechat()->app([
            'out_trade_no' => $data['out_trade_no'],
            'amount'       => [
                'total'          => $data['money'] * 100,
                'currency'       => 'CNY',
            ],
            'description'  => 'pay order' . $data['body']
        ])->toArray();

        return [
            'provider'  => 'wechat',
            'orderInfo' => $orderInfo,
        ];
    }


}
