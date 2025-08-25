<?php
// +----------------------------------------------------------------------
// | campus-procurement-mall
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/11/20 0:46
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\common;

use think\exception\ValidateException;
use think\Model;
use think\Validate;

/**
 *
 * Class BaseAdminService
 * @package addon\saler_tools\app\common
 */
class BaseAdminService extends \core\base\BaseAdminService
{

    protected $app;

    public function __construct()
    {
        parent::__construct();
        $this->app = app();
    }



    /**
     * @param $model
     * @param $each
     * @return Model
     */
    public function pageX($model, $each = null,)
    {
        $page_params = $this->getPageParam();
        $page = $page_params['page'];
        $limit = $page_params['limit'];
        $list = $model->paginate([
            'list_rows' => $limit,
            'page' => $page,
        ]);
        if (!empty($each)) {
            $list = $list->each($each);
        }
        return $list;
    }


}
