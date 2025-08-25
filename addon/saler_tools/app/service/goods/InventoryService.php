<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/25 7:38
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\goods;

use addon\saler_tools\app\common\BaseAdminService;

use addon\saler_tools\app\model\Inventory as InventoryModel;
use addon\saler_tools\app\model\InventoryGoods as InventoryGoodsModel;
use addon\saler_tools\app\model\Goods as GoodsModel;
use app\model\sys\SysUser;
use think\facade\Db;

/**
 * 商品盘点相关
 * Class InventoryService
 * @package addon\saler_tools\app\service\goods
 */
class InventoryService extends BaseAdminService
{

    public function lists($params)
    {

        $inventory_model = new InventoryModel();

        $model = $inventory_model->where('site_id', $this->site_id)
            ->withSearch(['inventory_status'], $params)
            ->with(['createNames']);

        $user_model = new SysUser();
        $result     = $this->pageQuery($model, function ($item) use ($user_model) {
            $item['inventory_user'] = $user_model->where('uid', 'in', $item['inventory_uids'])->field('uid,real_name')->select();
        });

        return success($result);

    }


    public function create($data)
    {

        $goods_model = new GoodsModel();

        $goods_model = $goods_model->where('site_id', $this->site_id);

        switch ($data['inventory_type']) {
            case 'store': // 仓库盘点
                if (empty($data['goods_attributes'])) return fail('goods_attributes_not_empty');
                $goods_model = $goods_model->where('goods_attribute', 'in', $data['goods_attributes']);
                if (!empty($data['category_ids'])) {
                    $goods_model = $goods_model->where('category_id', 'in', $data['category_ids']);
                }
                break;
            case 'temp_store': // 临时仓盘点
                if (empty($data['store_id'])) return fail('store_id_not_empty');
                $goods_model = $goods_model->where('store_id', $data['store_id']);
                break;
            case 'pawned_goods': // 质押盘点
                if (empty($data['category_ids'])) return fail('category_ids_not_empty');
                $goods_model = $goods_model->where('category_id', 'in', $data['category_ids']);
                $goods_model = $goods_model->where('is_sale', 1)->where('goods_attribute', 'pawned_goods');
                break;
            case 'watch_location': // 位置盘点
                $goods_model = $goods_model->where('watch_location', $data['watch_location'] ?? '');
                break;
            default:
                return fail('type_error');
        }


        $data['uid']              = $this->uid;
        $data['site_id']          = $this->site_id;
        $data['inventory_status'] = 0;

        $inventory_model       = new InventoryModel();
        $inventory_goods_model = new InventoryGoodsModel();
        $should_goods_num      = $goods_model->count();

//        if ($should_goods_num == 0) return fail('goods_not_found');

        try {
            $inventory_model->startTrans();

            $data['should_goods_num'] = $should_goods_num;

            $inventory = $inventory_model->create($data);

            $inventory_id = $inventory->inventory_id;

            $goods_model->chunk(100, function ($goods_list) use ($inventory_id, $inventory_goods_model) {
                foreach ($goods_list as $goods) {
                    $inventory_goods_model->create([
                        'inventory_id' => $inventory_id,
                        'goods_id'     => $goods['goods_id'],
                        'site_id'      => $this->site_id,
                        'goods_num'    => bcadd($goods['stock'], $goods['lock_num']),
                        'exist_num'    => 0,
                        'lose_num'     => 0,
                        'status'       => 0,
                        'remark'       => '',
                    ]);
                }
            });


            $inventory_model->commit();

            return success(['inventory_id' => $inventory_id]);

        } catch (\Exception $e) {
            $inventory_model->rollback();
            return fail();
        }

    }


    public function detail($inventory_id)
    {
        $inventory_model = new InventoryModel();
        $inventory       = $inventory_model->where('site_id', $this->site_id)->where('inventory_id', $inventory_id)->findOrEmpty();
        if ($inventory->isEmpty()) return fail('not_found');
        return success($inventory->toArray());
    }

    public function save($inventory_id)
    {
        $inventory_model = new InventoryModel();

        $inventory = $inventory_model->where('site_id', $this->site_id)->where('inventory_id', $inventory_id)->findOrEmpty();

        if ($inventory->isEmpty()) return fail('not_found');

        if ($inventory->inventory_status != 0) return fail('status_error');

        $inventory_model->startTrans();

        try {

            $inventory_goods_model = new InventoryGoodsModel();

            $goods_list = $inventory_goods_model->where('inventory_id', $inventory_id)
                ->where('site_id', $this->site_id)
                ->where('status', 'in', [-1, 1])
                ->select();

            $goods_model = new GoodsModel();

            foreach ($goods_list as $goods) {
                if ($goods['change_num'] == 0) continue;

                $res = Db::execute('update ' . $goods_model->getName() . ' set stock = stock + :change_num where goods_id = :goods_id and site_id = :site_id'
                    , [
                        'change_num' => $goods['change_num'],
                        'goods_id'   => $goods['goods_id'],
                        'site_id'    => $this->site_id,
                    ]);

                if ($res === false) throw new \Exception('update_goods_error');

            }

            $inventory->inventory_status = 1;

            $inventory->save();

            $inventory_model->commit();

            return success();

        } catch (\Exception $e) {
            $inventory_model->rollback();
            return fail();
        }
    }


    public function del($inventory_id)
    {
        $inventory_model = new InventoryModel();

        $inventory = $inventory_model->where('site_id', $this->site_id)->where('inventory_id', $inventory_id)->findOrEmpty();

        if ($inventory->isEmpty()) return fail('not_found');

        $inventory->save(['deleted_time' => time()]);

        return success();
    }


    public function inventoryGoodsLists($params)
    {
        $inventory_goods_model = new InventoryGoodsModel();

        $model = $inventory_goods_model->where($inventory_goods_model->getName() . '.site_id', $this->site_id)
            ->with(['goods'])
            ->withSearch(['status', 'inventory_id'], $params);

        if (!empty($params['category_id'])) {
            $model = $model->hasWhere('goods', [['category_id', '=', $params['category_id']]]);
        }

        return success($this->pageQuery($model));
    }


    public function inventoryModifyGoods($data)
    {
        $inventory_goods_model = new InventoryGoodsModel();

        $goods = $inventory_goods_model->where('site_id', $this->site_id)
            ->where('inventory_id', $data['inventory_id'])
            ->where('goods_id', $data['goods_id'])
            ->findOrEmpty();

        if ($goods->isEmpty()) return fail();

        $goods->status = $data['status'];

        if ($data['status'] == 1) {
            $goods->change_num = 0;
            $goods->lose_num   = 0;
            $goods->remark     = '';
            $goods->exist_num  = $goods->goods_num;
        } else {
            if ($goods->goods_num < $data['lose_num']) return fail();
            $goods->change_num = -abs($data['lose_num']);
            $goods->exist_num  = bcsub($goods->goods_num, $data['lose_num']);
            $goods->lose_num   = $data['lose_num'];
            $goods->remark     = $data['remark'];
        }

        $goods->save();

        return success();

    }


}
