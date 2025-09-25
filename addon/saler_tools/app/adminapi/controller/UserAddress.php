<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/13 20:13
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseController;
use addon\saler_tools\app\common\BaseService;
use addon\saler_tools\app\service\user\UserAddressService;

/**
 * 用户找回密码
 * Class UserForget
 * @package addon\saler_tools\app\adminapi\controller
 */
class UserAddress extends BaseController
{
    /**
     * 获取区域列表
     */
    public function selectRegion()
    {
        $data = $this->_vali([
           'parent_id.default'   => 0,
           'shortname.default'   => "",
        ]);
        return (new UserAddressService())->selectRegion($data['parent_id'], $data['shortname']);
    }


    /**
     * 添加收货地址
     */
    public function add(){
         $data = $this->_vali([
           'uid.require'   => "请输入用户id",
           'site_id.require'   => "请输入店铺id",
           'address_name.require'   => "请输入收货人名称",
           'mobile.require'   => "请输入手机号",
           'province_id.require'   => "请输入省份id",
           'city_id.require'   => "请输入城市id",
           'district_id.require'   => "请输入区县id",
           'address.require'   => "请输入详细地址",
           'is_default.default'   => 0,
        ]);
        return (new UserAddressService())->add($data);
    }

    /**
     * 删除地址
     */
    public function del(){
        $data = $this->_vali([
            'id.require'   => "请输入地址id",
        ]);
        return (new UserAddressService())->del($data['id']);
    }

    /**
     * 编辑回显
     */
    public function find(){
        $data = $this->_vali([
            'id.require'   => "请输入地址id",
        ]);
        return (new UserAddressService())->find($data['id']);
    }


    /**
     * 编辑地址
     */
    public function edit(){
        $data = $this->_vali([
           'uid.require'   => "请输入用户id",
           'site_id.require'   => "请输入店铺id",
           'address_name.require'   => "请输入收货人名称",
           'mobile.require'   => "请输入手机号",
           'province_id.require'   => "请输入省份id",
           'city_id.require'   => "请输入城市id",
           'district_id.require'   => "请输入区县id",
           'address.require'   => "请输入详细地址",
           'is_default.default'   => 0,
           'id.require'   => "请输入地址id",
        ]);
        return (new UserAddressService())->edit($data);
    }

    /**
     * 地址列表
     */
    public function list(){
        $data = $this->_vali([
           'uid.require'   => "请输入用户id",
        ]);
        return (new UserAddressService())->list($data['uid']);
    }

    /**
     * 识别文本
     */
    public function recognizeText(){
        $data = $this->_vali([
           'text.require'   => "请输入文本",
        ]);
        return (new UserAddressService())->recognizeText($data['text']);
    }


    /**
     * 商品列表
     */
    public function goodsList(){
        $data = $this->_vali([
           'page.require'   => "请输入当前页",
           'page_size.require'   => "请输入每页数量",
           'category_id.default'   => "",
           'brand_id.default'   => "",
        ]);
        return (new UserAddressService())->goodsList($data);
    }

    /**
     * 品牌列表
     */
    public function brandList(){
        return (new UserAddressService())->brandList();
    }

}