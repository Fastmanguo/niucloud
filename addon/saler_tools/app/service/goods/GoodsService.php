<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/11 19:36
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\goods;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\Goods as GoodsModel;
use addon\saler_tools\app\model\GoodsLog;
use addon\saler_tools\app\model\Order;
use addon\saler_tools\app\model\SalerToolsGoodsAttr as SalerToolsGoodsAttrModel;
use addon\saler_tools\app\model\SalerToolsGoodsCost;
use addon\saler_tools\app\service\dict\SiteDictService;
use addon\saler_tools\app\service\order\OrderService;
use addon\saler_tools\app\service\shop\ShopService;
use think\db\Query;
use think\db\Raw;

/**
 * 商品管理
 * Class GoodsService
 * @package addon\saler_tools\app\service
 */
class GoodsService extends BaseAdminService
{

    public function lists($params, $order = ['goods_id' => 'desc'])
    {
        $model = new GoodsModel();
        // 特殊检索
        $where = [
            ['site_id', '=', $this->site_id]
        ];
        $with  = ['brand', 'series', 'model', 'store', 'appraiserName', 'createName', 'recyclingName', 'updateName'];

        if (isset($params['query_type'])) {

            if ($params['query_type'] == 'position') {
                // 商品位置检索
                $where[] = new Raw('stock > 0 or lock_num > 0');
                if (empty($params['watch_location'])) {
                    $where[] = ['watch_location', '=', ''];
                }
            }

        }

        if (!empty($params['query_price_type']) && (
                (isset($params['query_price_min']) && $params['query_price_min'] !== '')
                ||
                (isset($params['query_price_max']) && $params['query_price_max'] !== '')
            )
        ) {
            if (isset($params['query_price_min']) && $params['query_price_min'] !== '' && isset($params['query_price_max']) && $params['query_price_max'] !== '') {
                $where[] = [$params['query_price_type'], 'between', [$params['query_price_min'], $params['query_price_max']]];
            } elseif (isset($params['query_price_min']) && $params['query_price_min'] !== '') {
                $where[] = [$params['query_price_type'], '>=', $params['query_price_min']];
            } elseif (isset($params['query_price_max']) && $params['query_price_max'] !== '') {
                $where[] = [$params['query_price_type'], '<=', $params['query_price_max']];
            }
        }

        // 未指定商品类型时查询的只有 自有商品 寄卖商品 其它
        if (empty($params['goods_attribute'])) {
            $params['goods_attribute'] = ['own_goods', 'consignment_goods', 'others',"pawned_goods"];
        } elseif ($params['goods_attribute'] == 'pawned_goods') {
            if (!empty($params['is_expire'])) {
                $where[] = ['expire_time', '<', date('Y-m-d H:i:s')];
            }
            $with['orderMoneys'] = function (Query $query) {
                $query->where('order_status', OrderService::FINISH_ORDER)->where('site_id', $this->site_id);
            };
        }

        if(array_key_exists("create_uid",$params) and !empty($params['create_uid'])){
            $model = $model->where($where)
                ->withSearch([
                    'search', 'brand_id', 'series_id', 'model_id', 'create_uid', 'goods_tag', 'target_audience'
                    , 'category_id', 'is_sale', 'watch_location', 'goods_attribute', 'recycling_time'
                ], $params)
                ->order($order)
                ->with($with);
        }else{
            $model = $model->where($where)
                ->withSearch([
                    'search', 'brand_id', 'series_id', 'model_id', 'store_id', 'goods_tag', 'target_audience'
                    , 'category_id', 'is_sale', 'watch_location', 'goods_attribute', 'recycling_time'
                ], $params)
                ->order($order)
                ->with($with);
        }


        return success($this->pageQuery($model));

    }


    public function detail($goods_id)
    {
        $model = new GoodsModel();

        $goods = $model->where('site_id', $this->site_id)
            ->where('goods_id', $goods_id)
            ->with([
                'brand'
                , 'series'
                , 'model'
                , 'store'
                , 'appraiserName'
                , 'createName'
                , 'recyclingName'
                , 'updateName'
                , 'goodsCost'
                , 'goodsAttr'
            ])
            ->findOrEmpty();

        if ($goods->isEmpty()) {
            return fail('find_goods_empty');
        }

        $goods               = $goods->toArray();
        $goods['goods_cost'] = $goods['goodsCost'] ?? [];
        $goods['goods_attr'] = $goods['goodsAttr'] ?? [];
        unset($goods['goodsCost'], $goods['goodsAttr']);

        // 质押商品查询出赎回价格
        if ($goods['goods_attribute'] == 'pawned_goods') {
            $goods['order_money'] = (new Order())->where('goods_id', $goods_id)
                ->where('order_status', OrderService::FINISH_ORDER)
                ->where('site_id', $this->site_id)
                ->value('money');
        }

        return success($goods);
    }


