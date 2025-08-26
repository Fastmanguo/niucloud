<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/20 23:38
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\admin\NotifyTemplateService;

/**
 * 通知模板管理
 * Class NotifyTemplateService
 * @package addon\saler_tools\app\adminapi\controller\admin
 */
class NotifyTemplate extends BaseAdminController
{

    const NOTIFY_TYPE = [
        [
            'label' => '用户注册',
            'key'   => 'REGISTER',
        ],
        [
            'label' => '绑定邮箱',
            'key'   => 'BIND_EMAIL',
        ]
    ];


    public function lists()
    {
        return app(NotifyTemplateService::class)->lists();
    }


    public function add()
    {
        $data = $this->_vali([
            'title.require'  => '请输入通知名称',
            'key.require'    => '请输入通知名称',
            'lang.require'   => '请输入通知名称',
            'is_use.default' => 0,
            'email.require'  => '请输入配置内容'
        ]);
        return app(NotifyTemplateService::class)->add($data);
    }


    public function update()
    {

        $data = $this->_vali([
            'id.require'     => '请选择修改的数据',
            'title.require'  => '请输入通知名称',
            'key.require'    => '请输入通知名称',
            'lang.require'   => '请输入通知名称',
            'is_use.default' => 0,
            'email.require'  => '请输入配置内容'
        ]);

        return app(NotifyTemplateService::class)->edit($data);

    }

    public function del($id)
    {
        return app(NotifyTemplateService::class)->del($id);
    }


    public function modify()
    {
        $data = $this->_vali([
            'id.require'     => '请选择修改的数据',
            'is_use.default' => 0,
        ]);

        return app(NotifyTemplateService::class)->modify($data);
    }


    public function detail($id)
    {
        return app(NotifyTemplateService::class)->detail($id);
    }

}
