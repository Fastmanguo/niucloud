<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/23 22:26
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\identify;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\Identify as IdentifyModel;
use addon\saler_tools\app\model\IdentifyLog as IdentifyLogModel;
use core\exception\AdminException;
use think\db\Query;

/**
 * 鉴定商品服务
 * Class IdentifyUserGoodsService
 * @package addon\saler_tools\app\service\identify
 */
class IdentifyUserGoodsService extends BaseAdminService
{

    public function __construct()
    {
        parent::__construct();
        // 识别鉴定师身份是否有效
        if ((new IdentifyUserService())->getStatus()['status'] != 1) throw new AdminException('identify_user_error');
    }

    /**
     * site 端获取鉴定商品
     */
    public function goodsLists($params)
    {

        $status = $params['status'] ?? 0;

        if ($status == 0) {// 查询待鉴定商品

            $model              = new IdentifyModel();
            $Identify_log_model = new IdentifyLogModel();
            $identify_ids       = $Identify_log_model->where('uid', $this->uid)->column('identify_id');

            $filed              = 'money';
            $model              = $model->whereNotIn('id', $identify_ids)->with(['brand', 'series', 'model'])
                ->where('create_uid', '<>', $this->uid)
                ->withoutField($filed)
                ->whereIn('status', ['wait_identify', 'identifying'])
                ->order('id', 'desc');

            return success($this->pageQuery($model));

        } elseif ($status == -2) { // 查询已放弃

            $model              = new IdentifyModel();
            $Identify_log_model = new IdentifyLogModel();
            $identify_ids       = $Identify_log_model->where('uid', $this->uid)
                ->where('status', -2)
                ->column('identify_id');
            $filed              = 'money';
            $model              = $model->whereIn('id', $identify_ids)->with(['brand', 'series', 'model'])->where('create_uid', '<>', $this->uid)
                ->withoutField($filed);

            return success($this->pageQuery($model));

        } else { // 查询已经鉴定的商品
            $model              = new IdentifyModel();
            $Identify_log_model = new IdentifyLogModel();
            $identify_ids       = $Identify_log_model->where('uid', $this->uid)
                ->whereIn('status', [1, -1])
                ->column('identify_id');
            $filed              = 'money';
            $model              = $model->whereIn('id', $identify_ids)->with([
                'identifyLogInfo' => function (Query $query) {
                    $query->where('uid', $this->uid);
                }, 'brand', 'series', 'model'
            ])->where('create_uid', '<>', $this->uid)
                ->withoutField($filed);

            return success($this->pageQuery($model));
        }

    }


    /**
     * site 端获取鉴定商品详情
     */
    public function goodsDetail($id)
    {
        $model = new IdentifyModel();

        $model = $model->where('id', $id)->with([
            'identifyLogInfo' => function (Query $query) {
                $query->where('uid', $this->uid);
            }, 'brand', 'series', 'model'
        ])->findOrEmpty();

        return success($model->toArray());
    }

    /**
     * site 端 对商品进行鉴定
     */
    public function editIdentify($data)
    {
        $model = new IdentifyModel();
        $order = $model->where('id', $data['id'])->findOrEmpty();

        if ($order->isEmpty()) {
            return fail('invalid_goods');
        }

        if ($order->status != 'wait_identify' && $order->status != 'identifying') return fail('invalid_goods');

        $Identify_log_model = new IdentifyLogModel();
        if ($order->status == 'wait_identify') {
            $order->status = 'identifying';
            $order->save();
        }
        $log = $Identify_log_model->where('identify_id', $data['id'])->where('uid', $this->uid)->findOrEmpty();
        if ($log->isEmpty()) {
            $log->create([
                'identify_id' => $data['id'],
                'uid'         => $this->uid,
                'price'       => $data['price'],
                'status'      => $data['status'],
                'remark'      => $data['remark']
            ]);
        } else {
            $log->save([
                'price'  => $data['price'],
                'status' => $data['status'],
                'remark' => $data['remark']
            ]);
        }

        return success();

    }

}
