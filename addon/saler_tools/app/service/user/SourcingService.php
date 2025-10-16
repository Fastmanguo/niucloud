<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/13 20:33
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\user;

use core\base\BaseAdminService;
use app\model\member\SourcingModel;
use addon\saler_tools\app\model\Goods as GoodsModel;
use app\model\sys\SysUser;
use think\facade\Db;



/**
 * 用户地址服务
 * Class UserAddressService
 * @package addon\saler_tools\app\service\user
 */
class SourcingService extends BaseAdminService
{   
    /**
     * 采购单添加
     * @param $params
     * @return bool
     */
    public function sourcingAdd($data)
    {
        $sourcingModel = new SourcingModel();
        $data['create_time'] = time();
        $data['status'] = 1;
        $data['sourc_on'] = "Sourceing" . date('YmdHis');
        $sourcingModel->save($data);
        return success($data);
    }

    /**
     * 采购单列表
     * @param $params
     * @return bool
     */
    public function sourcingLists($data)
    {
        $sourcingModel = new SourcingModel();
        
        // 构建查询条件
        $query = $sourcingModel->where('uid', '=', $data['uid']);
        
        // status搜索条件：不传或为空时查询所有，传入时根据实际值过滤
        if (isset($data['status']) && $data['status'] !== '') {
            $query->where('status', '=', $data['status']);
        }
        
        // 搜索条件
        if (!empty($data['search'])) {
            $search = $data['search'];
            $query->where(function($q) use ($search) {
                $q->whereLike('requirement', "%{$search}%")
                  ->whereOr('sourc_on', 'like', "%{$search}%")
                  ->whereOr('mobile', 'like', "%{$search}%");
            });
        }
        
        // 设置分页参数
        $page = $data['page'] ?? 1;
        $limit = $data['limit'] ?? 10;
        
        // 处理排序参数
        $orderFields = [];
        
        // 处理create_time排序
        if (isset($data['create_time']) && ($data['create_time'] == 'asc' || $data['create_time'] == 'desc')) {
            $orderFields['create_time'] = $data['create_time'];
        }
        
        // 处理deposit_price排序
        if (isset($data['deposit_price']) && ($data['deposit_price'] == 'asc' || $data['deposit_price'] == 'desc')) {
            $orderFields['deposit_price'] = $data['deposit_price'];
        }
        
        // 处理delivery_time排序
        if (isset($data['delivery_time']) && ($data['delivery_time'] == 'asc' || $data['delivery_time'] == 'desc')) {
            $orderFields['delivery_time'] = $data['delivery_time'];
        }
        
        // 如果没有指定排序字段，使用默认排序
        if (empty($orderFields)) {
            $orderFields['create_time'] = 'desc';
        }
        
        // 执行分页查询
        $list = $query->order($orderFields)
            ->paginate([
                'page' => $page,
                'list_rows' => $limit
            ]);
        
        $lists = $list->items();

        foreach($lists as $key => $val){
            // 获取关联的商品信息
            if($val['goods_id']){
                $goods = (new GoodsModel())->where('goods_id', $val['goods_id'])->select()->toArray();
                if($goods[0]['goods_attr_list']){
                    $goods[0]['goods_attr_list'] = json_decode($goods[0]['goods_attr_list'],true);
                }
                $lists[$key]['goods_info'] = $goods[0];
            }else{
                $lists[$key]['goods_info'] = [];
            }
        }
        

        // 返回数据
        return success([
            'total' => $list->total(),
            'page_size' => $limit,
            'page' => $page,
            'list' => $lists
        ]);
    }

