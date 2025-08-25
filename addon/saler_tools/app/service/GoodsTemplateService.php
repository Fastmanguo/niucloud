<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/11 19:41
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\SalerToolsGoodsTemplate as SalerToolsGoodsTemplateModel;

/**
 *
 * Class GoodsTemplateService
 * @package addon\saler_tools\app\service
 */
class GoodsTemplateService extends BaseAdminService
{

    public function lists($params)
    {
        $model = new SalerToolsGoodsTemplateModel();
        $model = $model->withSearch(['template_name'], $params)
            ->with(['category'])
            ->order('create_time desc');
        return success($this->pageQuery($model));
    }


    public function add($data)
    {
        $model           = new SalerToolsGoodsTemplateModel();
        $data['site_id'] = $this->site_id;
        $model->create($data);
        return success();
    }

    public function edit($data)
    {
        $model = new SalerToolsGoodsTemplateModel();

        $template = $model->where('template_id', $data['template_id'])
            ->where('site_id', $this->site_id)
            ->findOrEmpty();

        if ($template->isEmpty()) return fail('模板不存在');

        $template->save(['template_data' => $data['template_data']]);

        return success();
    }


    public function delete($template_id)
    {
        $model = new SalerToolsGoodsTemplateModel();
        $model->where('template_id', $template_id)
            ->where('site_id', $this->site_id)
            ->delete();
        return success();
    }


    public function detail($params)
    {
        $model    = new SalerToolsGoodsTemplateModel();
        $template = $model->where('template_id', $params['template_id'])
            ->where('site_id', $this->site_id)
            ->findOrEmpty();

        return success($template->toArray());
    }

    /**
     * 获取模板参数
     */
    public function templateAttr($params)
    {
        $model = new SalerToolsGoodsTemplateModel();

        $template = $model->with(['template_id', 'category_id'], $params)
            ->findOrEmpty();

        if ($template->isEmpty()) return fail('模板不存在');

        $template = $template->toArray();

        return success($template['template_data']);

    }


}
