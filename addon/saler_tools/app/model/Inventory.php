<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/25 7:35
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;
use app\model\sys\SysUser;
use think\model\concern\SoftDelete;

/**
 *
 * Class Inventory
 * @package addon\saler_tools\app\model
 */
class Inventory extends BaseModel
{

    use SoftDelete;

    protected $pk = 'inventory_id';

    protected $name = 'saler_tools_inventory';

    protected $deleteTime = 'deleted_time';

    protected $defaultSoftDelete = 0;

    protected $jsonAssoc = true;

    protected $type = [
        'inventory_uids'   => 'array',
        'category_ids'     => 'array',
        'goods_attributes' => 'array',
    ];


    public function searchInventoryStatusAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('inventory_status', $value);
    }



    /******************************************  关联数据 ****************************************/


    public function createNames()
    {
        return $this->hasOne(SysUser::class, 'uid', 'uid')->bind(['create_name' => 'real_name']);
    }


}
