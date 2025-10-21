<?php

return [
    [
        'key' => 'order_close',
        'name' => '未支付订单自动关闭',
        'desc' => '',
        'time' => [
            'type' => 'min',
            'min' => 1
        ],
        'class' => '',
        'function' => ''
    ],
    [
        'key' => 'site_expire_close',
        'name' => '站点到期自动关闭',
        'desc' => '',
        'time' => [
            'type' => 'day',
            'day' => 1,
            'hour' => 1,
            'min' => 1
        ],
        'class' => 'app\job\schedule\SiteExpireClose',
        'function' => ''
    ],
    [
        'key' => 'goods_cl_status_reset',
        'name' => '商品擦亮状态自动重置',
        'desc' => '每天凌晨0点将所有商品的cl_status修改为0',
        'time' => [
            'type' => 'day',
            'day' => 1,
            'hour' => 0,
            'min' => 0
        ],
        'class' => 'app\job\schedule\GoodsClStatusReset',
        'function' => ''
    ],
//    [
//        'key' => 'site_stat',
//        'name' => '站点统计',
//        'desc' => '',
//        'time' => [
//            'type' => 'hour',
//            'hour' => 1,
//        ],
//        'class' => 'app\job\schedule\SiteStatJob',
//        'function' => ''
//    ]
];
