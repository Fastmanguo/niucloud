<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/11 19:40
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\GoodsCategoryService;

/**
 *
 * Class Category
 * @package addon\saler_tools\app\adminapi\controller\admin
 */
class Category extends BaseAdminController
{

    public function lists()
    {
        $params = $this->_vali([
            'category_name.query' => ''
        ]);

        return app(GoodsCategoryService::class)->lists($params);

    }


    public function add()
    {
        $data = $this->_vali([
            'category_name.require'      => '分类名称不能为空',
            'category_key.require'       => '分类名称标识不能为空',
            'category_image.default'     => '',
            'category_level.default'     => '',
            'category_pid.default'       => 0,
            'template_id.default'        => 0,
            'category_full_name.default' => '',
            'is_show.default'            => 1,
            'sort.default'               => 0,
        ]);

        return app(GoodsCategoryService::class)->add($data);

    }


    public function edit()
    {
        $data = $this->_vali([
            'category_id.require'        => '请选择修改的数据',
            'category_key.require'       => '分类名称标识不能为空',
            'category_name.require'      => '分类名称不能为空',
            'category_image.default'     => '',
            'category_level.default'     => '',
            'template_id.default'        => 0,
            'category_pid.default'       => 0,
            'category_full_name.default' => '',
            'is_show.default'            => 1,
            'sort.default'               => 0,
        ]);

        return app(GoodsCategoryService::class)->edit($data);

    }


    public function del()
    {
        $data = $this->_vali([
            'category_id.require' => '请选择删除的数据',
        ]);
        return app(GoodsCategoryService::class)->del($data['category_id']);
    }


    public function transfer()
    {
        return app(GoodsCategoryService::class)->transfer();
    }



}
