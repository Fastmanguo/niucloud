<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/6 1:46
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\common\BaseModel;
use addon\saler_tools\app\model\Collect as CollectModel;
use addon\saler_tools\app\model\Goods as GoodsModel;
use core\exception\AdminException;
use think\Model;

/**
 * 收藏
 * Class CollectService
 * @package addon\saler_tools\app\service
 */
class CollectService extends BaseAdminService
{

    /** @var string 展会商品 */
    const  COLLECT_TYPE_ONLINE_EXPO = 40;

    /** @var string 展会帖子 */
    const  COLLECT_TYPE_ONLINE_EXPO_BBS = 41;

    // 配置需要关联的方法
    private $relation = [
        self::COLLECT_TYPE_ONLINE_EXPO     => '_onlineExpoGoods',
        self::COLLECT_TYPE_ONLINE_EXPO_BBS => '',
    ];


    private function _onlineExpoGoods($model, $params)
    {
        $model = $model->withJoin('goodsInfo', 'INNER')
            ->hidden(['goodsInfo']);

        if (isset($params['is_sale']) && $params['is_sale'] !== '') {
            $model = $model->where('is_sale', $params['is_sale']);
        }

        if (!empty($params['search'])) {
            $model = $model->whereLike('goods_name|goods_desc|remark|goods_code', "%{$params['search']}%");
        }

        return $model;
    }

    public function lists($params)
    {

        $type_code = $params['type_code'] ?? null;

        if (!isset($this->relation[$type_code])) throw new AdminException('error_type');

        $bind_fun = $this->relation[$type_code];

        $model = new CollectModel();

        $model = $model->where($model->getName() . '.type', $type_code)
            ->where($model->getName() . '.site_id', $this->site_id)
            ->where($model->getName() . '.uid', $this->uid);

        $model = $this->$bind_fun($model, $params);

        // 分页数据
        $result = $this->pageQuery($model);

        // 统计全部收藏数量（当前站点、当前用户、当前类型）
        $all_num = (new CollectModel())
            ->where('type', $type_code)
            ->where('site_id', $this->site_id)
            ->where('uid', $this->uid)
            ->count();

        // 统计失效数量（收藏中下架商品数量）
        $collectTable = (new CollectModel())->getName();
        $goodsTable = (new GoodsModel())->getName();
        $failure_num = (new CollectModel())
            ->alias('c')
            ->join([$goodsTable => 'g'], 'g.goods_id = c.relate_id')
            ->where('c.type', $type_code)
            ->where('c.site_id', $this->site_id)
            ->where('c.uid', $this->uid)
            ->where('g.is_sale', 0)
            ->count();

        // 附加统计数据到返回体
        $result['all_num'] = $all_num;
        $result['failure_num'] = $failure_num;

        return success($result);

    }


    public function check($data)
    {

        $type_code = $data['type_code'] ?? null;
        $relate_id = $data['relate_id'] ?? null;

        $model = new CollectModel();

        $model = $model->where($model->getName() . '.type', $type_code)
            ->where($model->getName() . '.site_id', $this->site_id)
            ->where($model->getName() . '.relate_id', $relate_id)
            ->where($model->getName() . '.uid', $this->uid)
            ->findOrEmpty();

        if ($model->isEmpty()) {
            return success(0);
        } else {
            return success(1);
        }
    }

    public function modify($data)
    {

        $type_code = $data['type_code'] ?? null;
        $relate_id = $data['relate_id'] ?? null;
        $status    = $data['status'] ?? null;

        $model = new CollectModel();

        if ($status == 1) {
            $check = $model->where($model->getName() . '.type', $type_code)
                ->where($model->getName() . '.site_id', $this->site_id)
                ->where($model->getName() . '.relate_id', $relate_id)
                ->where($model->getName() . '.uid', $this->uid)->findOrEmpty();
            if ($check->isEmpty()) {
                $model->insert([
                    'type'      => $type_code,
                    'site_id'   => $this->site_id,
                    'relate_id' => $relate_id,
                    'uid'       => $this->uid,
                ]);
            }
        } else {
            $model->where($model->getName() . '.type', $type_code)
                ->where($model->getName() . '.site_id', $this->site_id)
                ->where($model->getName() . '.relate_id', $relate_id)
                ->where($model->getName() . '.uid', $this->uid)->delete();
        }

        return success();


    }

}
