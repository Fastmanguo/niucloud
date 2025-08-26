<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/17 4:45
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\SalerToolsDict;
use think\facade\Cache;

/**
 *
 * Class SiteCustomService
 * @package addon\saler_tools\app\service
 */
class SiteCustomService extends BaseAdminService
{

    const DICT_TYPE_COST = 'cost';


    /**
     * 获取站点成本字典
     */
    public function getCostDict()
    {
        return cache_remember($this->app->siteCache->cost_cache_key, function () {

            $model = new SalerToolsDict();
            $list  = $model->where('site_id', $this->site_id)
                ->where('type', self::DICT_TYPE_COST)
                ->order('sort asc,id desc')
                ->field('id,name,sort')
                ->select()->toArray();

            return success($list);

        }, $this->app->siteCache->tag);

    }


    /**
     * 新增站点成本
     */
    public function addCost($data)
    {

        $model = new SalerToolsDict();

        // 检测是否存在
        $count = $model->where('site_id', $this->site_id)
            ->where('type', self::DICT_TYPE_COST)
            ->where('name', $data['name'])
            ->count();

        if ($count > 0) return fail('成本项已存在');

        $data['site_id'] = $this->site_id;
        $data['type']   = self::DICT_TYPE_COST;

        $model->create($data);

        Cache::delete($this->app->siteCache->cost_cache_key);

        return success();
    }


    /**
     * 修改成本排序
     */
    public function editCostSort($id)
    {

    }


}
