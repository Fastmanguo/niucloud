<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/17 4:52
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\dict\cache;

/**
 *
 * Class SiteCache
 * @package addon\saler_tools\app\dict\cache
 */
class SiteCache
{

    /** @var string 成本字典缓存 */
    private string $cost_cache_key = 'SITE_COST_DICT';


    /** @var string 站点缓存标签 */
    private string $tag = 'SITE_CACHE_TAG';

    /** @var string  */
    private string $user_cache_key = 'SITE_USER_DICT';

    /** @var string $shop_panel_data_key 店铺面便缓存 */
    private string $shop_panel_data_key = 'SITE_SHOP_PANEL_DATA';

    /** @var string $shop_info_key 店铺信息缓存 */
    private string $shop_info_key = 'SITE_SHOP_INFO_CACHE';


    /***
     * 获取缓存key
     * @deprecated app->siteCache->[私有成员]
     */
    public function __get(string $name)
    {
        return $this->$name . ':' . app()->request->siteId();
    }

    /**
     * 系统调用
     * @param string $name
     */
    public function sys(string $name = '',$siteId = 0)
    {
        return $this->$name . ':' . $siteId;
    }

    public function __construct()
    {

    }

}
