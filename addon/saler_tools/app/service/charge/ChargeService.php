<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/21 23:32
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\charge;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\Charge as ChargeModel;
use core\exception\AdminException;

/**
 *
 * Class ChargeService
 * @package addon\saler_tools\app\service\charge
 */
class ChargeService extends BaseAdminService
{


    const CHARGE_TYPE_LIST = [
        [
            'name' => '基础费用',
            'key'  => 'site_base'
        ],
        [
            'name' => '会员商城',
            'key'  => 'shop'
        ],
        [
            'name' => '拍卖会员',
            'key'  => 'auction'
        ],
    ];


    /**
     * 获取付款类型
     */
    public function getTypeList()
    {
        return self::CHARGE_TYPE_LIST;
    }

    public function list($data)
    {
        $charge_model = new ChargeModel();
        $model        = $charge_model->where('charge_type', $data['charge_type'])->order('sort desc');
        return success($model->select()->toArray());
    }


    public function add($data)
    {
        $charge_model = new ChargeModel();
        $charge_model->save($data);
        return success();
    }


    public function edit($data)
    {
        $charge_model = new ChargeModel();
        $charge       = $charge_model->where('id', $data['id'])->findOrEmpty();
        if ($charge->isEmpty()) throw new AdminException('数据不存在');
        $charge_model->withoutField(['charge_type'])->save($data);
        return success();
    }


    public function getPriceList($key)
    {
        $charge_model = new ChargeModel();
        $file         = ['name', 'currency_code', 'price', 'market_price', 'valid_time', 'desc'];
        $lang         = app()->appLang;
        $model        = $charge_model->where('charge_type', $key)->field($file)->where('lang', $lang)->order('sort desc');
        return success($model->select()->toArray());
    }


}
