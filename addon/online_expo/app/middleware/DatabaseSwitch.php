<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/11/25 2:41
// +----------------------------------------------------------------------

namespace addon\online_expo\app\middleware;

use addon\saler_tools\app\dict\cache\SiteCache;
use addon\saler_tools\app\dict\cache\SysCache;
use app\Request;
use Closure;
use core\dict\Console;

/**
 * 数据切换
 * Class DatabaseSwitch
 * @package addon\online_expo\app\middleware
 */
class DatabaseSwitch
{
    public function handle(Request $request, Closure $next)
    {
        // 获取语言设置语言
        $lang = $request->header('lang', 'zh-cn');

        app()->bind('appLang', function () use ($lang){
            return $lang;
        });

        /** @var SysCache $appCache */
        app()->bind('appCache', SysCache::class);

        /** @var SiteCache $siteCache */
        app()->bind('siteCache', SiteCache::class);

        return $next($request);
    }
}
