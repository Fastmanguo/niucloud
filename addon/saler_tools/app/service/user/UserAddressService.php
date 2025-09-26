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

/**
 * 用户地址服务
 * Class UserAddressService
 * @package addon\saler_tools\app\service\user
 */
class UserAddressService extends BaseAdminService
{   


    /**
     * 获取地理位置数据
     * @param int $parent_id 父级ID，默认为0
     * @return array
     */
    public function selectRegion($parent_id = 0, $shortname = "")
    {
        try {
            // 查询条件
            
            $where = [
                ['status', '=', 1] // 只查询有效的地区
            ];

            if($parent_id != 1){
                $where[] = ['pid', '=', $parent_id];
            }

            if($shortname){
                $where[] = ['shortname', '=', $shortname];
            }
            
            // 查询地区数据
            $regionModel = new SysArea();
            $list = $regionModel->where($where)
                ->field('id, pid, name, shortname, longitude, latitude, level, sort')
                ->order('shortname', 'asc')
                ->select()
                ->toArray();
            // 返回成功结果

            $hotList = array([
                'id' => 110000,
                'pid' => 0,
                'name' => '北京市',
                'shortname' => 'B',
                'longitude' => '116.40529',
                'latitude' => '39.904987',
                'level' => 1,
                'sort' => 0
            ],[
                'id' => 310000,
                'pid' => 0,
                'name' => '上海市',
                'shortname' => 'S',
                'longitude' => '121.47264',
                'latitude' => '31.231707',
                'level' => 1,
                'sort' => 0
            ],[
                'id' => 440100,
                'pid' => 440000,
                'name' => '广州市',
                'shortname' => 'G',
                'longitude' => '113.28064',
                'latitude' => '23.125177',
                'level' => 2,
                'sort' => 0
            ],[
                'id' => 440300,
                'pid' => 440000,
                'name' => '深圳市',
                'shortname' => 'S',
                'longitude' => '114.085945',
                'latitude' => '22.547',
                'level' => 2,
                'sort' => 0
            ],[
                'id' => 510100,
                'pid' => 510000,
                'name' => '成都市',
                'shortname' => 'C',
                'longitude' => '104.065735',
                'latitude' => '30.659462',
                'level' => 2,
                'sort' => 0
            ],[
                'id' => 120000,
                'pid' => 0,
                'name' => '天津市',
                'shortname' => 'T',
                'longitude' => '117.190186',
                'latitude' => '39.125595',
                'level' => 1,
                'sort' => 0
            ],[
                'id' => 330100,
                'pid' => 330000,
                'name' => '杭州市',
                'shortname' => 'H',
                'longitude' => '120.15358',
                'latitude' => '30.287458',
                'level' => 2,
                'sort' => 0
            ]
        );
            return success([
                'list' => $list,
                "hot_list" => $hotList,
                'parent_id' => $parent_id,
                'count' => count($list)
            ]);
            
        } catch (\Exception $e) {
            // 返回错误信息
            return fail($e->getMessage());
        }
    }

    /**
     * 添加收货地址
     * @param array $data 收货地址数据
     * @return array 操作结果
     */
    public function add($data)
    {
        try {
            // 实例化模型
            $model = new MemberAddress();
            
            // 处理用户ID字段（控制器使用uid，数据库使用member_id）
            if (isset($data['uid'])) {
                $data['member_id'] = $data['uid'];
                unset($data['uid']);
            }
            
            // 如果设置为默认地址，则将该会员的其他地址设置为非默认 1:默认地址
            if (intval($data['is_default']) == 1) {
                $model->where([
                    ['member_id', '=', $data['member_id']]
                ])->update(['is_default' => 0]);
            }
            
            // 设置站点ID
            // $data['site_id'] = $this->site_id;

            // 创建数据
            $res = $model->create($data);
            
            // 返回成功结果
            return success([
                'id' => $res->id,
                'message' => '地址添加成功'
            ]);
            
        } catch (\Exception $e) {
            // 返回错误信息
            return fail($e->getMessage());
        }
    }

