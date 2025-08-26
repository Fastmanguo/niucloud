<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/29 22:14
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\shop\ShopService;

/**
 * 获取店铺信息
 * Class Shop
 * @package addon\saler_tools\app\adminapi\controller\sys
 */
class Shop extends BaseAdminController
{

    // 获取店铺列表
    public function list()
    {
        return app(ShopService::class)->list();
    }

    public function detail()
    {
        return app(ShopService::class)->detail();
    }


    public function edit()
    {
        $data = $this->_vali([
            'shop_name.require'    => '',
            'logo.default'         => '',
            'share_poster.default' => '',
            'desc.default'         => '',
            'tel.default'          => '',
            'mobile.default'       => '',
            'address.default'      => '',
            'coordinates.default'  => '',
            'banner_list.default'  => [],
        ]);
        return app(ShopService::class)->edit($data);
    }


    /**
     * 店铺认证
     */
    public function verify()
    {
        $data = $this->_vali([
            'certificate_name.require'   => '',
            'certificate_no.require'     => '',
            'certificate_images.require' => '',
        ]);
        return app(ShopService::class)->verify($data);
    }


    /**
     * 便捷修改
     */
    public function modify()
    {
        $data = $this->_vali([
            'field.in:in:mobile,share_poster,logo' => 'error',
            'value.require'                        => 'error'
        ]);

        return app(ShopService::class)->modify([$data['field'] => $data['value']]);
    }


    /**
     * 申请开通店铺
     */
    public function apply()
    {
        $data = $this->_vali([
            'shop_name.require'        => 'input.shop_name.tips',
            'certificate_name.require' => 'input.certificate_name.tips',
            'address.require'          => 'input.shop_address.tips',
            'currency_code.require'    => 'input.currency_code.tips',
            'country_code.require'     => 'input.country_code.tips',
        ]);

        return app(ShopService::class)->apply($data);

    }


    /**
     * 申请开通店铺记录
     */
    public function applyList()
    {
        return app(ShopService::class)->applyList();
    }


    public function shopPanel()
    {
        return app(ShopService::class)->shopPanel();
    }

}
