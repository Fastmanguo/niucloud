<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/30 2:44
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\user;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\common\Utils;
use addon\saler_tools\app\model\Goods;
use addon\saler_tools\app\model\Order;
use addon\saler_tools\app\model\UserOauth;
use addon\saler_tools\app\service\CaptchaService;
use app\model\sys\SysUser;
use think\facade\Cache;
use think\helper\Str;
use addon\online_expo\app\model\Stat as OnlineStatModel;

/**
 *
 * Class UserService
 * @package addon\saler_tools\app\service\user
 */
class UserService extends BaseAdminService
{


    public function info()
    {
        $fields = 'uid,username,head_img,real_name,status,create_time';
        $user   = (new SysUser())->where('uid', $this->uid)->field($fields)->findOrEmpty();

        $user_oauth = (new UserOauth())->where('uid', $this->uid)->findOrEmpty();

        if ($user_oauth->isEmpty()) {
            $user_oauth->save([
                'uid'           => $this->uid,
                'wx_openid'     => '',
                'weapp_openid'  => '',
                'wx_unionid'    => '',
                'ali_openid'    => '',
                'douyin_openid' => '',
                'google_openid' => '',
                'email'         => '',
            ]);
        }

        $user_oauth = $user_oauth->toArray();

        $user = $user->toArray();

        return success(array_merge($user, $user_oauth));
    }


    public function getRealName($uid)
    {
        $key = $this->app->siteCache->user_cache_key . $uid;
        return cache_remember($key, function () use ($uid) {
            // 验证站点
            $user_model = new SysUser();
            $user       = $user_model->where($user_model->getName() . '.uid', $uid)->hasWhere('userrole', [['site_id', '=', $this->site_id]])->findOrEmpty();
            if ($user->isEmpty()) $real_name = '已离职';
            else $real_name = $user->real_name;

            return success(['real_name' => $real_name]);

        });
    }


    public function bindEmail($data)
    {

        $password = $data['password'];
        $email    = $data['email'];

        $user_model       = new SysUser();
        $user_oauth_model = new UserOauth();

        $user = $user_model->where('uid', $this->uid)->findOrEmpty();

        if (!check_password($password, $user->password)) {
            return fail('密码错误');
        }

        $captcha_service = new CaptchaService();

        $captcha_service->verify($email, 'REGISTER', $data['code'], 'email');

        $user_oauth = $user_oauth_model->where('uid', $this->uid)->findOrEmpty();

        if ($user_oauth->isEmpty()) {
            $user_oauth_model->create([
                'uid'   => $this->uid,
                'email' => $email,
            ]);
        } else {
            $user_oauth->email = $email;
            $user_oauth->save();
        }

        return success();
    }


    public function password($data)
    {

        $password     = $data['password'];
        $old_password = $data['old_password'];

        // 检查新旧密码是否一致
        if ($password === $old_password) {
            return fail('新旧密码不能相同，请重新输入');
        }

        $user_model = new SysUser();

        $user = $user_model->where('uid', $this->uid)->findOrEmpty();

        if (!check_password($old_password, $user->password)) {
            return fail('旧密码错误');
        }

        $user->password = create_password($password);

        $user->save();

        return success();

    }


    public function sendBindEmail($data)
    {
        $password = $data['password'];
        $email    = $data['email'];

        $user_model = new SysUser();

        $user = $user_model->where('uid', $this->uid)->findOrEmpty();

        if (!check_password($password, $user->password)) {
            return fail('密码错误');
        }

        $user_oauth_model = new UserOauth();

        $user_oauth = $user_oauth_model->where('email', $email)->findOrEmpty();

        if (!$user_oauth->isEmpty()) return fail('email_already_bind');

        // 加邮箱下发频次锁
        $res = Cache::get('SYS:register_email_send:' . $email);

        if ($res) {
            return fail('please_wait_a_minute');
        }

        Cache::set('SYS:register_email_send:' . $email, 1, 60);

        return (new CaptchaService())->send([
            'captcha_key'      => 'BIND_EMAIL',
            'acceptor_address' => $email,
            'channel'          => 'email',
            'ip'               => $this->request->ip(),
            'code'             => Str::random(type: 1)
        ]);

    }


    public function modify($field, $value)
    {
        $user_oauth_model = new UserOauth();
        $user_oauth       = $user_oauth_model->where('uid', $this->uid)->findOrEmpty();
        if ($user_oauth->isEmpty()) {
            $user_oauth_model->create([
                'uid'  => $this->uid,
                $field => $value,
            ]);
        } else {
            $user_oauth->$field = $value;
            $user_oauth->save();
        }
        return success();
    }


    public function updateHeadImg($file)
    {
        $dir = 'upload/head_img/' . date('Y') . '/' . date('m') . '/' . date('d');
        mkdirs($dir);
        $file_name = $dir . '/' . Utils::createnoEx() . '.jpg';

        resize_image($file->getRealPath(), 1024, 1024, $file_name);

        $user_model = new SysUser();

        $user = $user_model->where('uid', $this->uid)->findOrEmpty();

        $user->save(['head_img' => $file_name]);

        return success(['head_img' => $file_name]);
    }


    public function cancel($data)
    {
        $user = (new SysUser())->where('uid', $this->uid)->findOrEmpty();
        if ($user->isEmpty()) return fail();

        if (!check_password($data['password'], $user->password)) {
            return fail('密码错误');
        }

        $user->status = 0;
        $user->save();

        // 清理用户token，确保注销后无法登录
        \app\service\admin\auth\LoginService::clearToken($this->uid);

        return success();
    }

    public function stat()
    {
        $goods_total    = (new Goods())->where('site_id', $this->site_id)->where('create_uid', $this->uid)->count('goods_id');
        $order_total    = (new Order())->where('site_id', $this->site_id)->where('create_uid', $this->uid)->count('order_id');
        $visitors_count = (new OnlineStatModel())->where('site_id', $this->site_id)->where('date_key', date('Ymd'))->value('visitors_count', 0);
        return success([
            'goods_total'    => $goods_total,
            'order_total'    => $order_total,
            'visitors_count' => $visitors_count
        ]);
    }


}
