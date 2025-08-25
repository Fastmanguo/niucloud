<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/11/25 2:48
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\admin\CountryService;
use addon\saler_tools\app\service\admin\SettingService;
use addon\saler_tools\app\service\ExchangeRateService;
use addon\saler_tools\app\service\GoodsCategoryService;
use addon\saler_tools\app\service\sys\VersionService;

/**
 *
 * Class Index
 * @package addon\saler_tools\app\adminapi\controller
 */
class Index extends BaseAdminController
{

    /**
     * 读取注册配置
     */
    public function registerConfig()
    {
        return success('', [
            'is_username'               => 1,
            'is_mobile'                 => 0,
            'is_auth_register'          => 1,
            'is_force_access_user_info' => 0,
            'is_bind_mobile'            => 0,
            'agreement_show'            => 0,
            'bg_url'                    => "",
            'desc'                      => "精选好物，购物优惠的省钱平台",
        ]);
    }

    /**
     * 获取系统支持的货币编号
     */
    public function currencyList()
    {
        $data = app(ExchangeRateService::class)->typeList();
        return success($data);
    }


    /**
     * 检测更新
     */
    public function checkUpdate()
    {
        $data = $this->_vali([
            'appid.require'          => 'error',
            'platform.require'       => 'error',
            'edition_number.require' => 'error',
        ]);

        return app(VersionService::class)->checkUpdate($data);

    }

    /**
     * 获取app配置
     */
    public function getAppConfig()
    {
        return app(SettingService::class)->getAppConfig();
    }


    /**
     * 获取国家列表
     */
    public function getCountryList()
    {
        return success(app(CountryService::class)->getList());
    }

    public function getCategoryAndTemplate()
    {
        return app(GoodsCategoryService::class)->getCategoryAndTemplate();
    }

}
