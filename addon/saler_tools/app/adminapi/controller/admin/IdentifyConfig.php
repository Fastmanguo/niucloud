<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/5/15 0:50
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\identify\IdentifyConfigService;

/**
 * 鉴定估价配置
 * Class IdentifyConfig
 * @package addon\saler_tools\app\adminapi\controller\identify
 */
class IdentifyConfig extends BaseAdminController
{

    public function list()
    {
        return success(app(IdentifyConfigService::class)->list());
    }

    public function save()
    {

        $data = $this->_vali([
            'data.require' => '请填写数据'
        ]);

        return app(IdentifyConfigService::class)->save($data['data']);

    }



}
