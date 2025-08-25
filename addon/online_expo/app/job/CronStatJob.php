<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/27 5:00
// +----------------------------------------------------------------------

namespace addon\online_expo\app\job;

use core\base\BaseJob;
use think\facade\Db;
use addon\online_expo\app\model\Stat as StatModel;
use addon\online_expo\app\model\GoodsLog as GoodsLogModel;

/**
 * 线上展会统计
 * Class CronStatJob
 * @package addon\online_expo\app\job
 */
class CronStatJob extends BaseJob
{
    public function doJob()
    {

        $stat_model      = new StatModel();
        $goods_log_model = new GoodsLogModel();

        $day_list = $goods_log_model->group('date_time')->column('date_time');

        if (empty($day_list)) return true;

        Db::startTrans();
        try {

            foreach ($day_list as $day) {
                $visitors_data = $goods_log_model->where('type', 1)->where('date_time', $day)
                    ->group('site_id')->field('count(visitor_id) as visitors_num,site_id')
                    ->select()
                    ->toArray();

                $contacts_data = $goods_log_model->where('type', 20)->where('date_time', $day)
                    ->group('site_id')
                    ->field('count(visitor_id) as contacts_num,site_id')
                    ->select()
                    ->toArray();

                $visitors_count = $goods_log_model->where('date_time', $day)
                    ->group('site_id')
                    ->field('count(DISTINCT visitor_id) as visitors_count,site_id')
                    ->select()
                    ->toArray();

                $visitors_data  = array_column($visitors_data, null, 'site_id');
                $contacts_data  = array_column($contacts_data, null, 'site_id');
                $visitors_count = array_column($visitors_count, null, 'site_id');

                $stat_list = $this->merge($visitors_data, $contacts_data, $visitors_count);

                unset($visitors_data);
                unset($contacts_data);


                $timestamp = strtotime(substr($day, 0, 4) . '-' . substr($day, 4, 2) . '-' . substr($day, 6, 2));

                foreach ($stat_list as $site) {
                    $site_stat = $stat_model->where('date_key', $day)->where('site_id', $site['site_id'])->findOrEmpty();

                    if ($site_stat->isEmpty()) {
                        $stat_model->create([
                            'date_key'               => $day,
                            'site_id'                => $site['site_id'],
                            'visitors_count'         => $site['visitors_count'],
                            'products_viewed_count'  => $site['visitors_num'],
                            'contacts_fetched_count' => $site['contacts_num'],
                            'date_time'              => $timestamp
                        ]);
                    } else {
                        $site_stat->visitors_count         += $site['visitors_count'];
                        $site_stat->products_viewed_count  += $site['visitors_num'];
                        $site_stat->contacts_fetched_count += $site['contacts_num'];
                        $site_stat->save();
                    }
                }

                $goods_log_model->where('date_time', $day)->delete();

            }

            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
        }


    }

    // 合并两个统计数据
    protected function merge($data, $data2, $data3)
    {
        // 获取所有唯一的 site_id
        $allSiteIds = array_unique(array_merge(array_keys($data), array_keys($data2), array_keys($data3)));

        $result = [];

        foreach ($allSiteIds as $siteId) {
            $visitorsNum   = isset($data[$siteId]['visitors_num']) ? $data[$siteId]['visitors_num'] : 0;
            $contactsNum   = isset($data2[$siteId]['contacts_num']) ? $data2[$siteId]['contacts_num'] : 0;
            $visitorsCount = isset($data3[$siteId]['visitors_count']) ? $data3[$siteId]['visitors_count'] : 0;

            $result[] = [
                'site_id'        => $siteId,
                'visitors_num'   => $visitorsNum,
                'contacts_num'   => $contactsNum,
                'visitors_count' => $visitorsCount
            ];
        }

        return $result;
    }


}
