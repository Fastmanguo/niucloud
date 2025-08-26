<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/8 2:06
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\sys;

use addon\saler_tools\app\service\LanguageService;

/**
 * 语言
 * Class Language
 * @package addon\saler_tools\app\adminapi\controller\sys
 */
class Language
{

    public function list()
    {
        return app(LanguageService::class)->list();
    }


    public function package($key)
    {
        return app(LanguageService::class)->package($key);
    }

    public function version($key)
    {
        return (new LanguageService())->version($key);
    }

}
