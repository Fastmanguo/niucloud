<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/29 23:11
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\shop;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\dict\sys\SystemDict;
use addon\saler_tools\app\model\Shop as ShopModel;
use addon\saler_tools\app\model\ShopApply as ShopApplyModel;
use app\model\site\Site;
use app\model\sys\SysUserRole;
use app\service\core\sys\CoreConfigService;
use addon\saler_tools\app\model\Goods as GoodsModel;
use addon\saler_tools\app\model\SalerToolsGoodsBrand as GoodsBrandModel;
use think\facade\Cache;

/**
 *
 * Class ShopService
 * @package addon\saler_tools\app\service\shop
 */
class ShopService extends BaseAdminService
{


    public function list()
    {
        // 获取当前登录用户关联的所有门店
        $site_ids = (new SysUserRole())->where('uid', $this->uid)->column('site_id');

        $fields = 'site_id,shop_name,logo,desc,tel,mobile,address,coordinates,certificate_name,create_time,is_exp,exp_time';

        $list = (new ShopModel())->whereIn('site_id', $site_ids)->field($fields)->select()->toArray();

        return success($list);

    }


    public function detail()
    {
        $shop = $this->info();
        if (empty($shop['site_id'])) {
            return fail('shop_not_exist');
        }
        return success($shop);
    }


    public function info()
    {
        $key = app()->siteCache->shop_info_key;
        return cache_remember($key, function () {
            return (new ShopModel())->whereIn('site_id', $this->site_id)
                ->findOrEmpty()
                ->bindAttr('country', ['country_name'])
                ->toArray();
        });
    }


    public function edit($data)
    {
        $shop = (new ShopModel())->whereIn('site_id', $this->site_id)->findOrEmpty();
        $shop->allowField(['shop_name', 'logo', 'share_poster', 'desc', 'tel', 'mobile', 'address', 'banner_list'])->save($data);
        $key = app()->siteCache->shop_info_key;
        Cache::delete($key);
        return success();
    }

    public function editByAdmin($data)
    {
        $shop = (new ShopModel())->whereIn('site_id', $data['site_id'])->findOrEmpty();
        $shop->allowField(['shop_name', 'logo', 'share_poster', 'desc', 'tel', 'mobile', 'address'])->save($data);
        $key = app()->siteCache->sys('shop_info_key', $this->site_id);
        Cache::delete($key);
        return success();
    }


    public function modify($data)
    {
        $shop = (new ShopModel())->whereIn('site_id', $this->site_id)->findOrEmpty();
        $shop->allowField(['mobile', 'share_poster', 'logo'])->save($data);
        $key = app()->siteCache->shop_info_key;
        Cache::delete($key);
        return success();
    }


    /**
     * 根据share_code获取站点信息
     */
    public function getSiteInfoByShareCode($share_code)
    {
        $shop = (new ShopModel())->where('share_code', $share_code)->findOrEmpty()->toArray();
        return $shop;
    }


    /**
     * 申请店铺
     * 商家端调用
     */
    public function apply($data)
    {
        // 判断是否存在开通中的店铺
        $shop_apply_model = new ShopApplyModel();

        $shop_apply = $shop_apply_model->where('uid', $this->uid)->where('status', 'wait')->findOrEmpty();

        if (!$shop_apply->isEmpty()) {
            return fail('shop_apply_already_exist');
        }

        // 先查询已经开通的店铺
        $shop = (new ShopModel())->whereIn('shop_name', $data['shop_name'])->findOrEmpty();

        if (!$shop->isEmpty()) {
            return fail('shop_already_exist');
        }

        $data['create_time'] = time();
        $data['status']      = 'wait';
        $data['uid']         = $this->uid;

        $apply = (new ShopApplyModel())->create($data);

        // 系统开启自动审核时默认审核通过
        $config = $this->getApplyConfig();
        if (isset($config['is_auto_audit']) && $config['is_auto_audit'] == 1) {
            $day = $config['experience_time'] ?? 0;
            $this->audit([
                'id'       => $apply->id,
                'status'   => 1,
                'exp_time' => date('Y-m-d H:i:s', strtotime('+ ' . $day . ' day'))
            ]);
        }

        $key = app()->siteCache->shop_info_key;
        Cache::delete($key);

        return success();
    }


    public function applyList()
    {
        $shop_apply_model = new ShopApplyModel();

        $list = $shop_apply_model->where('uid', $this->uid)->order('create_time', 'desc')->select()->toArray();

        return success($list);
    }


