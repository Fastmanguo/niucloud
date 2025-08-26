<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/24 17:12
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\admin\CountryService;

/**
 * 国家管理
 * Class Country
 * @package addon\saler_tools\app\adminapi\controller\admin
 */
class Country extends BaseAdminController
{


    public function list()
    {
        return app(CountryService::class)->list();
    }

    public function save()
    {
        $data = $this->_vali([
            'country_id.query'      => 0,
            'country_name.require'  => '国家名称不能为空',
            'country_code.require'  => '国家代码不能为空',
            'country_image.default' => '国家封面',
            'sort.default'          => 0,
            'status.default'        => 1
        ]);

        return app(CountryService::class)->save($data);
    }

}
