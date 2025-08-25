<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/20 1:50
// +----------------------------------------------------------------------

namespace addon\online_expo\app\adminapi\controller;

use addon\online_expo\app\service\ContactService;
use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\model\Shop as ShopModel;

/**
 * 展会联系相关
 * Class Contact
 * @package addon\online_expo\app\adminapi\controller
 */
class Contact extends BaseAdminController
{

    /**
     * 获取展会联系信息
     */
    public function shop()
    {
        $data = $this->_vali([
            'site_id.require' => '请选择展会',
        ]);

        return app(ContactService::class)->shop($data);
    }


    /**
     * im沟通
     */
    public function im()
    {
        $data = $this->_vali([
            'goods_id.require' => 'please_select_goods',
            'site_id.require'  => 'please_select_goods'
        ]);

        return app(ContactService::class)->im($data);
    }


    /**
     * 获取好友店铺信息
     */
    public function siteListByUid()
    {
        $data = $this->_vali([
            'uid.require'  => 'please_select_fiend_user'
        ]);

        return app(ContactService::class)->siteListByUid($data);
    }

}
