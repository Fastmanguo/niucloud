<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/11 21:54
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\dict\cache\SysCache;
use addon\saler_tools\app\model\SalerToolsGoodsCategory;
use addon\saler_tools\app\model\SalerToolsLanguagePackage;
use think\image\Exception;

/**
 *
 * Class GoodsCategoryService
 * @package addon\saler_tools\app\service
 */
class GoodsCategoryService extends BaseAdminService
{

    public function lists($params)
    {
        $model = new SalerToolsGoodsCategory();
        $model = $model->withSearch(['category_name'], $params)->with(['template'])->order('sort desc,category_id desc');

        return success($this->pageQuery($model));
    }

    public function list()
    {
        return cache_remember($this->app->appCache->goods_category, function () {

            $model = new SalerToolsGoodsCategory();

            $list = $model
                ->where('is_show', 1)
                ->order('sort desc,category_id asc')
                ->field('category_name,category_full_name,category_id,category_key')
                ->select();

            $list = $list->toArray();

            return success($list);

        }, $this->app->appCache->app_tag);
    }


    public function add($data)
    {
        $data['site_id'] = $this->site_id;
        $model           = new SalerToolsGoodsCategory();

        // 检测标识是否为空
        $check = $model->where('category_key', $data['category_key'])->findOrEmpty();

        if (!$check->isEmpty()) return fail('标识已存在');

        $model->create($data);
        return success();
    }


    public function edit($data)
    {
        $model = new SalerToolsGoodsCategory();

        $category = $model->where('category_id', $data['category_id'])->where('site_id', $this->site_id)->findOrEmpty();

        if ($category->isEmpty()) return fail('分类不存在');

        // 检测标识是否为空
        $check = $model->where('category_key', $data['category_key'])->where('category_id', '<>', $data['category_id'])->findOrEmpty();

        if (!$check->isEmpty()) return fail('标识已存在');

        $category->save($data);

        return success();

    }

    public function del($category_id)
    {
        $model = new SalerToolsGoodsCategory();
        $model->where('category_id', $category_id)->where('site_id', $this->site_id)->delete();
        return success();
    }

    public function transfer()
    {
        $model = new SalerToolsGoodsCategory();

        $list = $model->with(['templateData'])->select()->toArray();

        $language_packages_model = new SalerToolsLanguagePackage();

        // 强制清理历史国际化残留数据
        $del_key = ['QT', 'PS', 'FS', 'XX', 'ZB', 'XB', 'BW'];

        $language_packages_model->startTrans();
        try {
            foreach ($del_key as $key) {
                $language_packages_model->where('key', $key)->delete();
                $language_packages_model->where('key', 'appc.' . $key)->delete();
            }
            $language_packages_model->where('key', 'df.%')->delete();
            // 导入分类国际化
            foreach ($list as $item) {
                $language = $language_packages_model->where('key', $item['category_key'])
                    ->where('language_key', 'zh-Hans')
                    ->findOrEmpty();

                if (!$language->isEmpty()) {
                    $language->value = $item['category_name'];
                    $language->save();
                } else {
                    $language_packages_model->create([
                        'language_key' => 'zh-Hans',
                        'key'          => $item['category_key'],
                        'value'        => $item['category_name']
                    ]);
                }

                // 填充小程序国际化内容
                $language = $language_packages_model->where('key', 'appc.' . $item['category_key'])
                    ->where('language_key', 'zh-Hans')
                    ->findOrEmpty();

                if (!$language->isEmpty()) {
                    $language->value = $item['category_name'];
                    $language->save();
                } else {
                    $language_packages_model->create([
                        'language_key' => 'zh-Hans',
                        'key'          => 'appc.' . $item['category_key'],
                        'value'        => $item['category_name']
                    ]);
                }
                if (empty($item['template_data'])) continue;
                // 填充自定义表单国际化
                foreach ($item['template_data'] as $attr) {

                    $diyform = [
                        [
                            'language_key' => 'zh-Hans',
                            'key'          => 'df.' . $attr['key'] . '.label',
                            'value'        => $attr['label']
                        ],
                        [
                            'language_key' => 'zh-Hans',
                            'key'          => 'df.' . $attr['key'] . '.tips',
                            'value'        => $attr['tips']
                        ],
                        [
                            'language_key' => 'zh-Hans',
                            'key'          => 'df.' . $attr['key'] . '.placeholder',
                            'value'        => $attr['placeholder']
                        ],
                    ];

                    $language_packages_model->insertAll($diyform);


                }

            }

            // TODO：导入模板国际化

            $language_packages_model->commit();
            return success('导入完成');

        } catch (Exception $e) {
            $language_packages_model->rollback();
            return fail($e->getMessage());
        }

    }

    // 获取分类列表以及模板配置
    public function getCategoryAndTemplate()
    {
        $model = new SalerToolsGoodsCategory();
        $list  = $model->with(['templateData'])->order('sort desc,category_id desc')->select()->toArray();
        return success($list);
    }

}
