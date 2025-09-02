<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/7 1:20
// +----------------------------------------------------------------------

namespace addon\online_expo\app\service;

use addon\saler_tools\app\common\BaseAdminService;
use addon\online_expo\app\model\Goods as GoodsModel;
use addon\saler_tools\app\service\shop\ShopService;
use app\model\sys\SysUser;
use think\facade\Log;

/**
 *
 * Class GoodsService
 * @package addon\online_expo\app\service
 */
class GoodsService extends BaseAdminService
{


    public function lists($data, $order = ['goods_id' => 'desc'])
    {
        $where = [
            ['is_online_expo', '=', 1],
            ['is_sale', '=', 1]
        ];

        if (empty($data['is_all']) && empty($data['site_id'])) { // 查询本地商品
            $shop    = (new ShopService())->info();
            $where[] = [
                'country_code', '=', $shop['country_code'] ?? ''
            ];
        }

        $model = new GoodsModel();

        $field = 'goods_id,site_id,goods_cover,goods_video,goods_image,condition,detail_image,category_id,goods_name,goods_desc,goods_attribute
        ,goods_attachment,brand_id,series_id,model_id,peer_price,update_time,currency_code';

        $model = $model->where($where)->withSearch(['category_id', 'search', 'site_id', 'brand_id'], $data)
            ->field($field)
            ->order($order);

        $result = $this->pageQuery($model);

        // TODO： 处理收藏字段

        return success($result);
    }


    public function detail($goods_id)
    {
        $model = new GoodsModel();

        $field = 'goods_id,site_id,goods_cover,goods_video,condition,goods_image,detail_image,category_id,goods_name,goods_desc,goods_attribute,goods_attachment
        ,brand_id,series_id,model_id,peer_price,is_sale,update_time,currency_code,country_code';

        $goods = $model->where('is_online_expo', 1)
            ->where('goods_id', $goods_id)
            ->with(['brand', 'series', 'model', 'goods_attr'])
            ->field($field)
            ->findOrEmpty();

        if ($goods->isEmpty()) {
            return fail('goods_sold_out');
        }

        if ($goods['is_sale'] == 0) {
            return fail('goods_sold_out');
        }

        StatService::setLog($goods->site_id, $this->uid, $goods_id, 1);

        #获取当前登录用户信息
        $user   = (new SysUser())->where('uid', $this->uid)->field("last_ip")->findOrEmpty();
        $goods['currency_info'] = $this->getLocationByIP( $user['last_ip']);

        # 金额 货币类型
        $money = $goods['peer_price'];
        $currency_code = $goods['currency_code'];

        $money_result_list = [
            ['address'=>'CN','id'=>'CNY','name'=>'人民币',"monery"=>$this->convertCurrency($money,$currency_code,'CNY')],
            ['address'=>'US','id'=>'USD','name'=>'美元',"monery"=>$this->convertCurrency($money,$currency_code,'USD')],
            ['address'=>'EU','id'=>'EUR','name'=>'欧元',"monery"=>$this->convertCurrency($money,$currency_code,'EUR')],
            ['address'=>'JP','id'=>'JPY','name'=>'日元',"monery"=>$this->convertCurrency($money,$currency_code,'JPY')],
            ['address'=>'GB','id'=>'GBP','name'=>'英镑',"monery"=>$this->convertCurrency($money,$currency_code,'GBP')],
            ['address'=>'HK','id'=>'HKD','name'=>'港币',"monery"=>$this->convertCurrency($money,$currency_code,'HKD')],
            ['address'=>'KR','id'=>'KRW','name'=>'韩元',"monery"=>$this->convertCurrency($money,$currency_code,'KRW')],
            ['address'=>'SG','id'=>'SGD','name'=>'新加坡元',"monery"=>$this->convertCurrency($money,$currency_code,'SGD')],
            ['address'=>'AU','id'=>'AUD','name'=>'澳元',"monery"=>$this->convertCurrency($money,$currency_code,'AUD')],
            ['address'=>'CA','id'=>'CAD','name'=>'加拿大元',"monery"=>$this->convertCurrency($money,$currency_code,'CAD')]
        ];

        $goods['money_result_list'] = $money_result_list;

        return success($goods->toArray());

    }




    #根据ip获取国家信息
    public function getLocationByIP($ip ) {

        // 检查是否为私有IP地址
        $isPrivateIP = false;
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE) === false) {
            $isPrivateIP = true;
        }

        // 如果是私有IP或本地IP，使用公共IP进行查询
        if ($isPrivateIP || in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            // 尝试获取真实的外网IP
            $publicIP = $this->getPublicIP();
            if ($publicIP) {
                $ip = $publicIP;
            } else {
                // 如果无法获取公网IP，使用默认IP
                return "";
            }
        }



        $url = "http://ip-api.com/json/{$ip}?fields=status,message,country,countryCode,region,regionName,city,zip,lat,lon,timezone,currency,currencyCode";

        $response = $this->makeRequest($url);
        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return $response['currency'];
        }

        return "";
    }

    private function makeRequest($url) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'user_agent' => 'IPCurrencyConverter/1.0'
            ]
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return null;
        }

        return json_decode($response, true);
    }

    #货币转换
    function convertCurrency($amount, $from_currency, $to_currency) {
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


        if (!is_numeric($amount) || $amount <= 0) {
            return 0;
        }
        $rate = $exchange_rates[$from_currency][$to_currency];
        $result = $amount * $rate;
        // 格式化输出
        $formatted_result = number_format($result, 2);

        return $formatted_result;
    }
}
