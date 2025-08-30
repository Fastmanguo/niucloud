<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/7 1:20
// +----------------------------------------------------------------------

namespace addon\online_expo\app\service;

use addon\saler_tools\app\common\BaseAdminService;
use addon\online_expo\app\model\Goods as GoodsModel;
use addon\saler_tools\app\service\shop\ShopService;
use think\facade\Log;

/**
 *
 * Class GoodsService
 * @package addon\online_expo\app\service
 */
class GoodsService extends BaseAdminService
{


    public function lists($data, $order = ['goods_id' => 'desc'])
    {
        $where = [
            ['is_online_expo', '=', 1],
            ['is_sale', '=', 1]
        ];

        if (empty($data['is_all']) && empty($data['site_id'])) { // 查询本地商品
            $shop    = (new ShopService())->info();
            $where[] = [
                'country_code', '=', $shop['country_code'] ?? ''
            ];
        }

        $model = new GoodsModel();

        $field = 'goods_id,site_id,goods_cover,goods_video,goods_image,condition,detail_image,category_id,goods_name,goods_desc,goods_attribute
        ,goods_attachment,brand_id,series_id,model_id,peer_price,update_time,currency_code';

        $model = $model->where($where)->withSearch(['category_id', 'search', 'site_id', 'brand_id'], $data)
            ->field($field)
            ->order($order);

        $result = $this->pageQuery($model);

        // TODO： 处理收藏字段

        return success($result);
    }


    public function detail($goods_id)
    {
        $model = new GoodsModel();

        $field = 'goods_id,site_id,goods_cover,goods_video,condition,goods_image,detail_image,category_id,goods_name,goods_desc,goods_attribute,goods_attachment
        ,brand_id,series_id,model_id,peer_price,is_sale,update_time,currency_code';

        $goods = $model->where('is_online_expo', 1)
            ->where('goods_id', $goods_id)
            ->with(['brand', 'series', 'model', 'goods_attr'])
            ->field($field)
            ->findOrEmpty();

        if ($goods->isEmpty()) {
            return fail('goods_sold_out');
        }

        if ($goods['is_sale'] == 0) {
            return fail('goods_sold_out');
        }

        StatService::setLog($goods->site_id, $this->uid, $goods_id, 1);

        return success($goods->toArray());

    }

}
