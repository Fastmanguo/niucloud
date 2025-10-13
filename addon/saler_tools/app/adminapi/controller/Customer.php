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
use addon\saler_tools\app\service\user\CustomerService;

/**
 * 客户管理
 * Class Customer
 * @package addon\saler_tools\app\adminapi\controller
 */
class Customer extends BaseController
{

    /**
     * 客户支付识别
     */
    public function paymentRecognizeText(){
        $data = $this->_vali([
            'text.require'   => "请输入支付信息",
        ]);
        return (new CustomerService())->paymentRecognizeText($data['text']);
    }

    /**
     * 添加客户支付信息
     */
    public function addPayment(){
        $data = $this->_vali([
            'name.require'   => "请输入支付名称",
            'account.require'   => "请输入支付账号",
            'bank_name.require'   => "请输入支付开户行",
            'uid.require'   => "请输入当前登录用户id",
            'site_id.require'   => "请输入当前站点id",
            'image.default'   => "",
        ]);
        return (new CustomerService())->addPayment($data);
    }

    /**
     * 客户支付信息列表
     */
    public function paymentList(){
        $data = $this->_vali([
            'uid.require'   => "请输入当前登录用户id",
            'site_id.require'   => "请输入当前站点id",
            'page.default' => 1,
            'page_size.default' => 10
        ]);
        return (new CustomerService())->paymentList($data);
    }

    /**
     * 删除客户支付信息
     */
    public function paymentDel(){
        $data = $this->_vali([
            'id.require'   => "请输入支付信息ID",
        ]);
        return (new CustomerService())->paymentDel($data['id']);
    }

    /**
     * 编辑回显接口
     */
    public function paymentFind(){
        $data = $this->_vali([
            'id.require'   => "请输入支付信息ID",
        ]);
        return (new CustomerService())->paymentFind($data['id']);
    }
    
    /**
     * 编辑客户支付信息
     */
    public function paymentEdit(){
        $data = $this->_vali([
            'id.require'   => "请输入支付信息ID",
            'name.require'   => "请输入支付名称",
            'account.require'   => "请输入支付账号",
            'bank_name.require'   => "请输入支付开户行",
            'image.default'   => "",
        ]);
        return (new CustomerService())->paymentEdit($data);
    }

    /**
     * 客户地址识别
     */
    public function addressRecognizeText(){
        $data = $this->_vali([
            'text.require'   => "请输入地址信息",
        ]);
        return (new CustomerService())->addressRecognizeText($data['text']);
    }
    
    /**
     * 添加客户收货信息
     */
    public function receiptAdd(){
        $data = $this->_vali([
            'name.require'   => "请输入名称",
            'mobile.require'   => "请输入手机号",
            'address.require'   => "请输入详细地址",
            'uid.require'   => "请输入当前登录用户id",
            'site_id.require'   => "请输入当前站点id",
        ]);
        return (new CustomerService())->receiptAdd($data);
    }
    
    /**
     * 客户收货信息列表
     */
    public function receiptList(){
         $data = $this->_vali([
            'uid.require'   => "请输入当前登录用户id",
            'site_id.require'   => "请输入当前站点id",
            'page.default' => 1,
            'page_size.default' => 10
        ]);
        return (new CustomerService())->receiptList($data);
    }
    
    /**
     * 删除客户收货信息
     */
    public function receiptDel(){
        $data = $this->_vali([
            'id.require'   => "请输入收货信息ID",
        ]);
        return (new CustomerService())->receiptDel($data['id']);
    }
    
    /**
     * 编辑回显接口
     */
    public function receiptFind(){
        $data = $this->_vali([
            'id.require'   => "请输入收货信息ID",
        ]);
        return (new CustomerService())->receiptFind($data['id']);
    }
    
    /**
     * 编辑客户收货信息
     */
    public function receiptEdit(){
        $data = $this->_vali([
            'id.require'   => "请输入收货信息ID",
            'name.require'   => "请输入名称",
            'mobile.require'   => "请输入手机号",
            'address.require'   => "请输入详细地址",
        ]);
        return (new CustomerService())->receiptEdit($data);
    }

    /**
     * 添加客户
     */
    public function customerAdd(){
        $data = $this->_vali([
            "uid.require"   => "请输入用户ID",
            "site_id.require"   => "请输入店铺ID",
            'customer_name.require'   => "请输入客户名称",
            'customer_mobile.require'   => "请输入客户手机号",
            'customer_type.require'   => "请输入客户类型",
        ]);
        return (new CustomerService())->customerAdd($data);
    }
    
    /**
     * 编辑客户
     */
    public function customerEdit(){
        $data = $this->_vali([
            'id.require'   => "请输入客户ID",
            'customer_name.require'   => "请输入客户名称",
            'customer_mobile.require'   => "请输入客户手机号",
            'customer_type.require'   => "请输入客户类型",
            'wx_name.default'   => "",
            'wx_number.default'   => "",
            'gender.require'   => "请输入客户性别",
            'birthday.default'   => "",
            'level.require'   => "请输入客户等级",
            "remarks.default"   => "",
            "payment_id.default"   => "",
            "receipt_id.default"   => "",
            "maintainer_id.default"   => "",
        ]);
        return (new CustomerService())->customerEdit($data);
    }

    /**
     * 编辑回显
     */
    public function customerFind(){
        $data = $this->_vali([
            'id.require'   => "请输入客户ID",
        ]);
        return (new CustomerService())->customerFind($data['id']);
    }
    
    /**
     * 客户列表
     */
    public function customerList(){
        $data = $this->_vali([
            "search_str.default"   => "",
            "page.default" => 1,
            "page_size.default" => 10,
            "uid.require"   => "请输入用户ID",
            "site_id.require"   => "请输入店铺ID",
        ]);
        return (new CustomerService())->customerList($data);
    }

    /**
     * 客户详情
     */
    public function customerDetails(){
        $data = $this->_vali([
            'id.require'   => "请输入客户ID",
        ]);
        return (new CustomerService())->customerDetails($data['id']);
    }

    /**
     * 删除客户
     */
    public function customerDel(){
        $data = $this->_vali([
            'id.require'   => "请输入客户ID",
        ]);
        return (new CustomerService())->customerDel($data['id']);
    }

    /**
     * 获取系统编码
     */
    public function getCode(){
        $code = date('YmdHis');
        return success($code);
    }
    
    /**
     * 客户统计
     */
    public function customerTj(){
        $data = $this->_vali([
            "uid.require"   => "请输入用户ID",
            "site_id.require"   => "请输入店铺ID",
        ]);
        return (new CustomerService())->customerTj($data);
    }

    /**
     * 物流地图轨迹查询
     */
    public function logisticsTrack(){
        $data = $this->_vali([
            'code.require'   => "请输入物流单号",
        ]);
        return (new CustomerService())->logisticsTrack($data['code']);
    }

    /**
     * 物流在途监控信息
     */
    public function logisticsFind(){
        $data = $this->_vali([
            'code.require'   => "请输入物流单号",
        ]);
        return (new CustomerService())->logisticsFind($data['code']);
    }
}
