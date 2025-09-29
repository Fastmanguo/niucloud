<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/4/13 20:33
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\user;

use core\base\BaseAdminService;
use app\model\sys\SysArea;
use app\model\member\MemberAddress;
use app\model\member\CustomerPayment;
use app\model\member\CustomerReceipt;
use app\model\member\CustomerModel;
use app\model\sys\SysUser;

/**
 * 用户地址服务
 * Class UserAddressService
 * @package addon\saler_tools\app\service\user
 */
class CustomerService extends BaseAdminService
{   
    /**
     * 客户支付识别
     */
    public function paymentRecognizeText($text){
        // 定义结果数组
        $result = [
            'name' => '',
            'account' => '',
            'bank' => ''
        ];
        
        // 判断文本格式并提取信息
        if (strpos($text, '姓名：') !== false && strpos($text, '账号：') !== false && strpos($text, '开户行：') !== false) {
            // 格式1: 姓名：xxx 账号：xxx 开户行：xxx
            $namePattern = '/姓名：([^\s]+)/';
            $accountPattern = '/账号：([^\s]+)/';
            $bankPattern = '/开户行：([^\s]+)/';
            
            if (preg_match($namePattern, $text, $nameMatches)) {
                $result['name'] = $nameMatches[1];
            }
            
            if (preg_match($accountPattern, $text, $accountMatches)) {
                $result['account'] = $accountMatches[1];
            }
            
            if (preg_match($bankPattern, $text, $bankMatches)) {
                $result['bank'] = $bankMatches[1];
            }
        } else {
            // 格式2: xxx xxx xxx (空格分隔)
            $parts = explode(' ', $text);
            if (count($parts) >= 3) {
                $result['name'] = $parts[0];
                
                // 查找账号（通常是数字，长度较长）
                foreach ($parts as $part) {
                    if (ctype_digit($part) && strlen($part) > 10) {
                        $result['account'] = $part;
                        break;
                    }
                }
                
                // 查找开户行（通常是包含银行名称的部分）
                $bankKeywords = ['银行', '信用社', '储蓄所', '支行'];
                foreach ($parts as $part) {
                    foreach ($bankKeywords as $keyword) {
                        if (strpos($part, $keyword) !== false) {
                            $result['bank'] = $part;
                            break 2;
                        }
                    }
                }
                
                // 如果没有找到账号，默认使用第二个部分
                if (empty($result['account']) && isset($parts[1])) {
                    $result['account'] = $parts[1];
                }
                
                // 如果没有找到开户行，默认使用第三个部分及以后的内容
                if (empty($result['bank'])) {
                    $bankParts = array_slice($parts, 2);
                    $result['bank'] = implode(' ', $bankParts);
                }
            }
        }
        
        return success($result);
    }

    /**
     * 添加客户支付信息
     */
    public function addPayment($data){
        $data['create_time'] = time();
        $data['is_del'] = 0;
        $data['image'] = json_encode($data['image']);
        $customer = new CustomerPayment();
        $customer->save($data);
        return success("添加成功");
    }

    /**
     * 客户支付信息列表
     */
    public function paymentList($data){
        $customer = new CustomerPayment();
        $model = $customer->where('is_del', '=', 0)->where('site_id', '=', $data['site_id'])->where('uid', '=', $data['uid']);
        
        // 设置分页参数
        $page = isset($data['page']) ? $data['page'] : 1;
        $page_size = isset($data['page_size']) ? $data['page_size'] : 10;
        
        // 执行分页查询
        $list = $model->paginate([
            'list_rows' => $page_size,
            'page' => $page,
            'var_page' => 'page'
        ]);
        
        // 处理数据
        $items = $list->items();
        foreach($items as $key => $val){
            $items[$key]['image'] = json_decode($val['image'], true);
        }
        
        // 格式化分页结果
        $result = [
            'data' => $items,
            'total' => $list->total(),
            'page' => $page,
            'page_size' => $page_size,
            'total_page' => ceil($list->total() / $page_size)
        ];
        
        return success($result);
    }

