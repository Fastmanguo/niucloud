<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/1 5:22
// +----------------------------------------------------------------------

namespace addon\online_expo\app\service;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\Shop as ShopModel;
/**
 *
 * Class ShopPeerService
 * @package addon\online_expo\app\service
 */
class ShopPeerService extends BaseAdminService
{

    public function info($site_id)
    {
        $shop_info = (new ShopModel())->where('site_id', $site_id)->findOrEmpty();

        if ($shop_info->isEmpty()) return fail();

        return success($shop_info->toArray());

    }




}
