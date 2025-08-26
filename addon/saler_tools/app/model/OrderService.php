<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/20 2:43
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;
use app\model\sys\SysUser;

/**
 * 保养/维护订单
 * Class OrderService
 * @package addon\saler_tools\app\model
 */
class OrderService extends BaseModel
{

    protected $pk = 'service_id';

    protected $name = 'saler_tools_order_service';

    protected $readonly = ['site_id'];

    protected $jsonAssoc = true;

    protected $autoWriteTimestamp = 'datetime';

    protected $type = [
        'goods_image'     => 'array',
        'additional_cost' => 'array',
        'paid_receipt'    => 'array',
        'service_type'    => 'array',
    ];

    public function searchServiceNoAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('service_no', $value);
    }


    public function searchServiceAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $value = $this->handelSpecialCharacter($value);
        $query->whereLike('goods_name|goods_code|remark', '%' . $value . '%');
    }


    public function searchSalesUidAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('sales_uid', $value);
    }


    public function searchServiceUidAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('service_uid', $value);
    }

    public function searchCreateUidAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('create_uid', $value);
    }

    /******************************** 关联事件 **********************************/
    public static function onBeforeInsert($model)
    {

    }


    public static function onBeforeUpdate($model)
    {

    }


    /********************************* 关联数据 **********************************/

    public function salesNames()
    {
        return $this->hasOne(SysUser::class, 'uid', 'sales_uid')->bind(['sale_name' => 'real_name']);
    }

    public function serviceNames()
    {
        return $this->hasOne(SysUser::class, 'uid', 'service_uid')->bind(['service_name' => 'real_name']);
    }


    public function createNames()
    {
        return $this->hasOne(SysUser::class, 'uid', 'create_uid')->bind(['create_name' => 'real_name']);
    }




}
