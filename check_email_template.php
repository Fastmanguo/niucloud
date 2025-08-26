<?php
// 检查邮件模板脚本
require_once 'vendor/autoload.php';

use addon\saler_tools\app\model\NotifyTemplate;
use addon\saler_tools\app\model\SalerToolsLanguage;

try {
    echo "开始检查邮件模板...\n";
    
    // 检查语言是否存在
    $language = SalerToolsLanguage::where('key', 'zh-Hans')->findOrEmpty();
    if ($language->isEmpty()) {
        echo "创建中文语言包...\n";
        SalerToolsLanguage::create([
            'key' => 'zh-Hans',
            'name' => '简体中文',
            'version' => 1
        ]);
    }
    
    // 检查REGISTER模板是否存在
    $registerTemplate = NotifyTemplate::where('key', 'REGISTER')
                                    ->where('lang', 'zh-Hans')
                                    ->findOrEmpty();
    
    if ($registerTemplate->isEmpty()) {
        echo "创建用户注册邮件模板...\n";
        NotifyTemplate::create([
            'title' => '用户注册验证码',
            'key' => 'REGISTER',
            'lang' => 'zh-Hans',
            'is_use' => 1,
            'email' => [
                'title' => '用户注册验证码',
                'content' => '<div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px;">
        <h2 style="color: #333; text-align: center;">用户注册验证码</h2>
        <p style="color: #666; font-size: 16px;">您好！</p>
        <p style="color: #666; font-size: 16px;">您的注册验证码是：</p>
        <div style="background-color: #007bff; color: white; padding: 15px; text-align: center; border-radius: 5px; margin: 20px 0;">
            <span style="font-size: 24px; font-weight: bold;">{$code}</span>
        </div>
        <p style="color: #666; font-size: 14px;">验证码有效期为10分钟，请尽快使用。</p>
        <p style="color: #666; font-size: 14px;">如果这不是您的操作，请忽略此邮件。</p>
        <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
        <p style="color: #999; font-size: 12px; text-align: center;">此邮件由系统自动发送，请勿回复。</p>
    </div>
</div>'
            ]
        ]);
        echo "用户注册邮件模板创建成功！\n";
    } else {
        echo "用户注册邮件模板已存在。\n";
    }
    
    // 检查BIND_EMAIL模板是否存在
    $bindEmailTemplate = NotifyTemplate::where('key', 'BIND_EMAIL')
                                     ->where('lang', 'zh-Hans')
                                     ->findOrEmpty();
    
    if ($bindEmailTemplate->isEmpty()) {
        echo "创建绑定邮箱邮件模板...\n";
        NotifyTemplate::create([
            'title' => '绑定邮箱验证码',
            'key' => 'BIND_EMAIL',
            'lang' => 'zh-Hans',
            'is_use' => 1,
            'email' => [
                'title' => '绑定邮箱验证码',
                'content' => '<div style="max-width: 600px; margin: 0 auto; padding: 20px; font-family: Arial, sans-serif;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px;">
        <h2 style="color: #333; text-align: center;">绑定邮箱验证码</h2>
        <p style="color: #666; font-size: 16px;">您好！</p>
        <p style="color: #666; font-size: 16px;">您的绑定邮箱验证码是：</p>
        <div style="background-color: #28a745; color: white; padding: 15px; text-align: center; border-radius: 5px; margin: 20px 0;">
            <span style="font-size: 24px; font-weight: bold;">{$code}</span>
        </div>
        <p style="color: #666; font-size: 14px;">验证码有效期为10分钟，请尽快使用。</p>
        <p style="color: #666; font-size: 14px;">如果这不是您的操作，请忽略此邮件。</p>
        <hr style="border: none; border-top: 1px solid #ddd; margin: 20px 0;">
        <p style="color: #999; font-size: 12px; text-align: center;">此邮件由系统自动发送，请勿回复。</p>
    </div>
</div>'
            ]
        ]);
        echo "绑定邮箱邮件模板创建成功！\n";
    } else {
        echo "绑定邮箱邮件模板已存在。\n";
    }
    
    echo "邮件模板检查完成！\n";
    
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}
