<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/12 4:01
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\GoodsBrandService;

/**
 * 品牌
 * Class Brand
 * @package addon\saler_tools\app\adminapi\controller\admin
 */
class Brand extends BaseAdminController
{

    public function lists()
    {
        $data = $this->_vali([
            'brand_name.query' => '',
        ]);

        return app(GoodsBrandService::class)->lists($data);
    }


    public function add()
    {
        $data = $this->_vali([
            'brand_name.require' => '请输入品牌名称',
            'brand_en.require'   => '请输入品牌名称',
            'letter.require'     => '请输入品牌首字母',
            'letter.upper'       => '首字母只能是大写字母',
            'letter_en.require'  => '请输入品牌首字母',
            'letter_en.upper'    => '首字母只能是大写字母',
            'logo.default'       => '',
            'sort.between:0,99'  => '排序必须是0-99之间的数字',
            'desc.length:1,250'  => '品牌描述最多250个字',
        ]);

        return app(GoodsBrandService::class)->add($data);
    }


    public function edit()
    {
        $data = $this->_vali([
            'brand_id.require'   => '请选择品牌',
            'brand_name.require' => '请输入品牌名称',
            'brand_en.require'   => '请输入品牌名称',
            'letter.require'     => '请输入品牌首字母',
            'letter.upper'       => '首字母只能是大写字母',
            'letter_en.require'  => '请输入品牌首字母',
            'letter_en.upper'    => '首字母只能是大写字母',
            'logo.default'       => '',
            'sort.between:0,99'  => '排序必须是0-99之间的数字',
            'desc.length:1,250'  => '品牌描述最多250个字',
        ]);

        return app(GoodsBrandService::class)->edit($data);
    }


    public function del()
    {
        $data = $this->_vali([
            'brand_id.require' => '请选择品牌',
        ]);

        return app(GoodsBrandService::class)->del($data['brand_id']);
    }


}
