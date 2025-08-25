<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/18 23:57
// +----------------------------------------------------------------------

namespace addon\online_expo\app\service;

use addon\saler_tools\app\common\BaseAdminService;
use addon\online_expo\app\model\GoodsEnquiry as GoodsEnquiryModel;
use addon\online_expo\app\model\Goods as GoodsModel;

/**
 *
 * Class GoodsEnquiryService
 * @package addon\online_expo\app\service
 */
class GoodsEnquiryService extends BaseAdminService
{

    public function lists($params)
    {

        $type = $params['type'] ?? 1;

        $model = new GoodsEnquiryModel();

        $with = [];

        if ($type == 1) {
            $model  = $model->where('site_id', $this->site_id);
            $with[] = 'create_names';
            $with[] = 're_shop_names';
        } else {
            $model  = $model->where('re_site_id', $this->site_id);
            $with[] = 're_create_names';
            $with[] = 'shop_names';
        }

        $model = $model->with($with)->withSearch(['status'], $params);

        return success($this->pageQuery($model));

    }

    /**
     * 发出出价
     */
    public function postBid($data)
    {
        $goods_id = $data['goods_id'];
        $money    = $data['money'];

        // 必要条件
        $where = [
            ['is_online_expo', '=', 1],
            ['is_sale', '=', 1],
            ['deleted_time', '=', 0],
            ['goods_id', '=', $goods_id],
        ];
        // 检查商品状态
        $goods = (new GoodsModel())->where($where)->findOrEmpty();

        if ($goods->isEmpty()) {
            return fail('goods_sold_out');
        }

        // 创建询价
        $model = new GoodsEnquiryModel();

        $model->save([
            'site_id'     => $this->site_id,
            're_site_id'  => $goods['site_id'],
            'goods_id'    => $goods_id,
            'shop_name'   => $goods['shop_name'],
            'goods_cover' => $goods['goods_cover'],
            'goods_price' => $goods['peer_price'],
            'money'       => $money,
            'uid'         => $this->uid,
            're_uid'      => 0,
            'status'      => 0,
        ]);

        return success();
    }

    /**
     * 回应询价
     */
    public function reBid($data)
    {
        $id = $data['id'];
        $status   = $data['status'];

        $model = new GoodsEnquiryModel();

        $goods = $model->where('id', $id)->where('re_site_id', $this->site_id)->findOrEmpty();

        if ($goods->isEmpty()) {
            return fail();
        }

        $goods->save(['status' => $status, 're_uid' => $this->uid]);

        return success();
    }

}
