<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/13 20:13
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseController;
use addon\saler_tools\app\service\user\UserBookkeepingService;

/**
 * 客户管理
 * Class Customer
 * @package addon\saler_tools\app\adminapi\controller
 */
class UserBookkeeping extends BaseController
{

    /**
     * 添加记账
     */
    public function add(){
        $data = $this->_vali([
            'price.require'   => "请输入金额",
            'f_id.require'   => "请输入分类id",
            'create_time.require'   => "请输入记账时间",
            'uid.require'   => "请输入用户ID",
            'type.require'   => "请输入类型",
            'images.default'   => "",
            'remarks.default'   => "",
        ]);
        if($data['images']){
            $data['images'] = json_encode($data['images']);
        }
        $data['create_time'] = strtotime($data['create_time']);
        $data['update_time'] = time();
        return (new UserBookkeepingService())->add($data);
    }

    /**
     * 编辑记账
     */
    public function edit(){
        $data = $this->_vali([
            'id.require'   => "请输入记账ID",
            'price.require'   => "请输入金额",
            'f_id.require'   => "请输入分类id",
            'create_time.require'   => "请输入交易时间",
            'type.require'   => "请输入类型",
            'images.default'   => "",
            'remarks.default'   => "",
        ]);
        if($data['images']){
            $data['images'] = json_encode($data['images']);
        }
        $data['create_time'] = strtotime($data['create_time']);
        
        return (new UserBookkeepingService())->edit($data);
    }

    /**
     * 记账列表
     */
    public function list(){
        $data = $this->_vali([
            'uid.default'   => [],
            'f_id.default'   => [],
            "month.default"   => "",
            'page.default'   => 1,
            'limit.default'   => 10,
        ]);
        return (new UserBookkeepingService())->list($data);
    }

    /**
     * 记账详情
     */
    public function details(){
        $data = $this->_vali([
            'id.require'   => "请输入记账ID",
        ]);
        return (new UserBookkeepingService())->details($data['id']);
    }

    /**
     * 删除记账
     */
    public function del(){
        $data = $this->_vali([
            'id.require'   => "请输入记账ID",
        ]);
        return (new UserBookkeepingService())->del($data['id']);
    }
}