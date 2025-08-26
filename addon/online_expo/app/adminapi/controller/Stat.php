<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/26 0:10
// +----------------------------------------------------------------------

namespace addon\online_expo\app\adminapi\controller;

use addon\online_expo\app\service\StatService;
use addon\saler_tools\app\common\BaseAdminController;

/**
 * 线上展会统计
 * Class Stat
 * @package addon\online_expo\app\adminapi\controller
 */
class Stat extends BaseAdminController
{

    public function lists()
    {
        $data = $this->_vali([
            'site_id.require' => 'error',
            'page.default'    => 1
        ], 'get', [
            'site_id' => $this->site_id
        ]);
        return app(StatService::class)->lists($data);
    }


    public function info($type)
    {
        $where = [
            ['site_id', '=', $this->site_id]
        ];
        if ($type == 'yesterday') {
            $where[] = ['date_key', '=', date('Ymd', strtotime('-1 day'))];
        }
        return app(StatService::class)->info($where);
    }

}
