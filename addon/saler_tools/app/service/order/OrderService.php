<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/21 3:16
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\order;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\Order as OrderModel;
use addon\saler_tools\app\model\Goods as GoodsModel;
use addon\saler_tools\app\service\diy\dict\GoodsDict;
use addon\saler_tools\app\service\goods\GoodsLogService;
use addon\saler_tools\app\service\shop\ShopService;
use app\model\sys\SysUser;
use core\exception\AdminException;
use think\Response;

/**
 * 订单服务
 * Class OrderService
 * @package addon\saler_tools\app\service\order
 */
class OrderService extends BaseAdminService
{

    /************   订单状态   ***************/

    /** @var string 退货 */
    const RETURN_ORDER = 'RETURN_ORDER';

    /** @var string 锁单 */
    const LOCK_ORDER = 'LOCK_ORDER';

    /** @var string 开单 */
    const ADD_ORDER = 'ADD_ORDER';

    /** @var string 订单完成 */
    const FINISH_ORDER = 'FINISH_ORDER';

    /** @var string 订单已取消 */
    const CANCEL_ORDER = 'CANCEL_ORDER';


    public function __construct()
    {
        parent::__construct();
    }

    public function lists($params, $order = [])
    {
        $order_model = new OrderModel();

        $where = [
            ['site_id', '=', $this->site_id]
        ];

        if (isset($params['is_delivery'])) {
            if ($params['is_delivery'] == 1) {
                $where[] = ['is_delivery', '=', 1];
            } else {
                $where[] = ['lock_time', '=', null];
                $where[] = ['is_delivery', '=', 0];
            }
        }

        if (!empty($params['order_status'])) {
            if (is_array($params['order_status'])){
                $where[] = ['order_status', 'in', $params['order_status']];
            }elseif (is_string($params['order_status'])){
                $where[] = ['order_status', '=', $params['order_status']];
            }
        } else {
            $where[] = ['order_status', 'in', [self::ADD_ORDER, self::FINISH_ORDER, self::CANCEL_ORDER, self::RETURN_ORDER]];
        }


        $model = $order_model->where($where)
            ->withSearch(['order_no', 'search', 'order_id', 'is_paid', 'transaction_time'], $params)
            ->with(['createName', 'lockName'])
            ->order($order);

        if (!empty($params['end_time'])) {
            $model->where('transaction_time', '<=', $params['end_time']);
        }

        if (!empty($params['start_time'])) {
            $model->where('transaction_time', '>=', $params['start_time']);
        }

        if (isset($params['type'])) {
            $model->where('type', $params['type']);
        }

        $result = $this->pageQuery($model);

        $sale_uid = [];

        foreach ($result['data'] as $item) {
            if (empty($item['sale_uids'])) continue;
            $sale_uid = array_merge($sale_uid, $item['sale_uids']);
        }

        $user_list = (new SysUser())->whereIn('uid', $sale_uid)->field('uid,real_name')->select()->toArray();

        $user_list = array_column($user_list, 'real_name', 'uid');

        foreach ($result['data'] as &$item) {
            $item['sale_name'] = implode(';', array_intersect_key($user_list, array_flip($item['sale_uids'])));
        }

        return success($result);

    }

    /**
     * 获取订单详情
     */
    public function detail($data)
    {
        $order_id = $data['order_id'];

        $order_model = new OrderModel();

        $order = $order_model->where('site_id', $this->site_id)
            ->where('order_id', $order_id)
            ->with(['createName'])
            ->findOrEmpty();

        if ($order->isEmpty()) throw new AdminException('find_note_order');

        $order = $order->toArray();

        $sale_uid = $order['sale_uids'];
        // 翻译销售人
        $real_name = (new SysUser())->whereIn('uid', $sale_uid)->column('real_name');

        $order['sale_name'] = implode(';', $real_name);

        // 检查是否有修改需求
        if (isset($data['option']) && $data['option'] == 'open_order') {
            $order['order_status'] = self::ADD_ORDER;
        }

        return success($order);

    }


