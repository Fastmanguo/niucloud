<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/3/28 23:00
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\im;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\common\HttpClient;
use app\model\sys\SysUser;
use app\service\core\sys\CoreConfigService;
use core\exception\AuthException;

/**
 * im核心服务
 * Class CoreImService
 * @package addon\saler_tools\app\service\im
 */
class CoreImService extends BaseAdminService
{

    public static $config;


    public function __construct()
    {
        parent::__construct();
        self::$config = (new ImConfigService())->getConfig();
    }

    /**
     * 用户上线获取IM TOKEN
     */
    public function userOnline()
    {
        $uid = $this->uid;

        $user_info = (new SysUser())->where('uid', $uid)->findOrEmpty()->toArray();

        $headers = [
            'Content-Type: application/json',
            'App-Key: ' . self::$config['app_key'],
            'App-Secret: ' . self::$config['app_secret'],
        ];

        $default = 'upload/app/logo_min.png';

        $data = [
            'id'             => $uid,
            'userName'       => $user_info['username'],
            'headImage'      => empty($user_info['head_img']) ? $default : $user_info['head_img'],
            'headImageThumb' => empty($user_info['head_img']) ? $default : $user_info['head_img'],
            'nickName'       => $user_info['real_name'],
        ];

        $res = HttpClient::postJson(self::$config['server_url'] . 'api/updateUserInfo', $data, [
            'headers' => $headers,
        ]);

        if ($res['code'] != 200) throw new AuthException('IM SERVICE ERROR');

        return $res['data'];
    }

    /**
     * 用户下线
     */
    public function userOffline()
    {
        // TODO: Implement userOffline() method.
    }

    /**
     * 推送消息
     */
    public function pushMessage($option)
    {
        $headers = [
            'Content-Type: application/json',
            'App-Key: ' . self::$config['app_key'],
            'App-Secret: ' . self::$config['app_secret'],
        ];

        $data = [
            'sendId'     => $this->uid,
            //
            'recvId'     => $option['uid'],
            'reHeadImg'  => $option['head_img'],
            'reUsername' => $option['username'],
            'reRealName' => $option['real_name'],

            'content'    => $option['content'],
            'type'       => $option['type'],
        ];

        $res = HttpClient::postJson(self::$config['server_url'] . 'api/sendHello', $data, [
            'headers' => $headers,
        ]);

        if ($res['code'] != 200) throw new AuthException('IM SERVICE ERROR');

        return $res['data'];
    }

    /**
     * 线上展会沟通
     */
    public function onlineExhibition()
    {
        // TODO: Implement onlineExhibition() method.
    }


}