    public function add($data)
    {
        $data['site_id']    = $this->site_id;
        $data['create_uid'] = $this->uid;

        // 没有封面图片
        if (empty($data['goods_cover'])) {
            $data['goods_cover'] = $data['goods_image'][0] ?? '';
        }

        // 处理 goods_image detail_image 默认值
        $data['goods_image']  = empty($data['goods_image']) ? [] : $data['goods_image'];
        $data['detail_image'] = empty($data['detail_image']) ? [] : $data['detail_image'];

        $model = new GoodsModel();

        $model->startTrans();
        try {

            // 当质押商品入库时强制设置 is_sale = 1 表示 商品待赎回
            if ($data['goods_attribute'] == 'pawned_goods') {
                $data['is_sale']        = 1;
                $data['is_online_expo'] = 0;
            }

            // 写入商品地区编码 使用货币
            $shop                  = (new ShopService())->info();
            $data['currency_code'] = $shop['currency_code'];
            $data['country_code']  = $shop['country_code'];

            $goods = $model->create($data);

            // 写入参数
            $attr_model = new SalerToolsGoodsAttrModel();

            foreach ($data['goods_attr'] as $attr) {
                $attr['site_id']  = $this->site_id;
                $attr['goods_id'] = $goods->goods_id;
                $attr['sort']     = $attr['sort'] ?? 0;
                $attr_model->create($attr);
            }

            // 写入成本
            $cost_model = new SalerToolsGoodsCost();

            foreach ($data['goods_cost'] as $cost) {
                $cost['site_id']  = $this->site_id;
                $cost['goods_id'] = $goods->goods_id;
                $cost_model->create($cost);
            }



            $model->commit();
            return success();
        } catch (\Exception $e) {
            $model->rollback();
            return fail($e->getMessage());
        }
    }


    public function edit($data)
    {
        $model = new GoodsModel();
        $goods = $model->where('goods_id', $data['goods_id'])->where('site_id', $this->site_id)->findOrEmpty();

        if ($goods->isEmpty()) {
            return fail('find_goods_empty');
        }

        $model->startTrans();
        try {

            $goods->save($data);

            // 写入参数
            $attr_model = new SalerToolsGoodsAttrModel();

            $attr_model->where('site_id', $this->site_id)->where('goods_id', $goods->goods_id)->delete();

            foreach ($data['goods_attr'] as $attr) {
                $attr['site_id']  = $this->site_id;
                $attr['goods_id'] = $goods->goods_id;
                $attr['sort']     = $attr['sort'] ?? 0;
                $attr_model->create($attr);
            }

            // 更新参数
            $cost_model = new SalerToolsGoodsCost();
            $cost_model->where('site_id', $this->site_id)->where('goods_id', $goods->goods_id)->delete();

            foreach ($data['goods_cost'] as $cost) {
                $cost['site_id']  = $this->site_id;
                $cost['goods_id'] = $goods->goods_id;
                $cost_model->create($cost);
            }

            $model->commit();

            return success();
        } catch (\Exception $e) {
            $model->rollback();
            return fail($e->getMessage());
        }

    }


    /**
     * 商品上架
     */
    public function onSale($goods_id)
    {
        $model = new GoodsModel();
        $goods = $model->where('goods_id', $goods_id)->where('site_id', $this->site_id)->findOrEmpty();

        if ($goods->isEmpty()) {
            return fail('find_goods_empty');
        }

        $model->startTrans();
        try {

            $goods->save(['is_sale' => 1, 'update_uid' => $this->uid]);

            $model->commit();

            return success();
        } catch (\Exception $e) {
            $model->rollback();
            return fail($e->getMessage());
        }
    }


    /**
     * 商品下架
     */
    public function offSale($goods_id)
    {
        $model = new GoodsModel();
        $goods = $model->where('goods_id', $goods_id)->where('site_id', $this->site_id)->findOrEmpty();

        if ($goods->isEmpty()) {
            return fail('find_goods_empty');
        }

        $model->startTrans();
        try {
            $goods->save(['is_sale' => 0, 'update_uid' => $this->uid]);
            $model->commit();
            return success();
        } catch (\Exception $e) {
            $model->rollback();
            return fail($e->getMessage());
        }
    }


    /**
     * 修改商品成本
     */
    public function editCost($goods_id, $cost_data)
    {
        $model = new GoodsModel();

        $goods = $model->where('goods_id', $goods_id)->where('site_id', $this->site_id)->findOrEmpty();

        if ($goods->isEmpty()) {
            return fail('find_goods_empty');
        }

        $model->startTrans();

        try {

            $goods->total_cost            = $cost_data['total_cost'];
            $goods->initial_cost          = $cost_data['initial_cost'];
            $goods->additional_total_cost = $cost_data['additional_total_cost'];
            $goods->update_uid            = $this->uid;
            $goods->save();

            $cost_model = new SalerToolsGoodsCost();

            $cost_model->where('goods_id', $goods_id)->where('site_id', $this->site_id)->delete();

            // 现存的成本id
            foreach ($cost_data['goods_cost'] as $cost) {
                $cost['site_id']  = $this->site_id;
                $cost['goods_id'] = $goods->goods_id;
                $cost_model->create($cost);
            }


            $model->commit();

            return success();
        } catch (\Exception $e) {
            $model->rollback();
            return fail($e->getMessage());
        }

    }


