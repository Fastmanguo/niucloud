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
use addon\saler_tools\app\model\SalerToolsGoodsCategory;
use addon\saler_tools\app\model\SalerToolsGoodsCost;
use addon\saler_tools\app\model\Collect;
use addon\saler_tools\app\service\dict\SiteDictService;
use addon\saler_tools\app\service\order\OrderService;
use addon\saler_tools\app\service\shop\ShopService;
use think\db\Query;
use think\db\Raw;
use app\model\sys\SysUser;
use app\model\member\CustomerModel;
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

        $resultList = $this->pageQuery($model);
        
        foreach ($resultList['data'] as $key=>$val){
            $create_time = $val['create_time'];
            $days = ceil((time() - strtotime($create_time))/86400);
            $resultList['data'][$key]['days'] = "在库".$days."天";

            if (empty($val['goods_attr_list'])){
                $resultList['data'][$key]['goods_attr_list'] = [];
                $resultList['data'][$key]['total_cost'] = 0;
                $resultList['data'][$key]['peer_price'] = 0;
                $resultList['data'][$key]['price'] = 0;
            }else{
                $goods_attr_list = json_decode($val['goods_attr_list'],true);
                $resultList['data'][$key]['goods_attr_list'] = $goods_attr_list;
                // 确保数组不为空且有索引0
                if (!empty($goods_attr_list) && isset($goods_attr_list[0])) {
                    $resultList['data'][$key]['total_cost'] = $goods_attr_list[0]['total_cost'] ?? 0;
                    $resultList['data'][$key]['peer_price'] = $goods_attr_list[0]['peer_price'] ?? 0;
                    $resultList['data'][$key]['price'] = $goods_attr_list[0]['price'] ?? 0;
                } else {
                    $resultList['data'][$key]['total_cost'] = 0;
                    $resultList['data'][$key]['peer_price'] = 0;
                    $resultList['data'][$key]['price'] = 0;
                }
            }
            
        }

        return success($resultList);

    }
    public function goodsTypePrice($site_id,$uid)
    {
        $model = new GoodsModel();
        $where = [['site_id', '=', $site_id],['create_uid', '=', $uid]];
        $goodsData = $model->where($where)->select();
        $goodsData = json_decode($goodsData,true);
        $priceList = [];
        $ownGoodsList = [];
        $consignmentGoodsList = [];
        $pawnedGoodsList = [];
        foreach ($goodsData as $key=>$val){
            $priceList[] = $val['total_cost'];
            if($val['goods_attribute'] == "own_goods"){
                $ownGoodsList[] = $val['total_cost'];
            }
            if($val['goods_attribute'] == "consignment_goods"){
                $consignmentGoodsList[] = $val['total_cost'];
            }
            if($val['goods_attribute'] == "pawned_goods"){
                $pawnedGoodsList[] = $val['total_cost'];
            }

        }


        $result = array([
            "total_cost"=>array_sum($priceList),
            "own_goods_cost"=>array_sum($ownGoodsList),
            "consignment_goods_cost"=>array_sum($consignmentGoodsList),
            "pawned_goods_cost"=>array_sum($pawnedGoodsList),
        ]);
        return success($result);
    }
    /**
     * @param $goods_attribute
     * @return \think\Response
     * 仓库各类商品数量统计
     */
    public function goodsWarehouseCount($goods_attribute,$uid){
        $model = new GoodsModel();
        
        // 基础查询条件
        $where = [['create_uid', '=', $uid]];

        // 如果传入了goods_attribute参数，则添加查询条件
        if (!empty($goods_attribute)) {
            $where = [['create_uid', '=', $uid],['goods_attribute', '=', $goods_attribute]];
        }
        
        // 统计总条数
        $total_count = $model->where($where)->count();
        // 统计category_id = 1的数量 腕表
        $category_1_count = $model->where($where)->where('category_id', 1)->count();
        
        // 统计category_id = 18的数量  箱包 
        $category_18_count = $model->where($where)->where('category_id', 18)->count();
        
        // 统计category_id = 29的数量   珠宝
        $category_29_count = $model->where($where)->where('category_id', 29)->count();
        
        // 统计category_id = 31的数量   鞋靴
        $category_31_count = $model->where($where)->where('category_id', 31)->count();

        // 统计category_id = 32的数量   服饰
        $category_32_count = $model->where($where)->where('category_id', 32)->count();

        // 统计category_id = 34的数量   配饰
        $category_34_count = $model->where($where)->where('category_id', 34)->count();

        // 统计category_id = 33的数量   其他
        $category_33_count = $model->where($where)->where('category_id', 33)->count();
        
        $result = [ $total_count, $category_1_count, $category_18_count,$category_29_count,$category_31_count,$category_32_count,
             $category_34_count,$category_33_count,
        ];

        
        return success($result);
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
        
        $category_id = $goods['category_id'];
        // 根据category_id查询对应的category_name
        if (!empty($category_id)) {
            $category_name = (new SalerToolsGoodsCategory())->where('category_id', $category_id)->value('category_name');
            $goods['category_name'] = $category_name;
        }

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
        $goods_attr_list = json_decode($goods['goods_attr_list'],true);
        $goods['goods_attr_list'] = $goods_attr_list;
        if($goods_attr_list){
            $goods['total_cost'] = $goods_attr_list[0]['total_cost'];
            $goods['peer_price'] = $goods_attr_list[0]['peer_price'];
            $goods['price'] = $goods_attr_list[0]['price'];
        }

        $user = (new SysUser())->where('uid', '=', $goods['create_uid'])->findOrEmpty()->toArray();
        $goods['userInfo'] = $user;

        $update_time = strtotime($goods['update_time']);
        $hour = round((time() - $update_time) / 3600);
        if($hour < 24){
            $goods['updte_hour'] = $hour.'小时前';
        }else{
            $goods['updte_hour'] = intval($hour/24).'天前';
        }
        //客户类型 /新增 1：同行  2：客户 3：平台 4：其他,
        if($goods['customer_type'] == '1'){
            $goods['customer_type_name'] = '同行';
        }elseif($goods['customer_type'] == '2'){
            $goods['customer_type_name'] = '客户';
        }elseif($goods['customer_type'] == '3'){
            $goods['customer_type_name'] = '平台';
        }else{
            $goods['customer_type_name'] = '其他';
        }
        if($goods['customer_id']){
            $customer = new CustomerModel();
            $data_info = $customer->where('id', '=', $goods['customer_id'])->find()->toArray();
            $goods['customer_name'] = $data_info['customer_name'];
        }else{
            $goods['customer_name'] = '';
        }
        $goods['detail_image'] = json_decode($goods['detail_image'],true);
        return success($goods);
        // $uid = $goods['create_uid'];
        // $site_id = $goods['site_id'];
        // $goods_id = $goods['goods_id'];

        // // 查询该商品的收藏信息
        // $collectModel = new Collect();
        // $collectInfo = $collectModel->where('relate_id', $goods_id)
        //     ->where('site_id', $site_id)
        //     ->where('uid', $uid)
        //     ->findOrEmpty();
        
        // if($collectInfo->isEmpty()){
        //     $goods['is_collected'] = 0;
        // }else{
        //     $goods['is_collected'] = 1;
        // }
        
        // $money = $goods['peer_price'];
        // $currency_code = $goods['currency_code'];

        // $money_peer_price = [
        //     ['address'=>'CN','id'=>'CNY','name'=>'人民币',"monery"=>$this->convertCurrency($money,$currency_code,'CNY')],
        //     ['address'=>'US','id'=>'USD','name'=>'美元',"monery"=>$this->convertCurrency($money,$currency_code,'USD')],
        //     ['address'=>'EU','id'=>'EUR','name'=>'欧元',"monery"=>$this->convertCurrency($money,$currency_code,'EUR')],
        //     ['address'=>'JP','id'=>'JPY','name'=>'日元',"monery"=>$this->convertCurrency($money,$currency_code,'JPY')],
        //     ['address'=>'GB','id'=>'GBP','name'=>'英镑',"monery"=>$this->convertCurrency($money,$currency_code,'GBP')],
        //     ['address'=>'HK','id'=>'HKD','name'=>'港币',"monery"=>$this->convertCurrency($money,$currency_code,'HKD')],
        //     ['address'=>'KR','id'=>'KRW','name'=>'韩元',"monery"=>$this->convertCurrency($money,$currency_code,'KRW')],
        //     ['address'=>'SG','id'=>'SGD','name'=>'新加坡元',"monery"=>$this->convertCurrency($money,$currency_code,'SGD')],
        //     ['address'=>'AU','id'=>'AUD','name'=>'澳元',"monery"=>$this->convertCurrency($money,$currency_code,'AUD')],
        //     ['address'=>'CA','id'=>'CAD','name'=>'加拿大元',"monery"=>$this->convertCurrency($money,$currency_code,'CAD')]
        // ];

        // $money_peer = [
        //     ['address'=>'CN','id'=>'CNY','name'=>'人民币',"monery"=>$this->convertCurrency($goods['price'],$currency_code,'CNY')],
        //     ['address'=>'US','id'=>'USD','name'=>'美元',"monery"=>$this->convertCurrency($goods['price'],$currency_code,'USD')],
        //     ['address'=>'EU','id'=>'EUR','name'=>'欧元',"monery"=>$this->convertCurrency($goods['price'],$currency_code,'EUR')],
        //     ['address'=>'JP','id'=>'JPY','name'=>'日元',"monery"=>$this->convertCurrency($goods['price'],$currency_code,'JPY')],
        //     ['address'=>'GB','id'=>'GBP','name'=>'英镑',"monery"=>$this->convertCurrency($goods['price'],$currency_code,'GBP')],
        //     ['address'=>'HK','id'=>'HKD','name'=>'港币',"monery"=>$this->convertCurrency($goods['price'],$currency_code,'HKD')],
        //     ['address'=>'KR','id'=>'KRW','name'=>'韩元',"monery"=>$this->convertCurrency($goods['price'],$currency_code,'KRW')],
        //     ['address'=>'SG','id'=>'SGD','name'=>'新加坡元',"monery"=>$this->convertCurrency($goods['price'],$currency_code,'SGD')],
        //     ['address'=>'AU','id'=>'AUD','name'=>'澳元',"monery"=>$this->convertCurrency($goods['price'],$currency_code,'AUD')],
        //     ['address'=>'CA','id'=>'CAD','name'=>'加拿大元',"monery"=>$this->convertCurrency($goods['price'],$currency_code,'CAD')]
        // ];

        // $cost_one = $goods['total_cost']/$goods['stock'];
        // $goods['cost_one'] = round($cost_one,2);
        // $total_cost = [
        //     ['address'=>'CN','id'=>'CNY','name'=>'人民币',"monery"=>$this->convertCurrency($cost_one,$currency_code,'CNY')],
        //     ['address'=>'US','id'=>'USD','name'=>'美元',"monery"=>$this->convertCurrency($cost_one,$currency_code,'USD')],
        //     ['address'=>'EU','id'=>'EUR','name'=>'欧元',"monery"=>$this->convertCurrency($cost_one,$currency_code,'EUR')],
        //     ['address'=>'JP','id'=>'JPY','name'=>'日元',"monery"=>$this->convertCurrency($cost_one,$currency_code,'JPY')],
        //     ['address'=>'GB','id'=>'GBP','name'=>'英镑',"monery"=>$this->convertCurrency($cost_one,$currency_code,'GBP')],
        //     ['address'=>'HK','id'=>'HKD','name'=>'港币',"monery"=>$this->convertCurrency($cost_one,$currency_code,'HKD')],
        //     ['address'=>'KR','id'=>'KRW','name'=>'韩元',"monery"=>$this->convertCurrency($cost_one,$currency_code,'KRW')],
        //     ['address'=>'SG','id'=>'SGD','name'=>'新加坡元',"monery"=>$this->convertCurrency($cost_one,$currency_code,'SGD')],
        //     ['address'=>'AU','id'=>'AUD','name'=>'澳元',"monery"=>$this->convertCurrency($cost_one,$currency_code,'AUD')],
        //     ['address'=>'CA','id'=>'CAD','name'=>'加拿大元',"monery"=>$this->convertCurrency($cost_one,$currency_code,'CAD')]
        // ];

        // $goods['money_peer_price'] = $money_peer_price;
        // $goods['money_peer'] = $money_peer;
        // $goods['money_total_cost'] = $total_cost;
        // return success($goods);
    }
    function convertCurrency($amount, $from_currency, $to_currency) {
        // 检查参数是否有效
        if (!is_numeric($amount) || $amount <= 0 || empty($from_currency) || empty($to_currency)) {
            return 0;
        }

        // 支持的货币列表
        $supported_currencies = [
            'USD' => '美元',
            'CNY' => '人民币',
            'EUR' => '欧元',
            'JPY' => '日元',
            'GBP' => '英镑',
            'HKD' => '港币',
            'KRW' => '韩元',
            'SGD' => '新加坡元',
            'AUD' => '澳元',
            'CAD' => '加拿大元'
        ];

// 汇率表（简化版本，实际应用中应该从API获取实时汇率）
        $exchange_rates = [
            'USD' => [
                'CNY' => 7.23,
                'EUR' => 0.92,
                'JPY' => 148.50,
                'GBP' => 0.79,
                'HKD' => 7.82,
                'KRW' => 1330.00,
                'SGD' => 1.35,
                'AUD' => 1.52,
                'CAD' => 1.36,
                'USD' => 1.00
            ],
            'CNY' => [
                'USD' => 0.138,
                'EUR' => 0.127,
                'JPY' => 20.54,
                'GBP' => 0.109,
                'HKD' => 1.082,
                'KRW' => 183.96,
                'SGD' => 0.187,
                'AUD' => 0.210,
                'CAD' => 0.188,
                'CNY' => 1.00
            ],
            'EUR' => [
                'USD' => 1.087,
                'CNY' => 7.86,
                'JPY' => 161.41,
                'GBP' => 0.859,
                'HKD' => 8.50,
                'KRW' => 1445.65,
                'SGD' => 1.47,
                'AUD' => 1.65,
                'CAD' => 1.48,
                'EUR' => 1.00
            ],
            'JPY' => [
                'USD' => 0.0067,
                'CNY' => 0.0487,
                'EUR' => 0.0062,
                'GBP' => 0.0053,
                'HKD' => 0.0527,
                'KRW' => 8.96,
                'SGD' => 0.0091,
                'AUD' => 0.0102,
                'CAD' => 0.0092,
                'JPY' => 1.00
            ],
            'GBP' => [
                'USD' => 1.266,
                'CNY' => 9.15,
                'EUR' => 1.164,
                'JPY' => 187.97,
                'HKD' => 9.90,
                'KRW' => 1683.54,
                'SGD' => 1.71,
                'AUD' => 1.92,
                'CAD' => 1.72,
                'GBP' => 1.00
            ]
        ];

        
        // 检查汇率是否存在
        if (!isset($exchange_rates[$from_currency]) || !isset($exchange_rates[$from_currency][$to_currency])) {
            return 0;
        }

        $rate = $exchange_rates[$from_currency][$to_currency];
        $result = $amount * $rate;
        // 格式化输出
        $formatted_result = number_format($result, 2);

        $result_array = [
            'price' => $amount,
            'to_price' => $formatted_result,
            'currency' => $from_currency,
            'to_currency' => $to_currency,
            'rate' => $rate,
        ];
        return $result_array;
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
        $data['detail_image'] = json_encode($data['detail_image'], JSON_UNESCAPED_UNICODE);

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

    /**
     * 统计仓库内商品金额
     * 
     */
    public function getCkTypePrice($data)
    {
        $model = new GoodsModel();
        $where = [
            ['site_id', '=', $data['site_id']],
            ["create_uid", '=', $data['uid']],
            ['deleted_time', '=', 0],
        ];
        $list = $model->where($where)->select()->toArray();
        
        $price_list = [];
        $total_cost_list = [];
        $jm_price_list = [];
        $zy_total_cost_list = [];
        foreach($list as $key => $val){

            if($val['goods_attr_list']){
                $goods_attr_list = json_decode($val['goods_attr_list'], true);
                foreach($goods_attr_list as $k => $v){
                    $price_list[] = $v['goods_num'] * $v['price'];
                }
            
            if($val['goods_attribute'] == "own_goods"){
                $goods_attr_list = json_decode($val['goods_attr_list'], true);
                foreach($goods_attr_list as $k => $v){
                    $total_cost_list[] = $v['goods_num'] * $v['total_cost'];
                }
            }
            
            if($val['goods_attribute'] == "consignment_goods"){
                $goods_attr_list = json_decode($val['goods_attr_list'], true);
                foreach($goods_attr_list as $k => $v){
                    $jm_price_list[] = $v['goods_num'] * $v['price'];
                }
            }

            if($val['goods_attribute'] == "pawned_goods"){
                $goods_attr_list = json_decode($val['goods_attr_list'], true);
                foreach($goods_attr_list as $k => $v){
                    $zy_total_cost_list[] = $v['goods_num'] * $v['total_cost'];
                }
            }

            }
        }

        return success([
            'price' => array_sum($price_list),
            'total_cost_price' => array_sum($total_cost_list),
            'jm_price' => array_sum($jm_price_list),
            'zy_total_cost_price' => array_sum($zy_total_cost_list),
        ]);
    }

}
