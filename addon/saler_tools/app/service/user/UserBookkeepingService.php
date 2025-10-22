<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/13 20:33
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\user;

use core\base\BaseAdminService;
use app\model\member\UserBookkeepingModel;
use app\model\member\ComplaintModel;

/**
 * 用户地址服务
 * Class UserAddressService
 * @package addon\saler_tools\app\service\user
 */
class UserBookkeepingService extends BaseAdminService
{   

    /**
     * 添加记账（使用原生SQL）
     */
    public function add($data){
        try {
            // 使用原生SQL插入
            $sql = "INSERT INTO user_bookkeeping (uid, price, f_id, create_time, type, images, remarks, update_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $data['uid'],
                $data['price'],
                $data['f_id'],
                $data['create_time'],
                $data['type'],
                $data['images'] ?? '',
                $data['remarks'] ?? '',
                $data['update_time'] ?? time()
            ];
            
            \think\facade\Db::execute($sql, $params);
            return success('添加成功');
        } catch (\Throwable $e) {
            return fail('添加失败：' . $e->getMessage());
        }
    }

    /**
     * 编辑记账
     */
    public function edit($data){
        try {
            // 使用原生SQL更新
            $sql = "UPDATE user_bookkeeping SET price = ?, f_id = ?, create_time = ?, type = ?, images = ?, remarks = ?, update_time = ? WHERE id = ?";
            $params = [
                $data['price'],
                $data['f_id'],
                $data['create_time'],
                $data['type'],
                $data['images'] ?? '',
                $data['remarks'] ?? '',
                $data['update_time'] ?? time(),
                $data['id']
            ];
            
            \think\facade\Db::execute($sql, $params);
            return success('编辑成功');
        } catch (\Throwable $e) {
            return fail('编辑失败：' . $e->getMessage());
        }
    }

    /**
     * 记账列表（分页、按月筛选、按天分组）
     */
    public function list($data){
        try {
            $model = new UserBookkeepingModel();
            
            // 构建基础查询条件
            $query = $model->where('uid', 'in', $data['uid']);
            
            // 月份筛选：month为空查询全部，非空按实际月份查询
            if (!empty($data['month'])) {
                $month = $data['month'];
                $startDate = $month . '-01';
                $endDate = date('Y-m-t', strtotime($startDate));
                $query = $query->where('create_time', '>=', strtotime($startDate))
                              ->where('create_time', '<=', strtotime($endDate) + 86399);
            }
            
            // 分类筛选
            if (!empty($data['f_id'])) {
                $query = $query->where('f_id', 'in', $data['f_id']);
            }
            
            // 分页查询
            $page = $data['page'] ?? 1;
            $limit = $data['limit'] ?? 10;
            
            $result = $query->order('create_time', 'desc')
                           ->paginate([
                               'page' => $page,
                               'list_rows' => $limit
                           ]);
            $list = $result->items();

            $result_count = $query->order('create_time', 'desc')
                           ->paginate([
                               'page' => 1,
                               'list_rows' => 10000
                           ]);
            
            // 获取所有涉及的uid
            $uids = array_unique(array_column($list, 'uid'));
            $userNames = [];
            if (!empty($uids)) {
                $userList = \app\model\sys\SysUser::whereIn('uid', $uids)
                    ->field('uid,real_name')
                    ->select()
                    ->toArray();
                $userNames = array_column($userList, 'real_name', 'uid');
            }
            
            // 为每条记录添加real_name
            foreach ($list as &$item) {
                $item['real_name'] = $userNames[$item['uid']] ?? '';
            }
          
            
            // 计算收入支出统计
            $totalIncome = [0];
            $totalExpense = [0];
            foreach($result_count->items() as $key=>$val){
                if($val['type'] == 1){
                    $totalIncome[] = $val['price'];
                }elseif($val['type'] == 2){
                    $totalExpense[] = $val['price'];
                }
            }
            
            // 组装返回数据
            $response = [
                'list' => $list,
                'total' => $result->total(),
                'page' => $page,
                'limit' => $limit,
                'total_income' => array_sum($totalIncome),
                'total_expense' => array_sum($totalExpense)
            ];
            
            return success($response);
        } catch (\Throwable $e) {
            return fail('查询失败：' . $e->getMessage());
        }
    }

     /**
      * 记账详情
      */
    public function details($id){
        $model = new UserBookkeepingModel();
        $data = $model->where('id', $id)->find()->toArray();
        
        if (empty($data)) {
            return fail('记录不存在');
        }
        
        // 关联用户表获取real_name
        if (!empty($data['uid'])) {
            $user = \app\model\sys\SysUser::where('uid', $data['uid'])
                ->field('uid,real_name')
                ->find();
            $data['real_name'] = $user ? $user['real_name'] : '';
        } else {
            $data['real_name'] = '';
        }
        
        // 处理图片字段
        if($data['images']){
            $data['images'] = json_decode($data['images'], true);
        }
        
        return success($data);
    }

     /**
      * 删除记账
      */
    public function del($id){
        $model = new UserBookkeepingModel();
        $data = $model->where('id', $id)->find();
        $data->delete();
        return success('删除成功');
    }



    
}
