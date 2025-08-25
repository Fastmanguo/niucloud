<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/12/13 23:20
// +----------------------------------------------------------------------

namespace app\test\controller;

use addon\saler_tools\app\common\HttpClient;
use think\facade\Cache;
use think\facade\Db;

/**
 *
 * Class SdjGather
 * @package app\test\controller
 */
class SdjGather
{

    private function sgin(array $data, string $key = "sign_key"): string
    {
        try {
            // 获取所有键并排序
            $keys = array_keys($data);
            sort($keys);

            $pairs = [];
            foreach ($keys as $k) {
                if ($k == 'sign') continue;
                $pairs[] = $k . '=' . $data[$k];
            }

            $query = implode('&', $pairs);
            return md5($query . $key);
        } catch (\Exception $e) {
            return '';
        }
    }


    private function getData($e)
    {
        $e = json_decode($e, true);
        // 解密data
        /**
         * const t = ko.enc.Utf8.parse("LuxuryAdmin12345"), n = ko.enc.Utf8.parse("12345LuxuryAdmin"),
         * r = ko.enc.Hex.parse(e), o = ko.enc.Base64.stringify(r),
         * a = ko.AES.decrypt(o, t, {iv: n, mode: ko.mode.CBC, padding: ko.pad.Pkcs7}).toString(ko.enc.Utf8);
         * console.log('解密', JSON.parse(a));
         */
        $e = $e['data'];
        return json_decode($this->decryptAES($e), true);
    }

    function decryptAES($data)
    {
        $key = "LuxuryAdmin12345"; // 密钥
        $iv  = "12345LuxuryAdmin";  // 初始化向量

        $base64Data = hex2bin($data);

        $decrypted = openssl_decrypt($base64Data, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
        // 去除PKCS7填充
        return $decrypted;

    }

    // PKCS7填充去除函数
    function pkcs7_unpad($text)
    {
        $length = strlen($text);
        $unpad  = ord($text[$length - 1]);
        if ($unpad > $length) {
            throw new Exception("Invalid padding");
        }
        return substr($text, 0, -$unpad);
    }

    // 采集系列 以及 型号
    public function actionGetSeries($s_id)
    {

        $url = 'https://pc-api.shedangjia.com/shop/user/brand/series/listSeriesNameByBrandId';

        $data = [
            'token'         => 'Adminc3dee2cc7d2a4c07b3924059ec64764a'
            , 'apiVersion'  => '3.5.30'
            , 'appVersion'  => '3.5.30'
            , 'brandId'     => (int)$s_id
            , 'deviceId'    => 'Win64; x64; rv:133.0) Gecko/20100101 Firefox/133.0'
            , 'timestamp'   => '1734106845214'
            , 'platform'    => 'pc'
            , 'phoneType'   => 'pc'
            , 'phoneSystem' => 'pc'
            , 'netType'     => 'wifi'
            , 'channel'     => "0"
            , 'sign'        => 'dd7723bcb2f82f678236d2658977695c'
        ];

        $data['timestamp'] = (int)microtime(true) * 1000;

        $data['sign'] = $this->sgin($data);

        // 转成表单数据
        $data = http_build_query($data);

        $res = HttpClient::post($url, $data);

        $data = $this->getData($res);

        return $data;

    }


    public function actionModel($series_id)
    {
        $url = 'https://pc-api.shedangjia.com/shop/user/brand/model/listModelNameBySeriesId';

        $data = [
            'token'       => "Adminc3dee2cc7d2a4c07b3924059ec64764a",
            'apiVersion'  => "3.5.30",
            'appVersion'  => "3.5.30",
            'seriesId'    => (int)$series_id,
            'deviceId'    => "Win64; x64; rv:133.0) Gecko/20100101 Firefox/133.0",
            'timestamp'   => "1734114397708",
            'platform'    => "pc",
            'phoneType'   => "pc",
            'phoneSystem' => "pc",
            'netType'     => "wifi",
            'channel'     => "0",
            'sign'        => "0a18230f01be51aaa996466e230c30e4"
        ];


        $data['timestamp'] = (int)microtime(true) * 1000;

        $data['sign'] = $this->sgin($data);

        // 转成表单数据
        $data = http_build_query($data);

        $res = HttpClient::post($url, $data);

        return $this->getData($res);

    }


    public function actionGetModel()
    {

        $brand_list = Db::name('saler_tools_goods_brand')->select()->toArray();
        $time       = date('Y-m-d H:i:s');
        foreach ($brand_list as $k => $v) {
            try {
                $series_list = $this->actionGetSeries($v['s_id']);
                $inset_data  = [];
                foreach ($series_list as $kk => $vv) {
                    $inset_data[] = [
                        's_id'        => $vv['seriesId'],
                        'brand_id'    => $v['brand_id'],
                        'series_name' => $vv['seriesName'],
                        'create_time' => $time,
                    ];
                }
                if (!empty($inset_data)) {
                    Db::name('saler_tools_goods_series')->insertAll($inset_data);
                }
                sleep(1);
            } catch (\Exception $e) {
                dump($e->getMessage());
            }
        }

    }

    public function actionGetModelLists()
    {

        $page = Cache::get('actionGetModelLists', 5);

        $brand_list = Db::name('saler_tools_goods_series')->page($page, 100)->select()->toArray();
        $time       = date('Y-m-d H:i:s');
        Cache::set('actionGetModelLists', $page + 1);
        foreach ($brand_list as $k => $v) {
            try {
                $series_list = $this->actionModel($v['s_id']);
                $inset_data  = [];
                foreach ($series_list as $kk => $vv) {
                    $inset_data[] = [
                        's_id'        => $vv['modelId'],
                        'site_id'     => 0,
                        'series_id'   => $v['series_id'],
                        'brand_id'    => $v['brand_id'],
                        'model_name'  => $vv['modelName'],
                        'attr_data'   => '[]',
                        'create_time' => $time,
                    ];
                }
                if (!empty($inset_data)) {
                    Db::name('saler_tools_goods_model')->insertAll($inset_data);
                }
            } catch (\Exception $e) {
                dump($e->getMessage());
            }
        }

        echo 'ok:' . $page;

    }


}
