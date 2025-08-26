<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/18 23:54
// +----------------------------------------------------------------------

namespace addon\online_expo\app\adminapi\controller;

use addon\online_expo\app\service\GoodsEnquiryService;
use addon\saler_tools\app\common\BaseAdminController;

/**
 * 商品出价
 * Class GoodsEnquiry
 * @package addon\online_expo\app\adminapi\controller
 */
class GoodsEnquiry extends BaseAdminController
{

    public function lists()
    {
        $data = $this->_vali([
            'type.default' => 1,
            'status.query' => ''
        ]);

        return app(GoodsEnquiryService::class)->lists($data);

    }


    /**
     * 出价
     */
    public function postBid()
    {
        $data = $this->_vali([
            'goods_id.require' => 'error',
            'money.require'    => 'error',
        ]);
        return app(GoodsEnquiryService::class)->postBid($data);
    }

    /**
     * 回复出价
     */
    public function reBid()
    {
        $data = $this->_vali([
            'id.require' => 'error',
            'status.require'   => 'error',
        ]);

        return app(GoodsEnquiryService::class)->reBid($data);
    }


}
