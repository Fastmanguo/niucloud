<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/5/14 22:52
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\pay;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\common\Utils;
use addon\saler_tools\app\service\channel\ChannelService;
use addon\saler_tools\app\service\shop\ShopService;
use app\model\pay\Pay as PayModel;
use Yansongda\Pay\Pay;

/**
 * 支付服务
 * Class PaymentService
 * @package addon\saler_tools\app\service\pay
 */
class PaymentService extends BaseAdminService
{

    const PAY_TYPE = [
        [
            'key'   => 'alipay',
            'label' => 'alipay',
            'icon'  => 'upload/pay_icon/alipay.png'
        ],
        [
            'key'   => 'wechat_pay',
            'label' => 'wechat_pay',
            'icon'  => 'upload/pay_icon/wechatpay.png'
        ]
    ];

    public function pay($data)
    {
        $pay_model = new PayModel();
        $type      = $data['type'];
        $pay_info  = $pay_model->where('main_id', $this->site_id)->withSearch(['trade_id', 'trade_no'], $data)->findOrEmpty();

        if ($pay_info->isEmpty()) return fail('fail_pay_info');

        $info = (new CorePaymentService($type))->service()->app($pay_info->toArray());

        $pay_info->status = 1;

        $pay_info->save();

        return success([
            'status'   => $pay_info->status,
            'pay_info' => $info,
        ]);
    }


    /**
     *
     */
    public function info($data)
    {

        $pay_model = new PayModel();

        $pay_info = $pay_model->where('main_id', $this->site_id)
            ->field('out_trade_no,trade_type,trade_id,trade_no,body,money,voucher,status,pay_time,cancel_time,currency_code')
            ->withSearch(['trade_id', 'trade_no'], $data)
            ->findOrEmpty();

        if ($pay_info->isEmpty()) return fail('fail_pay_info');

        $pay_info->pay_type_list = self::PAY_TYPE;


        return success($pay_info->toArray());
    }


    /**
     * 创建交易流水
     * @param $trade_id integer 业务id
     * @param $main_id integer 商户id
     * @param $trade_no string 业务单号
     * @param $trade_type string 业务类型
     * @param $money float 金额
     * @param $body string 业务描述
     * @param $currency_code string 货币代码
     */
    public function create($trade_id, $main_id, $trade_no = '', $trade_type = '', $money = 0, $body = '', $currency_code = 'CNY')
    {
        $pay_model = new PayModel();

        $out_trade_no = Utils::createno('saler_tools_pay');

        $data = [
            'trade_id'      => $trade_id,
            'main_id'       => $main_id,
            'trade_no'      => $trade_no,
            'trade_type'    => $trade_type,
            'money'         => $money,
            'body'          => $body,
            'out_trade_no'  => $out_trade_no,
            'create_time'   => time(),
            'site_id'       => 0,
            'currency_code' => $currency_code,
        ];

        if ((float)$money == 0) {
            $data['status'] = 2;
        }

        $pay_model->create($data);

        return $out_trade_no;

    }

}
