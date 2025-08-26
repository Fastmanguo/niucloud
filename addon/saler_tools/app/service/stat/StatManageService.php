<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/6/1 15:53
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\stat;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\common\BaseService;
use addon\saler_tools\app\model\Order as OrderModel;
use addon\saler_tools\app\service\order\OrderService;
use addon\saler_tools\app\model\Goods as GoodsModel;
use addon\saler_tools\app\model\OrderService as OrderServiceModel;
use addon\saler_tools\app\model\GoodsLog as GoodsLogModel;
use think\db\Raw;

/**
 *
 * Class StatManageService
 * @package addon\saler_tools\app\service\stat
 */
class StatManageService extends BaseService
{

    public function getStat($data)
    {

        $site_id     = $data['site_id'];
        $month_start = strtotime(date('Y-m-01', $data['month_time']));
        $month_end   = strtotime(date('Y-m-t', $data['month_time']));


        $order_model         = new OrderModel();
        $goods_model         = new GoodsModel();
        $order_service_model = new OrderServiceModel();
        $goods_log_model     = new GoodsLogModel();

        // 统计订单
        $order_count = $order_model->where([
            ['site_id', '=', $site_id],
            ['type', '=', 'sale'],
            ['order_status', 'in', [OrderService::FINISH_ORDER, OrderService::ADD_ORDER]],
            ['create_time', 'between', [date('Y-m-d H:i:s', $month_start), date('Y-m-d H:i:s', $month_end)]]
        ])->field('count(order_id) as order_count,IFNULL(SUM(money), 0) as order_money, IFNULL(SUM(money), 0) - IFNULL(SUM(total_cost), 0) AS profit_money')
            ->findOrEmpty()->toArray();

        // 统计商品类型
        $goods_count = $goods_model->where([
            ['site_id', '=', $site_id],
            ['create_time', 'between', [date('Y-m-d H:i:s', $month_start), date('Y-m-d H:i:s', $month_end)]]
        ])->field([
            new Raw('COUNT(CASE WHEN goods_attribute = "own_goods" THEN 1 END) AS own_goods_count'),
            new Raw('COUNT(CASE WHEN goods_attribute = "consignment_goods" THEN 1 END) AS consignment_goods_count'),
            new Raw('COUNT(CASE WHEN goods_attribute = "pawned_goods" THEN 1 END) AS pawned_goods_count'),
            new Raw('COUNT(CASE WHEN goods_attribute = "others" THEN 1 END) AS others_goods_count'),

            new Raw('sum(CASE WHEN goods_attribute = "own_goods" THEN total_cost ELSE 0 END) AS own_goods_cost'),
            new Raw('sum(CASE WHEN goods_attribute = "consignment_goods" THEN total_cost ELSE 0 END) AS consignment_goods_cost'),
            new Raw('sum(CASE WHEN goods_attribute = "pawned_goods" THEN total_cost ELSE 0 END) AS pawned_goods_cost'),
            new Raw('sum(CASE WHEN goods_attribute = "others" THEN total_cost ELSE 0 END) AS others_goods_cost'),
        ])->findOrEmpty()->toArray();


        // TODO:上架数量与锁单数量需要从日志中读取
        $on_sale_count = $goods_log_model->where([
            ['site_id', '=', $site_id],
            ['create_time', 'between', [$month_start, $month_end]],
            ['type', '=', 1]
        ])->field('sum(num)  as on_sale_count')->findOrEmpty()->toArray();

        $lock_count = $goods_log_model->where([
            ['site_id', '=', $site_id],
            ['create_time', 'between', [$month_start, $month_end]],
            ['type', '=', 10]
        ])->field('sum(num) as lock_count')->findOrEmpty()->toArray();

        $goods_count['on_sale_count'] = $on_sale_count['on_sale_count'] ?? 0;
        $goods_count['lock_count']    = $lock_count['lock_count'] ?? 0;

        // 维修保养
        $order_service_count = $order_service_model->where([
            ['site_id', '=', $site_id],
            ['status', '=', 'finish'],
            ['create_time', 'between', [date('Y-m-d H:i:s', $month_start), date('Y-m-d H:i:s', $month_end)]]
        ])->field([
            'count(service_id) as  service_count',
            'IFNULL(SUM(cost), 0) as  service_cost',
            'IFNULL(SUM(money), 0) as  service_money'
        ])->findOrEmpty()->toArray();

        $order_service_count['service_profit_money'] = bcsub(($order_service_count['service_money'] ?? 0), ($order_service_count['service_cost'] ?? 0), 2);

        $res = array_merge($order_count, $goods_count, $order_service_count);

        foreach ($res as $key => &$value) {
            $value = floatval($value);
        }

        return $res;

    }

}
