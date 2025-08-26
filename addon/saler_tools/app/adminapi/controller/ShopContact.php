<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/28 5:33
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\shop\ContactService;

/**
 * 店铺联系人管理
 * Class ShopContact
 * @package addon\saler_tools\app\adminapi\controller
 */
class ShopContact extends BaseAdminController
{


    public function list()
    {
        return app(ContactService::class)->list();
    }


    public function add()
    {
        $data = $this->_vali([
            'by_uid.require'    => 'check_form',
            'work_name.require' => 'check_form',
            'mobile.default'    => '',
            'email.default'     => '',
            'weacht.default'    => '',
            'whats_up.default'  => '',
            'status.default'    => 0,
        ]);

        return app(ContactService::class)->add($data);

    }


    public function edit()
    {
        $data = $this->_vali([
            'contact_id.require' => 'check_form',
            'by_uid.require'     => 'check_form',
            'work_name.require'  => 'check_form',
            'mobile.default'     => '',
            'email.default'      => '',
            'weacht.default'     => '',
            'whats_up.default'   => '',
            'status.default'    => 0,
        ]);

        return app(ContactService::class)->edit($data);
    }


    public function del($contact_id)
    {
        return app(ContactService::class)->del($contact_id);
    }

    public function detail($contact_id)
    {
        return app(ContactService::class)->detail($contact_id);
    }




}
