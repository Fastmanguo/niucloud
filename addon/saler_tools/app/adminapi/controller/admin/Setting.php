<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/20 21:42
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\admin\SettingService;

/**
 * 设置
 * Class Setting
 * @package addon\saler_tools\app\adminapi\controller\admin
 */
class Setting extends BaseAdminController
{

    /**
     * 获取邮件配置
     */
    public function getEmail()
    {
        return success(app(SettingService::class)->getEmail());
    }


    /**
     * 更新邮件配置
     */
    public function updateEmail()
    {
        $data = $this->_vali([
            'smtp_host.require' => '邮箱服务地址不能为空',
            'email.require'     => '邮箱账户不能为空',
            'password.require'  => '邮箱密码不能为空',
            'port.require'      => '端口号不能为空',
            'protocol.require'  => '协议不能为空',
            'email_sub.require' => '副标题不能为空',
            'sender.default'    => '系统通知',
        ]);

        return app(SettingService::class)->updateEmail($data);
    }


    public function getAppConfig()
    {
        return app(SettingService::class)->getAppConfig();
    }


    public function updateAppConfig()
    {
        $data = $this->request->post();
        return app(SettingService::class)->updateAppConfig($data);
    }


    public function testEmail()
    {
        $data = $this->request->post();
        return app(SettingService::class)->testEmail($data);
    }

}
