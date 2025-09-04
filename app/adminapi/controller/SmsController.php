<?php
// +----------------------------------------------------------------------
// | 短信验证码控制器
// +----------------------------------------------------------------------
// | Author: Niucloud Team
// +----------------------------------------------------------------------

namespace app\adminapi\controller;

use app\service\core\notice\NoticeService;
use core\base\BaseAdminController;
use think\facade\Cache;
use think\helper\Str;
use think\Response;

/**
 * 短信验证码控制器
 */
class SmsController extends BaseAdminController
{
    private $appId = "";
    private $secretId = "";
    private $secretKey = "";
    private $templateId = "";
    private $sign = "";
    private $endpoint = 'sms.tencentcloudapi.com';
    private $service = 'sms';
    private $version = '2021-01-11';
    private $action = 'SendSms';
    private $region = 'ap-guangzhou';
    /**
     * 发送短信验证码
     * @return Response
     */
    public function sendIphoneSms()
    {
        $data = $this->request->params([
            ['mobile', '']
        ]);

        // 验证手机号格式
        if (empty($data['mobile']) || !preg_match('/^1[3-9]\d{9}$/', $data['mobile'])) {
            return fail('mobile_format_error');
        }

        $mobile = $data['mobile'];

        // 加手机号下发频次锁
        $res = Cache::get('SMS:send:' . $mobile);
        if ($res) {
            return fail('one_minute_before_sending');
        }

        Cache::set('SMS:send:' . $mobile, 1, 60);

        // 生成验证码
        $code = str_pad(random_int(1, 999999), 6, 0, STR_PAD_LEFT);
//        $result = $this->sendSMS($mobile, [$code]);
        $result = True;
        // 发送短信
//        try {
//            $site_id = $this->request->adminSiteId();
//            $result = NoticeService::send($site_id, 'member_verify_code', [
//                'code' => $code,
//                'mobile' => $mobile
//            ]);

        if ($result) {
            // 将验证码存入缓存
            $key = md5(uniqid('', true));
            $cache_tag_name = "sms_key" . $mobile;
            $this->clearSmsCode($mobile);
            Cache::tag($cache_tag_name)->set($key, [
                'mobile' => $mobile,
                'code' => $code,
                'time' => time()
            ], 600);

            return success(['key' => $key,"code"=>$code]);
        } else {
            return fail('sms_send_failed');
        }
//        } catch (\Exception $e) {
//            return fail('sms_send_failed: ' . $e->getMessage());
//        }
    }

    /**
     * 验证短信验证码
     * @return Response
     */
    public function verifySms()
    {
        $data = $this->request->params([
            ['mobile', ''],
            ['sms_code', ''],
            ['mobile_key', '']
        ]);

        $mobile = $data['mobile'];
        $sms_code = $data['sms_code'];
        $mobile_key = $data['mobile_key'];

        // 验证参数
        if (empty($mobile) || !preg_match('/^1[3-9]\d{9}$/', $mobile)) {
            return fail('mobile_format_error');
        }

        if (empty($sms_code)) {
            return fail('please_sms_code_require');
        }

        if (empty($mobile_key)) {
            return fail('please_mobile_key_require');
        }

        try {
            $result = $this->verifySmsCode($mobile, $sms_code, $mobile_key);
            if ($result) {
                return success(['message' => '验证码验证成功']);
            } else {
                return fail('sms_code_error');
            }
        } catch (\Exception $e) {
            return fail($e->getMessage());
        }
    }

    /**
     * 清除短信验证码缓存
     */
    private function clearSmsCode($mobile)
    {
        $cache_tag_name = "sms_key" . $mobile;
        Cache::tag($cache_tag_name)->clear();
    }

    /**
     * 验证短信验证码
     */
    private function verifySmsCode($mobile, $sms_code, $mobile_key)
    {
        if (empty($mobile_key) || empty($sms_code)) {
            throw new \Exception('mobile_captcha_error');
        }

        $cache = Cache::get($mobile_key);
        if (empty($cache)) {
            throw new \Exception('mobile_captcha_expired');
        }

        $temp_mobile = $cache['mobile'];
        $temp_code = $cache['code'];
        $temp_time = $cache['time'];

        // 检查验证码是否过期（10分钟）
        if (time() - $temp_time > 600) {
            $this->clearSmsCode($temp_mobile);
            throw new \Exception('mobile_captcha_expired');
        }

        if ($temp_mobile != $mobile || $temp_code != $sms_code) {
            throw new \Exception('mobile_captcha_error');
        }

        // 验证成功后清除缓存
        $this->clearSmsCode($temp_mobile);
        return true;
    }



