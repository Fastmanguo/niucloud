<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/11 19:39
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\GoodsTemplateService;

/**
 *
 * Class Template
 * @package addon\saler_tools\app\adminapi\controller\admin
 */
class Template extends BaseAdminController
{
    public function lists()
    {
        $params = $this->_vali([
            'template_name.query' => ''
        ]);
        return app(GoodsTemplateService::class)->lists($params);
    }


    public function add()
    {

        $data = $this->_vali([
            'template_name.default' => '',
            'template_data.require' => '模板内容不能为空',
            'category_id.require'   => '请选择模板分类',
        ]);

        return app(GoodsTemplateService::class)->add($data);
    }


    public function edit()
    {
        $data = $this->_vali([
            'template_id.require'   => '请选择修改的数据',
            'category_id.require'   => '请选择模板分类',
            'template_name.default' => '',
            'template_data.require' => '模板内容不能为空',
        ]);

        return app(GoodsTemplateService::class)->edit($data);
    }


    public function delete()
    {
        $data = $this->_vali([
            'template_id.require' => '请选择修改的数据',
        ]);
        return app(GoodsTemplateService::class)->delete($data['template_id']);
    }


    public function detail()
    {
        $data = $this->_vali([
            'template_id.require' => '请选择修改的数据',
        ]);
        return app(GoodsTemplateService::class)->detail($data);
    }

    public function templateAttr()
    {
        $data = $this->_vali([
            'category_id.query' => '',
        ]);

        if (empty($data)) return success([]);

        return app(GoodsTemplateService::class)->templateAttr($data);

    }

}
