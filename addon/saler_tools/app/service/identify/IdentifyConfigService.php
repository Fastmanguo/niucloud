<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/5/15 0:51
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\identify;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\dict\sys\SystemDict;
use addon\saler_tools\app\service\admin\CountryService;
use addon\saler_tools\app\service\shop\ShopService;
use app\service\core\sys\CoreConfigService;

/**
 *
 * Class IdentifyConfigService
 * @package addon\saler_tools\app\service\identify
 */
class IdentifyConfigService extends BaseAdminService
{

    public function list()
    {
        $country_list = (new CountryService())->getList();
        $config       = (new CoreConfigService())->getConfigValue(0, SystemDict::IDENTIFY_CONFIG_KEY);

        foreach ($country_list as $key => &$item) {
            $item['open_identify']  = $config[$item['country_code']]['open_identify'] ?? 0;
            $item['identify_money'] = $config[$item['country_code']]['identify_money'] ?? 0;
        }

        return $country_list;
    }


    public function save($list)
    {
        // 以country_code为键 open_identify identify_money为内容返回新数组
        $newlist = array_reduce($list, function ($carry, $item) {
            // 将 country_code 作为键，保留指定字段组成的新数组作为值
            $carry[$item['country_code']] = [
                'open_identify'  => $item['open_identify'] ?? 0,
                'identify_money' => $item['identify_money'] ?? 0,
            ];
            return $carry;
        }, []);

        (new CoreConfigService())->setConfig(0, SystemDict::IDENTIFY_CONFIG_KEY, $newlist);

        return success();
    }


    public function ckeck()
    {
        $config = (new CoreConfigService())->getConfigValue(0, SystemDict::IDENTIFY_CONFIG_KEY);

        $shop = (new ShopService())->info();

        if (!isset($config[$shop['country_code']]) || $config[$shop['country_code']]['open_identify'] == 0) {
            return false; // 暂不支持鉴定
        }

        return [
            'identify_money'          => $config[$shop['country_code']]['identify_money'],
            'identify_original_money' => $config[$shop['country_code']]['identify_money'],
            'currency_code'           => 'CNY',
        ];

    }

}
