<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/22 21:53
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\ShopShareService;

/**
 * 店铺分享
 * Class ShopShare
 * @package addon\saler_tools\app\adminapi\controller
 */
class ShopShare extends BaseAdminController
{

    public function lists()
    {
        return app(ShopShareService::class)->lists([]);
    }


    public function del($share_id)
    {
        return app(ShopShareService::class)->deleted($share_id);
    }

    public function create()
    {

        $data = $this->_vali([
            'platform.require'     => '请选择分享渠道',
            'share_params.default' => [],
        ]);

        return app(ShopShareService::class)->create($data);

    }


}
