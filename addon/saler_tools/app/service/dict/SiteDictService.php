<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/15 19:35
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\dict;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\SalerToolsDict;
use core\exception\AdminException;

/**
 *
 * Class SiteDictService
 * @package addon\saler_tools\app\service\dict
 */
class SiteDictService extends BaseAdminService
{


    // 允许写入的数据
    protected $site_dict_config = [
        'product_position' => [ // 产品位置
            'write'  => true, // 是否允许写入
            'delete' => true, // 是否允许删除
            'repeat' => false,// 是否允许重复
        ],
        'recycle_type'     => [ // 回收类型
            'write'  => true, // 是否允许写入
            'delete' => true, // 是否允许删除
            'repeat' => false,// 是否允许重复
        ],
        'product_tag'      => [ // 产品标签
            'write'  => true, // 是否允许写入
            'delete' => true, // 是否允许删除
            'repeat' => false,// 是否允许重复
        ],
        'cost_type'        => [ // 成本类型
            'write'  => true, // 是否允许写入
            'delete' => true, // 是否允许删除
            'repeat' => false,// 是否允许重复
        ],
        'order_type'       => [ // 成本类型
            'write'  => true, // 是否允许写入
            'delete' => true, // 是否允许删除
            'repeat' => false,// 是否允许重复
        ],
        'order_service_type' => [ // 保养维护类型
            'write'  => true, // 是否允许写入
            'delete' => true, // 是否允许删除
            'repeat' => false,// 是否允许重复
        ]
    ];


    public function getList($key)
    {
        if (!isset($this->site_dict_config[$key])) {
            throw new AdminException('key_is_not_exist');
        }

        $model = new SalerToolsDict();
        $list  = $model->where('key', $key)
            ->where('site_id', $this->site_id)
            ->order('sort', 'asc')
            ->order('id', 'desc')
            ->field('id,key,value,sort')
            ->select()
            ->toArray();

        $data = array_merge($this->site_dict_config[$key], ['list' => $list]);

        return success($data);
    }

    public function list($key)
    {
        $model = new SalerToolsDict();
        return $model->where('key', $key)
            ->where('site_id', $this->site_id)
            ->order('sort', 'asc')
            ->order('id', 'desc')
            ->field('id,key,value,sort')
            ->select()
            ->toArray();
    }


    public function push($key, $value)
    {
        if (!isset($this->site_dict_config[$key])) {
            throw new AdminException('key_is_not_exist');
        }

        if (!$this->site_dict_config[$key]['write']) {
            throw new AdminException('write_is_not_allow');
        }

        $model = new SalerToolsDict();
        // 检测新数据是否存在
        if (!$this->site_dict_config[$key]['repeat']) {
            $is_exist = $model->where('key', $key)
                ->where('value', $value)
                ->where('site_id', $this->site_id)
                ->findOrEmpty();

            if (!$is_exist->isEmpty()) {
//                throw new AdminException('data_is_exist');
                throw new AdminException('数据已存在');
            }
        }

        $res = $model->save([
            'key'     => $key,
            'value'   => $value,
            'site_id' => $this->site_id,
        ]);

        if ($res === false) {
            throw new AdminException('push_fail');
        }

        return success();

    }


    public function edit($key, $data)
    {
        if (!isset($this->site_dict_config[$key])) {
            throw new AdminException('key_is_not_exist');
        }

        $model = new SalerToolsDict();

        $dict = $model->where('id', $data['id'])->where('site_id', $this->site_id)->where('key', $key)->findOrEmpty();

        if ($dict->isEmpty()) {
//            throw new AdminException('data_is_exist');
            throw new AdminException('数据已存在');
        }

        // 不允许重复时，校验同站点同key下是否存在除当前记录外的相同值
        if (!$this->site_dict_config[$key]['repeat']) {
            $exists = (new SalerToolsDict())
                ->where('key', $key)
                ->where('site_id', $this->site_id)
                ->where('value', $data['value'])
                ->where('id', '<>', $data['id'])
                ->findOrEmpty();

            if (!$exists->isEmpty()) {
                throw new AdminException('数据已存在');
            }
        }

        $dict->value = $data['value'];

        $res = $dict->save();

        if ($res === false) {
            throw new AdminException('edit_fail');
        }
        return success();
    }

    /**
     * @param $key string 键
     * @param $list array 列表
     * @return \think\Response
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function sort($key, $list)
    {
        $model = new SalerToolsDict();

        foreach ($list as $item) {
            $model->where('key', $key)
                ->where('site_id', $this->site_id)
                ->where('id', $item['id'])
                ->update(['sort' => $item['sort']]);
        }

        return success();
    }


    public function del($key, $id)
    {
        if (!isset($this->site_dict_config[$key])) {
            throw new AdminException('key_is_not_exist');
        }

        if (!$this->site_dict_config[$key]['delete']) {
            throw new AdminException('delete_is_not_allow');
        }

        $model = new SalerToolsDict();
        $res   = $model->where('id', $id)
            ->where('site_id', $this->site_id)
            ->where('key', $key)
            ->delete();

        if ($res === false) {
            throw new AdminException('delete_fail');
        }

        return success();
    }


}
