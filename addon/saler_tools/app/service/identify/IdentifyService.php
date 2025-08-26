<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/6 18:10
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\identify;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\common\Utils;
use addon\saler_tools\app\model\Identify as IdentifyModel;
use addon\saler_tools\app\service\pay\PaymentService;
use addon\saler_tools\app\service\shop\ShopService;
use core\exception\AdminException;

/**
 *
 * Class IdentifyService
 * @package addon\saler_tools\app\service\identify
 */
class IdentifyService extends BaseAdminService
{

    /** @var string 待支付 */
    const STATUS_WAIT_PAY = 'wait_pay';

    /** @var string 待鉴定 */
    const STATUS_WAIT_IDENTIFY = 'wait_identify';

    /** @var string 估价中 */
    const STATUS_IDENTIFYING = 'identifying';

    /** @var string 待补充 */
    const STATUS_WAIT_SUPPLY = 'wait_supply';

    /** @var string 已完成 */
    const STATUS_FINISH = 'finish';

    /** @var string 已取消 */
    const STATUS_CANCEL = 'cancel';


    /**
     * 获取店铺下的鉴定列表
     */
    public function lists($params)
    {

        $model = new IdentifyModel();

        $model = $model->where('site_id', $this->site_id)
            ->withSearch(['status', 'goods_name', 'order_no'], $params)
            ->with(['brand', 'series', 'model'])
            ->order('create_time', 'desc')
            ->order('id', 'desc');

        return success($this->pageQuery($model));

    }


    /**
     * 添加鉴定
     */
    public function add($data)
    {
        $model = new IdentifyModel();

        $config = app(IdentifyConfigService::class)->ckeck();

        if (empty($config)) return fail('fail_support');
        $model->startTrans();
        try {
            $data['cost']          = $config['identify_money'];
            $data['original_cost'] = $config['identify_money'];
            $data['currency_code'] = $config['currency_code'];

            if (empty($data['cost'])) { // 鉴定免费
                $data['status'] = self::STATUS_WAIT_IDENTIFY;
            } else { // 收取鉴定费用
                $data['status'] = self::STATUS_WAIT_PAY;
            }

            $data['goods_cover'] = $data['goods_image'][0];
            $data['site_id']     = $this->site_id;
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['create_uid']  = $this->uid;
            $data['order_no']    = Utils::createno('saler_tools_identify', 'C');
            $model->save($data);

            $out_trade_no = (new PaymentService())->create($model->id, $this->site_id, $model->order_no, 'indentify', $model->cost, $model->order_no);

            $model->commit();

            return success([
                'id'           => $model->id,
                'order_no'     => $model->order_no,
                'out_trade_no' => $out_trade_no,
                'cost'         => $model->cost
            ]);

        } catch (\Exception $e) {
            $model->rollback();
            return fail();
        }

    }


    public function detail($id)
    {
        $model = new IdentifyModel();

        $identify = $model->where('site_id', $this->site_id)
            ->with(['brand', 'series', 'model'])
            ->where('id', $id)
            ->findOrEmpty();


        if ($identify->isEmpty()) throw new AdminException();

        return success($identify->toArray());
    }


    public function del($id)
    {
        $model = new IdentifyModel();

        $model->where('site_id', $this->site_id)
            ->where('id', $id)
            ->update([
                'deleted_time' => time()
            ]);

        return success();

    }


}
