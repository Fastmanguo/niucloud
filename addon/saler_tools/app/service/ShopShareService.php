<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/22 21:40
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\common\Utils;
use addon\saler_tools\app\model\Goods;
use addon\saler_tools\app\model\Share as ShareModel;
use addon\saler_tools\app\model\Shop;
use addon\saler_tools\app\service\channel\ChannelService;
use think\helper\Str;


/**
 * 店铺分享服务
 * Class ShopShareService
 * @package addon\saler_tools\app\service
 */
class ShopShareService extends BaseAdminService
{

    public function lists($param)
    {
        $share_model = new ShareModel();
        $model       = $share_model->where('site_id', $this->site_id)
            ->with(['createNames'])
            ->order('share_id desc');
        return success($this->pageQuery($model));
    }


    public function deleted($share_id)
    {

        $share_model = new ShareModel();
        $share       = $share_model->where('site_id', $this->site_id)
            ->where('share_id', $share_id)
            ->findOrEmpty();

        if (!$share->isEmpty()) {
            $share->delete();
        }

        return success();
    }


    public function create($data)
    {
        $share_cover  = $data['share_params']['share_cover'] ?? '';
        $goods_ids    = $data['share_params']['goods_ids'] ?? [];
        $price_config = $data['share_params']['price_config'] ?? [];
        $class_ids    = $data['share_params']['class_ids'] ?? [];
        $share_type   = $data['share_params']['share_type'] ?? [];

        $platform = $data['platform'] ?? '';

        $share_code   = Utils::createno('share_code') . Str::random();
        $share_result = [];

        if (empty($share_cover)) {
            // 使用占地配置的默认图片
            $share_cover = 'upload/app/logo_min.png';
        }

        if (empty($price_config)){
            $price_config = ["price"];
        }

        $type = 'share';

        $desc  = '';
        $title = '';

        if ($share_type == 'goods') {
            $goods = Goods::whereIn('goods_id', $goods_ids)->field('goods_name,goods_desc,goods_cover')->findOrEmpty();
            if (!$goods->isEmpty()) {
                $title = $goods['goods_name'];
                $desc  = $goods['goods_desc'];
            }
        } else {
            $shop = Shop::where('site_id', $this->site_id)->field('shop_name,desc')->findOrEmpty();
            if (!$shop->isEmpty()) {
                $desc  = $shop['desc'];
                $title = $shop['shop_name'];
            }
        }

        if ($platform == 'weapp') {
            $weapp_path_url            = '';
            $channel_service           = (new ChannelService())->getChannelConnect($platform);
            $file_path                 = $channel_service->getwxacodeunlimit($weapp_path_url, $share_code);
            $share_result['file_path'] = $file_path;
        } elseif ($platform == 'wechat') {
            // 获取小程序配置
            $channel_service = app(ChannelService::class)->config('weapp');
            $share_result    = [
                "provider"    => 'weixin',
                "scene"       => "WXSceneSession",
                "type"        => 5,
                "imageUrl"    => fill_resource_url($share_cover),
                "title"       => $title,
                "miniProgram" => [
                    "id"     => $channel_service['wx_id'],
                    "path"   => 'app/pages/link/weapp?scene=' . $share_code,
                    "type"   => 0,
                    "webUrl" => 'https://app.84000lookingfor.com'
                ]
            ];

        } elseif ($platform == 'facebook') {

            $type = 'system';

            $share_result = [
                "href"    => 'https://app.84000lookingfor.com/link?scene=' . $share_code,
                "type"    => 'text',
                "summary" => '#' . $title
            ];
        } elseif ($platform == 'link') {
            $type         = 'copy';
            $share_result = [
                "link" => 'https://app.84000lookingfor.com/b/?scene=' . $share_code,
            ];
        }

        ShareModel::create([
            'site_id'      => $this->site_id,
            'share_code'   => $share_code,
            'title'        => $title,
            'desc'         => $desc,
            'platform'     => $platform,
            'share_type'   => $share_type,
            'type'         => $type,
            'share_cover'  => $share_cover,
            'uid'          => $this->uid,
            'goods_ids'    => $goods_ids,
            'price_config' => $price_config,
            'class_ids'    => $class_ids,
            'share_result' => $share_result,
        ]);

        return success(['type' => $type, 'share_code' => $share_code, 'share_result' => $share_result]);
    }


    public function getConfigByCode($shop_code)
    {
        $share_model = new ShareModel();
        $share       = $share_model->where('share_code', $shop_code)
            ->findOrEmpty();
        return $share->toArray();
    }

}
