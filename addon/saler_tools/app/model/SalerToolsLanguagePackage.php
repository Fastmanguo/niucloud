<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/7 19:17
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\model;

use addon\saler_tools\app\common\BaseModel;

/**
 *
 * Class SalerToolsLanguagePackage
 * @package addon\saler_tools\app\model
 */
class SalerToolsLanguagePackage extends BaseModel
{
    protected $pk = 'id';

    protected $name = 'saler_tools_language_package';


    public function searchLanguageKeyAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->where('language_key', $value);
    }


    public function searchKeyAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->whereLike('key','%' . $value . '%');
    }


    public function searchValueAttr($query, $value, $data)
    {
        if (!isset($value) || $value == '') return;
        $query->whereLike('value','%' . $value . '%');
    }



}
