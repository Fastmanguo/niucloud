<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/25 19:49
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\goodsPool;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\GoodsPool as GoodsPoolModel;
/**
 * 公共商品数据库
 * Class GoodsPoolService
 * @package addon\saler_tools\app\service\goodsPool
 */
class GoodsPoolService extends BaseAdminService
{

    public function lists($params)
    {
        $goods_pool_model = new GoodsPoolModel();
        $model = $goods_pool_model->where('status',1)
            ->with(['brand','series','model'])
            ->withoutField(['attr_data'])
            ->withSearch(['brand_id','series_id','model_id','search'],$params);

        return success($this->pageQuery($model));
    }


    public function detail($id)
    {
        $goods_pool_model = new GoodsPoolModel();
        $goods = $goods_pool_model->with(['brand','series','model'])->where('status',1)->where('id',$id)->findOrEmpty();
        if ($goods->isEmpty()) return fail();
        return success($goods->toArray());
    }

    public function save($data)
    {
        $goods_pool_model = new GoodsPoolModel();

        if (!empty($data['id'])){
            $goods = $goods_pool_model->where('id',$data['id'])->findOrEmpty();
            $goods->save($data);
        }else{
            $goods_pool_model->create($data);
        }

        return success();
    }


    public function del($data)
    {

        $goods_pool_model = new GoodsPoolModel();

        if (!empty($data['id'])){
            $goods_pool_model->where('id',$data['id'])->delete();
        }else{
            $goods_pool_model->whereIn('id',$data['ids'])->delete();
        }

        return success();

    }

}
