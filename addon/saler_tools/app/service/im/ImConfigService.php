<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/3/28 22:53
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\im;

use addon\saler_tools\app\common\BaseService;
use app\service\core\sys\CoreConfigService;
use think\facade\Cache;

/**
 * im配置服务
 * Class ImConfigService
 * @package addon\saler_tools\app\service\im
 */
class ImConfigService extends BaseService
{

    const IM_CONFIG_KEY = 'im_config';

    public function getConfig()
    {
        return cache_remember(self::IM_CONFIG_KEY, function () {
            $sys_service = new CoreConfigService();
            $config      = $sys_service->getConfigValue(0, self::IM_CONFIG_KEY);
            if (empty($config)) {
                return [
                    'server_url' => '',
                    'app_key'    => '',
                    'app_secret' => '',
                ];
            } else {
                return $config;
            }
        });
    }


    public function setConfig($data)
    {
        $sys_service = new CoreConfigService();
        $sys_service->setConfig(0, self::IM_CONFIG_KEY, $data);
        Cache::delete(self::IM_CONFIG_KEY);
        return success();
    }

}
