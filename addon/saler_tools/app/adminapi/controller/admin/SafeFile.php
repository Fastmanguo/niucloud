<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/5/15 20:17
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminService;

/**
 * 安全文件
 * Class SafeFile
 * @package addon\saler_tools\app\adminapi\controller\admin
 */
class SafeFile extends BaseAdminService
{

    public function upload()
    {
        $file = $this->request->file('file');
        $type = $this->request->get('type', 'file');

        $dir = 'safe' . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR;

        mkdirs($dir);

        $file_name = $file->hash() . '_' . $file->getSize() . '.' . $file->getOriginalExtension();

        $file->move($dir, $file_name);

        return success([
            'url' => $dir . $file_name,
        ]);

    }

}