    /**
     * 订单编辑
     */
    public function edit($data)
    {
        $order_model = new OrderModel();

        $order_id = $data['order_id'];

        $order = $order_model->where('site_id', $this->site_id)->where('order_id', $order_id)
            ->with(['createName'])
            ->findOrEmpty();

        if ($order->isEmpty()) {
            return fail('find_note_order');
        }

        if (!in_array($order->order_status, [self::ADD_ORDER, self::LOCK_ORDER])) return fail('order_status_error');
        if (!in_array($order['order_status'], [self::ADD_ORDER, self::LOCK_ORDER])) return fail('order_status_error');


        $order_model->startTrans();
        try {

            // 若订单从锁单改为开单，则去掉锁单库存增加销量
            if ($order->order_status == self::LOCK_ORDER && $data['order_status'] == self::ADD_ORDER) {

                $order->lock_time = null;

                $goods_model = new GoodsModel();
                $goods       = $goods_model->where('site_id', $this->site_id)
                    ->where('goods_id', $order['goods_id'])
                    ->withTrashed()->lock(true)
                    ->findOrEmpty();

                if (!$goods->isEmpty()) {
                    $goods->sale_num = bcadd($goods->sale_num, $order->goods_num);// 增加销量
                    $goods->lock_num = bcsub($goods->lock_num, $order->goods_num);// 减去锁单数量

                    // 库存和锁单数量都小于0则删除商品
                    if ($goods->stock <= 0 && $goods->lock_num <= 0) {
                        $goods->deleted_time = time();
                    }
                    $goods->save();
                }

            }

            $order->save($data);

            $order_model->commit();

            return success();
        } catch (\Exception $e) {
            $order_model->rollback();
            return fail($e->getMessage());
        }

    }


    /**
     * 创建订单
     * @param $data
     * @return Response
     */
    public function add($data)
    {
        $goods_model = (new GoodsModel());

        $goods_id = $data['goods_id'] ?? 0;

        // 如果存在商品先校验商品
        if (!empty($data['goods_id'])) {

            $goods = $goods_model->where('goods_id', $goods_id)
                ->where('site_id', $this->site_id)
                ->findOrEmpty()->toArray();

            if (empty($goods)) {
                return fail('find_note_goods');
            }

            if ($goods['stock'] <= 0) {
                return fail('goods_no_stock');
            }

            if ($goods['stock'] < $data['goods_num']) return fail('goods_no_stock');

            if ($goods['is_sale'] != 1) return fail('goods_no_sale');

            // 埋入商品数据到订单
            $data['recycling_uid']     = $goods['recycling_uid'];
            $data['appraiser_uid']     = $goods['appraiser_uid'];
            $data['recycling_time']    = $goods['recycling_time'];
            $data['contact_entrusted'] = $goods['contact_entrusted'];

        }

        $order_model = new OrderModel();

        $order_model->startTrans();
        try {
            if (!empty($goods_id)) {
                $goods_model->where('goods_id', $goods_id)->lock(true)->findOrEmpty();

                $goods_model->where('goods_id', $goods_id)->setDec('stock', $data['goods_num']);
                $goods_model->where('goods_id', $goods_id)->setInc('sale_num', $data['goods_num']);

                // 如果已经卖完则下架删除
                $goods_model->where([
                    ['stock', '<=', 0],
                    ['goods_id', '=', $goods_id],
                    ['lock_num', '<=', 0]
                ])->update([
                    'deleted_time' => time(),
                ]);
            }

            $data['site_id']      = $this->site_id;
            $data['order_no']     = create_no();
            $data['create_uid']   = $this->uid;
            $data['order_status'] = self::ADD_ORDER;
            $data['create_time']  = date('Y-m-d H:i:s');
            $data['is_delivery']  = 0;// 调整待发货
            $data['order_type']   = 'sale';

            // 填写店铺货币类型
            $shop                  = (new ShopService())->info();
            $data['currency_code'] = $shop['currency_code'];

            $res = $order_model->save($data);

            if ($res === false) throw new AdminException();

            $order_model->commit();

            return success([
                'order_id' => $order_model->order_id
            ]);
        } catch (\Exception $e) {
            $order_model->rollback();
            return fail($e->getMessage());
        }

    }


