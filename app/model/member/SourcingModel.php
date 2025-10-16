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
class SourcingModel extends BaseModel
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
    protected $name = 'saler_tools_goods_sourcing';

    protected $field = [
        'id',
        'goods_id',
        'uid',
        'sale_uids',
        'requirement',
        'mobile',
        'deposit_price',
        'delivery_time',
        'payment_images',
        'remarks',
        'status',
        'create_time',
        'warehousing_time',
        'sourc_on',
        'balance_price',
        'price',
        'order_id',
        'update_time',
        'billing_time',
        
    ];

}
