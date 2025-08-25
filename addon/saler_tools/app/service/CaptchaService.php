<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/23 20:08
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\job\QueueCaptchaJob;
use addon\saler_tools\app\service\notify\EmailNotifyService;
use core\exception\AdminException;
use addon\saler_tools\app\model\Captcha as CaptchaModel;


/**
 * 验证码服务
 * Class CaptchaService
 * @package addon\saler_tools\app\service
 */
class CaptchaService extends BaseAdminService
{

    /**
     * 添加发送验证码任务
     */
    public function send($data, $is_async = true)
    {

        if (empty($data['acceptor_address'])) throw new AdminException('acceptor_address_require');

        if (empty($data['captcha_key'])) throw new AdminException('captcha_key_require');

        if (empty($data['code'])) throw new AdminException('captcha_code_require');

        if (empty($data['exp_time'])) $data['exp_time'] = time() + 600;

        // 作废相同的验证码
        CaptchaModel::update(['status' => -1], [['captcha_key', '=', $data['captcha_key']], ['acceptor_address', '=', $data['acceptor_address']]]);

        $captcha = [
            'captcha_key'      => $data['captcha_key'],
            'lang'             => $data['lang'] ?? $this->app->appLang,
            'code'             => $data['code'],
            'acceptor_address' => $data['acceptor_address'],
            'channel'          => $data['channel'],
            'status'           => 0,
            'ip'               => $data['ip'] ?? $this->request->ip(),
            'create_time'      => time(),
            'exp_time'         => $data['exp_time'],
        ];

        $captcha = CaptchaModel::create($captcha);

        if ($is_async) {
            QueueCaptchaJob::dispatch([
                'data' => $captcha->toArray(),
            ]);
        } else {
            $this->doSend($captcha->toArray());
        }

        return success();
    }


    /**
     * 执行验证码下发
     * @param $data
     */
    public function doSend($data)
    {
        if ($data['channel'] == 'email') {
            (new EmailNotifyService())->sendTemplate([
                'email'        => $data['acceptor_address'],
                'template_key' => $data['captcha_key'],
                'lang_key'     => $data['lang'],
                'data'         => [
                    'code' => $data['code'],
                ],
            ]);
        } else if ($data['channel'] == 'sms') {

        }

        CaptchaModel::update(['status' => 1], [['id', '=', $data['id']]]);

        return true;

    }


    /**
     * 校验验证码是否正确
     * @param $acceptor_address
     * @param $captcha_key
     * @param $code
     * @param $channel
     * @return true
     */
    public function verify($acceptor_address, $captcha_key, $code, $channel)
    {
        $captcha_model = new CaptchaModel();

        $captcha = $captcha_model->where('acceptor_address', $acceptor_address)
            ->where('captcha_key', $captcha_key)
            ->where('channel', $channel)
            ->order('id', 'desc')
            ->findOrEmpty();

        if ($captcha->isEmpty()) throw new AdminException('captcha_error');

        if ($captcha['status'] != 1) throw new AdminException('captcha_expired');

        if ($captcha['exp_time'] < time()) throw new AdminException('captcha_expired');

        if ($captcha['code'] != $code) {
            // 防止爆破
            CaptchaModel::update(['status' => -1], [['id', '=', $captcha['id']]]);
            throw new AdminException('captcha_error');
        }

        CaptchaModel::update(['status' => 2], [['id', '=', $captcha['id']]]);

        return true;

    }


}
