<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/12 4:02
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\SalerToolsGoodsBrand;

/**
 *
 * Class GoodsBrandService
 * @package addon\saler_tools\app\service
 */
class GoodsBrandService extends BaseAdminService
{

    public function lists($data)
    {
        $brand_model = new SalerToolsGoodsBrand();

        $brand_model = $brand_model->withSearch(['brand_name'], $data)
            ->withCount(['series', 'brandModel'])
            ->order('letter asc,sort asc,brand_id desc');

        return success($this->pageQuery($brand_model));
    }


    public function list()
    {
        // 临时禁用缓存进行测试
//        return cache_remember($this->app->appCache->goods_brand, function () {
        return cache_remember($this->app->appCache->goods_brand . '_' . $this->site_id, function () {

            $model = new SalerToolsGoodsBrand();

            $list = $model->withSearch(['read_site_id'], ['read_site_id' => $this->site_id])
                ->field('brand_id,brand_name,brand_en,letter_en,letter,pinyin,logo')
                ->order('letter asc, brand_id asc')
                ->select()->each(function ($item) {
                    $item->show_name = $item->brand_name . '/' . $item->brand_en;
                });

            $list = $list->toArray();

            return success($list);

        }, $this->app->appCache->app_tag);
    }


    public function add($data)
    {
        $data['site_id'] = $this->site_id;
        $brand_model     = new SalerToolsGoodsBrand();
        $brand_model->create($data);
        return success();
    }

    public function edit($data)
    {
        $brand_model = new SalerToolsGoodsBrand();
        $brand       = $brand_model->where('brand_id', $data['brand_id'])->where('site_id', $this->site_id)->findOrEmpty();
        if ($brand->isEmpty()) return fail('品牌不存在');
        $brand->save($data);
        return success();

    }


    public function del($brand_id)
    {
        $brand_model = new SalerToolsGoodsBrand();
        $brand       = $brand_model->where('brand_id', $brand_id)->where('site_id', $this->site_id)->findOrEmpty();
        if ($brand->isEmpty()) return fail('品牌不存在');
        $brand->delete();
        return success();
    }

}