    /**
     * 删除收货地址
     */
    public function del($id)
    {
        try {
            // 实例化模型
            $model = new MemberAddress();
            
            // 删除数据
            $res = $model->where([
                ['id', '=', $id]
            ])->delete();
            
            // 返回成功结果
            return success([
                'message' => '地址删除成功'
            ]);
            
        } catch (\Exception $e) {
            // 返回错误信息
            return fail($e->getMessage());
        }
    }
    
    /**
     * 编辑回显
     */
    public function find($id)
    {
        try {
            // 实例化模型
            $model = new MemberAddress();
            
            // 查询数据
            $res = $model->where([
                ['id', '=', $id]
            ])->findOrEmpty();
            
            // 如果没有找到数据，直接返回
            if ($res->isEmpty()) {
                return success([]);
            }
            
            // 转换为数组
            $addressData = $res->toArray();
            
            // 实例化地区模型
            $areaModel = new SysArea();
            
            // 根据province_id获取省份名称
            if (!empty($addressData['province_id'])) {
                $province = $areaModel->where([
                    ['id', '=', $addressData['province_id']]
                ])->value('name');
                $addressData['province_name'] = $province ?? '';
            }
            
            // 根据city_id获取城市名称
            if (!empty($addressData['city_id'])) {
                $city = $areaModel->where([
                    ['id', '=', $addressData['city_id']]
                ])->value('name');
                $addressData['city_name'] = $city ?? '';
            }
            
            // 根据district_id获取区县名称
            if (!empty($addressData['district_id'])) {
                $district = $areaModel->where([
                    ['id', '=', $addressData['district_id']]
                ])->value('name');
                $addressData['district_name'] = $district ?? '';
            }
            
            // 返回成功结果
            return success($addressData);
            
        } catch (\Exception $e) {
            // 返回错误信息
            return fail($e->getMessage());
        }
    }

    /**
     * 编辑地址
     */
    public function edit($data)
    {
        try {
            // 实例化模型
            $model = new MemberAddress();
            
            // 处理用户ID字段（控制器使用uid，数据库使用member_id）
            if (isset($data['uid'])) {
                $data['member_id'] = $data['uid'];
                unset($data['uid']);
            }
            
            // 如果设置为默认地址，则将该会员的其他地址设置为非默认 1:默认地址
            if (intval($data['is_default']) == 1) {
                $model->where([
                    ['member_id', '=', $data['member_id']]
                ])->update(['is_default' => 0]);
            }
            
            // 更新数据
            $res = $model->where([
                ['id', '=', $data['id']]
            ])->update($data);
            
            // 返回成功结果
            return success([
                'message' => '地址更新成功'
            ]);
            
        } catch (\Exception $e) {
            // 返回错误信息
            return fail($e->getMessage());
        }
    }

    /**
     * 地址列表
     */
    public function list($uid){
        try {
            // 实例化模型
            $model = new MemberAddress();
            
            // 查询数据
            $res = $model->where([
                ['member_id', '=', $uid]
            ])->select();
            
            // 转换为数组
            $addressList = $res->toArray();
            
            // 如果地址列表为空，直接返回
            if (empty($addressList)) {
                return success([]);
            }
            
            // 实例化地区模型
            $areaModel = new SysArea();
            
            // 遍历地址列表，为每个地址添加省市区名称
            foreach ($addressList as &$addressData) {
                // 根据province_id获取省份名称
                if (!empty($addressData['province_id'])) {
                    $province = $areaModel->where([
                        ['id', '=', $addressData['province_id']]
                    ])->value('name');
                    $addressData['province_name'] = $province ?? '';
                }
                
                // 根据city_id获取城市名称
                if (!empty($addressData['city_id'])) {
                    $city = $areaModel->where([
                        ['id', '=', $addressData['city_id']]
                    ])->value('name');
                    $addressData['city_name'] = $city ?? '';
                }
                
                // 根据district_id获取区县名称
                if (!empty($addressData['district_id'])) {
                    $district = $areaModel->where([
                        ['id', '=', $addressData['district_id']]
                    ])->value('name');
                    $addressData['district_name'] = $district ?? '';
                }
            }
            
            // 返回成功结果
            return success($addressList);
            
        } catch (\Exception $e) {
            // 返回错误信息
            return fail($e->getMessage());
        }
    }

