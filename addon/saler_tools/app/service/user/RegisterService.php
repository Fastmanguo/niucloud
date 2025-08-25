<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/23 19:40
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\user;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\UserOauth;
use addon\saler_tools\app\service\CaptchaService;
use app\model\sys\SysUser;
use think\captcha\facade\Captcha;
use think\facade\Cache;
use think\helper\Str;

/**
 *
 * Class RegisterService
 * @package addon\saler_tools\app\service\user
 */
class RegisterService extends BaseAdminService
{

    public function captcha($data)
    {
        $captcha = Captcha::create(null, true);

        $res = [
            'captcha_key' => md5(uniqid('', true) . $data['email']),
            'img'         => $captcha['img'],
        ];

        Cache::tag('SYS')->set('captcha:' . $res['captcha_key'], [
            'code'  => $captcha['code'],
            'time'  => time(),
            'email' => $data['email'],
        ], 600);

        return success($res);

    }


    public function sendCaptcha($data)
    {
        // 加邮箱下发频次锁
        $res = Cache::get('SYS:register_email_send:' . $data['email']);

        if ($res) {
            return fail('one_minute_before_sending');
        }

        Cache::set('SYS:register_email_send:' . $data['email'], 1, 60);

        return (new CaptchaService())->send([
            'captcha_key'      => 'REGISTER',
            'acceptor_address' => $data['email'],
            'channel'          => 'email',
            'ip'               => $this->request->ip(),
            'code'             => Str::random(type: 1)
        ]);
    }


    public function index($data)
    {

        $email = $data['email'];

        // 先校验验证码
        $captcha_service = new CaptchaService();

        $captcha_service->verify($email, 'REGISTER', $data['code'], 'email');

        $user_model       = new SysUser();
        $user_oauth_model = new UserOauth();

        // 校验重复
        $user = $user_model->where('username', $email)->findOrEmpty();
        // 邮箱已注册过了
        if (!$user->isEmpty()) return fail('email_repeat');

        $user_oauth = $user_oauth_model->where('email', $email)->findOrEmpty();

        if (!$user_oauth->isEmpty()) return fail('email_repeat');

        $user_model->startTrans();

        try {

            // 获取邀请人
            $invitation_uid = 0;

            if ($data['invitation_code']) {
                $invitation_uid = $user_oauth_model->where('invitation_code', $data['invitation_code'])->value('uid') ?? 0;
            }

            $user = $user_model->create([
                'username'    => $data['email'],
                'real_name'   => $data['real_name'],
                'head_img'    => '',
                'password'    => create_password($data['password']),
                'last_ip'     => $this->request->ip(),
                'last_time'   => time(),
                'create_time' => time(),
                'login_count' => 0,
                'status'      => 1,
                'is_del'      => 0,
                'delete_time' => 0
            ]);


            $user_oauth_model->create([
                'uid'             => $user->uid,
                'invitation_uid'  => $invitation_uid,
                'invitation_code' => '',
                'email'           => $data['email'],
            ]);

            $user_model->commit();

            return success();

        } catch (\Exception $e) {
            $user_model->rollback();
            throw $e;
            return fail();
        }

    }


}