    /****************************************  管理员调用 ******************************************/
    public function lists($params)
    {

        $query_type = $params['query_type'] ?? 1; // 1 已开通店铺 2 未开通店铺

        if ($query_type == 1) {
            $model = new ShopModel();
            $model = $model->with([
                'createInfo' => function ($query) {
                    $query->field('uid,real_name,last_ip,last_time');
                }
            ]);
        } elseif ($query_type == 2) {
            $model = new ShopApplyModel();
            $model = $model->with([
                'createInfo' => function ($query) {
                    $query->field('uid,real_name,last_ip,last_time');
                }
            ])->where($model->getName() . '.status', 'wait');
        } else {

        }

        $res = $this->pageX($model)->toArray();

        return success($res);
    }


    /**
     * 审核店铺申请
     */
    public function audit($data)
    {
        $shop = (new ShopApplyModel())->where('id', $data['id'])->findOrEmpty();

        if ($shop->isEmpty()) return fail();

        if ($shop['status'] != 'wait') return fail();

        $shop_model = new ShopModel();

        $shop_model->startTrans();
        $shop_data = $shop->toArray();

        // 去掉干扰参数
        unset($shop_data['id'], $shop_data['fail_remark'], $shop_data['update_time'], $shop_data['status']);

        try {

            if ($data['status'] == 1) { // 通过
                $site_model = new Site();
                $exp_time   = $data['exp_time'];


                $site = $site_model->create([
                    'site_name'       => $shop_data['shop_name'],
                    'group_id'        => 1,
                    'status'          => 1,
                    'app_type'        => 'site',
                    'create_time'     => time(),
                    'expire_time'     => strtotime($exp_time),
                    'app'             => ["saler_tools"],
                    'addons'          => [],
                    'initalled_addon' => ["saler_tools", "online_expo"]
                ]);

                $shop_data['site_id']  = $site->site_id;
                $shop_data['exp_time'] = $exp_time;
                $shop_data['is_exp']   = 0;

                $sys_user_role = new SysUserRole();

                $sys_user_role->create([
                    'uid'         => $shop_data['uid'],
                    'site_id'     => $site->site_id,
                    'create_time' => time(),
                    'is_admin'    => 1,
                    'status'      => 1,
                ]);

                $shop_model->create($shop_data);

                $shop->status = 'success';

            } else { // 拒绝
                $shop->status      = 'error';
                $shop->fail_remark = $data['fail_remark'];
            }

            $shop->save();

            $key = app()->siteCache->sys('shop_info_key', $shop->site_id);
            Cache::delete($key);

            $shop_model->commit();
            return success();
        } catch (\Exception $e) {
            $shop_model->rollback();
            return fail();
        }

    }


    /**
     * 获取店铺注册配置
     */
    public function getApplyConfig()
    {
        return (new CoreConfigService())->getConfig($this->request->defaultSiteId(), SystemDict::SHOP_REGISTER_CONFIG)['value'] ?? [];
    }


    /**
     * 更新店铺配置
     */
    public function setApplyConfig($data)
    {
        (new CoreConfigService())->setConfig($this->request->defaultSiteId(), SystemDict::SHOP_REGISTER_CONFIG, $data);
        return success();
    }


    /**
     * 获取店铺面板信息
     */
    public function shopPanel()
    {
        // 获取店铺下拥有的品牌信息
        $key = $this->app->siteCache->shop_panel_data_key;
        return cache_remember($key, function () {

            $goods_model = new GoodsModel();
            $brand_model = new GoodsBrandModel();

            $brand_list = $goods_model->where('deleted_time', 0)
                ->where('site_id', $this->site_id)
                ->field('brand_id,count(brand_id) as count')
                ->group('brand_id')
                ->having('brand_id', '>', 0)
                ->order('count desc')
                ->limit(4)
                ->select()
                ->toArray();

            $brand_ids = array_column($brand_list, 'brand_id');

            if (count($brand_list) < 5) {
                $temp_ids  = $brand_model->where('brand_id', 'not in', $brand_ids)->limit(5 - count($brand_list))->column('brand_id');
                $brand_ids = array_merge($brand_ids, $temp_ids);
            }

            $brand_list = $brand_model->where('brand_id', 'in', $brand_ids)->field('brand_id,brand_name,logo')->select()->toArray();

            // 获取最新上架的商品
            $new_goods = $goods_model->where('deleted_time', 0)
                ->where('site_id', $this->site_id)
                ->field('goods_id,goods_name,goods_cover')
                ->order('goods_id desc')
                ->findOrEmpty();

            // 获取总在售商品数量
            $goods_count = $goods_model->where('deleted_time', 0)
                ->where('site_id', $this->site_id)
                ->where('is_sale', 1)
                ->count();

            $new_count = $goods_model->where('deleted_time', 0)
                ->where('site_id', $this->site_id)
                ->where('create_time', '>', strtotime('- 7 days'))
                ->where('is_sale', 1)
                ->count();

            return success([
                'goods_count' => $goods_count,
                'new_count'   => $new_count,
                'brand_list'  => $brand_list,
                'new_goods'   => $new_goods->toArray()
            ]);

        }, options: ['expire' => 60]);
    }

}