    public function preAdd($params)
    {

        $goods_id = $params['goods_id'];

        $goods_model = (new GoodsModel());
        $goods       = $goods_model->where('goods_id', $goods_id)
            ->with(['goodsCost'])
            ->where('site_id', $this->site_id)
            ->findOrEmpty();

        if ($goods->isEmpty()) {
            return fail('find_note_goods');
        }

        if ($goods['stock'] <= 0) {
            return fail('goods_no_stock');
        }

        if ($goods['is_sale'] != 1) return fail('goods_no_sale');

        $goods = $goods->toArray();

        $data = [
            'goods_id'              => $goods_id,
            'goods_name'            => $goods['goods_name'],
            'goods_image'           => $goods['goods_image'],
            'goods_cover'           => $goods['goods_cover'],
            'price'                 => $goods['price'],
            'peer_price'            => $goods['peer_price'],
            'agent_price'           => $goods['agent_price'],
            'guide_price'           => $goods['guide_price'],
            'goods_attribute'       => $goods['goods_attribute'],
            'original_total_cost'   => $goods['total_cost'],
            'total_cost'            => $goods['total_cost'],
            'initial_cost'          => $goods['initial_cost'],
            'additional_total_cost' => $goods['additional_total_cost'],
            'recycling_time'        => $goods['recycling_time'],
            'payment_receipt'       => [],
            'additional_cost'       => array_map(function ($item) {
                return [
                    'cost_name' => $item['cost_name'],
                    'money'     => $item['money'],
                    'images'    => $item['images'],
                ];
            }, $goods['goodsCost'] ?? [])
        ];

        return success($data);

    }


    public function lock($data)
    {
        $goods_id = $data['goods_id'];

        $goods_model = (new GoodsModel());
        $goods       = $goods_model->where('goods_id', $goods_id)
            ->with(['goodsCost'])
            ->where('site_id', $this->site_id)
            ->findOrEmpty()->toArray();

        if (empty($goods)) {
            return fail('find_note_goods');
        }

        if ($goods['stock'] <= 0) {
            return fail('goods_no_stock');
        }

        if ($goods['stock'] < $data['goods_num']) return fail('goods_no_stock');

        if ($goods['is_sale'] != 1) return fail('goods_no_sale');

        $order_model = new OrderModel();

        $order_model->startTrans();

        try {

            $goods_model->where('goods_id', $goods_id)->lock(true)->findOrEmpty();

            $goods_model->where('goods_id', $goods_id)->setDec('stock', $data['goods_num']);

            $goods_model->where('goods_id', $goods_id)->setInc('lock_num', $data['goods_num']);

            $goods_model->where('goods_id', $goods_id)->update([
                'is_locked' => 1
            ]);

            $data = [
                'order_status'          => self::LOCK_ORDER,
                'order_no'              => create_no(),
                'site_id'               => $this->site_id,
                'goods_id'              => $goods_id,
                'goods_cover'           => $goods['goods_cover'],
                'goods_name'            => $goods['goods_name'],
                'goods_image'           => $goods['goods_image'],
                'goods_price'           => $goods['price'],
                'total_cost'            => $goods['total_cost'],
                'initial_cost'          => $goods['initial_cost'],
                'additional_total_cost' => $goods['additional_total_cost'],
                'recycling_uid'         => $goods['recycling_uid'],
                'appraiser_uid'         => $goods['appraiser_uid'],
                'contact_entrusted'     => $goods['contact_entrusted'],
                'additional_cost'       => array_map(function ($item) {
                    return [
                        'cost_name' => $item['cost_name'],
                        'money'     => $item['money'],
                        'images'    => $item['images'],
                    ];
                }, $goods['goodsCost'] ?? []),

                'goods_num'           => $data['goods_num'],
                'deposit'             => $data['deposit'],
                'exp_trans_price'     => $data['exp_trans_price'],
                'lock_uid'            => $this->uid,
                'lock_remark'         => $data['lock_remark'] ?? '',
                'lock_receipt'        => $data['lock_receipt'] ?? [],
                'create_uid'          => $this->uid,
                'sale_uids'           => $data['sale_uids'] ?? [],
                'lock_time'           => date('Y-m-d H:i:s'),
                'create_time'         => date('Y-m-d H:i:s'),
                'after_sales_service' => [],
            ];

            GoodsLogService::setLog($this->site_id, $goods_id, $data['goods_num'], GoodsDict::LOCK);

            $order_model->create($data);

            $order_model->commit();
            return success();
        } catch (\Exception $e) {
            $order_model->rollback();
            return fail($e->getMessage());
        }

    }

