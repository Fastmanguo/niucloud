<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/23 5:24
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;

/**
 * 用户关联三方平台信息
 * Class UserOauth
 * @package addon\saler_tools\app\model
 */
class UserOauth extends BaseModel
{

    protected $pk = 'uid';

    protected $name = 'saler_tools_user_oauth';

    protected $autoWriteTimestamp = false;

    // 定义可填充字段
    protected $field = [
        'uid',
        'invitation_uid', 
        'invitation_code',
        'email',
        'mobile',
        'wx_openid',
        'weapp_openid',
        'wx_unionid',
        'ali_openid',
        'douyin_openid',
        'google_openid'
    ];

}
