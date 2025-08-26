<?php
// 测试邮件发送脚本
require_once 'vendor/autoload.php';

use addon\saler_tools\app\service\notify\EmailNotifyService;
use addon\saler_tools\app\service\admin\SettingService;
use addon\saler_tools\app\service\admin\NotifyTemplateService;

try {
    echo "开始测试邮件发送功能...\n";
    
    // 1. 检查邮件配置
    echo "1. 检查邮件配置...\n";
    $settingService = new SettingService();
    $emailConfig = $settingService->getEmail();
    
    if (empty($emailConfig)) {
        echo "错误：邮件配置为空！\n";
        echo "请先配置邮件设置：adminapi/saler_tools/setting/email\n";
        exit;
    }
    
    echo "邮件配置信息：\n";
    echo "- SMTP主机：" . ($emailConfig['smtp_host'] ?? '未设置') . "\n";
    echo "- 邮箱账户：" . ($emailConfig['email'] ?? '未设置') . "\n";
    echo "- 端口：" . ($emailConfig['port'] ?? '未设置') . "\n";
    echo "- 协议：" . ($emailConfig['protocol'] ?? '未设置') . "\n";
    
    // 2. 检查邮件模板
    echo "\n2. 检查邮件模板...\n";
    $templateService = new NotifyTemplateService();
    
    try {
        $template = $templateService->getTemplate('REGISTER', 'zh-Hans');
        echo "REGISTER模板存在\n";
    } catch (Exception $e) {
        echo "错误：REGISTER模板不存在 - " . $e->getMessage() . "\n";
        echo "请先运行 check_email_template.php 创建模板\n";
        exit;
    }
    
    // 3. 测试邮件发送
    echo "\n3. 测试邮件发送...\n";
    $emailService = new EmailNotifyService();
    
    $testEmail = 'test@example.com'; // 请替换为您的测试邮箱
    echo "测试发送到：" . $testEmail . "\n";
    
    $result = $emailService->sendTemplate([
        'email' => $testEmail,
        'template_key' => 'REGISTER',
        'lang_key' => 'zh-Hans',
        'data' => [
            'code' => '123456'
        ]
    ]);
    
    if ($result) {
        echo "邮件发送成功！\n";
    } else {
        echo "邮件发送失败！\n";
        echo "请检查：\n";
        echo "1. 邮件配置是否正确\n";
        echo "2. 网络连接是否正常\n";
        echo "3. 查看日志文件获取详细错误信息\n";
    }
    
} catch (Exception $e) {
    echo "测试过程中发生错误：" . $e->getMessage() . "\n";
    echo "错误文件：" . $e->getFile() . "\n";
    echo "错误行号：" . $e->getLine() . "\n";
}
