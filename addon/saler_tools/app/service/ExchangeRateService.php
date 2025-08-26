<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/7 22:16
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\common\HttpClient;
use addon\saler_tools\app\dict\sys\SystemDict;
use addon\saler_tools\app\model\SalerToolsExchangeRate;
use app\service\core\sys\CoreConfigService;
use think\facade\Cache;

/**
 *
 * Class ExchangeRateService
 * @package addon\saler_tools\app\service
 */
class ExchangeRateService extends BaseAdminService
{

    // 需要更新的货币类型
    const CURRENCY_TYPE = [
        'CNY' => 'CNY', // 人民币
        'USD' => 'USD', // 美元
        'HKD' => 'HKD', // 港币
        'EUR' => 'EUR', // 欧元
        'JPY' => 'JPY', // 日元
        'GBP' => 'GBP', // 英镑
        'AUD' => 'AUD', // 澳元
        'RUB' => 'RUB', // 俄罗斯卢布
        'CAD' => 'CAD', // 加拿大元
        'CHF' => 'CHF', // 瑞士法郎
        'DKK' => 'DKK', // 丹麦克朗
        'NOK' => 'NOK', // 挪威克朗
        'SEK' => 'SEK', // 瑞典克朗
        'NZD' => 'NZD', // 新西兰元
        'SGD' => 'SGD', // 新加坡元
        'THB' => 'THB', // 泰铢
        'BRL' => 'BRL', // 巴西雷亚尔
        'MXN' => 'MXN', // 墨西哥元
    ];

    // 使用刷新的key
    const FLUSH_KEY = 'ac3b4df83e66a188c92409ee';

    const CACHE_TAG = 'SALER_TOOLS_EXCHANGE_RATE_TAG';

    const CACHE_KEY = 'SALER_TOOLS_EXCHANGE_RATE:';

    public function flush()
    {
        foreach (self::CURRENCY_TYPE as $base_currency) {
            $url = "https://v6.exchangerate-api.com/v6/" . self::FLUSH_KEY . "/latest/{$base_currency}";
            $res = HttpClient::getJson($url);
            if ($res['result'] == 'success') {
                $this->pushRate($base_currency, $res['conversion_rates']);
            }
        }
        Cache::tag(self::CACHE_TAG)->clear();
        return success();
    }

    public function pushRate($base_currency, $data)
    {
        $array_key = array_keys(self::CURRENCY_TYPE);
        SalerToolsExchangeRate::where('base_currency', $base_currency)->delete();
        $inset_data  = [];
        $update_time = date('Y-m-d H:i:s');
        foreach ($data as $currency_type => $rate) {

            if ($currency_type == $base_currency) continue;
            if (!in_array($currency_type, $array_key)) continue;

            $inset_data[] = [
                'base_currency'   => $base_currency,
                'target_currency' => $currency_type,
                'exchange_rate'   => $rate,
                'update_time'     => $update_time
            ];
        }

        SalerToolsExchangeRate::insertAll($inset_data);
    }

    public function lists($params)
    {
        $model = new SalerToolsExchangeRate();
        $model = $model->withSearch(['base_currency', 'base_currency'], $params)
            ->order('base_currency', 'asc')
            ->order('target_currency', 'asc');

        return success($this->pageQuery($model));
    }

    public function list($base_currency)
    {
        $key = self::CACHE_KEY . $base_currency;
        return cache_remember($key, function () use ($base_currency) {
            $model = new SalerToolsExchangeRate();
            $list  = $model->where('base_currency', $base_currency)
                ->order('target_currency', 'asc')
                ->select();

            return success($list);

        }, self::CACHE_TAG);
    }

    public function typeList()
    {
        $res = (new CoreConfigService())->getConfigValue(0, 'EXCHANGE_RATE_TYPE');

        if (empty($res)) {
            foreach (self::CURRENCY_TYPE as $key => $value) {
                $res[] = [
                    'key'    => $key,
                    'name'   => $value,
                    'symbol' => ''
                ];
            }
        }

        return $res;
    }


    public function typeModify($data)
    {
        (new CoreConfigService())->setConfig(0, 'EXCHANGE_RATE_TYPE', $data['data']);
        return success();
    }

}
