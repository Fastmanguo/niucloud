<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/11/25 3:47
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\service\user\UserService;

/**
 *
 * Class User
 * @package addon\saler_tools\app\adminapi\controller
 */
class User extends BaseAdminController
{

    /**
     * 获取用户信息
     */
    public function info()
    {
        return app(UserService::class)->info();
    }


    /**
     * 绑定邮箱
     */
    public function bindEmail()
    {
        $data = $this->_vali([
            'email.require'    => 'input.email.tips',
            'password.require' => 'input.password.tips',
            'code.require'     => 'captchaPlaceholder',
        ]);

        return app(UserService::class)->bindEmail($data);
    }


    public function modify()
    {
        $data = $this->_vali([
            'field.require'           => 'error',
            'field.in:is_email_login' => 'error',
            'value.require'           => 'error',
        ]);

        return app(UserService::class)->modify($data['field'], $data['value']);
    }


    /**
     * 下发绑定邮箱验证码
     */
    public function sendBindEmail()
    {
        $data = $this->_vali([
            'email.require'    => 'please_email_require',
            'password.require' => 'please_password_require',
        ]);

        return app(UserService::class)->sendBindEmail($data);
    }


    /**
     * 修改密码
     */
    public function password()
    {
        $data = $this->_vali([
            'old_password.require' => 'please_old_password_require',
            'password.require'     => 'please_password_require',
        ]);

        return app(UserService::class)->password($data);

    }


    public function getRealName($uid)
    {
        return app(\addon\saler_tools\app\service\user\UserService::class)->getRealName($uid);
    }

    public function updateHeadImg()
    {
        $file = $this->request->file('file');
        $data = $this->_vali([
            'file.require'                                            => 'file_require',
            'file.fileMime:image/jpeg,image/png,image/gif,image/webp' => 'image_type_error',
            'file.fileSize:2024000'                                   => 'error'
        ], data: ['file' => $file]);

        return app(UserService::class)->updateHeadImg($data['file']);
    }


    public function cancel()
    {
        $data = $this->_vali([
            'password.require' => 'input.password.tips'
        ]);
        return app(UserService::class)->cancel($data);
    }


    public function stat()
    {
        return app(UserService::class)->stat();
    }

}
