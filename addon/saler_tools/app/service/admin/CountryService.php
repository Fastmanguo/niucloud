<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/25 14:01
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\admin;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\Country as CountryModel;
use addon\saler_tools\app\model\SalerToolsGoodsTemplate;
use think\facade\Cache;

/**
 *
 * Class CountryService
 * @package addon\saler_tools\app\service\admin
 */
class CountryService extends BaseAdminService
{

    public function list()
    {
        $model = new CountryModel();
        $list  = $model->order('sort', 'desc')->select()->toArray();
        return success($list);
    }

    public function save($data)
    {
        $model = new CountryModel();

        $country = $model->where('country_code', $data['country_code'])->findOrEmpty();

        if ($country->isEmpty()) {
            $model->create($data);
        } else {
            $country->save($data);
        }
        Cache::delete(app()->appCache->sys('country_list_key'));
        return success();
    }

    /**
     * 获取启用的国家列表
     */
    public function getList()
    {
        $key = app()->appCache->sys('country_list_key');
        return cache_remember($key, function () {
            $model = new CountryModel();
            return $model->where('status', 1)
                ->order('sort', 'desc')
                ->select()
                ->toArray();
        });
    }

}