    /**
     * 删除客户支付信息
     */
    public function paymentDel($id){
        $customer = new CustomerPayment();
        $customer->where('id', '=', $id)->update(['is_del' => 1]);
        return success("删除成功");
    }
    
    /**
     * 编辑回显接口
     */
    public function paymentFind($id){
        $customer = new CustomerPayment();
        $data = $customer->where('id', '=', $id)->find()->toArray();
        $data['image'] = json_decode($data['image'], true);
        return success($data);
    }
    
    /**
     * 编辑客户支付信息
     */
    public function paymentEdit($data){
        $customer = new CustomerPayment();
        $customer->where('id', '=', $data['id'])->update([
            'name' => $data['name'],
            'account' => $data['account'],
            'bank_name' => $data['bank_name'],
            'image' => json_encode($data['image']),
        ]);
        return success("编辑成功");
    }

    /**
     * 客户地址识别
     */
    public function addressRecognizeText($text){
        // 定义结果数组
        $result = [
            'name' => '',
            'phone' => '',
            'address' => ''
        ];
        
        // 预处理文本，添加空格（用户添加的代码）
        $text = $text." ";
        
        // 1. 优先识别手机号（支持多种可能的手机号格式）
        // 匹配11位手机号，可能有空格、连字符或无分隔符
        $phonePatterns = [
            '/1\d{10}/', // 标准11位手机号
            '/1\d{3}\s\d{4}\s\d{4}/', // 138 0000 0000格式
            '/1\d{3}-\d{4}-\d{4}/', // 138-0000-0000格式
            '/1\d{3}[\s-]*\d{4}[\s-]*\d{4}/' // 更通用的格式
        ];
        
        $foundPhone = false;
        foreach ($phonePatterns as $pattern) {
            if (preg_match($pattern, $text, $phoneMatches)) {
                // 清理手机号，去除可能的空格和连字符
                $result['phone'] = preg_replace('/[^0-9]/', '', $phoneMatches[0]);
                $foundPhone = true;
                break;
            }
        }
        
        // 2. 识别姓名（通常是连续的中文字符）
        $namePatterns = [
            // 匹配2-4个中文字符的姓名
            '/[\x{4e00}-\x{9fa5}]{2,4}/u',
            // 尝试在手机号前面找姓名
            '/([\x{4e00}-\x{9fa5}]{2,4})[\s,，]*(?=1\d{3})/u',
            // 尝试在"收货人"、"姓名"等关键词后找姓名
            '/[收货人姓名]{1,3}[:：]\s*([\x{4e00}-\x{9fa5}]{2,4})/u'
        ];
        
        foreach ($namePatterns as $pattern) {
            if (preg_match($pattern, $text, $nameMatches)) {
                $candidateName = $nameMatches[count($nameMatches) - 1];
                // 避免将地址中的部分误识别为姓名
                if (!preg_match('/[省市县区乡镇街道]/u', $candidateName)) {
                    $result['name'] = $candidateName;
                    break;
                }
            }
        }
        
        // 3. 识别地址信息（可选，尽量提取剩余的文本）
        $tempText = $text;
        
        // 移除已识别的手机号和姓名
        if (!empty($result['phone'])) {
            $tempText = preg_replace('/'.preg_quote($result['phone'], '/').'/', '', $tempText);
        }
        if (!empty($result['name'])) {
            $tempText = preg_replace('/'.preg_quote($result['name'], '/').'/', '', $tempText);
        }
        
        // 清理临时文本中的标签
        $tempText = preg_replace('/[收货人姓名手机号所在地区详细地址]{1,4}[:：]/u', '', $tempText);
        $tempText = preg_replace('/\s+/', ' ', $tempText);
        $tempText = trim($tempText);
        
        if (!empty($tempText)) {
            $result['address'] = $tempText;
        }
        
        // 4. 尝试处理标准格式（保留原有的功能）
        if (strpos($text, '收货人：') !== false && strpos($text, '手机号：') !== false && (strpos($text, '所在地区：') !== false || strpos($text, '详细地址：') !== false)) {
            // 格式1: 带标签的格式
            $namePattern = '/收货人：([^\s\r\n]+)/';
            $phonePattern = '/手机号：([^\s\r\n]+)/';
            $regionPattern = '/所在地区：([^\s\r\n]+)/';
            $detailPattern = '/详细地址：([^\s\r\n]+)/';
            
            if (preg_match($namePattern, $text, $nameMatches)) {
                $result['name'] = $nameMatches[1];
            }
            
            if (preg_match($phonePattern, $text, $phoneMatches)) {
                $result['phone'] = $phoneMatches[1];
            }
            
            $addressParts = [];
            if (preg_match($regionPattern, $text, $regionMatches)) {
                $addressParts[] = $regionMatches[1];
            }
            if (preg_match($detailPattern, $text, $detailMatches)) {
                $addressParts[] = $detailMatches[1];
            }
            if (!empty($addressParts)) {
                $result['address'] = implode(' ', $addressParts);
            }
        } else if (empty($result['name']) || empty($result['phone'])) {
            // 格式2: 空格分隔的格式（当智能识别失败时使用）
            $parts = preg_split('/\s+/', $text);
            if (count($parts) >= 3) {
                if (empty($result['name'])) {
                    $result['name'] = $parts[0];
                }
                
                // 查找手机号
                if (empty($result['phone'])) {
                    foreach ($parts as $index => $part) {
                        if (preg_match('/^1\d{10}$/', $part)) {
                            $result['phone'] = $part;
                            // 收集地址信息
                            $addressParts = [];
                            foreach ($parts as $addrIndex => $addrPart) {
                                if ($addrIndex !== 0 && $addrIndex !== $index) {
                                    $addressParts[] = $addrPart;
                                }
                            }
                            $result['address'] = implode(' ', $addressParts);
                            break;
                        }
                    }
                }
                
                // 如果没有找到标准手机号格式，默认使用第二个部分作为手机号
                if (empty($result['phone']) && isset($parts[1])) {
                    $result['phone'] = $parts[1];
                    $addressParts = array_slice($parts, 2);
                    $result['address'] = implode(' ', $addressParts);
                }
            }
        }
        
        return success($result);
    }

