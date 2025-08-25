<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/11 19:41
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\channel;

use addon\saler_tools\app\common\BaseService;
use app\service\core\sys\CoreConfigService;
use core\exception\AdminException;
use think\helper\Str;

/**
 *
 * Class ChannelService
 * @package addon\saler_tools\app\service\sys
 */
class ChannelService extends BaseService
{

    public function getBase()
    {
        return include root_path('addon/saler_tools/app/dict/sys') . 'channel.php';
    }


    public function detail($key)
    {

        $database_key = Str::upper('CHANNEL_' . $key);
        $data         = (new CoreConfigService())->getConfigValue(0, $database_key);

        $base_data = $this->getBase();

        $fields = array_column($base_data, null, 'key')[$key]['fields'];


        return success([
            'data'   => $data,
            'fields' => $fields,
        ]);
    }

    /**
     * 获取配置
     * @param $key
     * @return array|mixed
     */
    public function config($key)
    {
        $database_key = Str::upper('CHANNEL_' . $key);
        $data         = (new CoreConfigService())->getConfigValue(0, $database_key);
        return $data;
    }


    public function update($data)
    {
        $key = $data['key'];
        // 获取配置
        $base_data = $this->getBase();

        $fields = array_column($base_data, null, 'key')[$key]['fields'];

        $update = [];

        foreach ($fields as $field) {
            $update[$field['key']] = $data[$field['key']] ?? '';
        }

        $database_key = Str::upper('CHANNEL_' . $key);

        (new CoreConfigService())->setConfig(0, $database_key, $update);

        return success();
    }


    /**
     * @param $channel
     * @return WeappService
     */
    public function getChannelConnect($channel)
    {
        switch ($channel){
            case 'weapp':
                return new WeappService($channel);
                break;
            default:
                throw new AdminException('error');
        }
    }

}
