<?php


namespace addon\saler_tools\app\service\diy\dict;

use core\dict\DictLoader;

/**
 * 页面模板
 */
class TemplateDict
{

    /**
     * 根据关键字查询：key 格式：[ 'DIY_INDEX', 'DIY_MEMBER_INDEX','DIY_SHOP_MEMBER_INDEX' ]
     * 查询指定类型的页面：type 格式：'type' => 'member_index'
     * 查询指定插件定义的所有页面：addon 格式：'addon' => 'shop'
     * 查询可装修的页面类型：action 格式：'action' => 'decorate'
     * 查询存在模板页面的应用插件列表：query 格式：'query' => 'addon'
     * @param array $params
     * @return array|null
     */
    public static function getTemplate($params = [])
    {
        $system_pages = [
            'APP_HOME' => [
                'title'  => 'APP首页',
                'page'   => '/app/pages/home/index',
                'action' => 'decorate', // 页面是否装修标识，为空标识不装修，decorate：装修
                'type'   => 'index' // 页面类型，index：首页、member_index：个人中心，空：普通页面
            ],
            'APP_INDEX' => [
                'title'  => 'APP控制台',
                'page'   => '/app/pages/index/index',
                'action' => 'decorate', // 页面是否装修标识，为空标识不装修，decorate：装修
                'type'   => 'index' // 页面类型，index：首页、member_index：个人中心，空：普通页面
            ],
            'APP_MY'   => [
                'title'  => 'APP个人中心',
                'page'   => '/app/pages/member/index',
                'action' => 'decorate',
                'type'   => 'member_index'
            ],
            'DIY_PAGE' => [
                'title'  => 'APP微页',
                'page'   => '/app/pages/index/diy',
                'action' => '',
                'type'   => ''
            ]
        ];

        // 查询存在模板页面的应用插件列表
        if (!empty($params['query']) && $params['query'] == 'addon') {
            $system = [
                'app' => [
                    'title' => '系统',
                    'key'   => 'app',
                    'list'  => $system_pages
                ]
            ];
            $addon  = (new DictLoader("UniappTemplate"))->load(['data' => $system, 'params' => $params]);
            $app    = array_merge($system, $addon);
            return $app;
        } else {
            // 查询应用插件下的模板页面数据

            $pages = (new DictLoader("UniappTemplate"))->load(['data' => $system_pages, 'params' => $params]);

            // 根据关键字查询
            if (!empty($params['key'])) {
                $temp = [];
                foreach ($params['key'] as $k => $v) {
                    if (!empty($pages[$v])) {
                        $temp[$v] = $pages[$v];
                    }
                }
                return $temp;
            }

            // 查询指定类型的页面
            if (!empty($params['type'])) {
                $temp = [];
                foreach ($pages as $k => $v) {
                    if (isset($v['type']) && $params['type'] == $v['type']) {
                        $temp[$k] = $v;
                    }
                }
                return $temp;
            }

            // 查询可装修的页面类型
            if (!empty($params['action'])) {
                $temp = [];
                foreach ($pages as $k => $v) {
                    if (isset($v['action']) && $params['action'] == $v['action']) {
                        $temp[$k] = $v;
                    }
                }
                return $temp;
            }

            return $pages;

        }
    }

}
