<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/23 20:27
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\job;

use addon\saler_tools\app\service\CaptchaService;
use core\base\BaseJob;

/**
 *
 * Class QueueCaptchaJob
 * @package addon\saler_tools\app\job
 */
class QueueCaptchaJob extends BaseJob
{
    public function doJob($data)
    {
        return (new CaptchaService())->doSend($data);
    }
}
