<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/20 23:41
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\admin;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\NotifyTemplate as NotifyTemplateModel;
use core\exception\AdminException;


/**
 * 通知模板
 * Class NotifyTemplateService
 * @package addon\saler_tools\app\service\admin
 */
class NotifyTemplateService extends BaseAdminService
{

    

    public function lists()
    {
        $model = new NotifyTemplateModel();
        $model = $model->with(['language'])->field('id,title,key,lang,is_use');
        return success($this->pageQuery($model));
    }


    public function add($data)
    {
        $model = new NotifyTemplateModel();
        $model->create($data);
        return success();
    }


    public function edit($data)
    {
        $model = new NotifyTemplateModel();

        $config = $model->where('id', $data['id']);

        $config->save($data);

        return success();
    }


    public function del($id)
    {
        $model = new NotifyTemplateModel();
        $model->where('id', $id)->delete();
        return success();
    }


    public function modify($data)
    {
        $model    = new NotifyTemplateModel();
        $template = $model->where('id', $data['id'])->findOrEmpty();

        if ($template->isEmpty()) throw new AdminException('找不到模板');

        return success();
    }


    public function getTemplate($template_key, $lang_key)
    {
        $model    = new NotifyTemplateModel();
        $template = $model->where('key', $template_key)->where('lang', $lang_key)->findOrEmpty();

        if ($template->isEmpty()) throw new AdminException('找不到模板');

        return $template->toArray();
    }

    public function detail($id)
    {
        $model    = new NotifyTemplateModel();
        $template = $model->where('id', $id)->findOrEmpty();
        if ($template->isEmpty()) throw new AdminException('找不到模板');
        return success($template->toArray());
    }

}
