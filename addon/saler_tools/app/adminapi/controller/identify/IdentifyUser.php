<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/23 21:04
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\identify;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\identify\IdentifyUserGoodsService;
use addon\saler_tools\app\service\identify\IdentifyUserService;

/**
 * 鉴定师控制器
 * Class IdentifyUser
 * @package addon\saler_tools\app\adminapi\controller\identify
 */
class IdentifyUser extends BaseAdminController
{

    /**
     * 获取鉴定师状态
     */
    public function check()
    {
        return success(app(IdentifyUserService::class)->getStatus());
    }


    /**
     * 申请成为鉴定师
     */
    public function apply()
    {
        $data = $this->_vali([
            'nickname.require'        => 'identifyuser.tips',
            'mobile.require'          => 'input.mobile.tips',
            'reason.default'          => '',
            'certificate_image.array' => 'certificate_type_error'
        ]);

        if (empty($data['certificate_image'])) {
            $data['certificate_image'] = [];
        }

        return app(IdentifyUserService::class)->apply($data);

    }


    /**
     * 获取固件商品列表
     */
    public function goodsLists()
    {
        $data = $this->_vali([
            'goods_name.query'  => '',
            'category_id.query' => '',
            'brand_id.query'    => '',
            'series_id.query'   => '',
            'status.default'    => 0,
        ]);
        return app(IdentifyUserGoodsService::class)->goodsLists($data);
    }


    public function goodsDetail($id)
    {
        return app(IdentifyUserGoodsService::class)->goodsDetail($id);
    }

    /**
     * 鉴定师出价
     */
    public function editIdentify()
    {
        $data = $this->_vali([
            'id.require'     => '',
            'status.query'   => '',
            'price.default'  => 0,
            'remark.default' => '',
        ]);

        return app(IdentifyUserGoodsService::class)->editIdentify($data);

    }


}
