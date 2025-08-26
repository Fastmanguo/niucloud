<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/12 4:05
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\SalerToolsGoodsSeries;

/**
 *
 * Class GoodsSeriesService
 * @package addon\saler_tools\app\service
 */
class GoodsSeriesService extends BaseAdminService
{

    public function lists($params)
    {

        $model = new SalerToolsGoodsSeries();

        $model = $model->withSearch(['series_name', 'brand_id'], $params)
            ->order('series_name asc,series_id asc');

        return success($this->pageQuery($model));

    }


    public function list($params)
    {
        $key = $this->app->appCache->goods_series . '_' . serialize(func_get_args());

        return cache_remember($key, function () use ($params) {
            $model = new SalerToolsGoodsSeries();
            $list  = $model->withSearch(['read_site_id'], ['read_site_id' => $this->site_id])
                ->withSearch(['brand_id', 'category_key','series_name'], $params)
                ->field('series_name,series_id,brand_id')
                ->order('series_name asc,series_id asc')
                ->select();

            $list  = $list->toArray();

            return success($list);

            }, $this->app->appCache->app_tag);

    }


    public function add($data)
    {
        $data['site_id'] = $this->site_id;
        $model           = new SalerToolsGoodsSeries();
        $model->create($data);
        return success();
    }

    public function edit($data)
    {
        $model  = new SalerToolsGoodsSeries();
        $series = $model->where('series_id', $data['series_id'])->where('site_id', $this->site_id)->findOrEmpty();
        if ($series->isEmpty()) return fail('系列不存在');
        $series->save($data);
        return success();

    }


    public function del($series_id)
    {
        $model  = new SalerToolsGoodsSeries();
        $series = $model->where('series_id', $series_id)->where('site_id', $this->site_id)->findOrEmpty();
        if ($series->isEmpty()) return fail('系列不存在');
        $series->delete();
        return success();
    }


}
