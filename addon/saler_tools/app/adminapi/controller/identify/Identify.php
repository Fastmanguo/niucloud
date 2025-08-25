<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/6 18:29
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\identify;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\identify\IdentifyConfigService;
use addon\saler_tools\app\service\identify\IdentifyService;

/**
 *
 * Class Identify
 * @package addon\saler_tools\app\adminapi\controller
 */
class Identify extends BaseAdminController
{

    public function lists()
    {
        $data = $this->_vali([
            'status.query'     => '',
            'goods_name.query' => '',
            'order_no.query'   => '',
        ]);

        return app(IdentifyService::class)->lists($data);

    }


    public function detail($id)
    {
        return app(IdentifyService::class)->detail($id);
    }


    public function add()
    {
        $data = $this->_vali([
            'goods_image.require'      => '商品ID不能为空',
            'goods_name.require'       => '商品名称不能为空',
            'goods_attachment.default' => [],
            'category_id.require'      => '商品分类不能为空',
            'brand_id.query'           => '商品品牌不能为空',
            'series_id.query'          => '商品系列不能为空',
            'model_id.query'           => '商品型号不能为空',
        ]);

        return app(IdentifyService::class)->add($data);
    }


    public function del($id)
    {
        return app(IdentifyService::class)->del($id);
    }

    public function config()
    {
        $res = app(IdentifyConfigService::class)->ckeck();
        if (empty($res)) return fail('fail_support');
        return success($res);
    }

}
