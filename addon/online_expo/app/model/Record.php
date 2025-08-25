<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/6/1 4:49
// +----------------------------------------------------------------------

namespace addon\online_expo\app\model;

use addon\saler_tools\app\common\BaseModel;

/**
 *
 * Class Record
 * @package addon\online_expo\app\model
 */
class Record extends BaseModel
{

    protected $name = 'online_expo_record';

    protected $pk = 'record_id';


    public function goodsInfo()
    {
        return $this->hasOne(Goods::class,'goods_id','relate_id');
    }




}
