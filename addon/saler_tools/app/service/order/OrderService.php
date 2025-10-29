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
use think\facade\Db;
use app\model\sys\SysUser;
use core\exception\AdminException;
use think\Response;
use app\model\member\CustomerModel;
use Kkokk\Poster\Facades\Facade;

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

    public function goodsOrderCount($site_id,$uid)
    {
        $order_model = new OrderModel();
        // 基础查询条件
        $where = [['site_id', '=', $site_id],['create_uid', '=', $uid]];
        // 统计总条数
        $total_count = $order_model->where($where)->count();
        $shipment = $order_model->where($where)->where('order_status', 'ADD_ORDER')->count();
        $cancel = $order_model->where($where)->where('order_status', 'CANCEL_ORDER')->count();
        $result = [
            'count' => $total_count+$shipment+$cancel,
            'total_count' => $total_count,
            'shipment_count' => $shipment,
            'cancel_count' => $cancel
        ];
        return success($result);
    }

    public function lists($params, $order = [],$type = 0,$site_id = 0)
    {
        $order_model = new OrderModel();

        if($site_id != 0 ){
            $where = [['site_id', '=', $site_id]];
        }else{
            $where = [['site_id', '=', $this->site_id]];
        }

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
            $where[] = ['order_status', 'in', [self::ADD_ORDER, self::FINISH_ORDER, self::CANCEL_ORDER, self::RETURN_ORDER,self::LOCK_ORDER]];
        }


        $model = $order_model->where($where)
            ->withSearch(['order_no', 'search', 'order_id', 'is_paid', 'transaction_time'], $params)
            ->with(['createName', 'lockName'])
            ->order($order);

        // 入参 is_paid=0 时，排除已取消订单
        if (isset($params['is_paid']) && $params['is_paid'] !== '' && strval($params['is_paid']) === '0') {
            $model->where('order_status', '<>', self::CANCEL_ORDER);
        }

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

        if($type == 1){
            return $result['total'];
        }else{
            return success($result);
        }
        

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
        
        if(!empty($order['goods_attr_list'])){
            $order['goods_attr_list'] = json_decode($order['goods_attr_list'], true);
        }

        if(!empty($order['payment_receipt'])){
            $order['payment_receipt'] = json_decode($order['payment_receipt'], true);
        }

        if($order['customer_id']){
            $customer = new CustomerModel();
            $data_info = $customer->where('id', '=', $order['customer_id'])->find()->toArray();
            $order['customer_name'] = $data_info['customer_name'];
        }else{
            $order['customer_name'] = '';
        }

        // 若 goods_attribute 未包含或为空，则强制从数据库读取并补充
        if (!isset($order['goods_attribute']) || $order['goods_attribute'] === '' || $order['goods_attribute'] === null) {
            $order['goods_attribute'] = (new GoodsModel())
                ->where('goods_id', $order['goods_id'])
                ->value('goods_attribute', '');
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

                    // 记录商品操作日志 - 锁单转开单
                    // GoodsLogService::setLog($this->site_id, $goods->goods_id, $order->goods_num, GoodsDict::CREATE_ORDER, [
                    //     'order_id' => $data['order_id'] ?? '',
                    //     'order_no' => $order->order_no ?? ''
                    // ], $this->uid);

                    // 库存和锁单数量都小于0则删除商品
                    if ($goods->stock <= 0 && $goods->lock_num <= 0) {
                        $goods->deleted_time = time();
                    }
                    $goods->save();
                }

            }

            // 处理并保存指定的四个参数
            if (!empty($data['goods_attr_list'])) {
                $data['goods_attr_list'] = json_encode($data['goods_attr_list'], JSON_UNESCAPED_UNICODE);
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
            
            //计算所有规格的开单总数量
            $billing_goods_num = [0];
            if (!empty($data['goods_attr_list'])) {
                foreach ($data['goods_attr_list'] as $key=>$val) {
                    if(isset($val['billing_goods_num']) && $val['billing_goods_num']){
                        $billing_goods_num[] = $val['billing_goods_num'];
                    }
                }
            }
            $billing_goods_num = array_sum($billing_goods_num);
            $data['goods_num'] = $billing_goods_num;

            if ($goods['stock'] < $billing_goods_num) return fail('goods_no_stock');

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

                $goods_model->where('goods_id', $goods_id)->setDec('stock', $billing_goods_num);
                $goods_model->where('goods_id', $goods_id)->setInc('sale_num', $billing_goods_num);

                // 记录商品操作日志 - 开单销售
                GoodsLogService::setLog($this->site_id, $goods_id, $billing_goods_num, GoodsDict::CREATE_ORDER, [
                    'money' => $data['money'] ?? '',
                    'goods_num' => $data['goods_num'] ?? '',
                ], $this->uid);

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
            $data['transaction_time']  = date('Y-m-d');
            $data['is_delivery']  = 0;// 调整待发货
            // $data['order_type']   = 'sale';

            // 填写店铺货币类型
            $shop                  = (new ShopService())->info();
            $data['currency_code'] = $shop['currency_code'];

            // 处理并保存指定的四个参数
            if (!empty($data['goods_attr_list'])) {
                $data['goods_attr_list'] = json_encode($data['goods_attr_list'], JSON_UNESCAPED_UNICODE);
            }

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

        if (intval($goods['stock']) <= 0) {
            return fail('goods_no_stock');
        }

        if ($goods['is_sale'] != 1) return fail('goods_no_sale');

        $goods = $goods->toArray();
        $goods_attr_list = json_decode($goods['goods_attr_list'], true);
        foreach($goods_attr_list as $key => $item){
            $goods_attr_list[$key]['billing_goods_num'] = "";
            $goods_attr_list[$key]['money'] = "";
            $goods_attr_list[$key]['status'] = False;
        }
        $goods_attr_list[0]['status'] = True;

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
            "category_id"           => $goods['category_id'],
            "customer_id"           => $goods['customer_id'],
            "goods_code"           => $goods['goods_code'],
            "goods_attr_list"       => $goods_attr_list,
            'additional_cost'       => array_map(function ($item) {
                return [
                    'cost_name' => $item['cost_name'],
                    'money'     => $item['money'],
                    'images'    => $item['images'],
                ];
            }, $goods['goodsCost'] ?? [])
        ];


        $customer = new CustomerModel();
        if(!empty($goods['customer_id'])){
            $data_info = $customer->where('id', '=', $goods['customer_id'])->find()->toArray();
            $data['customer_name'] = $data_info['customer_name'];
        }else{
            $data['customer_name'] = '';
        }

        return success($data);

    }

    /**
     * 编辑锁单数据
     */
    public function lockEdit($data){
        // 允许更新的字段白名单（结合控制器入参）
        $allowedColumns = [
            'sale_uids',
            'deposit',
            'goods_attr_list',
            'payment_receipt',
            'exp_trans_price',
            'address_info',
            'lock_remark',
            'goods_num',
            'update_time',
        ];

        // sale_uids 若为数组，转为 JSON，保持与模型类型/原逻辑一致
        if (isset($data['sale_uids']) && is_array($data['sale_uids'])) {
            $data['sale_uids'] = json_encode($data['sale_uids'], JSON_UNESCAPED_UNICODE);
        }

        // 动态构造原生 SQL 与绑定参数
        $setClauseParts = [];
        $bindParams     = [];
        foreach ($allowedColumns as $column) {
            if (array_key_exists($column, $data)) {
                $setClauseParts[]   = "`{$column}` = :{$column}";
                $bindParams[$column] = $data[$column];
            }
        }

        // 若无可更新字段则直接返回成功
        if (empty($setClauseParts)) {
            return success("操作成功");
        }

        // where 条件参数
        $bindParams['order_id'] = $data['order_id'];

        $sql = "UPDATE `saler_tools_order` SET " . implode(', ', $setClauseParts) . " WHERE `order_id` = :order_id";
        Db::execute($sql, $bindParams);

        return success("操作成功");
    }

    public function lock($data)
    {
        $goods_id = $data['goods_id'];

        $goods_model = (new GoodsModel());
        $goods       = $goods_model->where('goods_id', $goods_id)
            ->with(['goodsCost'])
            ->where('site_id', $data['site_id'])
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
                'site_id'               => $data['site_id'],
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
                'lock_uid'            => $data['uid'],
                'lock_remark'         => $data['lock_remark'] ?? '',
                'lock_receipt'        => $data['lock_receipt'] ?? [],
                'create_uid'          => $data['uid'],
                'sale_uids'           => $data['sale_uids'] ?? [],
                'lock_time'           => date('Y-m-d H:i:s'),
                'create_time'         => date('Y-m-d H:i:s'),
                "payment_receipt"     =>json_encode($data['payment_receipt'], JSON_UNESCAPED_UNICODE) ?? "",
                'goods_attr_list'     => json_encode($data['goods_attr_list'], JSON_UNESCAPED_UNICODE),
                "update_time"         => date('Y-m-d H:i:s'),
                'address_info'        => $data['address_info'] ?? '',
            ];
            
            
            $money = 0;
            foreach (json_decode($data['goods_attr_list'], true) as $key => $val){
                $money += $val['price'] * $val['goods_num'];
            }
            GoodsLogService::setLog($data['site_id'], $goods_id, $data['goods_num'], GoodsDict::LOCK, [
                    'money' => $money,
                    'goods_num' => $data['goods_num'] ?? '',
                ], $data['create_uid']);

            $order_model->create($data);

            $order_model->commit();
            return success();
        } catch (\Exception $e) {
            $order_model->rollback();
            return fail($e->getMessage());
        }

    }

    /**
     * 取消锁单-有商品规格
     */
    public function lockCancel($data){
	        $order_model = new OrderModel();
	        $order       = $order_model->where('order_id', $data['order_id'])->findOrEmpty()->toArray();
	        $goods_id = $order['goods_id'];
	        $lock_goods_attr_list = json_decode($order['goods_attr_list'], true);
	        
	        $goods_num_list = [];
	        $goods_model = new GoodsModel();
	        $goods_info = $goods_model->where('goods_id', $goods_id)->findOrEmpty()->toArray();
	        $goods_attr_list = json_decode($goods_info['goods_attr_list'], true);

	        foreach ($goods_attr_list as $key => $val){
	            foreach ($lock_goods_attr_list as $lock_key => $lock_val){
	                if ($val['specifications'] == $lock_val['specifications']){
	                    $goods_attr_list[$key]['goods_num'] = $val['goods_num'] + $lock_val['lock_goods_num'];
	                }
	            }
	            $goods_num_list[] = $goods_attr_list[$key]['goods_num'];
	        }

	        // 订单与商品的更新数据
	        $order_update_data = [
	            'order_status' => self::LOCK_ORDER,
	            'deleted_time' => time(),
	        ];

	        $goods_update_data = [
	            'goods_attr_list' => json_encode($goods_attr_list, JSON_UNESCAPED_UNICODE),
	            'stock'           => array_sum($goods_num_list),
	        ];

	        // 使用事务与原生 SQL 更新
	        Db::startTrans();
	        try {
	            // 更新订单表：saler_tools_order
	            $orderSetParts = [];
	            $orderBinds    = ['order_id' => $data['order_id']];
	            foreach ($order_update_data as $col => $val) {
	                $orderSetParts[]   = "`{$col}` = :{$col}";
	                $orderBinds[$col]  = $val;
	            }
	            if (!empty($orderSetParts)) {
	                $orderSql = "UPDATE `saler_tools_order` SET " . implode(', ', $orderSetParts) . " WHERE `order_id` = :order_id";
	                Db::execute($orderSql, $orderBinds);
	            }

	            // 更新商品表：saler_tools_goods
	            $goodsSetParts = [];
	            $goodsBinds    = ['goods_id' => $goods_id];
	            foreach ($goods_update_data as $col => $val) {
	                $goodsSetParts[]  = "`{$col}` = :{$col}";
	                $goodsBinds[$col] = $val;
	            }
	            if (!empty($goodsSetParts)) {
	                $goodsSql = "UPDATE `saler_tools_goods` SET " . implode(', ', $goodsSetParts) . " WHERE `goods_id` = :goods_id";
	                Db::execute($goodsSql, $goodsBinds);
	            }

	            Db::commit();
	            return success("操作成功");
	        } catch (\Exception $e) {
	            Db::rollback();
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

                    $money = 0;
                    $attr_list = json_decode($goods->goods_attr_list, true);
                    foreach ($attr_list as $key => $val){
                        $money += $val['price'] * $order['goods_num'];
                    }
                    GoodsLogService::setLog($this->site_id, $goods->goods_id, $order['goods_num'], GoodsDict::UNLOCK, [
                        'money' => $money ?? '',
                        'goods_num' => $order['goods_num'] ?? '',
                    ], $this->uid);

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
        $data['order_status'] = self::FINISH_ORDER;

        $order->allowField(['order_status','paid_receipt', 'paid_remark', 'paid_time', 'paid_uid', 'is_paid', 'paid_type', 'shipment_type', 'logistics_code'])->save($data);

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


        if (!empty($query['end_time'])) {
            $model->where('transaction_time', '<=', $query['end_time']);
        }

        if (!empty($query['start_time'])) {
            $model->where('transaction_time', '>=', $query['start_time']);
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
            'logistics_code'  => $data['logistics_code'] ?? '',
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

    /**
     * 锁单金额统计
     */
    public function lockStatistics($data)
    {
        try {
            $model = new OrderModel();
            
            // 基础条件：site_id是必填的
            $query = $model->where('site_id', $data['site_id']);
            
            // 如果提供了uid参数，则添加uid条件
            if (!empty($data['uid'])) {
                $query = $query->where('create_uid', $data['uid']);
            }
            
            // 查询锁单类型的订单
            $query = $query->where('order_status', 'LOCK_ORDER');
            
            // 获取订单列表数据
            $orderList = $query->field('*')
                ->with(['createName', 'lockName'])
                ->order('create_time', 'desc')
                ->select()
                ->toArray();

            // 计算锁单数量
            $goods_num = array_sum(array_column($orderList, 'goods_num'));
            
            //定金
            $deposit = array_sum(array_column($orderList, 'deposit'));

            //预计成交金额
            $exp_trans_price = array_sum(array_column($orderList, 'exp_trans_price'));
            
            //锁单成本
            $lock_cost_list = [];
            foreach($orderList as $key => $val){
                if(!empty($val['goods_attr_list'])){
                    $goods_attr_list = json_decode($val['goods_attr_list'], true);
                    foreach($goods_attr_list as $k => $v){
                        $lock_cost_list[] = $v['total_cost'] * $v['lock_goods_num'];
                    }
                }
            }
            $lock_cost = array_sum($lock_cost_list);

            // 查询今日新增锁单记录数量
            $today_start = date('Y-m-d 00:00:00');
            $today_end = date('Y-m-d H:i:s');
            
            $today_count_query = $model->where('site_id', $data['site_id'])
                ->where('order_status', 'LOCK_ORDER')
                ->where('create_time', '>=', $today_start)
                ->where('create_time', '<=', $today_end);
            
            // 如果提供了uid参数，则添加uid条件
            if (!empty($data['uid'])) {
                $today_count_query = $today_count_query->where('create_uid', $data['uid']);
            }
            
            // 只统计今日新增锁单数量
            $today_order_count = $today_count_query->select()->toArray();
            if($today_order_count){
                $today_order_count = array_sum(array_column($orderList, 'goods_num'));
            }else{
                $today_order_count = 0;
            }
            // 查询今日删除锁单数量
            $today_delete_query = $model->where('site_id', $data['site_id'])
                ->where('deleted_time', 0)
                ->where('create_time', '>=', $today_start)
                ->where('create_time', '<=', $today_end);
            
            // 如果提供了uid参数，则添加uid条件
            if (!empty($data['uid'])) {
                $today_delete_query = $today_delete_query->where('create_uid', $data['uid']);
            }
            
            // 只统计今日删除锁单数量
            $today_delete_count = $today_delete_query->select()->toArray();
            if($today_delete_count){
                $today_delete_count = array_sum(array_column($orderList, 'goods_num'));
            }else{
                $today_delete_count = 0;
            }
            
            return success([
                  'goods_num' => $goods_num,
                  'lock_cost' => $lock_cost,
                  'deposit' => $deposit,
                  'exp_trans_price' => $exp_trans_price,
                  'today_order_count' => $today_order_count,
                  'today_delete_count' => $today_delete_count
              ]);
        } catch (\Exception $e) {
            return fail('查询失败：' . $e->getMessage());
        }
    }

    /**
     * 锁单比例统计
     */
    public function lockProportion($data)
    {

        $model = new OrderModel();
        
        // 基础条件：site_id是必填的
        $query = $model->where('site_id', $data['site_id']);
        
        // 如果提供了uid参数，则添加uid条件
        if (!empty($data['uid'])) {
            $query = $query->where('create_uid', $data['uid']);
        }
        
        // 查询锁单类型的订单
        $query = $query->where('order_status', 'LOCK_ORDER');
        
        // 获取订单列表数据
        $orderList = $query->field('*')
            ->with(['createName', 'lockName'])
            ->order('create_time', 'desc')
            ->select()
            ->toArray();
        
        // 计算锁单数量
        $goods_num = array_sum(array_column($orderList, 'goods_num'));
        
        // 计算锁单成本
        $lock_cost_list = [];
        foreach($orderList as $key => $val){
            if(!empty($val['goods_attr_list'])){
                $goods_attr_list = json_decode($val['goods_attr_list'], true);
                foreach($goods_attr_list as $k => $v){
                    $lock_cost_list[] = $v['total_cost'] * $v['lock_goods_num'];
                }
            }
        }
        $lock_cost = array_sum($lock_cost_list);

        $goods_model = new GoodsModel();
        $goods_info = $goods_model->where('site_id', $data['site_id'])->select()->toArray();

        //商品库存总数量
         $stock_num = array_sum(array_column($goods_info, 'stock'));


        //计算商品总成本
        $goods_cost_list = [];
        foreach($goods_info as $key => $val){
            if(!empty($val['goods_attr_list'])){
                $goods_attr_list = json_decode($val['goods_attr_list'], true);
                foreach($goods_attr_list as $k => $v){
                    $goods_cost_list[] = $v['total_cost'] * $v['goods_num'];
                }
            }
        }
        $total_cost = array_sum($goods_cost_list);

        //数量占比
        $lock_num_ratio = round($goods_num / $stock_num * 100, 4);

        //商品成本占比
        $lock_cost_price = round($lock_cost / $total_cost * 100, 4);

        return success([
            'num_ratio' => 100-$lock_num_ratio,
            'lock_num_ratio' => $lock_num_ratio,

            'cost_price' => 100-$lock_cost_price,
            'lock_cost_price' => $lock_cost_price,

            "num" => $stock_num-$goods_num,
            "lock_num" => $goods_num,

            "total_cost" => round(($total_cost-$lock_cost)/10000,4),
            "lock_cost" => round($lock_cost/10000,4),
        ]);
    }


    /**
     * 获取店铺员工信息
     */
    public function getPersonInfo($data)
    {
        $order_model = new OrderModel();
        $site_id = $data['site_id'];
        try {
            $list = (new \app\model\sys\SysUserRole())
                ->order('is_admin desc,id desc')
                ->with('userinfo')
                ->append(['status_name'])
                ->hasWhere('userinfo', [['is_del', '=', 0]])
                ->where([['SysUserRole.site_id', '=', $site_id]])
                ->select()
                ->toArray();
            
            foreach($list as $key => $val){
                $order_info = $order_model->where('create_uid', $val['uid'])->select()->toArray();
                $list[$key]['lock_num'] = 0;
                if(!empty($order_info)){
                    $lock_num = array_sum(array_column($order_info, 'goods_num'));
                    $list[$key]['lock_num'] = $lock_num;
                }

                //统计销售金额
                $order_info_pirce = $order_model->where('create_uid', $val['uid'])
                                                ->where('order_status', "FINISH_ORDER")
                                                ->select()->toArray();
                //销售额money
                $list[$key]['money'] = 0;
                if(!empty($order_info_pirce)){
                    $list[$key]['money'] = round(array_sum(array_column($order_info_pirce, 'order_price'))/10000,4);
                }

                //成本total_cost
                $list[$key]['total_cost'] = 0;
                if(!empty($order_info_pirce)){
                    $list[$key]['total_cost'] = round(array_sum(array_column($order_info_pirce, 'total_cost'))/10000,4);
                }
                //利润profit
                $list[$key]['profit'] = 0;
                if(!empty($order_info_pirce)){
                    $list[$key]['profit'] = round(array_sum(array_column($order_info_pirce, 'order_price'))/10000 - array_sum(array_column($order_info_pirce, 'total_cost'))/10000,4);
                }

                //利率interest_rate
                $list[$key]['interest_rate'] = 0;
                if(!empty($order_info_pirce) && $list[$key]['total_cost'] > 0){
                    $list[$key]['interest_rate'] = round($list[$key]['profit']/$list[$key]['total_cost']*100,4);
                }

            }

            return success($list);
        } catch (\Throwable $e) {
            return fail('查询失败：' . $e->getMessage());
        }
    }


    /**
     * 记账类型添加
     */
    public function addType($data)
    {
        $type_name = trim($data['type_name']);
        $create_time = $data['create_time'];
        $status = isset($data['status']) ? intval($data['status']) : 1; // 获取status参数，默认值为1
        
        try {
            // 校验名称唯一性
            $existing = \think\facade\Db::query(
                "SELECT COUNT(*) as count FROM user_bookkeeping_type WHERE type_name = ? AND status = ?",
                [$type_name,$data['status']]
            );
            
            if ($existing[0]['count'] > 0) {
                return fail('记账类型名称已存在');
            }
            
            // 使用原生SQL插入，添加status字段
            $sql = "INSERT INTO user_bookkeeping_type (type_name, create_time, status,uid) VALUES (?, ?, ?,?)";
            \think\facade\Db::execute($sql, [$type_name, $create_time, $status,$data['uid']]);
            
            return success('添加成功');
        } catch (\Throwable $e) {
            return fail('添加失败：' . $e->getMessage());
        }
    }

    /**
     * 记账类型删除
     */
    public function delType($data)
    {
        $type_id = $data['type_id'];
        
        try {
            // 检查是否绑定了记账记录
            $bind_count = \think\facade\Db::query(
                "SELECT COUNT(*) as count FROM user_bookkeeping WHERE f_id = ?",
                [$type_id]
            )[0]['count'];
            
            if ($bind_count > 0) {
                return fail('已绑定记账不能删除');
            }
            
            // 使用原生SQL直接删除
            $sql = "DELETE FROM user_bookkeeping_type WHERE id = ?";
            $result = \think\facade\Db::execute($sql, [$type_id]);
            
            if ($result > 0) {
                return success('删除成功');
            } else {
                return fail('记录不存在');
            }
        } catch (\Throwable $e) {
            return fail('删除失败：' . $e->getMessage());
        }
    }
       

    /**
     * 记账类型列表（无分页、无搜索）
     */
    public function typeList($status=1,$uid=0)
    {
        try {
            $list = \think\facade\Db::query(
                "SELECT id, type_name, create_time, status FROM user_bookkeeping_type WHERE status = ? AND uid = ? ORDER BY id DESC",
                [$status,$uid]
            );
            return success($list);
        } catch (\Throwable $e) {
            return fail('查询失败：' . $e->getMessage());
        }
    }


}
