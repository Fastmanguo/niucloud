<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/20 21:10
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\notify;

/**
 *
 * Class Notify
 * @package addon\saler_tools\app\service\notify
 */
abstract class Notify
{
    /** @var array $config 配置信息 */
    protected static $config = null;

    public function __construct()
    {
        $this->init();
    }

    /**
     * 初始化
     */
    abstract public function init();


    abstract public function send($data);


    abstract public function sendTemplate($data);

}
