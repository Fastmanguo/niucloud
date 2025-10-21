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
     * 添加记账
     */
    public function add($data){
        $model = new UserBookkeepingModel();
        $model->save($data);
        return success();
    }

    /**
     * 记账列表（按时间戳倒序，无分页），增加当月收入支出统计
     */
    public function list($data){
        try {
            // 使用框架模型链式操作，按时间戳倒序排序，获取全部数据
            $model = new UserBookkeepingModel();
            
            
            // 获取月份参数，默认为当前月
            if($data['month']){
                $month = $data['month'];
            }else{
                $month = date('Y-m');
            }
            $startDate = $month . '-01';
            $endDate = date('Y-m-t', strtotime($startDate));
            
            // 查询当月收入总金额
            $incomeQuery = new UserBookkeepingModel();
            $incomeQuery = $incomeQuery->where('uid', 'in', $data['uid'])
                                      ->where('create_time', '>=', strtotime($startDate))
                                      ->where('create_time', '<=', strtotime($endDate) + 86399) // 加一天的秒数-1秒
                                      ->where('type', 1); // 假设1表示收入
            $totalIncome = $incomeQuery->sum('price') ?: 0;

            // 查询当月支出总金额
            $expenseQuery = new UserBookkeepingModel();
            $expenseQuery = $expenseQuery->where('uid', 'in', $data['uid'])
                                        ->where('create_time', '>=', strtotime($startDate))
                                        ->where('create_time', '<=', strtotime($endDate) + 86399)
                                        ->where('type', 2); // 假设2表示支出
            $totalExpense = $expenseQuery->sum('price') ?: 0;
            

            $query = $model->order('create_time', 'desc')
                          ->where('uid', 'in', $data['uid'])
                          ->where('create_time', '>=', strtotime($startDate))
                        ->where('create_time', '<=', strtotime($endDate) + 86399);
            
            // 只有当f_id不为空时才添加f_id的查询条件
            if (!empty($data['f_id'])) {
                $query = $query->where('f_id', 'in', $data['f_id']);
            }
            
            $list = $query->select()->toArray();
            // 组装返回数据
            $result = [
                'list' => $list,
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense
            ];
            
            return success($result);
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
        if($data['images']){
            $data['images'] = json_decode($data['images'], true);
        }
        return success($data);
    }


    
}
