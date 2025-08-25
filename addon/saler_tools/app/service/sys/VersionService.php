<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/19 18:29
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\sys;

use addon\saler_tools\app\common\BaseService;
use addon\saler_tools\app\common\Utils;
use addon\saler_tools\app\model\Version as VersionModel;
use core\exception\AdminException;

/**
 * 版本服务
 * Class VersionService
 * @package addon\saler_tools\app\service\sys
 */
class VersionService extends BaseService
{

    /** 升级包存放目录 */
    const PACKAGE_DIR = 'upload/package/wgt';

    public function lists($params)
    {

        $model = new VersionModel();
        $model = $model->order('edition_number', 'desc');
        return success($this->pageQuery($model));

    }


    public function add($data)
    {
        $model = new VersionModel();
        /** @var \think\File $file */
        $file      = $data['file'];
        $file_name = Utils::createno() . '.wgt';

        // 获取文件hash
        $hash = $file->hash();

        // 检查路径
        if (!is_dir(self::PACKAGE_DIR)) {
            mkdirs(self::PACKAGE_DIR);
        }

        $edition_url = self::PACKAGE_DIR . '/' . $file_name;

        // 移动到升级目录
        $file->move(self::PACKAGE_DIR, $file_name);

        $model->create([
            'name'            => $data['name'],
            'describe'        => $data['describe'],
            'edition_number'  => $data['edition_number'],
            'edition_name'    => $data['edition_name'],
            'edition_issue'   => $data['edition_issue'],
            'edition_silence' => $data['edition_silence'],
            'package_type'    => $data['package_type'],
            'edition_force'   => $data['edition_force'],
            'edition_url'     => $edition_url,
            'edition_hash'    => $hash,
        ]);

        return success();

    }


    public function detail($id)
    {
        $data = (new VersionModel())->where('id', $id)->findOrEmpty();

        if ($data->isEmpty()) throw new AdminException('数据不存在');

        return success($data->toArray());
    }

    public function edit($data)
    {
        $model   = new VersionModel();
        $version = $model->where('id', $data['id'])->findOrEmpty();
        if ($version->isEmpty()) throw new AdminException('数据不存在');
        $version->save($data);
        return success();
    }

    public function del($id)
    {
        $model   = new VersionModel();
        $version = $model->where('id', $id)->findOrEmpty();
        if ($version->isEmpty()) throw new AdminException('数据不存在');
        $version->delete();
        return success();
    }


    public function checkUpdate($data)
    {
        $model = new VersionModel();

        $version = $model->where('edition_number', '>', $data['edition_number'])
            ->where('edition_issue', 1)
            ->order('edition_number', 'desc')
            ->findOrEmpty();

        if ($version->isEmpty()) return fail('no update');

        $version = $version->toArray();

        $version['edition_url'] = request()->domain() . '/' . $version['edition_url'];

        return success($version);
    }


}
