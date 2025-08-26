<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/12 21:10
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\api\middleware;

use addon\saler_tools\app\service\shop\ShopService;
use addon\saler_tools\app\service\ShopShareService;
use app\Request;
use Closure;
use core\exception\ApiException;

/**
 *
 * Class ApiMiddleware
 * @package addon\saler_tools\app\api\middleware
 */
class ApiMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $shop_code = $request->header('site-id', '');

        if (empty($shop_code)) throw new ApiException('店铺信息错误');
        // 先加载站点信息
        $site = app(ShopShareService::class)->getConfigByCode($shop_code);

        if (empty($site)) throw new ApiException('店铺信息错误');

        // 写入site_id
        app()->bind('siteConfig', function () use ($site){
            return $site;
        });

        $request->siteId($site['site_id']);

        return $next($request);

    }
}
