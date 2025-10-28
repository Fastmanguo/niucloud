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
use think\facade\Db;
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
        
        // 批量按 site_id 关联店铺信息
        $siteIds = array_unique(array_column($resultList['data'], 'site_id'));
        $siteIds = array_filter($siteIds);
        $shopInfoMap = [];
        if (!empty($siteIds)) {
            $shopList = \think\facade\Db::name('saler_tools_shop')
                ->whereIn('site_id', $siteIds)
                ->field('site_id,shop_name,logo,address')
                ->select()
                ->toArray();
            foreach ($shopList as $shop) {
                $shopInfoMap[$shop['site_id']] = [
                    'shop_name' => $shop['shop_name'] ?? '',
                    'shop_logo' => $shop['logo'] ?? '',
                    'shop_address' => $shop['address'] ?? '',
                ];
            }
        }
        
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
            
            // 追加店铺信息为指定字段名
            $siteId = $val['site_id'] ?? 0;
            if ($siteId && isset($shopInfoMap[$siteId])) {
                $resultList['data'][$key]['shop_name'] = $shopInfoMap[$siteId]['shop_name'];
                $resultList['data'][$key]['shop_logo'] = $shopInfoMap[$siteId]['shop_logo'];
                $resultList['data'][$key]['shop_address'] = $shopInfoMap[$siteId]['shop_address'];
            } else {
                $resultList['data'][$key]['shop_name'] = '';
                $resultList['data'][$key]['shop_logo'] = '';
                $resultList['data'][$key]['shop_address'] = '';
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
        foreach($goods_attr_list as $key => $value){
            $goods_attr_list[$key]['lock_goods_num'] = 0;
        }
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

        // 根据site_id查询店铺logo
        $shop_info = Db::table('saler_tools_shop')
            ->where('site_id', $goods['site_id'])
            ->field('logo, shop_name, address')
            ->find();
        
        // 设置店铺信息，为空时使用默认值'无'
        $goods['shop_logo'] = !empty($shop_info['logo']) ? $shop_info['logo'] : 'https://84000-1333979078.cos.ap-shanghai.myqcloud.com/upload/attachment/image/0/202510/24/17612696865a6e04ab683cc068542cf4f3cc4c1530_tencent.png';
        $goods['shop_name'] = !empty($shop_info['shop_name']) ? $shop_info['shop_name'] : '无';
        $goods['shop_address'] = !empty($shop_info['address']) ? $shop_info['address'] : '无';
        return success($goods);
        
    }

    /**
     * 根据规格内信息进行货币转换
     */
    public function toPriceDetails($goods_id){
        
        $model = new GoodsModel();
        $goods = $model->where('goods_id', $goods_id)
            ->select()
            ->toArray();
        if($goods[0]['goods_attr_list']){
            $goods_attr_list = json_decode($goods[0]['goods_attr_list'],true);
        }
        $currency_code = $goods[0]['currency_code'];
        $supported_currencies = ['USD','CNY','EUR','JPY','GBP','HKD','KRW','SGD','AUD','CAD' ];

        foreach($goods_attr_list as $key => $value){
            $cur_code_list = [];
            foreach($supported_currencies as $k => $v){
                $m_result = [];
                $price_result = $this->convertCurrency($value['price'],$currency_code,$v);
                $m_result['rate'] = $price_result['rate'];
                $m_result['currency'] = $price_result['currency'];
                $m_result['to_currency'] = $price_result['to_currency'];
                $m_result['price'] = $price_result['price'];
                $m_result['to_price'] = $price_result['to_price'];

                $price_result = $this->convertCurrency($value['total_cost'],$currency_code,$v);
                $m_result['total_cost'] = $price_result['price'];
                $m_result['to_total_cost'] = $price_result['to_price'];

                $price_result = $this->convertCurrency($value['peer_price'],$currency_code,$v);
                $m_result['peer_price'] = $price_result['price'];
                $m_result['to_peer_price'] = $price_result['to_price'];
                $cur_code_list[] = $m_result;
            }
            
            $goods_attr_list[$key]['cur_code_list'] = $cur_code_list;
        }

        return success($goods_attr_list);
        // if($goods_attr_list){
        //     foreach($goods_attr_list as $key => $value){
        //         $goods_attr_list[$key]['price'] = $this->convertCurrency($value['price'],$value['currency_code'],'CNY');
        //     }
        // }
        // return success($goods_attr_list);
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
            return success(['goods_id' => $goods->goods_id]);
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

        $own_goods_list = [];
        $consignment_goods = [];
        $pawned_goods_list = [];
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
                $own_goods_list[] = $val['stock'];
            }
            
            if($val['goods_attribute'] == "consignment_goods"){
                $goods_attr_list = json_decode($val['goods_attr_list'], true);
                foreach($goods_attr_list as $k => $v){
                    $jm_price_list[] = $v['goods_num'] * $v['price'];
                }
                $consignment_goods[] = $val['stock'];
            }

            if($val['goods_attribute'] == "pawned_goods"){
                $goods_attr_list = json_decode($val['goods_attr_list'], true);
                foreach($goods_attr_list as $k => $v){
                    $zy_total_cost_list[] = $v['goods_num'] * $v['total_cost'];
                }
                $pawned_goods_list[] = $val['stock'];
            }

            }
        }

        return success([
            'price' => array_sum($price_list),
            'total_cost_price' => array_sum($total_cost_list),
            'jm_price' => array_sum($jm_price_list),
            'zy_total_cost_price' => array_sum($zy_total_cost_list),
            'own_goods_num' => array_sum($own_goods_list),
            'consignment_goods_num' => array_sum($consignment_goods),
            'pawned_goods_num' => array_sum($pawned_goods_list),
        ]);
    }

    /**
     * 商品擦亮
     */
    public function goodsCl($data)
    {
        // 查询已上架且符合site_id的商品
        $sql = "SELECT * FROM saler_tools_goods WHERE is_sale = 1 AND site_id = :site_id";
        $goods_list = Db::query($sql, ['site_id' => $data['site_id']]);
        
        if (empty($goods_list)) {
            return fail('当前没有已上架商品');
        }
        
        // 更新cl_status
        $update_sql = "UPDATE saler_tools_goods SET cl_status = 1 WHERE is_sale = 1 AND site_id = :site_id";
        $update_result = Db::execute($update_sql, ['site_id' => $data['site_id']]);
        
        return success("商品已擦亮");
    }

    /**
     * 仓库统计
     */
    public function getCktj($data)
    {
        $monthlyData = [];
        
        // 获取当前月份的开始日期
        $currentYearMonth = date('Y-m');
        $currentMonthStart = $currentYearMonth . '-01';
        
        // 循环12个月（包括当前月）
        for ($i = 0; $i < 12; $i++) {
            // 计算每个月的开始日期
            $monthStart = date('Y-m-d', strtotime("$currentMonthStart -$i months"));
            $yearMonth = date('Y-m', strtotime($monthStart));
            // 计算该月的结束日期
            $monthEnd = date('Y-m-t', strtotime($monthStart));
            // 计算时间格式（不再使用时间戳）
            $startTime = $monthStart . ' 00:00:00';
            $endTime = $monthEnd . ' 23:59:59';
            // 添加每月的开始和结束时间到结果数组
            $monthlyData[] = [
                'year_month' => $yearMonth,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'month_text' => date('Y年m月', strtotime($monthStart))
            ];
        }

        foreach($monthlyData as $key => $val){
            $where = [
                ['site_id', '=', $data['site_id']],
                ['deleted_time', '=', 0],
                ['create_time', '<=', $val['end_time']],
                ['create_time', '>=', $val['start_time']],
            ];

            // 如果传入了ck_type且非空，则按照goods_attribute字段进行筛选
            if (!empty($data['ck_type'])) {
                $where[] = ['goods_attribute', '=', $data['ck_type']];
            }
            
            // 查询商品表数据
            $goodsModel = new GoodsModel();
            $goodsData = $goodsModel->where($where)
                ->field('goods_id, goods_name, goods_cover, price, total_cost, goods_attribute, create_time,goods_attr_list')
                ->select()
                ->toArray();
            
            // 统计数据
            $stock = $goodsModel->where($where)->sum('stock') ?? 0;
            $price_list = [];
            foreach($goodsData as $k => $v){
                if($v['goods_attr_list']){                    
                    $goods_attr_list = json_decode($v['goods_attr_list'], true);
                    foreach($goods_attr_list as $kk => $vv){                        
                        $price_list[] = $vv['goods_num'] * $vv['price'];
                    }
                }
            }
            $monthlyData[$key]['price'] = array_sum($price_list);
            $monthlyData[$key]['stock'] = $stock;
           
            
        }
        
        return success($monthlyData);
    }


    /**
     * 获取商品各维度分组统计数据
     * type=1:按商品分类(category_id)分组
     * type=2:按商品品牌(brand_id)分组
     * type=3:按商品成色(condition)分组
     */
    public function getGoodsTypeDetails($data){
        $site_id = $data['site_id'];
        $type = $data['type'] ?? 1;
        
        // 基础查询条件
        $where = [
            ['site_id', '=', $site_id],
            ['deleted_time', '=', 0]
        ];
        
        // 查询商品数据，根据类型调整查询字段
        $goodsModel = new GoodsModel();
        $fields = 'goods_id, goods_name, goods_cover, price, total_cost, category_id, brand_id, condition, goods_attribute, create_time, goods_attr_list';
        $goodsList = $goodsModel->where($where)
            ->field($fields)
            ->select()
            ->toArray();
        
        // 初始化变量
        $groupedData = [];
        $totalSummary = [
            'total_count' => 0,
            'total_price' => 0,
            'total_cost' => 0,
            'groups_count' => 0
        ];
        
        // 如果是按品牌分组，需要获取品牌信息
        $brandMap = [];
        if ($type == 2) {
            $brandIds = array_unique(array_column($goodsList, 'brand_id'));
            $brandIds = array_filter($brandIds);
            
            if (!empty($brandIds)) {
                // 假设品牌模型可以直接使用，如果需要导入请自行添加
                $brandModel = new \addon\saler_tools\app\model\SalerToolsGoodsBrand();
                $brandList = $brandModel->whereIn('brand_id', $brandIds)
                    ->field('brand_id, brand_name')
                    ->select()
                    ->toArray();
                
                foreach ($brandList as $brand) {
                    $brandMap[$brand['brand_id']] = $brand['brand_name'];
                }
            }
        } else if ($type == 1) {
            // 获取所有分类ID并查询对应的分类名称
            $categoryIds = array_unique(array_column($goodsList, 'category_id'));
            $categoryIds = array_filter($categoryIds);
            $categoryMap = [];
            
            if (!empty($categoryIds)) {
                $categoryModel = new SalerToolsGoodsCategory();
                $categoryList = $categoryModel->whereIn('category_id', $categoryIds)
                    ->field('category_id, category_name')
                    ->select()
                    ->toArray();
                
                foreach ($categoryList as $category) {
                    $categoryMap[$category['category_id']] = $category['category_name'];
                }
            }
        }
        
        // 根据type参数决定分组维度
        foreach ($goodsList as $goods) {
            $groupId = 0;
            $groupName = '未知';
            
            if ($type == 1) {
                // 按分类分组
                $groupId = $goods['category_id'] ?? 0;
                $groupName = $categoryMap[$groupId] ?? '未分类';
            } else if ($type == 2) {
                // 按品牌分组
                $groupId = $goods['brand_id'] ?? 0;
                $groupName = $brandMap[$groupId] ?? '未分类';
            } else if ($type == 3) {
                // 按商品成色分组
                $condition = $goods['condition'] ?? '';
                if ($condition == 'condition_new') {
                    $groupId = 1;
                    $groupName = '全新';
                } else if ($condition == 'condition_used') {
                    $groupId = 2;
                    $groupName = '二手';
                } else {
                    $groupId = 0;
                    $groupName = '未知';
                }
            }
            
            // 初始化该分组的数据
            if (!isset($groupedData[$groupId])) {
                $groupedData[$groupId] = [
                    'group_id' => $groupId,
                    'group_name' => $groupName,
                    'count' => 0,
                    'total_price' => 0,
                    'total_cost' => 0
                ];
                $totalSummary['groups_count']++;
            }
            
            // 处理商品属性列表，计算价格
            $goodsPrice = 0;
            $goodsCost = 0;
            $goodsNum = 0;
            if (!empty($goods['goods_attr_list'])) {
                $goodsAttrList = json_decode($goods['goods_attr_list'], true);
                foreach ($goodsAttrList as $attrItem) {
                    $goodsPrice += ($attrItem['price'] ?? 0) * ($attrItem['goods_num'] ?? 0);
                    $goodsCost += ($attrItem['total_cost'] ?? 0);
                    $goodsNum += ($attrItem['goods_num'] ?? 0);
                }
            }
            
            // 更新分组统计数据
            $groupedData[$groupId]['count'] += $goodsNum;
            $groupedData[$groupId]['total_price'] += $goodsPrice;
            $groupedData[$groupId]['total_cost'] += $goodsCost;
            
            // 更新总统计数据
            $totalSummary['total_count'] += $goodsNum;
            $totalSummary['total_price'] += $goodsPrice;
            $totalSummary['total_cost'] += $goodsCost;
        }
        
        // 计算每个分组的占比百分比
        foreach ($groupedData as &$group) {
            $group['count_proportion'] = $totalSummary['total_count'] > 0 ? round(($group['count'] / $totalSummary['total_count']) * 100, 2) : 0;
            $group['price_proportion'] = $totalSummary['total_price'] > 0 ? round(($group['total_price'] / $totalSummary['total_price']) * 100, 2) : 0;
        }
        unset($group);
        
        // 转换为数组格式，修改字段名称以适应不同类型
        $result = [
            'total_summary' => $totalSummary,
            'group_details' => array_values($groupedData)
        ];
        
        
        return success($result);
    }

    /**
     * 根据用户类型获取商品信息
     * @param $params
     * @return think\Response
     */
    public function getPersonOrder($params){
        $site_id = $params['site_id'];
        $type = $params['type'];
        $list = (new \app\model\sys\SysUserRole())
                ->order('is_admin desc,id desc')
                ->with('userinfo')
                ->append(['status_name'])
                ->hasWhere('userinfo', [['is_del', '=', 0]])
                ->where([['SysUserRole.site_id', '=', $site_id]])
                ->select()
                ->toArray();
        
        $goodsModel = new GoodsModel();
        $orderModel = new Order();
        $result_list = array(
            "yg_count"=>count($list),
            "rk_amount"=>0,
            "zk_amount"=>0,
            "xs_amount"=>0,
            "rk_count"=>0,
            "zk_count"=>0,
            "xs_count"=>0,
        );
        foreach ($list as $key => $val) {
            $uid = $val['uid'];
            // 根据type参数设置不同的查询条件
            switch ($type) {
                case 1:
                    // type==1时用户uid==商品表recycling_uid
                    $where = [['site_id', '=', $site_id], ['recycling_uid', '=', $uid]];
                    break;
                case 2:
                    // type==2时用户uid==商品表create_uid
                    $where = [['site_id', '=', $site_id], ['create_uid', '=', $uid]];
                    break;
                case 3:
                    // type==3时用户uid==商品表appraiser_uid
                    $where = [['site_id', '=', $site_id], ['appraiser_uid', '=', $uid]];
                    break;
                default:
                    $where = [['site_id', '=', $site_id]];
            }

            if(!empty($params['start_time']) && !empty($params['end_time'])){
                $where[] = ['create_time', '>=', $params['start_time']];
                $where[] = ['create_time', '<=', $params['end_time']];
            }
            return success($where);
            // 查询该用户相关的商品
            $userGoods = $goodsModel->where($where)->select()->toArray();
            $orderInfo = $orderModel->where($where)->select()->toArray();
            $totalStock = 0;
            $totalAmount = 0;
            if(!empty($userGoods)){
                foreach ($userGoods as $goods) {
                    $totalStock += $goods['stock'];
                    // 获取商品属性列表
                    if (!empty($goods['goods_attr_list'])) {
                        $goodsAttrList = json_decode($goods['goods_attr_list'], true);
                        foreach ($goodsAttrList as $attr) {
                            $totalAmount += $attr['price'] * $attr['goods_num'];
                        }
                    }
                }
            }
            $orderTotalStock = 0;
            $orderTotalAmount = 0;
            if(!empty($orderInfo)){
                foreach ($orderInfo as $order) {
                    $orderTotalAmount += $order['money'];
                    $orderTotalStock += $order['goods_num'];
                }
            }
            
            // 将统计信息添加到用户数据中
            $list[$key]['zk_total_stock'] = $totalStock;
            $list[$key]['zk_total_amount'] = $totalAmount;
            $list[$key]['zk_goods_count'] = count($userGoods);

            $list[$key]['xs_order_total_stock'] = $orderTotalStock;
            $list[$key]['xs_order_total_amount'] = $orderTotalAmount;
            $list[$key]['xs_order_count'] = count($orderInfo);

            $list[$key]['rk_stock'] = $orderTotalStock+$totalStock;
            $list[$key]['rk_total_amount'] = $orderTotalAmount+$totalAmount;
            $list[$key]['rk_count'] = count($orderInfo)+count($userGoods);

            $result_list['rk_amount'] += $list[$key]['rk_total_amount'];
            $result_list['zk_amount'] += $list[$key]['zk_total_amount'];
            $result_list['xs_amount'] += $list[$key]['xs_order_total_amount'];
            $result_list['rk_count'] += $list[$key]['rk_count'];
            $result_list['zk_count'] += $list[$key]['zk_goods_count'];
            $result_list['xs_count'] += $list[$key]['xs_order_count'];
        }
        $result_list['list'] = $list;
        return success($result_list);
    }

    
    /**
      * 根据时间区间统计商品金额和库存占比
      * @param $data
      * @return think\Response
      */
    public function getGoodsLong($data){
        $site_id = $data['site_id'];
        $date_type = $data['date_type'];
        
        // 获取该站点下的所有商品
        $model = new GoodsModel();
        $goodsList = $model->where('site_id', $site_id)->select()->toArray();
        
        // 初始化统计结果
        $totalAmount = 0; // 总金额
        $totalStock = 0; // 总库存
        $timeRangeData = [];
        
        // 根据date_type设置时间区间
        $timeRanges = [];
        if ($date_type == 1) {
            // 按天统计
            $timeRanges = [
                ['name' => 'less_15days', 'label' => '<15天', 'days' => 15, 'min' => 0, 'max' => 15],
                ['name' => '15_30days', 'label' => '15-30天', 'days' => 30, 'min' => 15, 'max' => 30],
                ['name' => '30_60days', 'label' => '30-60天', 'days' => 60, 'min' => 30, 'max' => 60],
                ['name' => 'more_60days', 'label' => '>60天', 'days' => 60, 'min' => 60, 'max' => PHP_INT_MAX]
            ];
        } elseif ($date_type == 2) {
            // 按月统计
            $timeRanges = [
                ['name' => 'less_3months', 'label' => '<3个月', 'days' => 90, 'min' => 0, 'max' => 90],
                ['name' => '3_6months', 'label' => '3-6个月', 'days' => 180, 'min' => 90, 'max' => 180],
                ['name' => '6_12months', 'label' => '6-12个月', 'days' => 365, 'min' => 180, 'max' => 365],
                ['name' => 'more_12months', 'label' => '>12个月', 'days' => 365, 'min' => 365, 'max' => PHP_INT_MAX]
            ];
        } elseif ($date_type == 3) {
            // 按年统计
            $timeRanges = [
                ['name' => 'less_1year', 'label' => '<1年', 'days' => 365, 'min' => 0, 'max' => 365],
                ['name' => '1_2years', 'label' => '1-2年', 'days' => 730, 'min' => 365, 'max' => 730],
                ['name' => '2_3years', 'label' => '2-3年', 'days' => 1095, 'min' => 730, 'max' => 1095],
                ['name' => 'more_3years', 'label' => '>3年', 'days' => 1095, 'min' => 1095, 'max' => PHP_INT_MAX]
            ];
        }
        
        // 初始化每个时间区间的数据
        foreach ($timeRanges as $range) {
            $timeRangeData[$range['name']] = [
                'label' => $range['label'],
                'amount' => 0,
                'stock' => 0,
                'amount_ratio' => 0,
                'stock_ratio' => 0
            ];
        }
        
        // 计算每个商品的金额和库存，并按时间区间分组
        foreach ($goodsList as $goods) {
            // 计算商品金额
            $goodsAmount = 0;
            if (!empty($goods['goods_attr_list'])) {
                $goodsAttrList = json_decode($goods['goods_attr_list'], true);
                if (is_array($goodsAttrList)) {
                    foreach ($goodsAttrList as $attr) {
                        $price = $attr['price'] ?? 0;
                        $goodsNum = $attr['goods_num'] ?? 0;
                        $goodsAmount += $price * $goodsNum;
                    }
                }
            }
            
            // 获取商品库存
            $goodsStock = $goods['stock'] ?? 0;
            
            // 累加到总金额和总库存
            $totalAmount += $goodsAmount;
            $totalStock += $goodsStock;
            
            // 计算商品创建时间到现在的天数
            $createTime = strtotime($goods['create_time']);
            $now = time();
            $days = floor(($now - $createTime) / (24 * 3600));
            
            // 按时间区间分组
            foreach ($timeRanges as $range) {
                if ($days > $range['min'] && $days <= $range['max']) {
                    $timeRangeData[$range['name']]['amount'] += $goodsAmount;
                    $timeRangeData[$range['name']]['stock'] += $goodsStock;
                    break;
                }
            }
        }
        
        // 计算每个时间区间的占比
        foreach ($timeRangeData as $key => &$data) {
            $data['amount_ratio'] = $totalAmount > 0 ? round(($data['amount'] / $totalAmount) * 100, 2) : 0;
            $data['stock_ratio'] = $totalStock > 0 ? round(($data['stock'] / $totalStock) * 100, 2) : 0;
        }
        
        // 将金额转换为万单位并保留两位小数
        $totalAmountWan = round($totalAmount / 10000, 2);
        foreach ($timeRangeData as $key => &$data) {
            $data['amount'] = round($data['amount'] / 10000, 2);
        }
        
        $result_list = [];
        foreach ($timeRangeData as $key => &$data) {
            $result_list[] = $data;
        }

        // 构建最终结果
        $result = [
            'total_amount' => $totalAmountWan,
            'total_stock' => $totalStock,
            'time_range_data' => $result_list
        ];
        
        return success($result);
    }
}