     /**
      * 添加客户收货信息
      */
    public function receiptAdd($data){
        $customer = new CustomerReceipt();
        $data['is_del'] = 0;
        $customer->save($data);
        return success("添加成功");
    }
    
    /**
     * 客户收货信息列表
     */
    public function receiptList($data){
        $customer = new CustomerReceipt();
        $model = $customer->where('is_del', '=', 0)
            ->where('site_id', '=', $data['site_id'])
            ->where('uid', '=', $data['uid']);
            
        // 设置分页参数
        $page = isset($data['page']) ? $data['page'] : 1;
        $page_size = isset($data['page_size']) ? $data['page_size'] : 10;
        
        // 执行分页查询
        $list = $model->paginate([
            'list_rows' => $page_size,
            'page' => $page,
            'var_page' => 'page'
        ]);
        
        // 格式化分页结果
        $result = [
            'data' => $list->items(),
            'total' => $list->total(),
            'page' => $page,
            'page_size' => $page_size,
            'total_page' => ceil($list->total() / $page_size)
        ];
        
        return success($result);
    }
    
    /**
     * 删除客户收货信息
     */
    public function receiptDel($id){
        $customer = new CustomerReceipt();
        $customer->where('id', '=', $id)->update([
            'is_del' => 1,
        ]);
        return success("删除成功");
    }
    
    /**
     * 编辑回显接口
     */
    public function receiptFind($id){
        $customer = new CustomerReceipt();
        $data = $customer->where('id', '=', $id)->find()->toArray();
        return success($data);
    }
    
    /**
     * 编辑客户收货信息
     */
    public function receiptEdit($data){
        $customer = new CustomerReceipt();
        $customer->where('id', '=', $data['id'])->update([
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'address' => $data['address'],
        ]);
        return success("编辑成功");
    }