    public function sendSMS($phoneNumber, $templateParams = []) {
        $timestamp = time();

        // 构建请求参数 - 中国大陆手机号不需要加+号
        $params = [
            'PhoneNumberSet' => [$phoneNumber],  // 中国大陆手机号直接使用
            'SmsSdkAppId' => $this->appId,
            'SignName' => $this->sign,
            'TemplateId' => $this->templateId,
            'TemplateParamSet' => array_values($templateParams)  // 转换为索引数组
        ];

        $payload = json_encode($params);

        $authorization = $this->sign($payload, $timestamp);

        // 构建请求头
        $headers = [
            'Content-Type: application/json; charset=utf-8',
            'Host: ' . $this->endpoint,
            'X-TC-Action: ' . $this->action,
            'X-TC-Version: ' . $this->version,
            'X-TC-Timestamp: ' . $timestamp,
            'X-TC-Region: ' . $this->region,
            'Authorization: ' . $authorization
        ];

        // 发送请求
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://' . $this->endpoint,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        if ($error) {
            return fail('curl_error');
        }

        $result = json_decode($response, true);
        echo $httpCode;

        print_r($result);
        if ($httpCode === 200 && isset($result['Response']['SendStatusSet'][0]['Code'])) {
            echo "------------------------------------";
            $sendStatus = $result['Response']['SendStatusSet'][0];
            if ($sendStatus['Code'] === 'Ok') {
                return success([
                    'success' => true,
                    'message' => '短信发送成功',
                    'data' => $result
                ]);
            } else {
                return success([
                    'success' => false,
                    'error' => '发送失败: ' . $sendStatus['Message'],
                    'code' => $sendStatus['Code']
                ]);
//                return fail('send_error');
            }
        } else {
            return success([
                'success' => false,
                'error' => '请求失败，HTTP状态码: ' . $httpCode,
                'response' => $result
            ]);
//            return fail('request_error');
        }
    }


    private function sign($payload, $timestamp) {
        $date = gmdate('Y-m-d', $timestamp);
        $credentialScope = $date . '/' . $this->service . '/tc3_request';

        // 1. 生成 CanonicalRequest
        $httpRequestMethod = 'POST';
        $canonicalUri = '/';
        $canonicalQueryString = '';
        $canonicalHeaders = "content-type:application/json; charset=utf-8\n" .
            "host:" . $this->endpoint . "\n";
        $signedHeaders = 'content-type;host';
        $hashedRequestPayload = hash('SHA256', $payload);
        $canonicalRequest = $httpRequestMethod . "\n" .
            $canonicalUri . "\n" .
            $canonicalQueryString . "\n" .
            $canonicalHeaders . "\n" .
            $signedHeaders . "\n" .
            $hashedRequestPayload;

        // 2. 生成 StringToSign
        $algorithm = 'TC3-HMAC-SHA256';
        $requestTimestamp = $timestamp;
        $stringToSign = $algorithm . "\n" .
            $requestTimestamp . "\n" .
            $credentialScope . "\n" .
            hash('SHA256', $canonicalRequest);

        // 3. 计算签名
        $secretDate = hash_hmac('SHA256', $date, 'TC3' . $this->secretKey, true);
        $secretService = hash_hmac('SHA256', $this->service, $secretDate, true);
        $secretSigning = hash_hmac('SHA256', 'tc3_request', $secretService, true);
        $signature = hash_hmac('SHA256', $stringToSign, $secretSigning);

        // 4. 生成 Authorization
        $authorization = $algorithm . ' ' .
            'Credential=' . $this->secretId . '/' . $credentialScope . ', ' .
            'SignedHeaders=' . $signedHeaders . ', ' .
            'Signature=' . $signature;

        return $authorization;
    }
}
