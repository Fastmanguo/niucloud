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
 * 客户模型
 * Class CustomerModel
 * @package app\model\member
 */
class ComplaintModel extends BaseModel
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
    protected $name = 'saler_tools_goods_complaint';

    /**
     * 数据表字段信息
     * @var array
     */
    protected $field = [
        "id",
        "goods_id",
        "uid",
        "complaint_type",
        "complaint_images",
        "create_time",
        "update_time",
    ];

}
