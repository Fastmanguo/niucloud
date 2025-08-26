<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/17 14:59
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\job;

use addon\saler_tools\app\model\SalerToolsLanguagePackage;
use addon\saler_tools\app\service\LanguageService;
use addon\saler_tools\app\service\translate\YoudaoService;
use core\base\BaseJob;
use think\facade\Cache;
use think\facade\Db;

/**
 *
 * Class LanguageTranslateJob
 * @package addon\saler_tools\app\job
 */
class LanguageTranslateJob extends BaseJob
{
    public function doJob($index = 0)
    {

        $data = Cache::get(LanguageService::CACHE_TAG . '_' . $index);

        $translateService = new YoudaoService();

        for ($i = 0; $i < 5; $i++) {
            try {
                $value = $translateService->translate($data['value'], $data['to'], 'zh-Hans');
                SalerToolsLanguagePackage::create([
                    'language_key' => $data['to'],
                    'key'          => $data['key'],
                    'value'        => $value,
                ]);
                break;
            } catch (\Exception $e) {
                sleep(5);
            }
        }

        $index = $index + 1;

        Cache::set(LanguageService::JOB_COMPLETE, Cache::get(LanguageService::JOB_COMPLETE) + 1);

        if ($index < Cache::get(LanguageService::JOB_TOTAL)) {
            LanguageTranslateJob::dispatch(['index' => $index]);
        } else {
            // 去除重复的数据
            $model = new SalerToolsLanguagePackage();
            Db::execute("DELETE t1 FROM {$model->getName()} t1 JOIN {$model->getName()} t2 ON t1.key = t2.key AND t1.id < t2.id where t1.language_key = '{$data['to']}' AND t2.language_key = '{$data['to']}'");
        }

        return true;

    }

}
