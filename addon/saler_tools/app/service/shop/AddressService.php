<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/28 5:08
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\shop;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\common\BaseModel;
use addon\saler_tools\app\model\Address as AddressModel;
/**
 * 商家地址
 * Class AddressService
 * @package addon\saler_tools\app\service\shop
 */
class AddressService extends BaseAdminService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new AddressModel();
    }

    public function list()
    {
        $list = $this->model->where('site_id', $this->site_id)
            ->with(['createNames'])
            ->select()
            ->order('address_id', 'desc')
            ->toArray();

        return success($list);
    }


    public function add($data)
    {
        $data['uid'] = $this->uid;
        $data['site_id'] = $this->site_id;

        $this->model->create($data);

        return success();
    }


    public function edit($data)
    {
        $address = $this->model->where('site_id', $this->site_id)
            ->where('address_id', $data['address_id'])
            ->findOrEmpty();

        if ($address->isEmpty()) return fail('not_found');

        $address->save($data);

        return success();
    }


    public function del($address_id)
    {
        $address = $this->model->where('site_id', $this->site_id)
            ->where('address_id', $address_id)
            ->findOrEmpty();

        if ($address->isEmpty()) return fail('not_found');

        $address->save(['deleted_time' => time()]);

        return success();
    }


    public function detail($address_id)
    {
        $address = $this->model->where('site_id', $this->site_id)
            ->where('address_id', $address_id)
            ->with(['createNames'])
            ->findOrEmpty();
        if ($address->isEmpty()) return fail('not_found');
        return success($address->toArray());
    }

}
