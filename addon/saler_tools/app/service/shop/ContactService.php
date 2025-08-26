<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/28 5:42
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\shop;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\Contact as ContactModel;

/**
 * 联系人
 * Class ContactService
 * @package addon\saler_tools\app\service\shop
 */
class ContactService extends BaseAdminService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new ContactModel();
    }

    public function list()
    {
        $list = $this->model->where('site_id', $this->site_id)
            ->with(['byNames'])
            ->order('sort', 'asc')
            ->select()
            ->toArray();
        return success($list);
    }


    public function del($contact_id)
    {
        $contact = $this->model->where('contact_id', $contact_id)->where('site_id', $this->site_id)->findOrEmpty();

        if ($contact->isEmpty()) return fail('not_found');

        $contact->save(['deleted_time' => time()]);

        return success();

    }


    public function add($data)
    {
        $data['site_id'] = $this->site_id;
        $this->model->create($data);
        return success();
    }


    public function edit($data)
    {
        $contact = $this->model->where('contact_id', $data['contact_id'])->where('site_id', $this->site_id)->findOrEmpty();

        if ($contact->isEmpty()) return fail('not_found');

        $contact->save($data);

        return success();

    }


    public function detail($contact_id)
    {

        $contact = $this->model->where('contact_id', $contact_id)->where('site_id', $this->site_id)->findOrEmpty();

        if ($contact->isEmpty()) return fail('not_found');

        return success($contact->toArray());

    }


    public function getContactExhibition($site_id)
    {

        $field = ['work_name','mobile','email','weacht','whats_up'];

        $contact = $this->model->where('site_id', $site_id)
            ->field($field)
            ->where('status', 1)
            ->order('sort', 'asc')
            ->select()
            ->toArray();

        return $contact;

    }

}