    /**
     * 取消锁单
     * @param $data
     * @return Response
     */
    public function unLock($data)
    {

        $order_model = new OrderModel();
        $order       = $order_model->where('order_id', $data['order_id'])->where('site_id', $this->site_id)->findOrEmpty();

        if ($order->isEmpty()) {
            return fail('find_note_order');
        }

        if ($order['order_status'] != self::LOCK_ORDER) {
            return fail('order_status_error');
        }

        $order_model->startTrans();

        try {

            $order->lock_remark = (empty($order->lock_remark) ? '' : $order->lock_remark . ';') . $data['un_lock_remark'];

            $order->order_status = self::CANCEL_ORDER;

            $order->deleted_time = time();

            $order->save();

            if (!empty($order->goods_id)) {
                $goods_model = new GoodsModel();

                $goods = $goods_model->where('goods_id', $order['goods_id'])
                    ->where('site_id', $this->site_id)
                    ->withTrashed()
                    ->lock(true)
                    ->findOrEmpty();

                if (!$goods->isEmpty()) {

                    $goods->lock_num = bcsub($goods->lock_num, $order['goods_num']);
                    $goods->stock    = bcadd($goods->stock, $order['goods_num']);

                    if ($goods->lock_num <= 0) {
                        $goods->is_locked = 0;
                    }

                    // 先恢复数据
                    if ($goods->stock >= 0 && $goods->deleted_time != 0) {
                        $goods->restore();
                    }

                    $goods->save();

                    GoodsLogService::setLog($this->site_id, $goods->goods_id, $order['goods_num'], GoodsDict::UNLOCK);

                }
            }


            $order_model->commit();
            return success();
        } catch (\Exception $e) {
            $order_model->rollback();
            throw $e;
            return fail($e->getMessage());
        }

    }


    /**
     * 取消订单并退款
     * @param $data
     */
    public function close($data)
    {
        $order_id     = $data['order_id'];
        $close_remark = $data['close_remark'];

        $model = new OrderModel();
        $order = $model->where('order_id', $order_id)->where('site_id', $this->site_id)->findOrEmpty();

        if ($order->isEmpty()) return fail('find_note_order');

        // 订单只能在已退货/开单/锁单中取消
        if (!in_array($order['order_status'], [self::RETURN_ORDER, self::ADD_ORDER, self::LOCK_ORDER, self::FINISH_ORDER])) return fail('order_status_error');

        $model->startTrans();

        try {

            if ($order->is_paid == 1) {
                $order->is_paid      = -1;
                $order->returnee_uid = $this->uid;
            }

            if ($order->is_delivery == 1) {
                $order->is_delivery  = -1;
                $order->returnee_uid = $this->uid;
            }

            if (!empty($order->goods_id)) {

                // 退还商品库存
                $goods_model = new GoodsModel();

                $goods = $goods_model->where('goods_id', $order['goods_id'])
                    ->where('site_id', $this->site_id)
                    ->withTrashed()
                    ->lock(true)
                    ->findOrEmpty();

                if (!$goods->isEmpty()) {

                    $goods->stock = bcadd($goods->stock, $order['goods_num']);

                    // 锁单去除锁单数量
                    if ($order['order_status'] == self::LOCK_ORDER) {
                        $goods->lock_num = bcsub($goods->lock_num, $order['goods_num']);
                    } else {
                        $goods->sale_num = bcsub($goods->sale_num,$order['goods_num']);
                    }

                    if ($goods->lock_num <= 0) {
                        $goods->is_locked = 0;
                    }

                    // 先恢复再修改
                    if ($goods->stock > 0 && $goods->deleted_time != 0) { // 商品库存大于0时恢复
                        $goods->restore();
                    }

                    $goods->save();


                }

            }

            $order->order_status = self::CANCEL_ORDER;
            $order->close_remark = $close_remark;

            $order->save();

            $model->commit();

            return success();
        } catch (\Exception $e) {
            $model->rollback();
            return fail($e->getMessage());
        }
    }


