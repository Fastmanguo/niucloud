<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/12 3:46
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\SalerToolsGoodsModel;

/**
 * 型号管理
 * Class GoodsModelService
 * @package addon\saler_tools\app\service
 */
class GoodsModelService extends BaseAdminService
{

    public function lists($params)
    {
        $model = new SalerToolsGoodsModel();

        $model = $model->withSearch(['model_name','series_id','brand_id'], $params)
            ->with(['brand', 'series'])
            ->order('model_name asc, model_id desc');

        return success($this->pageQuery($model));

    }


    public function list($params)
    {
        $key = $this->app->appCache->goods_model . '_' . serialize($params);

        return cache_remember($key, function () use ($params) {

            $model = new SalerToolsGoodsModel();

            $list  = $model->withSearch(['brand_id', 'series_id','model_name','read_site_id'], $params)
                ->field('series_id,brand_id,model_name,model_cover,model_image,attr_data')
                ->order('model_name asc')
                ->select();

            $list  = $list->toArray();

            return success($list);

        }, $this->app->appCache->app_tag);

    }



    public function add($data)
    {
        $data['site_id'] = $this->site_id;
        $model           = new SalerToolsGoodsModel();
        $model->create($data);
        return success();
    }


    public function edit($data)
    {
        $model = new SalerToolsGoodsModel();

        $model = $model->where('site_id', $this->site_id)->where('model_id', $data['model_id'])->findOrEmpty();

        if ($model->isEmpty()) return fail('数据不存在');

        $model->save($data);

        return success();
    }


    public function del($model_id)
    {
        $model = new SalerToolsGoodsModel();

        $model = $model->where('site_id', $this->site_id)
            ->where('model_id', $model_id)
            ->findOrEmpty();

        if ($model->isEmpty()) return fail('数据不存在');

        $model->delete();

        return success();
    }

}
