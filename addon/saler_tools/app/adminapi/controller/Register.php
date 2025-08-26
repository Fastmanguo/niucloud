<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/23 19:41
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\user\RegisterService;

/**
 *
 * Class Register
 * @package addon\saler_tools\app\adminapi\controller
 */
class Register extends BaseAdminController
{


    public function captcha()
    {
        $data = $this->_vali([
            'email.require' => 'please_email_require'
        ]);
        return app(RegisterService::class)->captcha($data);
    }


    public function sendCaptcha()
    {
        $data = $this->_vali([
            'email.require' => 'please_email_require'
        ]);

        return app(RegisterService::class)->sendCaptcha($data);
    }

    public function index()
    {
        $data = $this->_vali([
            'real_name.require'       => 'please_real_name_require',
            'email.require'           => 'please_email_require',
            'code.require'            => 'please_code_require',
            'password.require'        => 'please_password_require',
            'invitation_code.default' => '',
        ]);

        return app(RegisterService::class)->index($data);

    }


}
