<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/12 3:44
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\GoodsModelService;
use addon\saler_tools\app\service\GoodsTemplateService;

/**
 *
 * Class Model
 * @package addon\saler_tools\app\adminapi\controller\admin
 */
class Model extends BaseAdminController
{

    public function lists()
    {
        $params = $this->_vali([
            'model_name.query' => '',
            'series_id.query'  => '',
            'brand_id.query'   => '',
        ]);
        return app(GoodsModelService::class)->lists($params);
    }


    public function add()
    {

    }

}
