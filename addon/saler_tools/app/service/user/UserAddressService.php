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
            
            // 返回成功结果
            return success([
                'data' => $res->toArray(),
                'message' => '地址查询成功'
            ]);
            
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
            
            // 返回成功结果
            return success([
                'data' => $res->toArray(),
                'message' => '地址列表查询成功'
            ]);
            
        } catch (\Exception $e) {
            // 返回错误信息
            return fail($e->getMessage());
        }
    }
    
}