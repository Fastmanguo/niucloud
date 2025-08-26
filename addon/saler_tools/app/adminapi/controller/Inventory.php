<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/25 7:35
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\goods\InventoryService;

/**
 *
 * Class Inventory
 * @package addon\saler_tools\app\adminapi\controller
 */
class Inventory extends BaseAdminController
{

    public function lists()
    {
        $data = $this->_vali([
            'inventory_status.query' => ''
        ]);

        return app(InventoryService::class)->lists($data);
    }


    public function detail($inventory_id)
    {
        return app(InventoryService::class)->detail($inventory_id);
    }


    public function create()
    {
        $data = $this->_vali([
            'inventory_name.require'   => 'input.inventory_name',
            'inventory_type.require'   => 'input.inventory_type',
            'category_ids.default'     => [],
            'inventory_uids.default'   => [],
            'goods_attributes.default' => [],
            'store_id.default'         => 0,
            'watch_location.default'   => '',
        ]);

        return app(InventoryService::class)->create($data);

    }


    public function save($inventory_id)
    {
        return app(InventoryService::class)->save($inventory_id);
    }


    public function del($inventory_id)
    {
        return app(InventoryService::class)->del($inventory_id);
    }


    public function inventoryGoodsLists()
    {
        $data = $this->_vali([
            'inventory_id.require' => '盘点ID不能为空',
            'status.default'       => 0,
            'category_id.query'    => ''
        ]);
        return app(InventoryService::class)->inventoryGoodsLists($data);
    }


    public function inventoryModifyGoods()
    {
        $data = $this->_vali([
            'goods_id.require'     => '商品ID不能为空',
            'inventory_id.require' => '盘点ID不能为空',
            'status.require'       => '盘点状态不能为空',
            'status.in:-1,1'       => '盘点状态不合法',
            'remark.default'       => '',
            'lose_num.default'     => 0,
        ]);
        return app(InventoryService::class)->inventoryModifyGoods($data);
    }


}
