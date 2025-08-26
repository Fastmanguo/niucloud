<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/20 2:46
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\order;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\common\Utils;
use addon\saler_tools\app\model\OrderService as OrderServiceModel;

/**
 * 保养维护订单
 * Class OrderServiceService
 * @package addon\saler_tools\app\service\order
 */
class OrderServiceService extends BaseAdminService
{

    public function lists($params)
    {
        $order_service_model = new OrderServiceModel();

        $model = $order_service_model->where('site_id', $this->site_id)
            ->withSearch(['service_no', 'search', 'sales_uid', 'service_uid', 'create_uid'], $params)
            ->with(['salesNames', 'serviceNames', 'createNames']);
        return success($this->pageQuery($model));
    }


    public function detail($service_id)
    {
        $order_service_model = new OrderServiceModel();

        $service = $order_service_model->where('site_id', $this->site_id)
            ->where('service_id', $service_id)
            ->with(['salesNames', 'serviceNames', 'createNames'])->findOrEmpty();

        if ($service->isEmpty()) return fail();

        return success($service->toArray());
    }


    public function add($data)
    {
        $data['create_uid']   = $this->uid;
        $data['status']       = 'progress';
        $data['service_no']   = Utils::createno();
        $data['site_id']      = $this->site_id;
        $data['paid_receipt'] = [];
        $order_service_model  = new OrderServiceModel();
        $order_service_model->create($data);
        return success();
    }


    public function edit($data)
    {
        $service_id          = $data['service_id'];
        $order_service_model = new OrderServiceModel();
        $service             = $order_service_model->where('service_id', $service_id)->where('site_id', $this->site_id)->findOrEmpty();
        if ($service->isEmpty()) return fail();
        if ($service['status'] != 'progress') return fail('订单状态不正确');
        $service->save($data);
        return success();

    }


    public function operate($data)
    {
        $service_id = $data['service_id'];
        $status     = $data['status'];

        $order_service_model = new OrderServiceModel();

        $service = $order_service_model->where('service_id', $service_id)->where('site_id', $this->site_id)->findOrEmpty();

        if ($service->isEmpty()) return fail();

        if ($service['status'] != 'progress') return fail('订单状态不正确');

        switch ($status) {
            case 'finish':
                $service->status      = 'finish';
                $service->finish_time = date('Y-m-d H:i:s');
                $service->money       = $data['money'] ?? 0;
                $service->cost        = $data['cost'] ?? 0;

                if (!empty($data['paid_receipt'])) {
                    $service->paid_receipt = $data['paid_receipt'];
                }

                $service->save();

                return success();

            case 'cancel':
                $service->status = 'cancel';
                $service->save();
                return success();

        }

        return fail();
    }
}
