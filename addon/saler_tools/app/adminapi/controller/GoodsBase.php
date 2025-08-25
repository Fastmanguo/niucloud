<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/14 19:17
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\model\SalerToolsGoodsCategory;
use addon\saler_tools\app\service\dict\SiteDictService;
use addon\saler_tools\app\service\GoodsBrandService;
use addon\saler_tools\app\service\GoodsCategoryService;
use addon\saler_tools\app\service\GoodsModelService;
use addon\saler_tools\app\service\GoodsSeriesService;
use addon\saler_tools\app\service\SiteCustomService;

/**
 *
 * Class GoodsBase
 * @package addon\saler_tools\app\adminapi\controller
 */
class GoodsBase extends BaseAdminController
{

    public function category()
    {
        return app(GoodsCategoryService::class)->list();
    }


    public function brand()
    {
        return app(GoodsBrandService::class)->list();
    }


    public function series()
    {
        $params = $this->_vali([
            'brand_id.query'    => '',
            'category_id.query' => '',
            'series_name.query' => ''
        ]);

        return app(GoodsSeriesService::class)->list($params);

    }

    public function seriesLists()
    {
        $params = $this->_vali([
            'brand_id.query'    => '',
            'category_id.query' => '',
            'series_name.query' => ''
        ]);

        return app(GoodsSeriesService::class)->lists($params);

    }


    public function model()
    {
        $params = $this->_vali([
            'brand_id.query'   => '',
            'series_id.query'  => '',
            'model_name.query' => ''
        ]);

        return app(GoodsModelService::class)->list($params);

    }


    public function modelLists()
    {
        $params = $this->_vali([
            'brand_id.query'   => '',
            'series_id.query'  => '',
            'model_name.query' => ''
        ]);

        return app(GoodsModelService::class)->lists($params);

    }


    /**
     * @param $data
     */
    public function dataList($key)
    {
        return app(SiteDictService::class)->getList($key);
    }


    public function dataAdd($key)
    {
        $data = $this->_vali([
            'value.require' => '值不能为空',
        ]);
        return app(SiteDictService::class)->push($key, $data['value']);
    }


    public function dataEdit($key)
    {
        $value = $this->_vali([
            'id.require'    => 'select_edit_data',
            'value.require' => 'check_form_data',
        ]);
        return app(SiteDictService::class)->edit($key, $value);
    }

    public function dataDel()
    {
        $data = $this->_vali([
            'id.require'  => 'select_edit_data',
            'key.require' => 'check_form_data',
        ]);

        return app(SiteDictService::class)->del($data['key'], $data['id']);
    }

    public function dataSort()
    {
        $data = $this->_vali([
            'key.require'  => 'error',
            'list.require' => 'error',
        ]);
        return app(SiteDictService::class)->sort($data['key'], $data['list']);
    }

}
