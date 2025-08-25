<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/7 19:19
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\job\LanguageTranslateJob;
use addon\saler_tools\app\model\SalerToolsLanguage;
use addon\saler_tools\app\model\SalerToolsLanguagePackage;
use think\db\Query;
use think\facade\Cache;
use think\facade\Log;
use ZipArchive;

/**
 *
 * Class LanguageService
 * @package addon\saler_tools\app\service
 */
class LanguageService extends BaseAdminService
{

    const CACHE_TAG = 'saler_tools_language_tag';

    const CACHE_KEY = 'saler_tools_language_key_';

    const JOB_TAG = 'saler_tools_language_job_tag';

    /** 任务完成数量 */
    const JOB_COMPLETE = 'saler_tools_language_job_complete';
    /** 任务总数量 */
    const JOB_TOTAL = 'saler_tools_language_job_total';

    public function list()
    {
        $list = (new SalerToolsLanguage())->select()->toArray();
        return success($list);
    }


    public function packageLists($params)
    {
        $model = new SalerToolsLanguagePackage();
        $model = $model->withSearch(['language_key', 'key', 'value'], $params)
            ->order('key', 'asc')
            ->order('id', 'asc');

        return success($this->pageQuery($model));
    }


    public function importData($data)
    {

        $model = new SalerToolsLanguagePackage();

        $language_key = $data['language_key'];

        $delKey     = [];
        $force      = $data['force'];
        $list       = $data['data'];
        $inset_data = [];
        $time       = date('Y-m-d H:i:s');
        foreach ($list as $item) {
            if (!$force) continue;
            $inset_data[] = [
                'language_key' => $language_key,
                'key'          => $item['key'],
                'value'        => $item['value'],
                'create_time'  => $time,
            ];
        }

        $delKey = array_column($inset_data, 'key');

        if (!empty($delKey)) {
            $model->where('language_key', $language_key)->whereIn('key', $delKey)->delete();
        }

        foreach ($inset_data as $key => $item) {
            try {
                $model->insert($item);
            } catch (\Exception $e) {
                dd($key, $item);
            }
        }

        Cache::tag(self::CACHE_TAG)->clear();

        return success();
    }

    public function editPackage($data)
    {
        $model = new SalerToolsLanguagePackage();

        $model->update($data, ['id' => $data['id']]);
        Cache::tag(self::CACHE_TAG)->clear();
        return success();
    }


    public function checkPackage($data)
    {
        $model = new SalerToolsLanguagePackage();

        $keys = $model->where('language_key', $data['language_key'])->whereIn('key', $data['keys'])->column('key');

        return success($keys);
    }

    public function package($key)
    {
        $cache_key = self::CACHE_KEY . $key;
        return cache_remember($cache_key, function () use ($key) {

            $model   = new SalerToolsLanguagePackage();
            $version = (new SalerToolsLanguage())->where('key', $key)->value('version');

            $list = $model->where('language_key', $key)
                ->order('key', 'asc')
                ->field('key,value')
                ->select()
                ->toArray();

            $list = array_column($list, 'value', 'key');

            return success($version, $list);

        }, self::CACHE_TAG);
    }


    public function version($key)
    {
        $version = (new SalerToolsLanguage())->where('key', $key)->value('version');
        return success([
            'version' => $version,
        ]);
    }


    public function buildUniappLanguage()
    {
        // 读取所有语言
        $list = (new SalerToolsLanguage())->select()->toArray();
        // 读取所有语言中包含配置页面的page.开头的数据
        $package_list = (new SalerToolsLanguagePackage())
            ->select()
            ->toArray();

        // 逐个生成文件并准备打包
        $file_list = [];
        foreach ($list as $item) {
            $lag_item = [
                'key'       => $item['key'],
                'file_name' => $item['key'] . '.json',
                'content'   => ''
            ];

            $content = [];
            foreach ($package_list as $package_item) {
                if ($package_item['language_key'] == $item['key']) {
                    $content[$package_item['key']] = $package_item['value'];
                }
            }

            $lag_item['content'] = json_encode($content, JSON_UNESCAPED_UNICODE);
            $file_list[]         = $lag_item;
        }

        // 创建压缩包
        $zip      = new \ZipArchive();
        $zip_file = create_download_file('uniapp国际化配置.zip');

        if (!$zip->open($zip_file['path'], ZIPARCHIVE::CREATE)) {
            return false;
        }

        // 把 $file_list 数据生成文件并放入压缩包内
        foreach ($file_list as $file) {
            $zip->addFromString($file['file_name'], $file['content']);
        }

        $zip->close();

        return success(['url' => $zip_file['path']]);

    }


