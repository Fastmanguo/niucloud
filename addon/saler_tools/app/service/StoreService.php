<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/12 19:01
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\SalerToolsStore as StoreModel;
use core\exception\AdminException;
use addon\saler_tools\app\model\Goods as GoodsModel;

/**
 *
 * Class StoreService
 * @package addon\saler_tools\app\service
 */
class StoreService extends BaseAdminService
{

    public function list($params)
    {
        $model = new StoreModel();

        $list = $model->withSearch(['store_name'], $params)
            ->with(['createBy'])
            ->where('site_id', $this->site_id)
            ->select()
            ->toArray();


        if ($params['is_stat']) {

            // 一次统计全部
            $goods_model = new GoodsModel();

            $goods_count = $goods_model->where('site_id', $this->site_id)
                ->group('store_id')
                ->field('store_id,count(goods_id) as goods_count')
                ->select()->toArray();

            $goods_count = array_column($goods_count, null, 'store_id');

            $sale_count = $goods_model->where('site_id', $this->site_id)
                ->where('is_sale', 1)
                ->group('store_id')
                ->field('store_id,count(goods_id) as sale_count')
                ->select()->toArray();

            $sale_count = array_column($sale_count, null, 'store_id');

            $stat_data = array_merge2($goods_count, $sale_count);

            // 执行仓库统计
            foreach ($list as $key => &$item) {
                $item['goods_count'] = $stat_data[$item['store_id']]['goods_count'] ?? 0;
                $item['sale_count']  = $stat_data[$item['store_id']]['sale_count'] ?? 0;
            }

        }

        return success($list);
    }


    public function detail($store_id)
    {
        $model = new StoreModel();
        $store = $model->where('store_id', $store_id)->where('site_id', $this->site_id)->findOrEmpty();
        if ($store->isEmpty()) throw new AdminException('find_store_empty');
        return success($store->toArray());
    }

    public function add($data)
    {

        $model              = new StoreModel();
        $data['site_id']    = $this->site_id;
        $data['create_uid'] = $this->uid;

        $model->save($data);

        return success();
    }

    public function edit($data)
    {
        $model = new StoreModel();

        $store = $model->where('store_id', $data['store_id'])->where('site_id', $this->site_id)->findOrEmpty();

        if ($store->isEmpty()) throw new AdminException('find_store_empty');

        return success();
    }


    public function delete($data)
    {
        $model = new StoreModel();

        $store = $model->where('store_id', $data['store_id'])->where('site_id', $this->site_id)->findOrEmpty();

        if ($store->isEmpty()) throw new AdminException('find_store_empty');

        $store->delete();

        return success();
    }


}
