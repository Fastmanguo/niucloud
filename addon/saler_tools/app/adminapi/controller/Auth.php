<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Useror  : 琦森 admin@musp.cn
// | DateTime: 2025/1/14 22:41
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\auth\AuthUserService;
use addon\saler_tools\app\service\AuthService;
use app\dict\sys\RoleStatusDict;
use app\dict\sys\UserDict;
use app\service\admin\site\SiteUserService;
use app\service\admin\sys\RoleService;
use addon\saler_tools\app\service\user\RoleService as SalerRoleService;

/**
 *
 * Class User
 * @package addon\saler_tools\app\adminapi\controller
 */
class Auth extends BaseAdminController
{

    public function list()
    {
        $data = $this->_vali([
            'search.query' => '',
            'is_use.query' => '',
        ]);

        return app(AuthService::class)->list($data);

    }

    public function stat()
    {
        return app(AuthService::class)->stat();
    }


    public function editUser()
    {
        $data = $this->_vali([
            'uid.require'       => 'uid_require',
            'real_name.require' => 'real_name_require',
            'status.default'    => UserDict::ON,
            'role_ids.default'  => [],
        ]);

        (new SiteUserService())->edit($data['uid'], $data);

        return success();
    }


    public function addUser()
    {
        $data = $this->_vali([
            'username.require' => 'input.role_name',
            'role_ids.require' => 'select_role',
            'status.default'    => UserDict::ON,
        ]);

        return (new AuthUserService())->add($data);
    }

    public function userDetail($uid)
    {
        return success((new SiteUserService())->getInfo($uid));
    }


    public function delUser($uid)
    {
        (new SiteUserService())->del($uid);
        return success('DELETE_SUCCESS');
    }


    /**
     * 锁定账户
     * @param $uid
     */
    public function userLock($uid)
    {
        (new SiteUserService())->lock($uid);
        return success('MODIFY_SUCCESS');
    }


    /**
     * 解锁账户
     */
    public function userUnlock($uid)
    {
        (new SiteUserService())->unlock($uid);
        return success('MODIFY_SUCCESS');
    }


    public function roleList()
    {
        return (new SalerRoleService())->list();
    }

    /**
     * 新增角色
     * @return \think\Response
     */
    public function addRole()
    {
        $data = $this->_vali([
            'role_name.require' => '',
            'rules.default'     => [],
            'status.default'    => RoleStatusDict::ON
        ], 'post');
        
        // 检查同名称身份是否已存在
        $existingRole = \app\model\sys\SysRole::where([
            ['site_id', '=', $this->site_id],
            ['role_name', '=', $data['role_name']]
        ])->find();
        
        if ($existingRole) {
            return fail('身份已存在');
        }
        
        (new RoleService())->add($data);
        return success('ADD_SUCCESS');
    }


    public function roleDetail($role_id)
    {
        return success((new RoleService())->getInfo($role_id));
    }

    /**
     * 修改角色
     */
    public function editRole()
    {
        $data = $this->_vali([
            'role_id.require'   => 'role_id_require',
            'role_name.require' => 'role_name_require',
            'rules.default'     => [],
            'status.default'    => RoleStatusDict::ON
        ], 'put');
        
        // 检查同名称身份是否已存在（排除当前编辑的角色）
        $existingRole = \app\model\sys\SysRole::where([
            ['site_id', '=', $this->site_id],
            ['role_name', '=', $data['role_name']],
            ['role_id', '<>', $data['role_id']]
        ])->find();
        
        if ($existingRole) {
            return fail('身份已存在');
        }
        
        (new RoleService())->edit($data['role_id'], $data);
        return success('EDIT_SUCCESS');
    }


    public function delRole()
    {
        $data = $this->_vali([
            'role_id.require' => 'role_id_require',
        ]);

        (new RoleService())->del($data['role_id']);
        return success('EDIT_SUCCESS');
    }


}
