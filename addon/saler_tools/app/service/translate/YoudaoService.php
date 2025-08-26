<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/15 15:42
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\translate;

use addon\saler_tools\app\common\HttpClient;
use addon\saler_tools\app\common\Utils;

/**
 *
 * Class YoudaoService
 * @package addon\saler_tools\app\service\translate
 */
class YoudaoService
{

    private $lang_list = [
        [
            'name'       => '简体中文',
            'sys_key'    => 'zh-Hans',
            'youdao_key' => 'zh-CHS'
        ],
        [
            'name'       => '繁体中文',
            'sys_key'    => 'zh-Hant',
            'youdao_key' => 'zh-CHT'
        ],
        [
            'name'       => '英文',
            'sys_key'    => 'en',
            'youdao_key' => 'en'
        ],
        [
            'name'       => '日文',
            'sys_key'    => 'ja',
            'youdao_key' => 'ja'
        ],
        [
            'name'       => '韩文',
            'sys_key'    => 'ko',
            'youdao_key' => 'ko'
        ],
        [
            'name'       => '法文',
            'sys_key'    => 'fr',
            'youdao_key' => 'fr'
        ],
        [
            'name'       => '德文',
            'sys_key'    => 'de',
            'youdao_key' => 'de'
        ],
        [
            'name'       => '西班牙文',
            'sys_key'    => 'es',
            'youdao_key' => 'es'
        ],
        [
            'name'       => '意大利文',
            'sys_key'    => 'it',
            'youdao_key' => 'it'
        ],
    ];


    const APP_ID    = '5d61b1b540a5dbba';
    const SECRET_KEY = 'cKPWG9b2BXcGuAPCkUZLPudwQnRjciuV';

    public function aiTranslate()
    {
        $url = 'https://openapi.youdao.com/llm_trans';


    }

    private function getInput($str)
    {
        if (empty($str)) {
            return null;
        }
        $len = mb_strlen($str, 'utf-8');
        return $len <= 20 ? $str : (mb_substr($str, 0, 10) . $len . mb_substr($str, $len - 10, $len));
    }

    private function doPost($url, $data, $header = [])
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_URL, $url);
        $r           = curl_exec($curl);
        curl_close($curl);
        return json_decode($r, true);
    }

    public function translate($str, $to, $from = '', $params = [])
    {
        $url = 'https://openapi.youdao.com/api';

        $to   = $this->sysLangToYoudaoLang($to);
        $from = $this->sysLangToYoudaoLang($from);

        $data = [
            'q'        => $str,
            'from'     => $from,
            'to'       => $to,
            'appKey'   => self::APP_ID,
            'salt'     => Utils::createno('YOUDAPAPI'),
            'signType' => 'v3',
            'curtime'  => time()
        ];

        $data['sign'] = hash('sha256', self::APP_ID . $this->getInput($data['q']) . $data['salt'] . $data['curtime'] . self::SECRET_KEY);

        $res = $this->doPost($url, $data);

        if (!empty($res['translation'])){
            return $res['translation'][0];
        }
        throw new \Exception('翻译失败');

    }

    private function youdaoLangToSysLang($key)
    {
        foreach ($this->lang_list as $item) {
            if ($item['youdao_key'] == $key) {
                return $item['sys_key'];
            }
        }

    }

    private function sysLangToYoudaoLang($key)
    {
        foreach ($this->lang_list as $item) {
            if ($item['sys_key'] == $key) {
                return $item['youdao_key'];
            }
        }
    }

}