     /**
      * 添加客户
      */
    public function customerAdd($data){
        $customer = new CustomerModel();
        $data['is_del'] = 0;
        $data['create_time'] = time();
        $data['create_id'] = $data['uid'];
        $data['maintainer_id'] = $data['uid'];
        $customer->save($data);
        return success("添加成功");
    }

    /**
     * 编辑客户信息
     */
    public function customerEdit($data){
        $customer = new CustomerModel();
        if($data['payment_id']){
            $data['payment_id'] = json_encode($data['payment_id']);
        }
        if($data['receipt_id']){
            $data['receipt_id'] = json_encode($data['receipt_id']);
        }

        $customer->where('id', '=', $data['id'])->update([
            'customer_name' => $data['customer_name'],
            'customer_mobile' => $data['customer_mobile'],
            'customer_type' => $data['customer_type'],
            'wx_name' => $data['wx_name'],
            'wx_number' => $data['wx_number'],
            'gender' => $data['gender'],
            'birthday' => $data['birthday'],
            'level' => $data['level'],
            'remarks' => $data['remarks'],
            'payment_id' => $data['payment_id'],
            'receipt_id' => $data['receipt_id'],
            "update_time" => time(),
            "maintainer_id" => $data['maintainer_id'],
        ]);
        return success("编辑成功");
    }

    /**
     * 编辑回显
     */
    public function customerFind($id){
        $customer = new CustomerModel();
        $data = $customer->where('id', '=', $id)->find()->toArray();
        
        if($data['payment_id']!= ""){
            $data['payment_id'] = json_decode($data['payment_id'], true);
        }
        if($data['receipt_id']!= ""){
            $data['receipt_id'] = json_decode($data['receipt_id'], true);
        }

        $user = (new SysUser())->where('uid', '=', $data['create_id'])->findOrEmpty()->toArray();
        $data['create_name'] = $user['real_name'];
        $data['create_image'] = $user['head_img'];
        
        $maintainerUser = (new SysUser())->where('uid', '=', $data['maintainer_id'])->findOrEmpty()->toArray();
        if($maintainerUser){
            $data['maintainer_name'] = $maintainerUser['real_name'];
            $data['maintainer_image'] = $maintainerUser['head_img'];
        }else{
            $data['maintainer_name'] = "";
            $data['maintainer_image'] = "";
        }
        

        return success($data);
    }

     /**
      * 客户列表
      */
    public function customerList($params){
        $customer = new CustomerModel();
        $model = $customer->where('is_del', '=', 0);
        
        // 添加uid查询条件
        if(!empty($params['uid'])) {
            $model = $model->where('uid', '=', $params['uid']);
        }
        
        // 添加site_id查询条件
        if(!empty($params['site_id'])) {
            $model = $model->where('site_id', '=', $params['site_id']);
        }
        
        // 搜索条件
        $model = $model->where(function($query) use ($params) {
            // 使用闭包构建OR条件，匹配多个字段中的任意一个
            if(!empty($params['search_str'])) {
                $query->where('customer_name', 'like', '%' . $params['search_str'] . '%')
                      ->whereOr('customer_mobile', 'like', '%' . $params['search_str'] . '%')
                      ->whereOr('remarks', 'like', '%' . $params['search_str'] . '%')
                      ->whereOr('id', '=', $params['search_str']); // id是精确匹配
            }
        });
        
        // 设置分页参数
        $page = isset($params['page']) ? $params['page'] : 1;
        $page_size = isset($params['page_size']) ? $params['page_size'] : 10;
        
        // 添加根据创建时间倒序排序
        $model = $model->order('create_time', 'desc');
        
        // 执行分页查询
        $list = $model->paginate([
            'list_rows' => $page_size,
            'page' => $page,
            'var_page' => 'page'
        ]);
        
        $result_list = $list->items();

        foreach($result_list as $key => $item){
            $result_list[$key]['c_order_num'] = 0;
            $result_list[$key]['c_transaction_monery'] = 0;
            $result_list[$key]['c_profit'] = 0;
        }

        // 格式化分页结果
        $result = [
            'data' => $result_list,
            'total' => $list->total(),
            'page' => $page,
            'page_size' => $page_size,
            'total_page' => ceil($list->total() / $page_size)
        ];
        
        return success($result);
    }
    
