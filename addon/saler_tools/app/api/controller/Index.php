<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/6 0:12
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\api\controller;

use addon\saler_tools\app\api\service\ShopService;
use addon\saler_tools\app\common\BaseApiController;
use addon\saler_tools\app\model\Shop;
use addon\saler_tools\app\service\LanguageService;
use addon\saler_tools\app\service\ShopShareService;
use think\Cache;
use think\facade\View;

/**
 *
 * Class Index
 * @package addon\saler_tools\app\api\controller
 */
class Index extends BaseApiController
{
    /**
     * 拉取店铺配置
     */
    public function index()
    {
        return app(ShopService::class)->getInit();
    }


    public function lang($key)
    {
        return app(LanguageService::class)->getAppByC($key);
    }


    public function render()
    {
        $path = app()->getRootPath() . 'public/h5/index.html';
        return view($path, ['append' => '']);
    }


    public function link()
    {

        $http_user_agent = $_SERVER['HTTP_USER_AGENT'];

        $scene = request()->get('scene');

        $config = app(ShopShareService::class)->getConfigByCode($scene);

        if (str_contains($http_user_agent, 'www.facebook.com')) {
            $path   = app()->getRootPath() . 'public/h5/index.html';
            $append = [];
            $url    = request()->url(true);

            $append[] = '<meta property="og:title" content="' . $config['title'] . '">';
            $append[] = '<meta property="og:image:alt" content="' . $config['title'] . '">';
            $append[] = '<meta property="og:description" content="' . $config['desc'] . '">';
            $append[] = '<meta property="og:site_name" content="' . $url . '">';
            $append[] = '<meta property="og:url" content="' . $url . '">';
            $append[] = '<meta property="og:type" content="article">';
            $append[] = '<meta property="fb:app_id" content="2472101009794185">';
            $append[] = '<meta property="og:image" content="' . fill_resource_url($config['share_cover']) . '">';
            $html = VIEW::fetch($path);
            $facebook_html = implode('', $append);
            return display(str_replace('<!--facebook-->', $facebook_html, $html));
        }

        $url = 'https://app.84000lookingfor.com/h5?scene=' . $scene;

        return redirect($url);

    }

}
