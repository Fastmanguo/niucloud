<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/6/1 18:54
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\diy\dict;

/**
 *
 * Class GoodsDict
 * @package addon\saler_tools\app\service\diy\dict
 */
class GoodsDict
{

    const CREATE         = 1;
    const UPDATE         = 2;
    const ON_SALE        = 3;
    const OFF_SALE       = 4;
    const CREATE_ON_SALE = 5;
    const DELETE         = 9;
    const LOCK           = 10;
    const UNLOCK         = 11;

    const CREATE_ORDER = 20;

    const GOODS_LOG_TYPE = [
        self::CREATE         => '创建商品',
        self::UPDATE         => '修改信息',
        self::ON_SALE        => '上架商品',
        self::OFF_SALE       => '下架商品',
        self::CREATE_ON_SALE => '创建并上架',
        self::DELETE         => '删除商品',

        self::LOCK   => '创建锁单',
        self::UNLOCK => '取消锁单',

        self::CREATE_ORDER => '开单销售'
    ];

}
