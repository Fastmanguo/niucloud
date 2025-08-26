<?php


namespace addon\saler_tools\app\service\diy\dict;

use core\dict\DictLoader;

/**
 * 自定义链接
 * Class LinkDict
 * @package app\dict\diy
 */
class LinkDict
{
    /**
     * 查询存在页面路由的应用插件列表 query 格式：'query' => 'addon'
     * 查询插件的链接列表，包括系统的链接 addon 格式：'addon' => 'shop'
     * @param array $params
     * @return array|null
     */
    public static function getLink($params = [])
    {
        $system_links = [
            'SYSTEM_LINK'           => [
                'title'      => get_lang('dict_diy.system_link'),
                'addon_info' => [
                    'title' => '系统',
                    'key'   => 'app'
                ],
                'child_list' => [
                    [
                        'name'     => 'INDEX',
                        'title'    => 'APP首页',
                        'url'      => '/app/pages/index/index',
                        'is_share' => 1,
                        'action'   => 'decorate' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'MEMBER_INDEX',
                        'title'    => '会员中心',
                        'url'      => '/app/pages/member/index',
                        'is_share' => 1,
                        'action'   => 'decorate' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'PRODUCT_ADD',
                        'title'    => '入库',
                        'url'      => '/addon/saler_tools/pages/product/add',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'ORDER_ADD',
                        'title'    => '不在库商品开单',
                        'url'      => '/addon/saler_tools/pages/order/add',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'PRODUCT_SAMPLE_GRAPH',
                        'title'    => '示例图',
                        'url'      => '/addon/saler_tools/pages/product/sampleGraph',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'PRODUCT_EDIT_PARAM',
                        'title'    => '附加成本类型设置',
                        'url'      => '/addon/saler_tools/pages/product/editParam',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'PRODUCT_DETAIL',
                        'title'    => '商品详情',
                        'url'      => '/addon/saler_tools/pages/product/detail',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'PRODUCT_BILLING',
                        'title'    => '商品开单',
                        'url'      => '/addon/saler_tools/pages/product/billing',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'ORDER_LISTS',
                        'title'    => '订单列表',
                        'url'      => '/addon/saler_tools/pages/order/lists',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'ORDER_DETAIL',
                        'title'    => '订单详情',
                        'url'      => '/addon/saler_tools/pages/order/detail',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'LOG_ORDER',
                        'title'    => '订单修改记录',
                        'url'      => '/addon/saler_tools/pages/log/order',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'ORDER_LISTS',
                        'title'    => '商品列表',
                        'url'      => '/addon/saler_tools/pages/order/lists',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'AUTH_LIST',
                        'title'    => '员工权限',
                        'url'      => '/addon/saler_tools/pages/auth/list',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'WAREHOUSE_LIST',
                        'title'    => '仓库列表',
                        'url'      => '/addon/saler_tools/pages/warehouse/list',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'PRODUCT_BRAND',
                        'title'    => '品牌',
                        'url'      => '/addon/saler_tools/pages/product/brand',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'PRODUCT_POSITION',
                        'title'    => '商品位置',
                        'url'      => '/addon/saler_tools/pages/product/position',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'STOCK_LIST',
                        'title'    => '临时仓列表',
                        'url'      => '/addon/saler_tools/pages/store/list',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'STOCK_DETAIL',
                        'title'    => '临时仓详情',
                        'url'      => '/addon/saler_tools/pages/store/detail',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'REPAIR_LIST',
                        'title'    => '维修保养',
                        'url'      => '/addon/saler_tools/pages/repair/list',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'REPAIR_DETAIL',
                        'title'    => '维修保养受理单详情',
                        'url'      => '/addon/saler_tools/pages/repair/detail',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'REPIR_EDIT',
                        'title'    => '编辑维修保养受理单',
                        'url'      => '/addon/saler_tools/pages/repair/edit',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'CONSIGNMENT_DELIVERY_LIST',
                        'title'    => '寄卖传送',
                        'url'      => '/addon/saler_tools/pages/consignment/list',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'CONSIGNMENT_DELIVERY_ITEM',
                        'title'    => '寄卖传送列表',
                        'url'      => '/addon/saler_tools/pages/consignment/item',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'LOCKORDER_LIST',
                        'title'    => '锁单商品',
                        'url'      => '/addon/saler_tools/pages/lockOrder/lists',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'LOCKORDER_DETAIL',
                        'title'    => '锁单详情',
                        'url'      => '/addon/saler_tools/pages/lockOrder/detail',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ], [
                        'name'     => 'DELIVERY_LIST',
                        'title'    => '发货管理',
                        'url'      => '/addon/saler_tools/pages/delivery/lists',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'COMMODITY_INVENTORY_LIST',
                        'title'    => '商品盘点',
                        'url'      => '/addon/saler_tools/pages/inventory/lists',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'COMMODITY_INVENTORY_UNDERWAY',
                        'title'    => '盘点列表-进行中',
                        'url'      => '/addon/saler_tools/pages/inventory/check',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'STORAGE_AGE_LIST',
                        'title'    => '库龄预警',
                        'url'      => '/addon/saler_tools/pages/storageAge/list',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'FINANCE_LIST',
                        'title'    => '薪资管理',
                        'url'      => '/addon/saler_tools/pages/finance/list',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'FINANCE_DETAIL',
                        'title'    => '薪资提成明细',
                        'url'      => '/addon/saler_tools/pages/finance/salaryEdit',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'COMMODITY_INVENTORY_TEMPORARY_WAREHOUSE',
                        'title'    => '选择要转入的临时仓',
                        'url'      => '/addon/saler_tools/pages/inventory/temporaryWarehouse',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'COMMODITY_INVENTORY_WAREHOUSE',
                        'title'    => '创建仓库盘点',
                        'url'      => '/addon/saler_tools/pages/inventory/select',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'FINANCE_TALLY_LISTS',
                        'title'    => '其他记账',
                        'url'      => '/addon/saler_tools/pages/finance/tallyLists',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'FINANCE_TALLY_ADD',
                        'title'    => '记一笔',
                        'url'      => '/addon/saler_tools/pages/finance/tallyAdd',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'KEEP_ACCOUNTS_DETAIL',
                        'title'    => '账单详情',
                        'url'      => '/addon/saler_tools/pages/finance/tallyDetail',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'SHOPBILL_LIST',
                        'title'    => '对账单',
                        'url'      => '/addon/saler_tools/pages/shopBill/lists',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'SHOPBILL_EDIT',
                        'title'    => '编辑对账单',
                        'url'      => '/addon/saler_tools/pages/shopBill/edit',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'SHOPBILL_DETAIL',
                        'title'    => '对账单详情',
                        'url'      => '/addon/saler_tools/pages/shopBill/detail',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'LOG_LIST',
                        'title'    => '操作日志',
                        'url'      => '/addon/saler_tools/pages/log/list',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'AUTH_EDIT',
                        'title'    => '修改员工权限',
                        'url'      => '/addon/saler_tools/pages/auth/edit',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'DATA_ANALYSIS_LIST',
                        'title'    => '数据分析',
                        'url'      => '/addon/saler_tools/pages/dataAnalysis/list',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'DATA_ANALYSIS_NEWSPAPER',
                        'title'    => '数据报表',
                        'url'      => '/addon/saler_tools/pages/dataAnalysis/newspaper',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'DATA_ANALYSIS_SALES_VOLUME',
                        'title'    => '销售分析',
                        'url'      => '/addon/saler_tools/pages/dataAnalysis/salesVolume',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'DATA_ANALYSIS_RECYCLE',
                        'title'    => '回收分析',
                        'url'      => '/addon/saler_tools/pages/dataAnalysis/recycle',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'DATA_ANALYSIS_ASSETS',
                        'title'    => '资产分析',
                        'url'      => '/addon/saler_tools/pages/dataAnalysis/assets',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'DATA_ANALYSIS_ADDITIONAL_COST',
                        'title'    => '附加成本分析',
                        'url'      => '/addon/saler_tools/pages/dataAnalysis/additionalCost',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                    [
                        'name'     => 'DATA_ANALYSIS_PLEDGED_GOODS',
                        'title'    => '质押商品分析',
                        'url'      => '/addon/saler_tools/pages/dataAnalysis/pledgedGoods',
                        'is_share' => 1,
                        'action'   => '' // 默认空，decorate 表示支持装修
                    ],
                ]
            ],
            'MEMBER_LINK'           => [
                'title'      => get_lang('dict_diy.member_link'),
                'addon_info' => [
                    'title' => '系统',
                    'key'   => 'app'
                ],
                'child_list' => [
                    [
                        'name'     => 'MEMBER_CONTACT',
                        'title'    => '我的',
                        'url'      => '/app/pages/member/contact',
                        'is_share' => 0,
                        'action'   => ''
                    ],
                ]
            ],
            'DIY_PAGE'              => [
                'title'      => get_lang('dict_diy.diy_page'),
                'addon_info' => [
                    'title' => '系统',
                    'key'   => 'app'
                ],
                'child_list' => []
            ],
            'DIY_LINK'              => [
                'title'      => get_lang('dict_diy.diy_link'),
                'addon_info' => [
                    'title' => '系统',
                    'key'   => 'app'
                ],
                'child_list' => []
            ],
            'DIY_JUMP_OTHER_APPLET' => [
                'title'      => get_lang('dict_diy.diy_jump_other_applet'),
                'addon_info' => [
                    'title' => '系统',
                    'key'   => 'app'
                ],
                'child_list' => []
            ],
            'DIY_MAKE_PHONE_CALL'   => [
                'title'      => get_lang('dict_diy.diy_make_phone_call'),
                'addon_info' => [
                    'title' => '系统',
                    'key'   => 'app'
                ],
                'child_list' => []
            ]
        ];

        // 查询存在页面路由的应用插件列表
        if (!empty($params['query']) && $params['query'] == 'addon') {
            $system = [
                'app' => [
                    'title' => '系统',
                    'key'   => 'app'
                ]
            ];
            $addons = (new DictLoader("UniappLink"))->load(['data' => $system, 'params' => $params]);
            $app    = array_merge($system, $addons);
            return $app;
        } else {
            return (new DictLoader("UniappLink"))->load(['data' => $system_links, 'params' => $params]);
        }
    }

}
