<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/13 20:33
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\user;

use addon\saler_tools\app\common\BaseService;
use addon\saler_tools\app\model\UserOauth;
use addon\saler_tools\app\service\CaptchaService;
use app\model\sys\SysUser;
use think\captcha\facade\Captcha;
use think\facade\Cache;
use think\helper\Str;
use function DI\get;

/**
 * 找回密码
 * Class UserForgetService
 * @package addon\saler_tools\app\service\user
 */
class UserForgetService extends BaseService
{

    public function captcha()
    {
        $captcha = Captcha::create(null, true);

        $res = [
            'captcha_key' => md5(uniqid('', true)),
            'img'         => $captcha['img'],
        ];

        Cache::tag('SYS')->set('captcha:' . $res['captcha_key'], [
            'code' => $captcha['code'],
            'time' => time()
        ], 600);

        return success($res);
    }


    public function sendCode($data)
    {
        // 检查图像验证码
        $captcha_key   = $data['captcha_key'];
        $captcha_value = $data['captcha_value'];

        $captcha_data = Cache::get('captcha:' . $captcha_key);

        if (empty($captcha_data) || $captcha_data['code'] != $captcha_value) return fail('captcha_error');

        // 图像验证码只能使用一次
        Cache::delete('captcha:' . $captcha_key);

        // 验证账户是否存在
        $auth = (new UserOauth())->where('email', $data['email'])->findOrEmpty();

        if ($auth->isEmpty()) return fail('error_email_not_exist');

        // 加邮箱下发频次锁
        $res = Cache::get('SYS:forget_email_send:' . $data['email']);

        if ($res) {
            return fail('please_wait_a_minute');
        }

        Cache::set('SYS:forget_email_send:' . $data['email'], 1, 60);

        return (new CaptchaService())->send([
            'captcha_key'      => 'FORGET',
            'acceptor_address' => $data['email'],
            'channel'          => 'email',
            'ip'               => $this->request->ip(),
            'code'             => Str::random(type: 1)
        ]);
    }


    public function resetPassword($data)
    {
        $email    = $data['email'];
        $code     = $data['code'];
        $password = $data['password'];

        (new CaptchaService())->verify($email, 'FORGET', $code, 'email');

        $auth = (new UserOauth())->where('email', $data['email'])->findOrEmpty();

        if ($auth->isEmpty()) return fail('error_email_not_exist');

        SysUser::update(
            [
                'password' => create_password($password)
            ],
            ['uid' => $auth->uid]);

        return success();
    }

}
