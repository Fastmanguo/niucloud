<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/14 19:20
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\dict\cache;

/**
 *
 * Class SysCache
 * @package addon\saler_tools\app\dict\cache
 */
class SysCache
{

    /** 应用缓存标识 */
    private $app_tag = 'saler_tools';

    /** 分类缓存 */
    private $goods_category = 'saler_tools_goods_category';

    /** 品牌 */
    private $goods_brand = 'saler_tools_goods_brand';

    /** 系列 */
    private $goods_series = 'saler_tools_goods_series';


    /** 型号 */
    private $goods_model = 'saler_tools_goods_model';

    /** 字典缓存 */
    private $dict = 'saler_tools_dict';

    /** @var string 鉴定师缓存 */
    private string $identify_user_key = 'SITE_IDENTIFY_USER_CACHE';

    /** @var string $country_list_key 国家列表缓存 */
    private string $country_list_key = 'SITE_COUNTRY_LIST';

    /** 页面缓存标识 */
    private string $page_key = 'SITE_PAGE_CACHE';



    public function __get(string $name)
    {
        // TODO：追加语言标识
        return $this->$name . '_' . app()->appLang;
    }


    public function user($name, $uid)
    {
        return $this->$name . '_' . $uid;
    }

    /**
     * 根缓存无需区别语言
     * @param $name
     */
    public function sys($name)
    {
        return $this->$name;
    }

}
