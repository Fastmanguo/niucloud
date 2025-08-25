<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/28 6:55
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\api\middleware;

/**
 *
 * Class AllowCrossDomain
 * @package addon\saler_tools\app\api\middleware
 */
class AllowCrossDomain extends \think\middleware\AllowCrossDomain
{

    protected $header = [
        'Access-Control-Allow-Credentials' => 'true',
        'Access-Control-Max-Age'           => 1800,
        'Access-Control-Allow-Methods'     => 'GET, POST, PATCH, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers'     => 'Authorization, site-id, Site-Id',
    ];

}
