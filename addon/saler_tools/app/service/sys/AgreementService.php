<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/13 18:47
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\service\sys;

use addon\saler_tools\app\common\BaseAdminService;
use addon\saler_tools\app\model\Agreement as AgreementModel;


/**
 * 协议服务
 * Class AgreementService
 * @package addon\saler_tools\app\service\sys
 */
class AgreementService extends BaseAdminService
{

    const AGREEMENT_TYPE = [
        [
            'name'    => '《用户协议》',
            'type'    => 'user_agreement',
            'content' => '用户协议内容'
        ],
        [
            'name'    => '《隐私协议》',
            'type'    => 'privacy_policy',
            'content' => '隐私政策内容'
        ],
        [
            'name'    => '《服务协议》',
            'type'    => 'service_agreement',
            'content' => '服务协议内容'
        ],
        [
            'name'    => '《注销协议》',
            'type'    => 'cancel_agreement',
            'content' => '服务协议内容'
        ],
        [
            'name'    => '《鉴定估价须知》',
            'type'    => 'valuation_agreement',
            'content' => '鉴定估价须知'
        ]
    ];


    public function allow()
    {
        return self::AGREEMENT_TYPE;
    }

    public function lists($params)
    {
        $agreement_model = new AgreementModel();
        $model           = $agreement_model->withSearch(['key', 'name'], $params);
        return success($this->pageQuery($model));
    }


    public function add($data)
    {

        // 先判断是否存在
        $agreement_model = new AgreementModel();

        $agreement = $agreement_model->where([
            ['key', '=', $data['key']],
            ['lang', '=', $data['lang']]
        ])->findOrEmpty();

        if (!$agreement->isEmpty()) {
            return fail('该协议已存在');
        }

        $agreement_model->save($data);

        return success();

    }


    public function edit($data)
    {
        $agreement_model = new AgreementModel();

        $agreement = $agreement_model->where('id', $data['id'])->findOrEmpty();

        if ($agreement->isEmpty()) {
            return fail('该协议不存在');
        }

        $agreement->save($data);

        return success();
    }


    /**
     * 获取协议
     */
    public function getAgreement($key)
    {
        $agreement_model = new AgreementModel();

        $lang      = $this->app->appLang;
        $agreement = $agreement_model->where('key', $key)->where('lang', $lang)->findOrEmpty();

        if ($agreement->isEmpty()) {
            return fail('该协议不存在');
        }

        return success($agreement->toArray());

    }

}