    /**
     * 识别文本
     */
    public function recognizeText($input){

        $result = [
            'name' => '',
            'phone' => '',
            'region' => '',
            'province_name' => '',
            'city_name' => '',
            'district_name' => '',
            'detail_address' => '',
            'province_id' => 0,
            'city_id' => 0,
            'district_id' => 0
        ];

        // 检测输入格式
        if ($this->isLabeledFormat($input)) {
            $result = $this->parseLabeledFormat($input);
        } else {
            $result = $this->parseCompactFormat($input);
        }
        
        // 解析省市区县信息
        if (!empty($result['region'])) {
            $regionInfo = $this->parseRegion($result['region']);
            $result['province_name'] = $regionInfo['province'];
            $result['city_name'] = $regionInfo['city'];
            $result['district_name'] = $regionInfo['district'];
        }
        
        // 根据省市区名称查询对应的id
        $areaModel = new SysArea();
        
        // 查询省份id
        if (!empty($result['province_name'])) {
            $provinceId = $areaModel->where([
                ['name', '=', $result['province_name']]
            ])->value('id');
            $result['province_id'] = $provinceId ?? 0;
        }
        
        // 查询城市id
        if (!empty($result['city_name'])) {
            $city_id = $areaModel->where([
                ['name', '=', $result['city_name']]
            ])->value('id');
            $result['city_id'] = $city_id ?? 0;
        }

        // 查询区县id
        if (!empty($result['district_name'])) {
            $district_id = $areaModel->where([
                ['name', '=', $result['district_name']]
            ])->value('id');
            $result['district_id'] = $district_id ?? 0;
        }
       
        return success($result);

    }
    
    /**
     * 解析省市区县信息
     * @param string $region
     * @return array
     */
    private function parseRegion($region) {
        $result = [
            'province' => '',
            'city' => '',
            'district' => ''
        ];
        
        // 常见的省份简称和全称
        $provinces = [
            '北京市', '上海市', '天津市', '重庆市',
            '河北省', '山西省', '辽宁省', '吉林省', '黑龙江省',
            '江苏省', '浙江省', '安徽省', '福建省', '江西省', '山东省', '河南省', '湖北省', '湖南省', '广东省', '海南省', '四川省', '贵州省', '云南省', '陕西省', '甘肃省', '青海省', '台湾省',
            '内蒙古自治区', '广西壮族自治区', '西藏自治区', '宁夏回族自治区', '新疆维吾尔自治区',
            '香港特别行政区', '澳门特别行政区'
        ];
        
        // 查找省份
        foreach ($provinces as $province) {
            if (strpos($region, $province) === 0) {
                $result['province'] = $province;
                $region = substr($region, strlen($province));
                break;
            }
        }
        
        // 如果未找到省份，但region中包含'省'、'市'、'自治区'等关键字，尝试提取省份
        if (empty($result['province'])) {
            $provincePatterns = [
                '/^(.*?省)/',
                '/^(.*?市)(?=[^市]*区|县)/',  // 匹配直辖市如北京市、上海市
                '/^(.*?自治区)/',
                '/^(.*?特别行政区)/'
            ];
            
            foreach ($provincePatterns as $pattern) {
                if (preg_match($pattern, $region, $matches)) {
                    $result['province'] = $matches[1];
                    $region = substr($region, strlen($matches[1]));
                    break;
                }
            }
        }
        
        // 查找城市 - 优化正则表达式以确保正确分离城市
        $cityPatterns = [
            '/^(.*?市)(?!.*市)/',  // 匹配最后一个市字前的部分
            '/^(.*?地区)/',
            '/^(.*?自治州)/'
        ];
        
        foreach ($cityPatterns as $pattern) {
            if (preg_match($pattern, $region, $matches)) {
                $result['city'] = $matches[1];
                $region = substr($region, strlen($matches[1]));
                break;
            }
        }
        
        // 如果城市未找到，尝试另一种方式提取
        if (empty($result['city']) && !empty($region)) {
            $cityKeywords = ['市', '地区', '自治州'];
            foreach ($cityKeywords as $keyword) {
                if (strpos($region, $keyword) !== false) {
                    $cityEndPos = strpos($region, $keyword) + strlen($keyword);
                    $result['city'] = substr($region, 0, $cityEndPos);
                    $region = substr($region, $cityEndPos);
                    break;
                }
            }
        }
        
        // 查找区县 - 确保区县信息能正确提取
        if (!empty($region)) {
            $districtKeywords = ['区', '县', '旗', '市辖区', '自治县'];
            foreach ($districtKeywords as $keyword) {
                if (strpos($region, $keyword) !== false) {
                    $districtEndPos = strpos($region, $keyword) + strlen($keyword);
                    $result['district'] = substr($region, 0, $districtEndPos);
                    break;
                }
            }
        }
        
        // 最后的兜底方案，确保省市区县能够尽可能分离
        if (empty($result['district']) && !empty($region)) {
            $result['district'] = $region;
        }
        
        return $result;
    }