    /**
     * 修改商品鉴定人
     */
    public function editAppraiser($data)
    {
        $goods_id      = $data['goods_id'];
        $appraiser_uid = $data['appraiser_uid'] ?? 0;


        $model = new GoodsModel();

        $goods = $model->where('goods_id', $goods_id)->where('site_id', $this->site_id)->findOrEmpty();

        if ($goods->isEmpty()) {
            return fail('find_goods_empty');
        }

        $goods->appraiser_uid = $appraiser_uid;
        $goods->update_uid    = $this->uid;

        $goods->save();

        return success();
    }


    /**
     * 修改商品位置
     */
    public function editWatchLocation($data)
    {
        $goods_id       = $data['goods_id'];
        $watch_location = $data['watch_location'] ?? '';

        $model = new GoodsModel();

        $goods = $model->where('goods_id', $goods_id)->where('site_id', $this->site_id)->findOrEmpty();

        if ($goods->isEmpty()) {
            return fail('find_goods_empty');
        }

        $goods->watch_location = $watch_location;
        $goods->update_uid     = $this->uid;

        $goods->save();

        return success();
    }


    /**
     * 获取存放统计
     */
    public function positionStatistics()
    {
        $list  = (new SiteDictService())->list('product_position');
        $model = new GoodsModel();

        $common_where = [
            ['site_id', '=', $this->site_id],
            ['deleted_time', '=', 0],
        ];

        $total_list = $model->where($common_where)
            ->field('sum(COALESCE(stock,0) + COALESCE(lock_num,0)) as goods_num,watch_location')
            ->group('watch_location')
            ->select()
            ->toArray();

        $total_list = array_column($total_list, 'goods_num', 'watch_location');

        foreach ($list as &$item) {
            $item['goods_num'] = $total_list[$item['value']] ?? 0;
        }

        // 往头部插入
        array_unshift($list, [
            'label'     => '',
            'value'     => '',
            'goods_num' => $total_list[''] ?? 0,
        ]);

        return success($list);

    }


    /**
     * 获取仓库统计情况
     */
    public function storeStatistics($data)
    {
        $model = new GoodsModel();

        $where = [
            ['site_id', '=', $this->site_id],
            ['deleted_time', '=', 0],
        ];

        $where_field = ['search', 'brand_id', 'category_id', 'appraiser_uid', 'create_uid', 'recycling_uid', 'store_id', 'watch_location', 'goods_tag'];
        $model       = $model->where($where)->withSearch($where_field, $data);
        $t1m         = clone $model;
        $t2m         = clone $model;
        $t3m         = clone $model;
        $t1          = $t1m->where($where)->withSearch($where_field, $data)
            ->field('count(goods_id) as goods_id,sum(stock) as stock,sum(peer_price) as peer_price,sum(total_cost) as total_cost')
            ->select()->toArray()[0];

        // 获取今日上架数量
        $today_goods_num = $t2m->whereBetween('create_time', [date('Y-m-d 00:00:00', strtotime('-1 day')), date('Y-m-d 23:59:59', strtotime('-1 day'))])
            ->count('stock');

        // 获取昨日上架数量
        $yest_goods_num = $t3m->whereBetween('create_time', [date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')])
            ->count('stock');

        return success([
            'yesterday_goods_num' => $yest_goods_num,
            'today_goods_num'     => $today_goods_num,
            'stock'               => $t1['stock'],
            'peer_price'          => $t1['peer_price'],
            'total_cost'          => $t1['total_cost'],
        ]);
    }


    public function del($goods_id)
    {
        $model = new GoodsModel();
        $goods = $model->where('goods_id', $goods_id)->where('site_id', $this->site_id)->findOrEmpty();

        if ($goods->isEmpty()) {
            return fail('find_goods_empty');
        }

        if ($goods->lock_num > 0) {
            // 锁单中不能删除
            return fail('goods_lock_num_not_del');
        }

        $goods->deleted_time = time();
        $goods->save();

        return success();
    }

    public function batchDel($goods_ids)
    {
        $model      = new GoodsModel();
        $goods_list = $model->where('goods_id', 'in', $goods_ids)->where('site_id', $this->site_id)->select();

        foreach ($goods_list as $goods) {
            $goods->deleted_time = time();
            $goods->save();
        }

        return success();

    }

    public function moveGoods($data)
    {
        $model = new GoodsModel();

        $goods_list = $model->where('site_id', $this->site_id)
            ->withSearch(['goods_id'], $data)
            ->select();

        if ($goods_list->isEmpty()) {
            return fail('find_goods_empty');
        }

        foreach ($goods_list as $goods) {
            $goods->store_id = $data['store_id'];
            $goods->save();
        }

        return success();

    }

}
