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
use app\model\member\SysUserSalary;
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
        
        // 关联记账分类表获取分类名称
        if (!empty($data['f_id'])) {
            $typeInfo = \think\facade\Db::name('user_bookkeeping_type')
                ->where('id', $data['f_id'])
                ->field('id, type_name')
                ->find();
            $data['type_name'] = $typeInfo ? $typeInfo['type_name'] : '';
        } else {
            $data['type_name'] = '';
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


     /**
     * 获取薪资列表
     */
    public function getSalaryList($data){
        try {
            $year = $data['year'] ?? date('Y'); // 默认当前年份
            $currentYear = date('Y'); // 当前年份
            $currentMonth = date('n'); // 当前月份（1-12）

            // 生成月份列表：当年只到当前月，往年查询全年12个月
            $monthList = [];
            $maxMonth = ($year == $currentYear) ? $currentMonth : 12;
            for ($month = 1; $month <= $maxMonth; $month++) {
                $yearMonth = $year . '-' . sprintf('%02d', $month);
                $startTime = strtotime($yearMonth . '-01 00:00:00'); // 该月1号0点
                $endTime = strtotime($yearMonth . '-' . date('t', $startTime) . ' 23:59:59'); // 该月最后一天23:59:59
                
                $monthList[] = [
                    'month' => $month,
                    'month_name' => $month . '月',
                    'year_month' => $yearMonth,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'is_current' => ($year == $currentYear && $month == $currentMonth)
                ];
            }
            
            # 根据月份限制获取到该月新增的用户信息
            $price_list = [0];
            foreach($monthList as $key => $val){
                $start_time = $val['start_time'];
                $end_time = $val['end_time'];
                
                // 根据店铺ID获取员工信息，并添加月份条件
                $userList = (new \app\model\sys\SysUserRole())
                    ->order('is_admin desc,id desc')
                    ->with('userinfo')
                    ->append(['status_name'])
                    ->hasWhere('userinfo', [['is_del', '=', 0]])
                    ->where([['SysUserRole.site_id', '=', $data['site_id']]])
                    // ->where([['SysUserRole.create_time', '>=', $start_time]])
                    ->where([['SysUserRole.create_time', '<=', $end_time]])
                    ->select()
                    ->toArray();
                
                $monthList[$key]['person'] = 0;   #已发放工资员工数量
                $monthList[$key]['count_person'] = 0;    #总员工数量
                $monthList[$key]['price'] = 0;      #已发放工资金额

                 # 根据员工信息去工资表查询本月员工薪资发放情况
                 $uid_list = [];
                 if(!empty($userList)){
                     foreach($userList as $k => $v){
                         $uid = $v['uid'];
                         $uid_list[] = $uid;
                         # 0 未发放 1 已发放
                         $userList[$k]['status'] = 0;
                         $userList[$k]['should_pirce'] = 0;
                         $userList[$k]['actual_pirce'] = 0;
                         $userList[$k]['remarks'] = '';
                         $userList[$k]['create_time'] = "";
                         $userList[$k]['update_time'] = "";

                         // 使用SysUserSalary查询该员工该月的薪资记录
                         $salaryInfo = \app\model\member\SysUserSalary::where('uid', $uid)
                             ->where('create_time', '>=', $start_time)
                             ->where('create_time', '<=', $end_time)
                             ->find();
                         
                         if($salaryInfo){
                             $monthList[$key]['person']++; // 已发放工资员工数量+1
                            //  $monthList[$key]['price'] += $salaryInfo['actual_pirce']; // 累加工资金额
                            $price_list[] =  $salaryInfo['actual_pirce']; // 累加工资金额
                             $userList[$k]['status'] = 1;
                             $userList[$k]['should_pirce'] = $salaryInfo['should_pirce'];
                             $userList[$k]['actual_pirce'] = $salaryInfo['actual_pirce'];
                             $userList[$k]['remarks'] = $salaryInfo['remarks'];
                             $userList[$k]['create_time'] = $salaryInfo['create_time'];
                             $userList[$k]['update_time'] = $salaryInfo['update_time'];
                         }
                         $monthList[$key]['count_person']++; // 总员工数量+1
                     }
                 }
                 $monthList[$key]['uid_list'] = $uid_list;
                //  $price_list[] = $monthList[$key]['price'];
            }
            
            return success([
                'year' => $year,
                'year_price' => array_sum($price_list),
                'month_list' => $monthList,
                'total_months' => count($monthList)
            ]);
            
        } catch (\Throwable $e) {
            return fail('获取失败：' . $e->getMessage());
        }
    }


    /**
     * 获取薪资管理详情-按月份
     */
    public function getSalaryMonthDetails($data){
        try {
          
            $month = $data['month']; // 格式如：2025-01
            $search = $data['search'] ?? ''; // 搜索关键词
            
            // 计算该月的开始时间和结束时间
            $startTime = strtotime($month . '-01 00:00:00'); // 该月1号0点
            $endTime = strtotime($month . '-' . date('t', $startTime) . ' 23:59:59'); // 该月最后一天23:59:59
            
            // 根据店铺ID获取员工信息，并添加月份条件
            $userList = (new \app\model\sys\SysUserRole())
                ->order('is_admin desc,id desc')
                ->with('userinfo')
                ->append(['status_name'])
                ->hasWhere('userinfo', [['is_del', '=', 0]])
                ->where([['SysUserRole.site_id', '=', $data['site_id']]])
                ->where([['SysUserRole.create_time', '<=', $endTime]])
                ->select()
                ->toArray();

            # 根据搜索条件过滤结果
            if($search and !empty($userList)){
                $filteredUserList = [];
                foreach ($userList as $k =>$v) {
                    if(strpos($v['username'], $search) !== false || strpos($v['real_name'], $search) !== false){
                        $filteredUserList[] = $v;
                    }
                }
                $userList = $filteredUserList; // 使用过滤后的列表替换原列表
            }
            
            # 信息总和
            $count_price = [0];
            $yf_user_list = [];
            $wf_user_list = [];
            # 获取本月已发放 未发放薪资列表
            if(!empty($userList)){
                foreach($userList as $k => $v){
                    $uid = $v['uid'];
                    // 使用SysUserSalary查询该员工该月的薪资记录
                    $salaryInfo = \app\model\member\SysUserSalary::where('uid', $uid)
                        ->where('create_time', '>=', $startTime)
                        ->where('create_time', '<=', $endTime)
                        ->find();
                    
                    # 存在=已发  不存在=未发
                    if($salaryInfo){
                        $count_price[] = $salaryInfo['actual_pirce']; // 累加工资金额
                        $userList[$k]['should_pirce'] = $salaryInfo['should_pirce'];
                        $userList[$k]['actual_pirce'] = $salaryInfo['actual_pirce'];
                        $userList[$k]['remarks'] = $salaryInfo['remarks'];
                        $userList[$k]['create_time'] = $salaryInfo['create_time'];
                        $userList[$k]['update_time'] = $salaryInfo['update_time'];
                        $userList[$k]['salary_status'] = 1;
                        $userList[$k]['salary_id'] = $salaryInfo['id'];
                        $yf_user_list[] = $userList[$k];
                    }else{
                        $userList[$k]['should_pirce'] = 0;
                        $userList[$k]['actual_pirce'] = 0;
                        $userList[$k]['remarks'] = '';
                        $userList[$k]['create_time'] = "";
                        $userList[$k]['update_time'] = "";
                        $userList[$k]['salary_status'] = 0;
                        $wf_user_list[] = $userList[$k];
                    }
                }
            }

            return success([
                'month' => $month,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'yf_user_list' => $yf_user_list,
                'wf_user_list' => $wf_user_list,
                'count_price' => array_sum($count_price),
            ]);
        } catch (\Throwable $e) {
            return fail('获取失败：' . $e->getMessage());
        }
    }

    /**
     * 给用户发放薪资
     */
    public function givePrice($data){
        try {
            // 获取当前月份的开始和结束时间
            $currentMonth = $data['month'] ?? date('Y-m');
            $startTime = strtotime($currentMonth . '-01 00:00:00'); // 本月1号0点
            $endTime = strtotime($currentMonth . '-' . date('t', $startTime) . ' 23:59:59'); // 本月最后一天23:59:59
            
            // 查询该用户本月是否已发放薪资
            $existingSalary = \app\model\member\SysUserSalary::where('uid', $data['uid'])
                ->where('create_time', '>=', $startTime)
                ->where('create_time', '<=', $endTime)
                ->find();
            
            if ($existingSalary) {
                return fail('本月已发放薪资，请勿重复发放');
            }
            
            // 插入薪资记录
            $salary = new \app\model\member\SysUserSalary();
            $salary->save([
                'uid' => $data['uid'],
                'should_pirce' => $data['should_pirce'],
                'actual_pirce' => $data['actual_pirce'],
                'remarks' => $data['remarks'],
                'create_time' => $startTime ?? time(),
                'update_time' => time(),
            ]);
            
            return success('发放薪资成功');
        } catch (\Throwable $e) {
            return fail('发放薪资失败：' . $e->getMessage());
        }
    }

    /**
     * 编辑已发放用户薪资
     */
    public function givePriceEdit($data){
        try {
            // 查找薪资记录
            $salary = \app\model\member\SysUserSalary::where('id', $data['id'])->find();
            if (!$salary) {
                return fail('薪资记录不存在');
            }
            // 更新薪资记录
            $updateData = [
                'should_pirce' => $data['should_pirce'],
                'actual_pirce' => $data['actual_pirce'],
                'remarks' => $data['remarks'],
                'update_time' => time()
            ];
            
            $salary->save($updateData);
            return success('编辑薪资成功');
        } catch (\Throwable $e) {
            return fail('编辑薪资失败：' . $e->getMessage());
        }
    }


    /**
     * 给所有用户发放薪资
     */
    public function givePriceAll($data){
        try {
            $month = strtotime($data['month'])+60*60 ?? time();
            // 遍历薪资列表数组
            foreach ($data['xz_list'] as $key => $val) {
                $uid = $val['uid'];
                
                // 直接插入薪资记录
                \think\facade\Db::name('sys_user_salary')->insert([
                    'uid' => $uid,
                    'should_pirce' => 0,
                    'actual_pirce' => $val['actual_pirce'],
                    'remarks' => "",
                    'create_time' => $month,
                ]);
            }
            return success('给所有用户发放薪资成功');
        } catch (\Throwable $e) {
            return fail('给所有用户发放薪资失败：' . $e->getMessage());
        }
    }


    /**
     * 获取用户销售金额详情
     */
    public function getUserPrice($data){
        try {
            $uid = $data['uid'];
            $currentMonth = $data['month'] ?? date('Y-m');
            $startDate = $currentMonth . '-01 00:00:00'; // 本月1号0点
            $endDate = $currentMonth . '-' . date('t', strtotime($startDate)) . ' 23:59:59'; // 本月最后一天23:59:59

            $start_time = strtotime($currentMonth . '-01 00:00:00'); // 本月1号0点
            $end_time = strtotime($currentMonth . '-' . date('t', $start_time) . ' 23:59:59'); // 本月最后一天23:59:59
            
            // 查询本月已完成订单 - 使用时间字符串格式查询时间字段
            $orderQuery = \think\facade\Db::name('saler_tools_order')
                ->where('create_uid', $uid)
                ->where('create_time', '>=', $startDate) // 使用字符串时间格式查询
                ->where('create_time', '<=', $endDate) // 使用字符串时间格式查询
                ->where('order_status', 'FINISH_ORDER');
            
            // 统计销售额和成本
            $stats = $orderQuery->field([
                'sum(money) as total_sales',
                'sum(total_cost) as total_cost'
            ])->find();
            
            // 统计订单记录数
            $orderCount = $orderQuery->count();
            
            // 计算利润
            $totalSales = $stats['total_sales'] ?? 0;
            $totalCost = $stats['total_cost'] ?? 0;
            $profit = $totalSales - $totalCost;
            
            // 查询本月记账记录
            $bookkeepingQuery = \think\facade\Db::name('user_bookkeeping')
                ->where('uid', $uid)
                ->where('create_time', '>=', $start_time)
                ->where('create_time', '<=', $end_time);
            $bookkeepingCount = $bookkeepingQuery->count();
            
            // 统计收入支出金额
            $bookkeepingStats = $bookkeepingQuery->field([
                'sum(CASE WHEN type = 1 THEN price ELSE 0 END) as total_income', // 收入总额
                'sum(CASE WHEN type = 2 THEN price ELSE 0 END) as total_expense' // 支出总额
            ])->find();
            
            // 统计记账记录总数
            
            
            $totalIncome = $bookkeepingStats['total_income'] ?? 0;
            $totalExpense = $bookkeepingStats['total_expense'] ?? 0;
            

            // 查询用户薪资数据
            $salary_info = \think\facade\Db::name('sys_user_salary')
                ->where('uid', $uid)
                ->where('create_time', '>=', $start_time)
                ->where('create_time', '<=', $end_time)
                ->find();
        
            
            if($salary_info){
                $salary_info['salary_status'] = 1;
            }

            return success([
                'total_sales' => $totalSales,
                // 'total_cost' => $totalCost,
                'profit' => $profit,
                'total_income' => $totalIncome, // 本月收入总额
                'total_expense' => $totalIncome-$totalExpense, // 本月支出总额
                'month' => $currentMonth,
                'bookkeeping_count' => $bookkeepingCount, // 记账记录总数
                'order_count' => $orderCount // 订单记录总数
                ,'salary_info' => $salary_info
            ]);
        } catch (\Throwable $e) {
            return fail('获取销售详情失败：' . $e->getMessage());
        }
    }
       
    
}