    /**
     * 品牌列表
     */
    public function brandList(){
        try {
            // 查询品牌列表的原生SQL
            $sql = "SELECT * FROM saler_tools_goods_brand";
            
            // 执行SQL查询
            $list = \think\facade\Db::query($sql);
            
            // 返回成功结果
            return success($list);
        } catch (\Exception $e) {
            // 返回错误信息
            return fail($e->getMessage());
        }
    }

    /**
     * 商品列表
     */
    public function goodsList($data){
        try {
            $brand_id = $data['brand_id'] ?? '';
            $category_id = $data['category_id'] ?? '';
            $page = $data['page'] ?? 1;
            $page_size = $data['page_size'] ?? 10;
            $offset = ($page - 1) * $page_size;
            
            // 构建WHERE条件
            $where_conditions = ['deleted_time = 0', 'is_sale = 1'];
            $count_params = [];
            $list_params = [
                'offset' => $offset,
                'page_size' => $page_size
            ];
            
            // 添加category_id条件
            if($category_id){
                $where_conditions[] = 'category_id = :category_id';
                $count_params['category_id'] = $category_id;
                $list_params['category_id'] = $category_id;
            }
            
            // 添加brand_id条件
            if($brand_id){
                $where_conditions[] = 'brand_id = :brand_id';
                $count_params['brand_id'] = $brand_id;
                $list_params['brand_id'] = $brand_id;
            }
            
            // 构建完整的WHERE子句
            $where_clause = implode(' AND ', $where_conditions);
            
            // 计算总数的原生SQL
            $count_sql = "SELECT COUNT(*) as total FROM saler_tools_goods WHERE {$where_clause}";
            $list_sql = "SELECT * FROM saler_tools_goods WHERE {$where_clause} ORDER BY create_time DESC LIMIT :offset, :page_size";

            $count_result = \think\facade\Db::query($count_sql, $count_params);
            $total = $count_result[0]['total'] ?? 0;
            
            // 查询商品列表的原生SQL
            $list = \think\facade\Db::query($list_sql, $list_params);

            foreach ($list as $key => $value) {
                $list[$key]['goods_image'] = json_decode($value['goods_image']);
            }
            
            // 返回成功结果
            return success([
                'list' => $list,
                'total' => $total,
                'current_page' => $page,
                'per_page' => $page_size,
                'last_page' => ceil($total / $page_size)
            ]);
        } catch (\Exception $e) {
            // 返回错误信息
            return fail($e->getMessage());
        }
    }