    public function buildLanguage($key)
    {

        $list = (new SalerToolsLanguagePackage())->where('language_key', $key)->order('key', 'asc')->select()->toArray();

        $list = array_column($list, 'value', 'key');

        $json = $this->mergeKeys($list);

        $json = json_encode($json, JSON_UNESCAPED_UNICODE);

        $file = create_download_file($key . '.json');

        file_put_contents($file['path'], $json);

        return success(['url' => $file['path']]);

    }


    function mergeKeys($obj)
    {
        $result = [];

        foreach ($obj as $key => $value) {
            $parts   = explode('.', $key);
            $current = &$result;

            foreach ($parts as $index => $part) {
                if (!isset($current[$part]) || !is_array($current[$part])) {
                    $current[$part] = [];
                }
                if ($index === count($parts) - 1) {
                    $current[$part] = $value;
                } else {
                    $current = &$current[$part];
                }
            }
        }

        return $result;
    }


    /**
     * 获取c端国际化语言包
     */
    function getAppByC($key)
    {
        $cache_key = self::CACHE_KEY . 'app_c_' . $key;
        return cache_remember($cache_key, function () use ($key) {

            $model = new SalerToolsLanguagePackage();
            $list  = $model->where('language_key', $key)
                ->whereLike('key', 'appc.%')
                ->order('key', 'asc')
                ->field('key,value')
                ->select()
                ->toArray();
            $list  = array_column($list, 'value', 'key');
            return success($list);

        }, self::CACHE_KEY);
    }


    public function clearTranslate($key)
    {
        if ($key == 'zh-Hans') return fail('主语言不能被清空');
        $model = new SalerToolsLanguagePackage();
        $model->where('language_key', $key)->delete();
        return success();
    }


    public function translate($key)
    {

        $total = Cache::get(self::JOB_TOTAL);
        if (!empty($total)) {
            return fail('当前正在翻译中');
        }

        if ($key == 'zh-Hans') return fail('主语言不能被翻译');

        $list = (new SalerToolsLanguagePackage())
            ->where('language_key', 'zh-Hans')
            ->where('key', 'not in', function (Query $query) use ($key) {
                $query->table($query->getName())->where('language_key', $key)->field('key');
            })
            ->column('key,value');

        foreach ($list as $index => $item) {
            $item['to'] = $key;
            Log::debug(json_encode($item, JSON_UNESCAPED_UNICODE));
            Cache::set(self::CACHE_TAG . '_' . $index, $item);
        }

        Cache::set(self::JOB_COMPLETE, 0);
        Cache::set(self::JOB_TOTAL, count($list));

        LanguageTranslateJob::dispatch(['index' => 0]);

        return success();

    }


    /**
     * 获取任务进度
     */
    public function getJobProgress()
    {

        $total = Cache::get(self::JOB_TOTAL);

        if (!empty($total)) {
            return success([
                'complete' => Cache::get(self::JOB_COMPLETE),
                'total'    => Cache::get(self::JOB_TOTAL),
            ]);
        }
        return fail();

    }


    /**
     * 任务执行完成确认
     */
    public function jobComplete()
    {
        $total = Cache::get(self::JOB_TOTAL);

        if (!empty($total)) {
            Cache::delete(self::JOB_COMPLETE);
            Cache::delete(self::JOB_TOTAL);
            for ($i = 0; $i < $total; $i++) {
                Cache::delete(self::CACHE_TAG . '_' . $i);
            }
        }

        return success();

    }

    /**
     * 刷新语言版本
     */
    public function flushVersion()
    {
        Cache::tag(self::CACHE_TAG)->clear();
        $version = (new SalerToolsLanguage())->findOrEmpty()->value('version', 0);
        $version++;
        (new SalerToolsLanguage())->where(true)->update(['version' => $version]);
        return success();
    }

}
