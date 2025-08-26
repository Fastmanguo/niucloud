<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/13 18:57
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\sys\AgreementService;

/**
 *
 * Class Agreement
 * @package addon\saler_tools\app\adminapi\controller\admin
 */
class Agreement extends BaseAdminController
{

    public function allowList()
    {
        return success(AgreementService::AGREEMENT_TYPE);
    }


    public function lists()
    {
        $data = $this->_vali([
            'name.query' => '',
            'key.query'  => '',
        ]);

        return app(AgreementService::class)->lists($data);
    }


    public function add()
    {
        $data = $this->_vali([
            'name.require'    => '协议名称不能为空',
            'key.require'     => '协议类型不能为空',
            'lang.require'    => '协议应用语言不能为空',
            'content.require' => '协议内容不能为空',
        ]);
        return app(AgreementService::class)->add($data);
    }

    public function edit()
    {
        $data = $this->_vali([
            'id.require'      => '请选择修改的协议',
            'name.require'    => '协议名称不能为空',
            'key.require'     => '协议类型不能为空',
            'lang.require'    => '协议应用语言不能为空',
            'content.require' => '协议内容不能为空',
        ]);
        return app(AgreementService::class)->edit($data);
    }


}
