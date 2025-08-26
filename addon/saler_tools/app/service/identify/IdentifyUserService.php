<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/23 21:03
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\identify;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\Identify as IdentifyModel;
use addon\saler_tools\app\model\IdentifyUser;
use think\db\Query;
use think\facade\Cache;
use addon\saler_tools\app\model\IdentifyLog as IdentifyLogModel;

/**
 * 鉴定师
 * Class IdentifyUserService
 * @package addon\saler_tools\app\service\identify
 */
class IdentifyUserService extends BaseAdminService
{


    public function getStatus()
    {
        $user = $this->userInfo();
        if (empty($user)) return ['status' => -1];
        return ['status' => $user['status']];
    }


    public function userInfo()
    {
        $key = $this->app->appCache->user('identify_user_key', $this->uid);
        return cache_remember($key, function () {
            $model = new IdentifyUser();
            $user  = $model->where('uid', $this->uid)->findOrEmpty();
            return $user->toArray();
        });
    }


    /**
     * 申请成为鉴定师
     */
    public function apply($data)
    {
        // 现在仅支持一个鉴定师
        $model = new IdentifyUser();

        $check = $model->where('uid', $this->uid)->findOrEmpty();
        if (!$check->isEmpty()) {
            return fail();
        }

        $data['create_time'] = time();
        $data['uid']         = $this->uid;
        $data['status']      = 0;
        $model->save($data);

        return success();
    }




    /**
     * 获取鉴定师列表 admin 端
     */
    public function lists($params)
    {
        $model = new IdentifyUser();

        $model = $model->withSearch(['status', 'nickname'], $params)
            ->with(['userInfo']);

        return success($this->pageQuery($model, function ($item) {
            $item['username']  = $item['userInfo']['username'];
            $item['real_name'] = $item['userInfo']['real_name'];
            unset($item['userInfo']);
            return $item;
        }));
    }


    /**
     * 编辑鉴定师 admin 端
     */
    public function edit($data)
    {
        $model = new IdentifyUser();

        $user = $model->where('uid', $data['uid'])->findOrEmpty();

        if ($user->isEmpty()) {
            $data['create_time'] = time();
            $model->create($data);
        } else {
            $user->save($data);
        }
        $key = $this->app->appCache->user('identify_user_key', $data['uid']);
        Cache::delete($key);

        return success();


    }


}
