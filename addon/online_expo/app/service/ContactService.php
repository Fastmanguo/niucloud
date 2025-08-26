<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/20 2:01
// +----------------------------------------------------------------------

namespace addon\online_expo\app\service;

use addon\online_expo\app\model\Goods;
use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\Shop as ShopModel;
use addon\saler_tools\app\service\im\CoreImService;
use addon\saler_tools\app\service\shop\ContactService as ShopContactService;
use app\model\sys\SysUser;
use core\exception\AdminException;
use think\db\Raw;

/**
 *
 * Class ContactService
 * @package addon\online_expo\app\service
 */
class ContactService extends BaseAdminService
{
    public function shop($data)
    {
        $site_id    = $data['site_id'];
        $field      = ['site_id', 'shop_name', 'logo', 'mobile', 'tel', 'address'];
        $shop_model = new ShopModel();
        $shop       = $shop_model->field($field)->where('site_id', $site_id)->findOrEmpty()->toArray();

        // 获取店铺下联系人
        $contact_list = app(ShopContactService::class)->getContactExhibition($site_id);

        $shop['contact_list'] = $contact_list;
        StatService::setLog($site_id, $this->uid, 0, 20);
        return success($shop);
    }


    public function im($data)
    {
        // 先校验合法性
        $goods_id = $data['goods_id'];
        // 联系人
        $site_id = $data['site_id'];

        // 获取店铺信息
        $goods = (new Goods())->where('goods_id', $goods_id)
            ->where('site_id', $site_id)
            ->where('is_online_expo', 1)
            ->where('is_sale', 1)
            ->findOrEmpty();

        if ($goods->isEmpty()) {
            return fail('invalid_goods');
        }

        $user = (new SysUser())->hasWhere('roles', [
            ['site_id', '=', $goods['site_id']],
            ['site_id', '=', $site_id],
            ['is_admin', '=', 1]
        ])->findOrEmpty();

        if ($user->isEmpty()) {
            return fail('invalid_goods');
        }

        if ($user['uid'] == $this->uid) {
            return fail('unable_to_contact_oneself');
        }

        try {

            $res = app(CoreImService::class)->pushMessage([
                'uid'       => $user['uid'],
                'head_img'  => $user['head_img'],
                'username'  => $user['username'],
                'real_name' => $user['real_name'],
                'content'   => json_encode([
                    'goods_id'    => $goods_id,
                    'goods_name'  => $goods['goods_name'],
                    'goods_desc'  => $goods['goods_desc'],
                    'goods_cover' => $goods['goods_cover'],
                ]),
                'type'      => 7
            ]);

            StatService::setLog($site_id, $this->uid, 0, 20);
        } catch (\Exception $e) {
            throw new AdminException("shop_not_available_im");
        }

        return success([
            'id'        => $user['uid'],
            'real_name' => $user['real_name'],
            'head_img'  => $user['head_img'],
            'content'   => $res
        ]);

    }

    public function siteListByUid($data)
    {
        $shop_model = new ShopModel();
        $field      = ['site_id', 'shop_name', 'logo', 'mobile', 'tel', 'address'];
        $shop_list  = $shop_model->field($field)->where('uid', $data['uid'])->select()->toArray();
        return success($shop_list);
    }

}
