<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/6/1 4:49
// +----------------------------------------------------------------------

namespace addon\online_expo\app\service;

use addon\online_expo\app\model\Record as RecordModel;
use addon\saler_tools\app\common\BaseAdminService;
use think\db\Query;

/**
 * 收藏服务
 * Class RecordService
 * @package addon\online_expo\app\model
 */
class RecordService extends BaseAdminService
{

    public function lists($data)
    {
        $where = [
            ['uid', '=', $this->uid],
            ['type ', '=', $data['type']]
        ];

        $model = new RecordModel();

        $model = $model->where($where);

        if ($data['type'] == 1) {

            $model = $model->with(['goods_info'=>function (Query $query) {
                $query->field('site_id,goods_id,is_sale,is_online_expo');
            }])->order('create_time', 'desc')->order('record_id', 'desc');

            $res = $this->pageX($model, function ($item) {
                $item['is_enabled'] = (isset($item['goods_info']) && $item['goods_info']['is_sale'] == 1 && $item['goods_info']['is_online_expo'] == 1);

                $item['goods_cover']   = $item['o_goods_cover'];
                $item['goods_name']    = $item['o_goods_name'];
                $item['peer_price']    = $item['o_peer_price'];
                $item['currency_code'] = $item['o_currency_code'];
                $item['condition']     = $item['o_condition'];

                return $item;

            })->hidden(['goods_info'])->toArray();

        } else if ($data['type'] == 2) {

        }

        return success($res);
    }


}
