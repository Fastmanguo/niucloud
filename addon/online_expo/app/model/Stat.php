<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/26 0:12
// +----------------------------------------------------------------------

namespace addon\online_expo\app\model;

use addon\saler_tools\app\common\BaseModel;

/**
 * 线上展会统计
 * Class Stat
 * @package addon\online_expo\app\model
 */
class Stat extends BaseModel
{

    protected $pk = ['date_key','site_id'];

    protected $name = 'online_expo_stat';


    public function searchSiteIdAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('site_id', $value);
    }


}
