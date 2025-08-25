<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/23 2:02
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;

/**
 * 账单计算
 * Class BillRecord
 * @package addon\saler_tools\app\model
 */
class BillRecord extends BaseModel
{

    protected $pk = 'record_id';

    protected $name = 'saler_tools_bill_record';

    protected $autoWriteTimestamp = false;

    public function billNames()
    {
        return $this->hasOne(Bill::class, 'bill_id', 'bill_id')->bind(['bill_name']);
    }

}
