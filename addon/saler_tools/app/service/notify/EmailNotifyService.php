<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/20 21:10
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\notify;

use addon\saler_tools\app\service\admin\NotifyTemplateService;
use addon\saler_tools\app\service\admin\SettingService;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use think\facade\Log;
use think\facade\View;

/**
 *
 * Class EmailNotifyService
 * @package addon\saler_tools\app\service\notify
 */
class EmailNotifyService extends Notify
{

    public function init()
    {
        $email        = (new SettingService())->getEmail();
        self::$config = $email;
    }


    public function debug($config)
    {
        self::$config = $config;
        return $this->getInterface();
    }

    /**
     * @return PHPMailer
     */
    private function getInterface()
    {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host       = self::$config['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = self::$config['email'];
        $mail->Password   = self::$config['password']; // 确保使用应用专用密码
        $mail->Port       = self::$config['port'];
        $mail->SMTPSecure = self::$config['protocol'] == 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet    = "utf-8";
        $mail->setFrom(self::$config['email'], self::$config['sender']);
        return $mail;
    }

    /**
     * @param $data
     *  var email 邮件地址
     *  var title 邮件标题
     *  var content 邮件内容
     */
    public function send($data)
    {
        $mail = $this->getInterface();
        $mail->addAddress($data['email']);
        $mail->isHTML();
        $mail->Subject   = $data['title'] ?? '';
        $mail->Body      = $data['content'] ?? '';
        $mail->SMTPDebug = 0;
        $res             = $mail->send();
        Log::debug('发送邮件：' . $data['email'] . PHP_EOL);
        if (!$res) {
            Log::debug('邮件发送失败，接收人为：' . $data['email'] . PHP_EOL . '内容：' . $data['content'] . PHP_EOL . '错误信息：' . $mail->ErrorInfo);
            return false;
        }
        return true;
    }


    /**
     * 通过模板发送邮件
     * @param $data
     *  var email 邮件地址
     *  var template_key 模板key
     *  var lang_key 语言key
     *  var data 发送数据
     */
    public function sendTemplate($data)
    {

        $template = (new NotifyTemplateService())->getTemplate($data['template_key'], $data['lang_key']);

        $send_data = $this->translateTemplate($template['email'], $data['data']);

        $send_data['email'] = $data['email'];

        return $this->send($send_data);

    }


    /**
     * 翻译模板
     */
    public function translateTemplate($template, $data)
    {
        if (is_array($template)) {
            $template = json_encode($template, JSON_UNESCAPED_UNICODE);
        }
        $data     = array_merge($data, self::$config);
        $template = View::display($template, $data);
        return json_decode($template, true);

    }

}
