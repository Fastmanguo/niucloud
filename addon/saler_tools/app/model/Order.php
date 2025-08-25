<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/21 3:15
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;
use app\model\sys\SysUser;
use think\db\Query;
use think\model\concern\SoftDelete;

/**
 *
 * Class Order
 * @package addon\saler_tools\app\model
 */
class Order extends BaseModel
{
    use SoftDelete;

    protected $deleteTime = 'deleted_time';
    protected $defaultSoftDelete = 0;

    protected $pk = 'order_id';

    protected $name = 'saler_tools_order';

    protected $readonly = ['site_id', 'order_no'];

    protected $autoWriteTimestamp = 'datetime';

    protected $type = [
        'goods_image'         => 'array',
        'goods_price'         => 'float',
        'sale_uids'          => 'array',
        'additional_cost'     => 'array',
        'paid_receipt'        => 'array',
        'after_sales_service' => 'array',
    ];


    public function searchSearchAttr(Query $query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->whereLike('goods_name|order_no|remark', '%' . $value . '%');
    }

    public function searchOrderIdAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('order_id', $value);
    }

    public function searchOrderStatusAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        if (is_array($value)){
            $query->where('order_status', 'in', $value);
        }else{
            $query->where('order_status', $value);
        }
    }

    public function searchIsPaidAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('is_paid', $value);
    }


    public function searchTransactionTimeAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        if (is_array($value)) {
            $query->where('transaction_time', 'between', [intval($value[0]), intval($value[1])]);
        }
    }


    public function searchIsDeliveryAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('is_delivery', $value);
    }


    /********************************* 关联数据 **********************************/


    public function createName()
    {
        return $this->hasOne(SysUser::class, 'uid', 'create_uid')->bind(['create_name' => 'real_name']);
    }

    public function lockName()
    {
        return $this->hasOne(SysUser::class, 'uid', 'lock_uid')->bind(['lock_name' => 'real_name']);
    }


}
