<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/23 22:32
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\admin\IdentifyService;

/**
 * admin端鉴定商品控制器
 * Class Identify
 * @package addon\saler_tools\app\adminapi\controller\admin
 */
class Identify extends BaseAdminController
{


    public function lists()
    {
        $data = $this->_vali([
            'status.query'     => '',
            'goods_name.query' => ''
        ]);

        return app(IdentifyService::class)->lists($data);

    }


    public function edit()
    {
        $data = $this->_vali([
            'id.require'              => 'id不能为空',
            'status.require'          => 'status不能为空',
            'result_status.require'   => 'result_status不能为空',
            'goods_name.require'      => '商品名称不能为空',
            'money.require'           => '鉴定价格不能为空',
            'identify_log_id.default' => 0,
            'result_remark.default'   => '',
            'result_time.default'     => null
        ]);
        return app(IdentifyService::class)->edit($data);
    }


    public function logLists()
    {
        $data = $this->_vali([
            'identify_id.require'              => '请选择鉴定订单',
        ]);
        return app(IdentifyService::class)->logLists($data);
    }


}
