<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/23 2:21
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\bill;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\Bill as BillModel;
use addon\saler_tools\app\model\BillRecord;
use addon\saler_tools\app\model\Goods;

/**
 * 店铺对账单
 * Class ShopBillService
 * @package addon\saler_tools\app\service\bill
 */
class ShopBillService extends BaseAdminService
{


    public function lists($params)
    {
        $bill_model = new BillModel();

        $model = $bill_model->where('site_id', $this->site_id)->with(['createNames']);

        if (!empty($params['is_recycle'])) {
            $model = $model->onlyTrashed();
        }

        return success($this->pageQuery($model));
    }


    public function deleted($bill_id)
    {
        // 先查询账单信息，包括已删除的记录
        $bill = BillModel::withTrashed()
                        ->where('site_id', $this->site_id)
                        ->where('bill_id', $bill_id)
                        ->findOrEmpty();
        
        if ($bill->isEmpty()) {
            return fail('账单不存在');
        }
        
        // 判断是否已经进行过伪删除
        if ($bill->deleted_time == 0) {
            // 未进行过伪删除，执行伪删除
            $bill->deleted_time = time();
            $bill->save();
            return success('账单已删除');
        } else {
            // 已经进行过伪删除，直接从数据库删除
            // 使用原生SQL强制删除，绕过软删除机制
            $bill_model = new BillModel();
            \think\facade\Db::execute(
                "DELETE FROM {$bill_model->getTable()} WHERE site_id = ? AND bill_id = ?",
                [$this->site_id, $bill_id]
            );
            return success('账单已永久删除');
        }
    }


    public function detail($params)
    {

        $bill_record_model = new BillRecord();

        $where             = [
            ['site_id', '=', $this->site_id],
            ['bill_id', '=', $params['bill_id']],
            ['date_time', 'between', $params['date_time']],
        ];


        $bill = $bill_record_model->where($where)->order('record_id', 'desc')->with(['billNames'])->findOrEmpty();

        return success($bill->toArray());
    }


    public function create($data)
    {
        $bill_model                 = new BillModel();
        $data['init_user_money']    = $data['user_money'];
        $data['own_goods_money']    = $data['init_own_goods_money'];
        $data['pawned_goods_money'] = $data['init_pawned_goods_money'];
        $data['others_goods_money'] = $data['init_others_goods_money'];
        $data['site_id']            = $this->site_id;
        $data['uid']                = $this->uid;

        $bill_model->create($data);
        return success();
    }


    public function recordLists($params)
    {
        $bill_record_model = new BillRecord();
        $where             = [
            ['site_id', '=', $this->site_id],
            ['bill_id', '=', $params['bill_id']],
            ['date_time', 'between', $params['date_time']],
        ];

        $model = $bill_record_model->where($where)->order('record_id', 'asc');

        return success($this->pageQuery($model));
    }

    /**
     * 统计库存商品总金额
     * @desc 自有商品 其他商品 质押商品
     */
    public function queryStat()
    {
        $goods_model = new Goods();

        $where = [
            ['site_id', '=', $this->site_id]
        ];

        $own_goods    = $goods_model->where($where)->where('goods_attribute', 'own_goods')->field('sum((stock + lock_num) * total_cost)')->value('total_cost',0);
        $pawned_goods = $goods_model->where($where)->where('goods_attribute', 'pawned_goods')->field('sum((stock + lock_num) * total_cost)')->value('total_cost',0);
        $others       = $goods_model->where($where)->where('goods_attribute', 'others')->field('sum((stock + lock_num) * total_cost)')->value('total_cost',0);

        return success([
            'own_goods'    => $own_goods,
            'pawned_goods' => $pawned_goods,
            'others'       => $others,
        ]);
    }


}
