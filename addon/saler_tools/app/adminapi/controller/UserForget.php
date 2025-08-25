<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/13 20:13
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseController;
use addon\saler_tools\app\common\BaseService;
use addon\saler_tools\app\service\user\UserForgetService;

/**
 * 用户找回密码
 * Class UserForget
 * @package addon\saler_tools\app\adminapi\controller
 */
class UserForget extends BaseController
{

    public function captcha()
    {
        return (new UserForgetService())->captcha();
    }

    public function sendCode()
    {
        $data = $this->_vali([
            'captcha_key.require'   => 'error_captcha_empty',
            'captcha_value.require' => 'error_captcha_value_empty',
            'email.require'         => 'error_email_empty',
            'forget_type.default'   => 'email'
        ]);

        return (new UserForgetService())->sendCode($data);
    }


    public function resetPassword()
    {
        $data = $this->_vali([
            'captcha_key.require'   => 'captchaPlaceholder',
            'captcha_value.require' => 'captchaPlaceholder',
            'email.require'         => 'error_email_empty',
            'code.default'          => 'captchaPlaceholder',
            'password.require'      => 'input.password.tips',
            'forget_type.default'   => 'email',
        ]);

        return (new UserForgetService())->resetPassword($data);
    }


}
