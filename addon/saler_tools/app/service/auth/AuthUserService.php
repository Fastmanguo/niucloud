<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/10 4:35
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\auth;

use addon\saler_tools\app\common\BaseAdminService;
use app\model\sys\SysUser;
use app\model\sys\SysUserRole;

/**
 * 站点管理员管理员工
 * Class AuthUserService
 * @package addon\saler_tools\app\service\auth
 */
class AuthUserService extends BaseAdminService
{

    /**
     * 添加员工
     */
    public function add($data)
    {
        // TODO: 添加员工实现
        $username = $data['username'];
        $role_ids = $data['role_ids'];

        $user = (new SysUser())->where('username', $username)->findOrEmpty();
        // 如果用户不存在，则提示用户尚未注册，不可添加
        // if ($user->isEmpty()) return fail('user_not_exist');
        if ($user->isEmpty()) return fail('当前用户尚未注册，不可添加');

        $sys_user_role = (new SysUserRole())->where('uid', $user->uid)->where('site_id', $this->site_id)->findOrEmpty();

        if ($sys_user_role->isEmpty()) {
            (new SysUserRole())->create([
                'uid'      => $user->uid,
                'site_id'  => $this->site_id,
                'role_ids' => $role_ids,
                'status'   => $data['status'],
                'is_admin' => 0
            ]);
        }

        return success();
    }

}
