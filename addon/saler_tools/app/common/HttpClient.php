<?php
// +----------------------------------------------------------------------
// | sd_dx_php
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/10/27 3:03
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\common;

use think\facade\Log;
use think\helper\Str;

/**
 *
 * Class HttpClient
 * @package addon\bpms\app\service\http
 */
class HttpClient
{

    const USER_AGENT = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/536.11 (KHTML, like Gecko) Chrome/20.0.1132.57 Safari/536.11";

    public static function request(string $method, string $location, array $options = [])
    {
        // GET 参数设置
        if (!empty($options['query'])) {
            $location .= strpos($location, '?') !== false ? '&' : '?';
            if (is_array($options['query'])) {
                $location .= http_build_query($options['query']);
            } elseif (is_string($options['query'])) {
                $location .= $options['query'];
            }
        }

        $curl = curl_init();
        // Agent 代理设置
        curl_setopt($curl, CURLOPT_USERAGENT, $options['user-agent'] ?? static::USER_AGENT);
        // Cookie 信息设置
        if (!empty($options['cookie'])) {
            curl_setopt($curl, CURLOPT_COOKIE, $options['cookie']);
        }
        // Header 头信息设置
        if (!empty($options['headers'])) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $options['headers']);
        }

        // 写入cookie
        if (!empty($options['cookie_file'])) {
            curl_setopt($curl, CURLOPT_COOKIEJAR, $options['cookie_file']);
            curl_setopt($curl, CURLOPT_COOKIEFILE, $options['cookie_file']);
        }

        // 设置请求方式
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        if (strtolower($method) === 'head') {
            curl_setopt($curl, CURLOPT_NOBODY, 1);
        } elseif (!empty($options['data'])) { // POST 请求
            if (is_array($options['data'])) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($options['data']));
            } else {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $options['data']);
            }
        }
        // 请求超时设置
        if (isset($options['timeout']) && is_numeric($options['timeout'])) {
            curl_setopt($curl, CURLOPT_TIMEOUT, $options['timeout']);
        } else {
            curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        }
        // 是否返回前部内容
        if (empty($options['returnHeader'])) {
            curl_setopt($curl, CURLOPT_HEADER, false);
        } else {
            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        }
        curl_setopt($curl, CURLOPT_URL, $location);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $content = curl_exec($curl);
        curl_close($curl);

        return $content;
    }

    public static function get(string $url, $data = [], array $options = [])
    {
        $options['query'] = $data;
        return static::request('get', $url, $options);
    }

    public static function getJson($url, $params = [])
    {
        $res = static::get($url, $params);
        return json_decode($res, true);
    }

    public static function post($url, $data = [], $options = [])
    {
        $options['data'] = $data;
        return static::request('post', $url, $options);
    }

    public static function postJson($url, $data = [], $options = [])
    {
        $res = static::post($url, $data, $options);
        return json_decode($res, true);
    }

    public static function postXml($url, $data = [], $options = [])
    {
        $xml = "<xml>";
        foreach ($data as $k => $v) {
            $xml .= "<{$k}>" . $v . "</{$k}>";
        }
        $xml .= "</xml>";
        return static::post($url, $xml, $options);
    }

    public static function submit(string $url, array $data = [], array $file = [], $option = [])
    {
        [$line, $boundary] = [[], Str::random(18)];
        foreach ($data as $key => $value) {
            $line[] = "--{$boundary}";
            $line[] = "Content-Disposition: form-data; name=\"{$key}\"";
            $line[] = "";
            $line[] = $value;
        }

        if (!empty($file) && is_array($file)) {

            if (!isset($file[0])) $file = [$file];

            foreach ($file as $key => $value) {
                if (isset($value['field']) && isset($value['name'])) {
                    $line[] = "--{$boundary}";
                    $line[] = "Content-Disposition: form-data; name=\"{$value['field']}\"; filename=\"{$value['name']}\"";
                    $line[] = "";
                    $line[] = $value['content'];
                }
            }
        }

        $line[]         = "--{$boundary}--";
        $header[]       = "Content-type:multipart/form-data;boundary={$boundary}";
        $option['data'] = join("\r\n", $line);

        if (empty($option['headers'])) {
            $option['headers'] = $header;
        } else {
            $option['headers'] = array_merge($option['headers'], $header);
        }

        return static::request('POST', $url, $option);

    }


}
