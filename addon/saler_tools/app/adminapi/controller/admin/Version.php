<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/19 18:25
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\sys\VersionService;

/**
 * 版本管理
 * Class Version
 * @package addon\saler_tools\app\adminapi\controller\admin
 */
class Version extends BaseAdminController
{

    public function lists()
    {
        $data = $this->_vali([

        ]);
        return app(VersionService::class)->lists($data);
    }


    public function add()
    {
        $data                    = $this->_vali([
            'file.require'            => '请上传wgt文件',
            'file.fileExt:wgt'        => 'wag文件类型错误',
            'name.require'            => '请输入版本名称',
            'describe.require'        => '请输入本次更新介绍',
            'edition_number.require'  => '请输入本次版本号',
            'edition_name.require'    => '请输入版本名称',
            'edition_issue.default'   => 0, // 是否发行
            'edition_silence.default' => 0, // 是否静默更新
            'package_type.default'    => 1, // 整包升级 0:包升级 1:wag升级
            'edition_force.default'   => 0, // 是否强制更新
        ], data: ['file' => request()->file('file')]);

        $data['edition_silence'] = empty($data['edition_silence']) ? 0 : 1;

        return app(VersionService::class)->add($data);

    }


    public function detail($id)
    {
        return app(VersionService::class)->detail($id);
    }

    public function edit()
    {
        $data = $this->_vali([
            'id.require'              => '请选择修改的版本',
            'name.require'            => '请输入版本名称',
            'describe.require'        => '请输入本次更新介绍',
            'edition_number.require'  => '请输入本次版本号',
            'edition_name.require'    => '请输入版本名称',
            'edition_issue.default'   => 0, // 是否发行
            'edition_silence.default' => 0, // 是否静默更新
            'package_type.default'    => 1, // 整包升级 0:包升级 1:wag升级
            'edition_force.default'   => 0, // 是否强制更新
        ]);

        return app(VersionService::class)->edit($data);
    }


    public function del($id)
    {
        return app(VersionService::class)->del($id);
    }

}
