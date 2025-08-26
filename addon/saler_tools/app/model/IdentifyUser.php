<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/23 22:08
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;
use app\model\sys\SysUser;

/**
 * 鉴定师
 * Class IdentifyUser
 * @package addon\saler_tools\app\model
 */
class IdentifyUser extends BaseModel
{

    protected $pk = 'uid';

    protected $name = 'saler_tools_identify_user';

    protected $autoWriteTimestamp = false;

    protected $type = [
        'certificate_image' => 'array'
    ];


    public function searchStatusAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('status', $value);
    }


    public function searchNicknameAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->whereLike('nickname', '%' . $this->handelSpecialCharacter($value) . '%');
    }


    public function userInfo()
    {
        return $this->hasOne(SysUser::class,'uid', 'uid');
    }






}
