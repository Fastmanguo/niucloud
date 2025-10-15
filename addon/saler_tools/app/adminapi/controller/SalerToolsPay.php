<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 集成支付宝沙箱支付
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller;

use addon\saler_tools\app\common\BaseAdminController;
use Yansongda\Pay\Pay as YansongdaPay;

class SalerToolsPay extends BaseAdminController
{

    protected function getAlipayConfig()
    {
        // 支付宝沙箱配置
        $privateKey = '';
        $alipayPublicKey = '';
        
        // 从 appshiyao.txt 文件读取应用私钥
        if (file_exists(__DIR__ . '/appshiyao.txt')) {
            $privateKey = file_get_contents(__DIR__ . '/appshiyao.txt');
            // 清理私钥内容，移除可能的换行符和空格
            $privateKey = trim($privateKey);
        }
        
        // 从环境变量读取支付宝公钥（可选）
        if (env('ALIPAY_PUBLIC_KEY')) {
            $alipayPublicKey = env('ALIPAY_PUBLIC_KEY');
        }
        
        // 如果私钥为空，使用默认值（开发测试用）
        if (empty($privateKey)) {
            $privateKey = '...'; // 请替换为您的沙箱应用私钥
        }
        
        // 如果支付宝公钥为空，使用默认值（开发测试用）
        if (empty($alipayPublicKey)) {
            $alipayPublicKey = '...'; // 请替换为沙箱支付宝公钥
        }

        $config = [
            'alipay' => [
                'default' => [
                    // 沙箱 appid
                    'app_id' => '9021000156665942',
                    // 应用私钥（从文件读取）
                    'app_secret_cert' => $privateKey,
                    // 支付宝公钥（字符串形式）
                    'alipay_public_key' => $alipayPublicKey,
                    // 证书路径配置
                    'app_public_cert_path' => __DIR__ . '/appPublicCert.crt',
                    'alipay_public_cert_path' => __DIR__ . '/alipayPublicCert.crt',
                    'alipay_root_cert_path' => __DIR__ . '/alipayRootCert.crt',
                    // 回调地址
                    'return_url' => request()->domain() . '/adminapi/saler_tools/pay/alipay/return',
                    'notify_url' => request()->domain() . '/adminapi/saler_tools/pay/alipay/notify',
                    // 沙箱模式
                    'mode' => YansongdaPay::MODE_SANDBOX,
                ],
            ],
            'logger' => [
                'enable' => true,
                'file' => runtime_path() . 'log/yansongda.log',
                'level' => 'debug',
                'type' => 'single',
            ],
        ];
        return $config;
    }

    /**
     * 生成PC网页支付
     */
    public function alipayWeb()
    {
        try {
            $config = $this->getAlipayConfig();

            $order = [
                'out_trade_no' => date('YmdHis') . mt_rand(1000, 9999),
                'total_amount' => '0.01',
                'subject' => 'SaaS测试订单',
            ];

            $response = YansongdaPay::alipay($config)->web($order);
            return response($response->getBody()->getContents());
        } catch (\Exception $e) {
            return error($e->getMessage());
        }
    }

    /**
     * 生成手机网页支付
     */
    public function alipayWap()
    {
        try {
            $config = $this->getAlipayConfig();

            $order = [
                'out_trade_no' => date('YmdHis') . mt_rand(1000, 9999),
                'total_amount' => '0.01',
                'subject' => 'SaaS测试订单',
            ];

            $response = YansongdaPay::alipay($config)->wap($order);
            return response($response->getBody()->getContents());
        } catch (\Exception $e) {
            return error($e->getMessage());
        }
    }

    /**
     * 生成APP支付
     */
    public function alipayApp()
    {
        try {
            $config = $this->getAlipayConfig();

            $order = [
                'out_trade_no' => date('YmdHis') . mt_rand(1000, 9999),
                'total_amount' => '0.01',
                'subject' => 'SaaS测试订单',
            ];

            $response = YansongdaPay::alipay($config)->app($order);
            return success($response);
        } catch (\Exception $e) {
            return error($e->getMessage());
        }
    }

    /**
     * 支付宝异步通知
     */
    public function alipayNotify()
    {
        try {
            $config = $this->getAlipayConfig();
            $alipay = YansongdaPay::alipay($config);
            $data = $alipay->callback();
            $alipay->verify($data->all());

            $tradeStatus = $data->get('trade_status');
            if (in_array($tradeStatus, ['TRADE_SUCCESS', 'TRADE_FINISHED'], true)) {
                $outTradeNo = $data->get('out_trade_no');
                $tradeNo = $data->get('trade_no');
                $totalAmount = $data->get('total_amount');
                
                // 记录支付成功日志
                \think\facade\Log::info('支付宝支付成功', [
                    'out_trade_no' => $outTradeNo,
                    'trade_no' => $tradeNo,
                    'total_amount' => $totalAmount,
                    'trade_status' => $tradeStatus
                ]);
                
                // TODO: 根据$outTradeNo 幂等更新订单状态为已支付，记录$tradeNo
                // 这里可以调用您的订单服务来更新订单状态
            }

            return $alipay->success();
        } catch (\Exception $e) {
            \think\facade\Log::error('支付宝异步通知处理失败', [
                'error' => $e->getMessage(),
                'data' => request()->post()
            ]);
            return error($e->getMessage());
        }
    }

    /**
     * 支付宝同步返回
     */
    public function alipayReturn()
    {
        // 同步返回页面仅展示结果，实际以notify为准
        return success(['msg' => '支付流程已完成，请以异步通知结果为准']);
    }
}


