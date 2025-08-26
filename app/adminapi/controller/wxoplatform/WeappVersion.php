<?php
// +----------------------------------------------------------------------
// | Niucloud-admin 企业快速开发的saas管理平台
// +----------------------------------------------------------------------
// | 官方网址：https://www.niucloud.com
// +----------------------------------------------------------------------
// | niucloud团队 版权所有 开源版本可自由商用
// +----------------------------------------------------------------------
// | Author: Niucloud Team
// +----------------------------------------------------------------------

namespace app\adminapi\controller\wxoplatform;

use app\service\admin\wxoplatform\WeappVersionService;
use core\base\BaseAdminController;
use think\Response;

class WeappVersion extends BaseAdminController
{
    /**
     * 平台提交小程序版本
     * @return Response
     */
    public function weappCommit() {
        (new WeappVersionService())->add();
        return success();
    }

    /**
     * 获取最后一次提交记录
     * @return Response
     */
    public function lastCommitRecord() {
        return success(data:(new WeappVersionService())->getLastCommitRecord());
    }

    public function commitRecord() {
        return success(data:(new WeappVersionService())->getPage());
    }

    /**
     * 站点小程序提交
     * @return Response
     */
    public function siteWeappCommit() {
        (new WeappVersionService())->siteWeappCommit();
        return success();
    }
}
