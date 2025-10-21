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

namespace app\model\member;

use core\base\BaseModel;

/**
 * 会员收货地址模型
 * Class MemberAddress
 * @package app\model\member
 */
class UserBookkeepingModel extends BaseModel
{

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'user_bookkeeping';

    protected $field = [
        'id',
        'price',
        'type',
        'create_time',
        'update_time',
        'images',
        'remarks',
        'uid',
        'f_id',
        'prepare',
    ];

}
