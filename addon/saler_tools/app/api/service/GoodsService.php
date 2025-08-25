<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/12 21:36
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\api\service;

use addon\saler_tools\app\common\BaseApiService;
use addon\saler_tools\app\model\Goods as GoodsModel;

/**
 *
 * Class GoodsService
 * @package addon\saler_tools\app\api\service
 */
class GoodsService extends BaseApiService
{

    public function lists($params)
    {
        $goods_model = new GoodsModel();

        $model = $goods_model->withSearch(['search', 'brand_id', 'series_id', 'model_id', 'store_id', 'category_id'], $params)
            ->with(['brand', 'series', 'model', 'store', 'appraiserName', 'createName', 'recyclingName', 'updateName'])
            ->where('site_id', $this->site_id)
            ->where('deleted_time', 0)
            ->where('is_sale', 1)
            ->order('goods_id', 'desc')
            ->hidden(['is_locked']);

        return success($this->pageQuery($model));

    }


    public function detail($goods_id)
    {
        $goods_model = new GoodsModel();

        $goods = $goods_model->where('goods_id', $goods_id)->where('site_id', $this->site_id)
            ->with(['brand', 'series', 'model', 'store', 'appraiserName', 'createName', 'recyclingName', 'updateName'])
            ->hidden(['is_locked'])->findOrEmpty();

        if ($goods->isEmpty()) return fail('goods_not_found');

        return success($goods->toArray());

    }

}
