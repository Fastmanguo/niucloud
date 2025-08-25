<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/22 21:27
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\channel;

use addon\saler_tools\app\common\Utils;
use EasyWeChat\Kernel\HttpClient\Response;
use EasyWeChat\MiniApp\Application;
use think\helper\Str;

/**
 * 微信小程序渠道服务
 * Class WeappService
 * @package addon\saler_tools\app\service\channel
 */
class WeappService extends ChannelFactory
{

    /** @var */
    private $app;

    function initialize()
    {
        $config = array(
            'app_id'  => $this->config['appid'],
            'secret'  => $this->config['secret'],
            'token'   => $this->config['token'] ?? '',
            'aes_key' => $this->config['encryption_type'] ?? '',// 明文模式请勿填写 EncodingAESKey
            'http'    => [
                'throw'   => true, // 状态码非 200、300 时是否抛出异常，默认为开启
                'timeout' => 5.0,
                'retry'   => true, // 使用默认重试配置
            ],
        );

        $this->app = new Application($config);

    }

    /**
     * 生产小程序码
     */
    public function getwxacodeunlimit($page, $scene)
    {

        /** @var Response $response */
        $response = $this->app->getClient()->postJson('/wxa/getwxacodeunlimit', [
            'scene'      => '123',
            'page'       => 'pages/index/index',
            'width'      => 430,
            'check_path' => false,
        ]);
        $dir      = 'upload/share_image/' . date('Y/m/d');

        if (!is_dir($dir)) {
            mkdir(public_path($dir), 0777, true);
        }
        $file = $dir . Utils::createno('share_image') . '_' . Str::random() . '.jpg';
        $response->saveAs(public_path($dir) . Utils::createno('share_image') . '_' . Str::random() . '.jpg');
        return $file;
    }


}