    /**
     * 采购单详情
     * @param $params
     * @return bool
     */
    public function sourcingDetails($data)
    {
        $sourcingModel = new SourcingModel();
        $sourcing = $sourcingModel->where('id', $data['id'])->findOrEmpty()->toArray();
        if($sourcing['goods_id']){
            $sourcing['goods_info'] = (new GoodsModel())->where('goods_id', $sourcing['goods_id'])->findOrEmpty()->toArray();
            $sourcing['goods_info']['goods_attr_list'] = json_decode($sourcing['goods_info']['goods_attr_list'],true);
        }
        if($sourcing['sale_uids']){
            $sourcing['sale_uids'] = json_decode($sourcing['sale_uids'],true);
            $real_name_list = [];
            foreach($sourcing['sale_uids'] as $k => $v){
                    $user = (new SysUser())->where('uid', '=', $v)->findOrEmpty()->toArray();
                    $real_name_list[] = $user['real_name'];
                }
            $sourcing['sale_uids_name'] = implode(',',$real_name_list);
        }

        if($sourcing['payment_images']){
            $sourcing['payment_images'] = json_decode($sourcing['payment_images'],true);
        }

        if($sourcing['warehousing_time']){
            $sourcing['warehousing_time'] = date('Y-m-d H:i:s',$sourcing['warehousing_time']);
        }

        if($sourcing['delivery_time']){
            $sourcing['delivery_time'] = date('Y-m-d H:i:s',$sourcing['delivery_time']);
        }

        return success($sourcing);
    }

    /**
     * 编辑采购单 商品添加
     * @param $data
     * @return bool
     */
    public function sourcingGoodsAdd($data)
    {
        $sourcingModel = new SourcingModel();
        $updateData = [
            'goods_id' => $data['goods_id'],
            'warehousing_time' => time(),
            'status' => 2,
        ];
        
        // 执行更新
        $result = $sourcingModel->where('id', $data['id'])->update($updateData);
        if ($result === false) {
            return fail('更新失败');
        }
        return success("操作成功");
    }

    /**
     * 采购单结束
     * @param $data
     * @return bool
     */
    public function sourcingEnd($data)
    {
        $sourcingModel = new SourcingModel();
        $result = $sourcingModel->where('id', $data['id'])->update(['status' => 3]);
        if ($result === false) {
            return fail('更新失败');
        }
        return success("操作成功");
    }

    /**
     * 采购单重新找货
     * @param $data
     * @return bool
     */
    public function sourcingAgain($data)
    {
        $sourcingModel = new SourcingModel();
        $update_data = [
            'status' => 1,
            'goods_id' => "",
            'warehousing_time' => "",
            'update_time' => time(),
        ];
        $result = $sourcingModel->where('id', $data['id'])->update($update_data);
        if ($result === false) {
            return fail('更新失败');
        }
        return success("操作成功");
    }
    
    /**
     * 采购单删除
     * @param $data
     * @return bool
     */
    public function sourcingDel($data)
    {
        $sourcingModel = new SourcingModel();
        $result = $sourcingModel->where('id', $data['id'])->delete();
        if ($result === false) {
            return fail('删除失败');
        }
        return success("操作成功");
    }
    
    /**
     * 采购单编辑
     * @param $data
     * @return bool
     */
    public function sourcingEdit($data)
    {   
        $sourcingModel = new SourcingModel();
        // 修改关联商品的客户类型
        if($data['customer_type']!="" and $data['balance_price'] != "" and $data['price'] != ""){
            $sourcing = $sourcingModel->where('id', $data['id'])->findOrEmpty()->toArray();
            if($sourcing['goods_id']){
                // 使用原生SQL更新客户类型
                Db::execute("UPDATE saler_tools_goods SET customer_type = ".$data['customer_type']." WHERE goods_id = ".$sourcing['goods_id']);
            }
        }
        if($data['balance_price'] == "" or $data['price'] == "" or $data['customer_type'] == ""){
            unset($data['balance_price']);
            unset($data['price']);
        }
        unset($data['customer_type']);
        $result = $sourcingModel->where('id', $data['id'])->update($data);
        if ($result === false) {
            return fail('定金找货表更新失败');
        }
        
        
        return success("操作成功");
    }
        
    
}
