<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/21 2:22
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\job;

use addon\saler_tools\app\service\notify\EmailNotifyService;
use core\base\BaseJob;

/**
 * 发送邮件队列任务
 * Class QueueSendEmailJob
 * @package addon\saler_tools\app\job
 */
class QueueSendEmailJob extends BaseJob
{

    public function doJob($data)
    {
        return (new EmailNotifyService())->sendTemplate($data);
    }

}
