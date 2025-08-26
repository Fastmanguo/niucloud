<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/23 22:32
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\identify\IdentifyUserService;

/**
 * admin端鉴定师控制器
 * Class IdentifyUser
 * @package addon\saler_tools\app\adminapi\controller\admin
 */
class IdentifyUser extends BaseAdminController
{

    public function lists()
    {
        $data = $this->_vali([
            'status.query'   => '',
            'nickname.query' => ''
        ]);

        return app(IdentifyUserService::class)->lists($data);
    }

    public function edit()
    {
        $data = $this->_vali([
            'uid.require'      => '请选择鉴定师',
            'status.require'   => '请选择状态',
            'status.in:1,0,-2' => '状态不正确',
            'nickname.require' => '请填写鉴定师昵称',
            'remark.default'   => ''
        ]);
        return app(IdentifyUserService::class)->edit($data);
    }


}