    /**
      * 客户详情
      */
    public function customerDetails($id){
        $customer = new CustomerModel();
        $data = $customer->where('id', '=', $id)->findOrEmpty()->toArray();
        //购买
        $data['buy_order_num'] = 0;
        $data['buy_transaction_monery'] = 0;
        $data['buy_profit'] = 0;
        
        //寄卖
        $data['consignment_order_num'] = 0;
        $data['consignment_transaction_monery'] = 0;
        $data['consignment_profit'] = 0;

        //质押
        $data['staking_order_num'] = 0;
        $data['staking_transaction_monery'] = 0;
        $data['staking_profit'] = 0;

        //保养
        $data['maintainer_order_num'] = 0;
        $data['maintainer_transaction_monery'] = 0;
        $data['maintainer_profit'] = 0;

        $user = (new SysUser())->where('uid', '=', $data['create_id'])->findOrEmpty()->toArray();
        $data['create_name'] = $user['real_name'];
        $data['create_image'] = $user['head_img'];
        
        $maintainerUser = (new SysUser())->where('uid', '=', $data['maintainer_id'])->findOrEmpty()->toArray();
        if($maintainerUser){
            $data['maintainer_name'] = $maintainerUser['real_name'];
            $data['maintainer_image'] = $maintainerUser['head_img'];
        }else{
            $data['maintainer_name'] = "";
            $data['maintainer_image'] = "";
        }

        return success($data);
    }

     /**
      * 删除客户
      */
    public function customerDel($id){
        $customer = new CustomerModel();
        $customer->where('id', '=', $id)->update([
            'is_del' => 1,
        ]);
        return success("删除成功");
    }

     /**
      * 客户统计
      */
    public function customerTj($data){
        $customer = new CustomerModel();
        
        // 获取当前时间
        $now = time();
        $today_start = strtotime(date('Y-m-d', $now));
        $today_end = $today_start + 86399; // 今天结束时间（23:59:59）
        
        $this_month_start = strtotime(date('Y-m-01', $now));
        $this_month_end = strtotime(date('Y-m-t', $now)) + 86399; // 本月最后一天结束时间
        
        $last_month_start = strtotime(date('Y-m-01', strtotime('-1 month', $now)));
        $last_month_end = strtotime(date('Y-m-t', strtotime('-1 month', $now))) + 86399; // 上月最后一天结束时间
        
        // 1. 统计表中未删除的总数量
        $total_count = $customer->where('is_del', '=', 0)
            ->where('uid', '=', $data['uid'])
            ->where('site_id', '=', $data['site_id'])
            ->count();
        
        // 2. 统计未删除的今日新增
        $today_count = $customer->where('is_del', '=', 0)
            ->where('uid', '=', $data['uid'])
            ->where('site_id', '=', $data['site_id'])
            ->whereBetween('create_time', [$today_start, $today_end])
            ->count();
        
        // 3. 统计未删除的本月新增
        $this_month_count = $customer->where('is_del', '=', 0)
            ->where('uid', '=', $data['uid'])
            ->where('site_id', '=', $data['site_id'])
            ->whereBetween('create_time', [$this_month_start, $this_month_end])
            ->count();
        
        // 4. 统计未删除的上月新增
        $last_month_count = $customer->where('is_del', '=', 0)
            ->where('uid', '=', $data['uid'])
            ->where('site_id', '=', $data['site_id'])
            ->whereBetween('create_time', [$last_month_start, $last_month_end])
            ->count();
        
        // 构建返回数据
        $result = [
            'total_count' => $total_count,        // 总数量
            'today_count' => $today_count,        // 今日新增
            'this_month_count' => $this_month_count,  // 本月新增
            'last_month_count' => $last_month_count   // 上月新增
        ];
        
        return success($result);
    }



}
