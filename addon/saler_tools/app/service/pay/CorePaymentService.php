<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/5/16 6:46
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\pay;

use addon\saler_tools\app\service\channel\ChannelService;
use Yansongda\Pay\Pay;

/**
 * 支付驱动
 * Class CorePaymentService
 * @package addon\saler_tools\app\service\pay
 * @method  array app($data) app支付
 * @method  array weapp($data) 小程序支付
 */
class CorePaymentService
{

    private   $channel;
    protected $config;

    /**
     * 初始化
     * @param array $config
     * @param string $type
     * @return mixed
     */
    protected function payConfig(array $config, string $type)
    {
        return array_merge(
            [
                'logger' => [
                    'enable'   => true,
                    'file'     => root_path('runtime') . 'paylog' . DIRECTORY_SEPARATOR . date('Ym') . DIRECTORY_SEPARATOR . date('d') . '.log',
                    'level'    => env('app_debug') ? 'debug' : 'info', // 建议生产环境等级调整为 info，开发环境为 debug
                    'type'     => 'single', // optional, 可选 daily.
                    'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
                ],
                'http'   => [ // optional
                    'timeout' => 5.0,
                ]
            ],
            [
                $type => [
                    'default' => $config
                ]
            ],
            ['_force' => true]
        );
    }

    public function __construct($channel)
    {
        $config        = (new ChannelService())->config($channel);
        $this->channel = $channel;
        switch ($channel) {
            case 'alipay':
                $config['app_public_cert_path']    = url_to_path($config['app_public_cert_path'] ?? '');
                $config['alipay_public_cert_path'] = url_to_path($config['alipay_public_cert_path'] ?? '');
                $config['alipay_root_cert_path']   = url_to_path($config['alipay_root_cert_path'] ?? '');
                $config['mode']                    = Pay::MODE_NORMAL;
                $this->config                      = $this->payConfig($config, 'alipay');
                Pay::config($this->config);
                break;
            case 'wechat_pay':
                $config['private_key']   = url_to_path($config['private_key'] ?? '');
                $config['certificate']   = url_to_path($config['certificate'] ?? '');
                $config['mch_id']        = $config['mch_id'] ?? '';
                $config['v2_secret_key'] = $config['v2_secret_key'] ?? '';
                $config['notify_url']    = (string)url('notify/pay/' . $channel, [], false, true);
                $this->config            = $this->payConfig($config, 'wechat');
                Pay::config($this->config);
                break;
        }
    }

    public function service()
    {
        switch ($this->channel) {
            case 'alipay':
                return (new AlipayService());
            case 'wechat_pay':
                return (new WechatPayService());
        }
    }


}
