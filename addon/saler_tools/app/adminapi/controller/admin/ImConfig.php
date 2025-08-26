<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/3/28 23:34
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\im\ImConfigService;

/**
 * im服务配置
 * Class ImConfig
 * @package addon\saler_tools\app\adminapi\controller\admin
 */
class ImConfig extends BaseAdminController
{

    public function getConfig()
    {
        $res = app(ImConfigService::class)->getConfig();
        return success($res);
    }


    public function setConfig()
    {

        $data = $this->_vali([
            'server_url.require' => '服务器地址不能为空',
            'app_key.require'    => '通讯令牌不能为空',
            'app_secret.require' => '通讯密钥不能为空',
        ]);

        return app(ImConfigService::class)->setConfig($data);

    }

}
