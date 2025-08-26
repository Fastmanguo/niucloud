<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/26 0:11
// +----------------------------------------------------------------------

namespace addon\online_expo\app\service;

use addon\online_expo\app\model\Stat as StatModel;
use addon\online_expo\app\model\GoodsLog as GoodsLogModel;
use addon\saler_tools\app\common\BaseService;
use think\facade\Db;
use addon\online_expo\app\model\Record as RecordModel;
use addon\online_expo\app\model\Goods as GoodsModel;

/**
 *
 * Class StatService
 * @package addon\online_expo\app\service
 */
class StatService extends BaseService
{

    public function lists($params)
    {
        $model = new StatModel();

        $model = $model->withSearch(['site_id'], $params)->order('date_key', 'desc');
        $res   = $this->pageQuery($model);

        return success($res);
    }


    public function info($where)
    {
        $model = new StatModel();
        $stat  = $model->where($where)->findOrEmpty();
        if ($stat->isEmpty()) return success([
            'visitors_count'          => 0,
            'products_viewed_count'   => 0,
            'contacts_fetched_count'  => 0,
            'products_for_sale_count' => 0,
            'date_time'               => strtotime('yesterday')
        ]);
        return success($stat->toArray());
    }

    public static function setLog($site_id, $visitor_id, $id, $type)
    {
        $key = date('ymd') . '_' . $site_id . '_' . $visitor_id . '_' . $id . '_' . $type;
        Db::name((new GoodsLogModel())->getName())->extra('IGNORE')
            ->insert([
                'log_key'     => $key,
                'site_id'     => $site_id,
                'visitor_id'  => $visitor_id,
                'type'        => $type,
                'date_time'   => date('Ymd'),
                'create_time' => time(),
            ]);

        if ($type == 1) {
            $record_id = $visitor_id . '_' . $id;

            // 获取商品基础信息
            $goods_info = (new GoodsModel())->where('goods_id', $id)
                ->field('goods_cover,goods_name,peer_price,currency_code,condition')
                ->findOrEmpty();

            if (!$goods_info->isEmpty()) {
                Db::name((new RecordModel())->getName())
                    ->extra('IGNORE')
                    ->insert([
                        'record_id'       => $record_id,
                        'uid'             => $visitor_id,
                        'type'            => 1,
                        'relate_id'       => $id,
                        'date_key'        => date('Ymd'),
                        'create_time'     => time(),
                        'o_goods_cover'   => $goods_info['goods_cover'] ?? '',
                        'o_goods_name'    => $goods_info['goods_name'] ?? '',
                        'o_peer_price'    => $goods_info['peer_price'] ?? 0,
                        'o_currency_code' => $goods_info['currency_code'] ?? '',
                        'o_condition'     => $goods_info['condition'] ?? '',
                    ]);
            }

        }

    }


    /**
     * 处理上一天的数据
     */
    public function disposeStat()
    {

    }

}
