<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/14 1:34
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\shop\ShopService;

/**
 * 店铺管理
 * Class Shop
 * @package addon\saler_tools\app\adminapi\controller\admin
 */
class Shop extends BaseAdminController
{

    /**
     * 获取店铺列表
     */
    public function lists()
    {
        $data = $this->_vali([
            'query_type.default' => 1,
            'search.query'       => ''
        ]);

        return app(ShopService::class)->lists($data);
    }

    public function getApplyConfig()
    {
        return success(app(ShopService::class)->getApplyConfig());
    }


    public function edit()
    {
        $data = $this->_vali([
            'site_id.require'   => '请选择店铺',
            'shop_name.require' => '请填写店铺名称',
            'logo.require'      => '请上传店铺logo',
            'address.require'   => '请填写店铺地址',
            'tel.query'         => '请填写店铺电话',
            'mobile.query'      => '请填写店铺电话',
            'desc.require'      => '请填写店铺介绍',
        ]);
        return app(ShopService::class)->editByAdmin($data);
    }

    public function setApplyConfig()
    {
        $data = $this->_vali([
            'is_auto_audit.default'   => 0,// 是否自动审核
            'experience_time.default' => 0,// 体验时间
        ]);
        return app(ShopService::class)->setApplyConfig($data);
    }

    public function audit()
    {
        $data = $this->_vali([
            'id.require'       => '请选择审核的店铺',
            'status.require'   => '状态不能为空',
            'exp_time.default' => '',
        ]);

        if (!isset($data['exp_time'])) {
            $data['exp_time'] = date('Y-m-d H:i:s');
        }

        return app(ShopService::class)->audit($data);
    }

}
