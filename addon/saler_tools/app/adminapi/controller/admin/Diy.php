<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/1/8 20:51
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\adminapi\controller\admin;

use addon\saler_tools\app\common\BaseAdminController;
use addon\saler_tools\app\service\diy\DiyService;
use addon\saler_tools\app\service\diy\dict\ComponentDict;
use addon\saler_tools\app\service\diy\dict\LinkDict;
use addon\saler_tools\app\service\diy\dict\PagesDict;
use addon\saler_tools\app\service\diy\dict\TemplateDict;

/**
 * diy管理
 * Class Diy
 * @package addon\saler_tools\app\adminapi\controller\admin
 */
class Diy extends BaseAdminController
{

    /**
     * @notes 获取自定义页面分页列表
     */
    public function lists()
    {
        $data = $this->request->params([
            [ "title", "" ],
            [ "type", "" ],
            [ 'mode', '' ],
            [ 'addon_name', '' ]
        ]);
        return success(( new DiyService() )->getPage($data));
    }

    /**
     * @notes 获取自定义页面分页列表，轮播搜索组件用
     */
    public function getPageByCarouselSearch()
    {
        $data = $this->request->params([]);
        return success(( new DiyService() )->getPageByCarouselSearch($data));
    }

    /**
     * @notes 获取自定义页面列表
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getList()
    {
        $data = $this->request->params([
            [ "title", "" ],
            [ "type", "" ],
            [ 'mode', '' ]
        ]);
        return success(( new DiyService() )->getList($data));
    }

    /**
     * 自定义页面详情
     * @param int $id
     */
    public function info(int $id)
    {
        return success(( new DiyService() )->getInfo($id));
    }

    /**
     * 添加自定义页面
     */
    public function add()
    {
        $data = $this->request->params([
            [ "page_title", "" ],
            [ "title", "" ],
            [ "name", "" ],
            [ "type", "" ],
            [ 'template', '' ],
            [ 'mode', 'diy' ], // 页面展示模式，diy：自定义，fixed：固定
            [ "value", "", false ],
            [ 'is_default', 0 ],
            [ 'is_change', '' ]
        ]);

        $this->validate($data, 'app\validate\diy\Diy.add');
        $id = ( new DiyService() )->add($data);
        return success('ADD_SUCCESS', [ 'id' => $id ]);
    }

    /**
     * 自定义页面编辑
     * @param $id
     */
    public function edit($id)
    {
        $data = $this->request->params([
            [ "page_title", "" ],
            [ "title", "" ],
            [ "name", "" ],
            [ 'template', '' ],
            [ 'mode', 'diy' ], // 页面展示模式，diy：自定义，fixed：固定
            [ "value", "", false ],
            [ 'is_change', '' ]
        ]);

        $this->validate($data, 'app\validate\diy\Diy.edit');
        ( new DiyService() )->edit($id, $data);
        return success('MODIFY_SUCCESS');
    }

    /**
     * 自定义页面删除
     * @param int $id
     */
    public function del(int $id)
    {
        ( new DiyService() )->del($id);
        return success('DELETE_SUCCESS');
    }

    /**
     * 设为使用
     * @param $id
     */
    public function setUse($id)
    {
        ( new DiyService() )->setUse($id);
        return success('MODIFY_SUCCESS');
    }

    /**
     * 获取页面初始化数据
     */
    public function getPageInit()
    {
        $params = $this->request->params([
            [ 'id', "" ],
            [ "name", "" ],
            [ "type", "" ],
            [ "title", "" ],
        ]);

        $diy_service = new DiyService();
        return success($diy_service->getInit($params));
    }

    /**
     * 获取自定义链接列表
     */
    public function getLink()
    {
        $diy_service = new DiyService();
        return success($diy_service->getLink());
    }

    /**
     * 获取页面模板
     */
    public function getTemplate()
    {
        $params = $this->request->params([
            [ 'key', '' ], // 页面模板标识
            [ 'action', '' ], // 页面是否装修标识，为空标识不装修，decorate：装修
            [ 'mode', '' ], // 页面展示模式，diy：自定义，fixed：固定
            [ 'type', '' ], // 页面类型，index：首页、member_index：个人中心，空：普通页面
            [ 'addon', '' ], // 插件标识
        ]);
        $diy_service = new DiyService();
        return success($diy_service->getTemplate($params));
    }

    /**
     * 修改页面分享内容
     * @param int $id
     */
    public function modifyShare(int $id)
    {
        $data = $this->request->params([
            [ "share", "" ],
        ]);
        ( new DiyService() )->modifyShare($id, $data);
        return success('MODIFY_SUCCESS');
    }

    /**
     * 获取装修页面列表
     */
    public function getDecoratePage()
    {
        $params = $this->request->params([
            [ 'type', '' ],
        ]);
        return success(( new DiyService() )->getDecoratePage($params));
    }

    /**
     * 切换模板
     */
    public function changeTemplate()
    {
        $data = $this->request->params([
            [ 'type', '' ], // 页面类型
            [ 'name', '' ], // 链接名称标识
            [ 'parent', '' ], // 链接父级名称标识
            [ 'page', '' ], // 链接路由
            [ 'title', '' ], // 链接标题
            [ 'action', '' ] // 是否存在操作，decorate 表示支持装修
        ]);
        ( new DiyService() )->changeTemplate($data);
        return success('MODIFY_SUCCESS');
    }

    /**
     * 获取模板页面列表
     */
    public function getTemplatePages()
    {
        $params = $this->request->params([
            [ 'type', '' ], // 页面类型
            [ 'mode', '' ] // 页面模式：diy：自定义，fixed：固定
        ]);
        $pages = PagesDict::getPages($params);
        return success($pages);
    }

    /**
     * 获取模板页面（存在的应用插件列表）
     */
    public function getApps()
    {
        return success(( new DiyService() )->getApps());
    }


    /**
     * 修改应用语言
     */
    public function lang()
    {
        $data = $this->_vali([
           'id.require' => '请选择修改的数据',
           'lang.default' => '',
        ]);
        return ( new DiyService() )->modifyLang($data);
    }

}
