<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/14 4:29
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service;

use addon\saler_tools\app\common\BaseAdminService;
use app\model\sys\SysRole;
use app\model\sys\SysUserRole;

/**
 *
 * Class AuthService
 * @package addon\saler_tools\app\service
 */
class AuthService extends BaseAdminService
{

    public function stat()
    {
        $list = (new SysUserRole())->field('status,count(uid) as num')->group('status')->where('site_id', $this->site_id)->select()->toArray();

        $data = array_column($list, 'num', 'status');

        return success([
            'user_num'    => $data[1] ?? 0,
            'disable_num' => $data[0] ?? 0,
        ]);
    }

    public function list($params)
    {
        $search_model = (new SysUserRole())->order('is_admin desc,id desc')
            ->with('userinfo')
            ->append(['status_name'])
            ->hasWhere('userinfo', function ($query) use ($params) {
                $condition = [];
                if (isset($params['search']) && $params['search'] !== '') {
                    $sysUserRole = new SysUserRole();
                    $condition[] = ['real_name', 'like', "%{$sysUserRole->handelSpecialCharacter($params['search'])}%"];
                }
                $query->where($condition);
            })
            ->where([['SysUserRole.site_id', '=', $this->site_id]]);

        if (isset($params['is_use'])) {
            $search_model->where([['SysUserRole.status', '=', $params['is_use']]]);
        }

        $list = $search_model->select()->toArray();

        foreach ($list as &$item) {
            if (!empty($item['role_ids'])) {
                $item['role_array'] = (new SysRole())->where([['role_id', 'in', $item['role_ids']]])->column('role_name');
            } else {
                $item['role_array'] = [];
            }
        }


        return success($list);
    }

}
