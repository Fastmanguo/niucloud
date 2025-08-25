<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/7 19:20
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\LanguageService;

/**
 *
 * Class Language
 * @package addon\saler_tools\app\adminapi\controller\admin
 */
class Language extends BaseAdminController
{

    public function list()
    {
        return app(LanguageService::class)->list();
    }


    public function packageLists()
    {
        $data = $this->_vali([
            'language_key.query' => '',
            'key.query'          => '',
            'value.query'        => '',
        ]);
        return app(LanguageService::class)->packageLists($data);
    }


    public function editPackage()
    {
        $data = $this->_vali([
            'id.require'           => '语言包ID不能为空',
            'key.require'          => '语言包标识不能为空',
            'language_key.require' => '语言包所属语言标识不能为空',
            'value.require'        => '语言包内容不能为空',
        ]);
        return app(LanguageService::class)->editPackage($data);
    }


    /**
     * 检测语言包中的键值是否重复
     */
    public function checkPackage()
    {
        $data = $this->_vali([
            'language_key.require' => '语言包所属语言标识不能为空',
            'keys.require'         => '语言包内容不能为空',
        ]);
        return app(LanguageService::class)->checkPackage($data);
    }


    public function importData()
    {
        $data = $this->_vali([
            'language_key.require' => '语言包所属语言标识不能为空',
            'data.require'         => '导入数据不能为空',
            'force.default'        => false,
        ]);

        return app(LanguageService::class)->importData($data);

    }



    /**
     * 生成uniapp页面语言包
     */
    public function buildUniappLanguage()
    {
        return app(LanguageService::class)->buildUniappLanguage();
    }

    public function buildLanguage($key)
    {
        return app(LanguageService::class)->buildLanguage($key);
    }



    /**
     * 清空翻译翻译数据
     */
    public function clearTranslate($key)
    {
        return app(LanguageService::class)->clearTranslate($key);
    }


    /**
     * 翻译语言数据数据
     */
    public function translate($key)
    {
        set_time_limit(0);
        return app(LanguageService::class)->translate($key);
    }


    public function taskProgress()
    {
        return app(LanguageService::class)->getJobProgress();
    }

    public function taskComplete()
    {
        return app(LanguageService::class)->jobComplete();
    }

    public function flushVersion()
    {
        return app(LanguageService::class)->flushVersion();
    }

}
