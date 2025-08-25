<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/7 22:56
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\ExchangeRateService;

/**
 *
 * Class ExchangeRate
 * @package addon\saler_tools\app\adminapi\controller\admin
 */
class ExchangeRate extends BaseAdminController
{

    public function lists()
    {
        $data = $this->_vali([
            'base_currency.query'   => '',
            'target_currency.query' => '',
        ]);
        return app(ExchangeRateService::class)->lists($data);
    }


    public function flush()
    {
        return app(ExchangeRateService::class)->flush();
    }


    public function typeList()
    {
        return success(app(ExchangeRateService::class)->typeList());
    }


    public function typeModify()
    {

        $data = $this->_vali([
            'data.require' => '保存的数据不能为空'
        ]);

        return app(ExchangeRateService::class)->typeModify($data);

    }

}
