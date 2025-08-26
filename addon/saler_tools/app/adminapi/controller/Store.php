<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/12 19:01
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\StoreService;
use think\cache\driver\Redis;

/**
 * 仓库管理
 * Class Store
 * @package addon\saler_tools\app\adminapi\controller
 */
class Store extends BaseAdminController
{

    public function list()
    {
        $data = $this->_vali([
            'store_name.query' => '',
            'is_stat.default'  => 0
        ]);

        return app(StoreService::class)->list($data);

    }


    public function detail($store_id)
    {
        return app(StoreService::class)->detail($store_id);
    }


    public function add()
    {
        $data = $this->_vali([
            'store_name.require'     => 'store_name_empty',
            'store_name.length:1,30' => 'store_name_max',
        ]);

        return app(StoreService::class)->add($data);
    }


    public function edit()
    {
        $data = $this->_vali([
            'store_id.require'       => 'store_id_empty',
            'store_name.require'     => 'store_name_empty',
            'store_name.length:1,30' => 'store_name_max',
        ]);

        return app(StoreService::class)->edit($data);
    }

    public function delete()
    {
        $data = $this->_vali([
            'store_id.require' => 'store_id_empty',
        ]);

        return app(StoreService::class)->delete($data);
    }


}
