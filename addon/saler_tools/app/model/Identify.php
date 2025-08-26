<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/6 18:09
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;
use addon\saler_tools\app\service\identify\IdentifyUserService;
use think\db\Query;
use think\model\concern\SoftDelete;

/**
 *
 * Class Identify
 * @package addon\saler_tools\app\model
 */
class Identify extends BaseModel
{

    use SoftDelete;

    protected $defaultSoftDelete = 0;

    protected $deleteTime        = 'deleted_time';

    protected $autoWriteTimestamp = 'datetime';

    protected $name = 'saler_tools_identify';

    protected $pk = 'id';

    protected $readonly = ['site_id'];

    protected $type = [
        'goods_image' => 'array'
    ];

    public function searchOrderNoAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->whereLike('order_no', '%' . $this->handelSpecialCharacter($value) . '%');
    }


    public function searchStatusAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        if (is_string($value) && str_contains($value, ',')) {
            $query->whereIn('status', explode(',', $value));
        } else if (is_array($value)) {
            $query->whereIn('status', $value);
        } else {
            $query->where('status', $value);
        }
    }


    /*****************************************  关联数据 ****************************************/
    public function brand()
    {
        return $this->hasOne(SalerToolsGoodsBrand::class, 'brand_id', 'brand_id')->bind(['brand_name']);
    }


    public function series()
    {
        return $this->hasOne(SalerToolsGoodsSeries::class, 'series_id', 'series_id')->bind(['series_name']);
    }


    public function model()
    {
        return $this->hasOne(SalerToolsGoodsModel::class, 'model_id', 'model_id')->bind(['model_name']);
    }


    public function shopInfo()
    {
        return $this->hasOne(Shop::class, 'site_id', 'site_id')->bind(['shop_name','address']);
    }

    public function identifyLogInfo()
    {
        return $this->hasOne(IdentifyLog::class, 'identify_id', 'id');
    }

    public function identifyLog()
    {
        return $this->hasMany(IdentifyLog::class, 'identify_id', 'id')->with([
            'identifyUserInfo'
        ]);
    }

}
