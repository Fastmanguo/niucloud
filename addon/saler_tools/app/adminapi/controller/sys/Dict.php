<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/9 4:12
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\sys;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\model\SalerToolsDict;
use addon\saler_tools\app\service\dict\SiteDictService;
use app\service\admin\dict\DictService;

/**
 *
 * Class Dict
 * @package addon\saler_tools\app\adminapi\controller\sys
 */
class Dict extends BaseAdminController
{

    public function getDict($key)
    {
        $cache_key = $this->app->appCache->dict . '_' . $key;
        return cache_remember($cache_key, function () use ($key) {
            $list = (new DictService())->getKeyInfo($key);
            return success($list['dictionary']);
        });
    }


}
