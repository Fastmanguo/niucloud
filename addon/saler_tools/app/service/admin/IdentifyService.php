<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/24 16:13
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\admin;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\Identify as IdentifyModel;
use addon\saler_tools\app\model\IdentifyLog as IdentifyLogModel;

/**
 *
 * Class IdentifyService
 * @package addon\saler_tools\app\service\admin
 */
class IdentifyService extends BaseAdminService
{

    public function lists($params)
    {
        $model = new IdentifyModel();

        $model = $model->withSearch(['status', 'goods_name', 'order_no'], $params)
            ->with(['shopInfo', 'brand', 'series', 'model', 'identifyLog'])
            ->order('create_time', 'desc')
            ->order('id', 'desc');

        return success($this->pageQuery($model, function ($item) {
            // 找出最高价格
            $identifyLog            = empty($item['identifyLog']) ? [] : $item->identifyLog->toArray();
            $item['identify_num']   = empty($identifyLog) ? 0 : count($identifyLog);
            $item['identify_price'] = empty($identifyLog) ? 0 : max(array_column($identifyLog, 'price'));
            return $item;
        }));
    }


    public function edit($data)
    {
        $model    = new IdentifyModel();
        $identify = $model->where('id', $data['id'])->findOrEmpty();
        if ($identify->isEmpty()) return fail('商品不存在');
        $identify->save($data);
        return success();
    }

    public function logLists($data)
    {
        $Identify_log_model = new IdentifyLogModel();

        $model = $Identify_log_model->where('identify_id', $data['identify_id'])
            ->with(['identifyUserInfo'])
            ->order('create_time', 'desc');

        return success($this->pageQuery($model));

    }

}