    /**
     * 订单结款
     */
    public function paid($data)
    {
        $model = new OrderModel();
        $order = $model->where('order_id', $data['order_id'])->where('site_id', $this->site_id)->findOrEmpty();
        if ($order->isEmpty()) return fail('find_note_order');

        if ($order['is_paid'] == 1) return fail('order_is_paid');

        $data['is_paid']   = 1;
        $data['paid_uid']  = $this->uid;
        $data['paid_time'] = date('Y-m-d H:i:s');

        $order->allowField(['paid_receipt', 'paid_remark', 'paid_time', 'paid_uid', 'is_paid'])
            ->save($data);

        return success();
    }

    /**
     * 退款
     */
    public function refund($data)
    {
        $model = new OrderModel();
        $order = $model->where('order_id', $data['order_id'])->where('site_id', $this->site_id)->findOrEmpty();
        if ($order->isEmpty()) return fail('find_note_order');
        if ($order['is_paid'] != 1) return fail('order_is_refund');

        $order->save([
            'is_paid'    => -1,
            'refund_uid' => $this->uid
        ]);

        $this->autoComplete($order->order_id);

        return success();
    }


    /**
     * 退货
     */
    public function returnGoods($data)
    {
        $model = new OrderModel();

        $order = $model->where('order_id', $data['order_id'])->where('site_id', $this->site_id)->findOrEmpty();

        if ($order->isEmpty()) return fail('find_note_order');

        if ($order['is_delivery'] != 1) return fail('order_is_returneed');

        $order->save([
            'is_delivery'  => -1,
            'returnee_uid' => $this->uid
        ]);

        $this->autoComplete($order->order_id);

        return success();
    }

    /**
     * 订单状态自动完成
     */
    public function autoComplete($order_id)
    {
        $model = new OrderModel();
        $order = $model->where('order_id', $order_id)->where('site_id', $this->site_id)->findOrEmpty();
        if (!$order->isEmpty()) {
            if ($order['is_paid'] == 1 && $order['is_delivery'] == 1 && $order->order_status != self::FINISH_ORDER) {
                $order->order_status = self::FINISH_ORDER;
                $order->finish_time  = date('Y-m-d H:i:s');
                $order->save();
            } elseif ($order['is_delivery'] == -1) { // 退款操作时
                $order->order_status = self::RETURN_ORDER;
                $order->finish_time  = null;
                $order->save();
            }
        }
    }


    /**
     * 锁单统计
     */
    public function lockStat($query)
    {
        $model = new OrderModel();

        // 统计锁单
        $model = $model->where('site_id', $this->site_id)
            ->where('order_status', self::LOCK_ORDER)
            ->withSearch(['order_no', 'order_status', 'search', 'order_id', 'is_paid', 'transaction_time'], $query);


        if (!empty($params['end_time'])) {
            $model->where('transaction_time', '<=', $params['end_time']);
        }

        if (!empty($params['start_time'])) {
            $model->where('transaction_time', '>=', $params['start_time']);
        }

        $model = $model->field('sum(goods_num) as goods_num,sum(goods_price) as goods_price,sum(deposit) as deposit,sum(total_cost) as total_cost,' .
            'sum(initial_cost) as initial_cost,sum(additional_total_cost) as additional_total_cost,sum(exp_trans_price) as exp_trans_price')
            ->group('order_id')
            ->findOrEmpty();

        return success($model->toArray());
    }


