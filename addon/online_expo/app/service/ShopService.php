<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/23 19:34
// +----------------------------------------------------------------------

namespace addon\online_expo\app\service;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\Shop as shopModel;

/**
 * 店铺服务层
 * Class ShopService
 * @package addon\online_expo\app\service
 */
class ShopService extends BaseAdminService
{

    /**
     * 线上展会店铺搜索
     */
    public function lists($params)
    {
        $shop_model = new ShopModel();
        $where      = [];

        if (empty($params['is_all'])) {
            $shop    = (new \addon\saler_tools\app\service\shop\ShopService())->info();
            $where[] = [
                'country_code', '=', $shop['country_code']
            ];
        }else if (!empty($params['country_code'])){
            $where[] = [
                'country_code', '=', $params['country_code']
            ];
        }


        if (!empty($params['search'])) {
            $search  = $shop_model->handelSpecialCharacter($params['search']);
            $where[] = ['shop_name', 'like', '%' . $search . '%'];
        }
        $fields = 'site_id,shop_name,logo,desc,tel,mobile,address,currency_code,country_code';
        $model  = $shop_model->field($fields)->where($where)->with('country');
        return success($this->pageQuery($model, function ($item) {
            $item['country_name'] = $item['country']['country_name'] ?? '';
            unset($item['country']);
            return $item;
        }));
    }
}
