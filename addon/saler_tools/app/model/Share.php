<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/22 21:38
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;
use app\model\sys\SysUser;

/**
 * 分享
 * Class Share
 * @package addon\saler_tools\app\model
 */
class Share extends BaseModel
{

    protected $pk = 'share_id';

    protected $name = 'saler_tools_share';

    protected $readonly = ['site_id', 'uid'];

    protected $type = [
        'price_config' => 'json',
        'share_result' => 'json',
        'class_ids'    => 'array',
        'goods_ids'    => 'array',
    ];


    public function createNames()
    {
        return $this->hasOne(SysUser::class,'uid','uid')->bind(['create_name' => 'real_name']);
    }

}