    /**
     * 订单发货
     */
    public function send($data)
    {
        $model = new OrderModel();
        $order = $model->where('order_id', $data['order_id'])->where('site_id', $this->site_id)->findOrEmpty();
        if ($order->isEmpty()) return fail('find_note_order');


        if (!in_array($order['order_status'], [self::ADD_ORDER, self::FINISH_ORDER])) {
            return fail('order_status_error');
        }


        if ($order['is_delivery'] == 1) {
            return fail('order_is_send');
        }

        $update = [
            'is_delivery'     => 1,
            'delivery_uid'    => $this->uid,
            'delivery_remark' => $data['delivery_remark'] ?? '',
            'delivery_time'   => date('Y-m-d H:i:s'),
        ];

        $order->save($update);

        return success();
    }


    public function deleted($data)
    {
        $order_model = new OrderModel();
        $goods_model = new GoodsModel();

        $order_id = $data['order_id'];

        if (is_array($order_id)) {
            $model = $order_model->where('order_id', 'in', $order_id)->where('site_id', $this->site_id);
        } else {
            $model = $order_model->where('order_id', $order_id)->where('site_id', $this->site_id);
        }

        $list = $model->select();

        $order_model->startTrans();
        try {
            foreach ($list as $item) {
                // 直接设置deleted_time字段，避免自动时间戳的问题
                $item->deleted_time = time();
                $item->save();
            }
            $order_model->commit();
            return success();
        } catch (\Exception $e) {
            $order_model->rollback();
            return fail($e->getMessage());
        }
    }


    /**
     * 赎回商品
     */
    public function ransom($data)
    {

        $order_model = new OrderModel();
        $goods_model = new GoodsModel();

        $goods = $goods_model->where('goods_id', $data['goods_id'])->where('site_id', $this->site_id)->findOrEmpty();

        if ($goods->isEmpty()) {
            return fail('find_note_goods');
        }

        try {
            $order_model->startTrans();

            $goods = $goods_model->where('goods_id', $goods->goods_id)->lock(true)->findOrEmpty();

            if ($goods->isEmpty() || $goods->goods_attribute != 'pawned_goods' || $goods->stock <= 0 || $goods->is_sale == 0) return fail();

            // 商品转换成订单数据
            $order_model->create([
                'order_no'        => create_no(),
                'site_id'         => $this->site_id,
                'goods_cover'     => $goods->goods_cover,
                'goods_name'      => $goods->goods_name,
                'goods_id'        => $goods->goods_id,
                'goods_image'     => $goods->goods_image,
                'goods_num'       => $goods->goods_num,
                'goods_price'     => $goods->goods_price,
                'goods_code'      => $goods->goods_code,
                'money'           => $data['money'],
                'payment_receipt' => [],
                'deposit'         => 0,
                'order_status'    => self::FINISH_ORDER,
                'exp_trans_price' => 0,
                'create_uid'      => $this->uid,
                'sale_uids'       => [$this->uid],
                'recycling_uid'   => $goods->recycling_uid,
                'recycling_time'  => $goods->recycling_time,
                'is_paid'         => 1,
                'is_delivery'     => null,
                'create_time'     => date('Y-m-d H:i:s'),
                'finish_time'     => date('Y-m-d H:i:s'),
                'type'            => 'pledged',
            ]);

            $goods->is_sale  = 0;
            $goods->sale_num = $goods->stock;
            $goods->stock    = 0;
            $goods->save();

            $order_model->commit();

            return success();
        } catch (\Exception $e) {
            $order_model->rollback();
            return fail();
        }


    }


}
