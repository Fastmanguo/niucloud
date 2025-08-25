<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/20 21:44
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\admin;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\dict\sys\SystemDict;
use addon\saler_tools\app\service\notify\EmailNotifyService;
use app\service\admin\auth\ConfigService;
use app\service\core\sys\CoreConfigService;
use stdClass;

/**
 *
 * Class SettingService
 * @package addon\saler_tools\app\service\admin
 */
class SettingService extends BaseAdminService
{

    public function getEmail()
    {
        return (new CoreConfigService())->getConfig($this->request->defaultSiteId(), SystemDict::EMAIL_CONFIG)['value'] ?? [];
    }


    public function updateEmail($data)
    {
        (new CoreConfigService())->setConfig($this->request->defaultSiteId(), SystemDict::EMAIL_CONFIG, $data);
        return success();
    }


    public function getAppConfig()
    {
        $result = (new CoreConfigService())->getConfig($this->request->defaultSiteId(), SystemDict::APP_CONFIG);
        if (empty($result)) $result = [
            'default' => [
                'app_name'            => '应用名称',
                'app_name_full'       => '应用名称',
                'app_log'             => '',
                'login_type'          => ['email', 'username', 'phone', 'sim'],
                'default_login_type'  => 'username',
                'service_type'        => 'wechat',
                'service_url'         => '',
                'service_appid'       => '',
                'map_key'             => '',
                'user_avatar_default' => '',
                'shop_avatar_default' => ''
            ]
        ];
        else $result = $result['value'];
        return success(data: $result);
    }

    public function updateAppConfig($data)
    {
        (new CoreConfigService())->setConfig($this->request->defaultSiteId(), SystemDict::APP_CONFIG, $data);
        return success();
    }


    public function testEmail($data)
    {
        $mail = (new EmailNotifyService())->debug($data);

        $mail->addAddress($data['test_email']);
        $mail->isHTML();
        $mail->Subject   = '这是一封测试邮件';
        $mail->Body      = '系统发送于 ' . date('Y-m-d H:i:s') . ' 客户端IP：' . $this->request->ip();
        $mail->SMTPDebug = 0;
        $res             = $mail->send();
        if ($res) {
            return success(data: '发送成功');
        } else {
            return success(data: $mail->ErrorInfo);
        }

    }

}
