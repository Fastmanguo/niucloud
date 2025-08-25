<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/28 5:12
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\shop\AddressService;

/**
 *
 * Class Address
 * @package addon\saler_tools\app\adminapi\controller
 */
class Address extends BaseAdminController
{


    public function list()
    {
        return app(AddressService::class)->list();
    }


    public function add()
    {
        $data = $this->_vali([
            'name.require'    => 'error',
            'address.require' => 'error',
            'mobile.require'  => 'error',
            'remark.default'  => '',
        ]);

        return app(AddressService::class)->add($data);
    }


    public function edit()
    {
        $data = $this->_vali([
            'address_id.require' => 'error',
            'name.require'       => 'error',
            'address.require'    => 'error',
            'mobile.require'     => 'error',
            'remark.default'     => '',
        ]);

        return app(AddressService::class)->edit($data);
    }


    public function del($address_id)
    {
        return app(AddressService::class)->del($address_id);
    }


    public function detail($address_id)
    {
        return app(AddressService::class)->detail($address_id);
    }


}