    /**
     * 商品详情
     */
    public function goodsDetails($goods_id){
        try {
            // 查询商品详情的原生SQL
            $sql = "SELECT * FROM saler_tools_goods WHERE goods_id = :goods_id";
            
            // 执行SQL查询
            $result = \think\facade\Db::query($sql, ['goods_id' => $goods_id]);
            
            // 返回成功结果
            foreach ($result as $key => $value) {
                $result[$key]['goods_image'] = json_decode($value['goods_image']);
            }
            return success($result[0] ?? []);
        } catch (\Exception $e) {
            // 返回错误信息
            return fail($e->getMessage());
        }
    }


    /**
     * 检测是否为带标签格式
     * @param string $input
     * @return bool
     */
    private function isLabeledFormat($input)
    {
        return strpos($input, '收件人:') !== false || 
               strpos($input, '手机号码:') !== false || 
               strpos($input, '所在地区:') !== false || 
               strpos($input, '详细地址:') !== false;
    }

    /**
     * 解析带标签格式
     * @param string $input
     * @return array
     */
    private function parseLabeledFormat($input)
    {
        $result = [
            'name' => '',
            'phone' => '',
            'region' => '',
            'detail_address' => ''
        ];

        // 按行分割
        $lines = explode("\n", $input);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            if (strpos($line, '收件人:') === 0) {
                $result['name'] = trim(substr($line, 4));
            } elseif (strpos($line, '手机号码:') === 0) {
                $result['phone'] = trim(substr($line, 5));
            } elseif (strpos($line, '所在地区:') === 0) {
                $result['region'] = trim(substr($line, 5));
            } elseif (strpos($line, '详细地址:') === 0) {
                $result['detail_address'] = trim(substr($line, 5));
            }
        }

        return $result;
    }

    /**
     * 解析紧凑格式
     * @param string $input
     * @return array
     */
    private function parseCompactFormat($input)
    {
        $result = [
            'name' => '',
            'phone' => '',
            'region' => '',
            'detail_address' => ''
        ];

        // 提取手机号（11位数字）
        if (preg_match('/1[3-9]\d{9}/', $input, $matches)) {
            $result['phone'] = $matches[0];
            $input = str_replace($matches[0], '', $input);
        }

        // 分割剩余部分
        $parts = preg_split('/\s+/', trim($input));
        
        if (count($parts) >= 2) {
            // 第一个部分通常是姓名
            $result['name'] = $parts[0];
            
            // 剩余部分重新组合
            $remaining = implode(' ', array_slice($parts, 1));
            
            // 尝试分离地区和详细地址
            $this->separateRegionAndDetail($remaining, $result);
        }

        return $result;
    }

    /**
     * 分离地区和详细地址
     * @param string $address
     * @param array &$result
     */
    private function separateRegionAndDetail($address, &$result)
    {
        // 常见的地区关键词
        $regionKeywords = [
            '省', '市', '区', '县', '街道', '镇', '乡', '村',
            '开发区', '新区', '高新区', '经济开发区'
        ];

        // 查找地区部分（通常包含省市区县等关键词）
        $regionEndPos = 0;
        foreach ($regionKeywords as $keyword) {
            $pos = strrpos($address, $keyword);
            if ($pos !== false && $pos > $regionEndPos) {
                $regionEndPos = $pos + strlen($keyword);
            }
        }

        if ($regionEndPos > 0) {
            $result['region'] = trim(substr($address, 0, $regionEndPos));
            $result['detail_address'] = trim(substr($address, $regionEndPos));
        } else {
            // 如果无法分离，将整个地址作为详细地址
            $result['detail_address'] = $address;
        }
    }

    
    
}