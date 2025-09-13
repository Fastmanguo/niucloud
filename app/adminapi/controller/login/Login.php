<?php
// +----------------------------------------------------------------------
// | Niucloud-admin 企业快速开发的saas管理平台
// +----------------------------------------------------------------------
// | 官方网址：https://www.niucloud.com
// +----------------------------------------------------------------------
// | niucloud团队 版权所有 开源版本可自由商用
// +----------------------------------------------------------------------
// | Author: Niucloud Team
// +----------------------------------------------------------------------

namespace app\adminapi\controller\login;

use app\service\admin\auth\ConfigService;
use app\service\admin\auth\LoginService;
use app\service\core\addon\WapTrait;
use core\base\BaseAdminController;
use think\Response;

class Login extends BaseAdminController
{
    use WapTrait;

    /**
     * 微信一键登录
     */
    public function loginWchat()
    {
        $data = $this->request->params([
            [ 'openId', '' ],
            [ 'nickName', '' ],
            [ 'mobile', '' ]
        ]);
        return success($data);
        $result = ( new LoginService() )->loginWchat($app_id,$app_secret,$code);
        return success($result);

    }


    /**
     * 登录
     * @return Response
     * login_type 1手机密码登录 2邮箱密码登录   3:手机验证码登录 4邮箱验证码登录 5 一键登录，不存在自动注册  6微信一键登录，不存在自动注册
     */
    public function login($app_type)
    {

        $data = $this->request->params([
            [ 'username', '' ],
            [ 'password', '' ],
            [ 'login_type', '0' ],
            [ 'wx_openid', '0' ],
            [ 'real_name', '' ],
            [ 'wx_image', '' ],
        ]);
        
        $login_type = strval($data['login_type']);
        if($login_type== "3" or $login_type == "4" or $login_type == "5" or $login_type == "6"){
            $result = ( new LoginService() )->login($data[ 'username' ], "0-01", $app_type,$login_type,$data);
        }else{
            $result = ( new LoginService() )->login($data[ 'username' ], $data[ 'password' ], $app_type,$login_type,$data);
        }
        if (array_key_exists('msg', $result)) {
            return fail($result['msg']);
        }
        return success($result);

    }

    /**
     * 登出
     * @return Response
     */
    public function logout()
    {
        ( new LoginService )->logout();
        return success('LOGOUT');
    }

    /**
     * 获取登录设置
     * @return Response
     */
    public function getConfig()
    {
        return success(( new ConfigService() )->getConfig());
    }
}
