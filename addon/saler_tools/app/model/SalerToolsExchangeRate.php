<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/7 22:12
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;
use think\helper\Str;

/**
 *
 * Class SalerToolsExchangeRate
 * @package addon\saler_tools\app\model
 */
class SalerToolsExchangeRate extends BaseModel
{

    protected $name = 'saler_tools_exchange_rate';

    protected $pk = ['base_currency', 'target_currency'];


    public function searchBaseCurrencyAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('base_currency', Str::upper($value));
    }


    public function searchTargetCurrencyAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('target_currency', Str::upper($value));
    }



}
