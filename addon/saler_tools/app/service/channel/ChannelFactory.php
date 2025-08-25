<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/22 21:25
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\channel;

/**
 *
 * Class ChannelFactory
 * @package addon\saler_tools\app\service\channel
 */
abstract class ChannelFactory
{

    /** @var array|mixed 渠道配置 */
    protected $config;

    abstract function initialize();

    public function __construct($channel)
    {
        $this->config = app(ChannelService::class)->config($channel);
        $this->initialize();
    }

}
