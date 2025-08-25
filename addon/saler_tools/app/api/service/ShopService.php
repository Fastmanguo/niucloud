<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/12 20:46
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\api\service;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\common\BaseApiService;
use addon\saler_tools\app\model\SalerToolsGoodsCategory;
use addon\saler_tools\app\model\Shop as ShopModel;

/**
 * 店铺服务
 * Class ShopService
 * @package addon\saler_tools\app\api\service
 */
class ShopService extends BaseApiService
{

    /**
     * 获取店铺初始化信息
     */
    public function getInit()
    {
        $shop_model = new ShopModel();

        $info = $shop_model->where('site_id', $this->site_id)
            ->findOrEmpty();

        if ($info->isEmpty()) {
            return fail();
        }

        $shop = [
            'shop_name'    => $info['shop_name'],
            'logo'         => $info['logo'],
            'share_poster' => $info['share_poster'],
            'desc'         => $info['desc'],
            'tel'          => $info['tel'],
            'mobile'       => $info['mobile'],
            'address'      => $info['address'],
            'banner_list'  => $info['banner_list']
        ];

        $category = (new SalerToolsGoodsCategory())->field('category_id,category_name,category_key')->select()->toArray();

        $config = app()->siteConfig;

        $run_url = '/app/pages/index/index';

        if ($config['share_type'] == 'goods' && count($config['goods_ids']) == 1) {
            $run_url = '/app/pages/goods/detail?goods_id=' . $config['goods_ids'][0];
        }


        return success([
            'shop'         => $shop,
            'category'     => $category,
            'price_config' => $config['price_config'],
            'run_url'      => $run_url
        ]);

    }

}
