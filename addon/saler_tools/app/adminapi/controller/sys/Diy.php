<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/11/25 3:45
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\sys;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\diy\DiyService;

/**
 *
 * Class Diy
 * @package addon\saler_tools\app\adminapi\controller\sys
 */
class Diy extends BaseAdminController
{

    /**
     * 获取首页布局
     */
    public function index()
    {
        $data = $this->_vali([
            'id.query'       => '',
            'name.query'     => '',
            'template.query' => '',
        ]);

        $result_data = app(DiyService::class)->getDiy($data);

        return success($result_data);

    }

    public function getImages()
    {
        $result_data = app(DiyService::class)->getImages();
        $array_data =  json_decode($result_data['value'], true);

        $images_list = $array_data['value'][1]['list'];
        return success($images_list);
    }


}
