<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/1 5:20
// +----------------------------------------------------------------------

namespace addon\online_expo\app\adminapi\controller;

use addon\online_expo\app\service\ShopPeerService;
use addon\saler_tools\app\common\BaseAdminController;

/**
 * 同行控制器
 * Class ShopPeer
 * @package addon\online_expo\app\adminapi\controller
 */
class ShopPeer extends BaseAdminController
{

    public function info($site_id)
    {
        return app(ShopPeerService::class)->info($site_id);
    }


}
