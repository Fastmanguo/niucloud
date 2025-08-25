<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/11 15:42
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\channel\ChannelService;

/**
 *
 * Class Channel
 * @package addon\saler_tools\app\adminapi\controller\admin
 */
class Channel extends BaseAdminController
{

    public function list()
    {
        return success((new ChannelService())->getBase());
    }


    public function detail($key)
    {
        return (new ChannelService())->detail($key);
    }


    public function update()
    {
        $data = request()->put();
        return (new ChannelService())->update($data);
    }

}
