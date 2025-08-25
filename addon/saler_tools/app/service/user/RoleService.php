<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/15 18:14
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\user;

use addon\saler_tools\app\common\BaseAdminService;
use app\model\sys\SysRole;

/**
 *
 * Class RoleService
 * @package addon\saler_tools\app\service\user
 */
class RoleService extends BaseAdminService
{

    public function list()
    {
        $where = [['site_id', '=', $this->site_id]];
        $field = 'role_id,role_name,status,create_time';
        $list = (new SysRole())->where($where)->field($field)->order('create_time desc')->append(['status_name'])->select()->toArray();
        return success($list);
    }

}
