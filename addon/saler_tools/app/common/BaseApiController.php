<?php
// +----------------------------------------------------------------------
// | campus-procurement-mall
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/11/19 23:21
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\common;

use core\base\BaseController;
use think\exception\ValidateException;
use think\App;
use think\Validate;

/**
 *
 * Class BaseApiController
 * @package addon\saler_tools\app\common
 */
class BaseApiController extends BaseController
{


    protected $site_id;

    protected $member_id;

    public function __construct(App $app)
    {
        parent::__construct($app);
    }


    public function initialize()
    {
        parent::initialize();
        $this->site_id   = $this->request->apiSiteId();
        $this->member_id = $this->request->memberId();
    }

    public function setSiteId(&$data)
    {
        $data['site_id'] = $this->site_id;
    }


    /**
     * 快捷输入并验证（ 支持 规则 # 别名 ）
     * @param array $rules 验证规则（ 验证信息数组 ）
     * @param string $type 输入方式 ( post. 或 get. )
     * @param callable|null $callable 异常处理操作
     * @return array
     */
    protected function _vali(array $rules, string $type = '', array $data = [], ?callable $callable = null)
    {

        if (empty($type)) $type = $this->request->method();
        $input = request()->$type();

        $input = array_merge($input, $data);

        [$data, $rule, $info] = [[], [], []];

        foreach ($rules as $name => $message) {
            if (preg_match('|^(.*?)\.(.*?)#(.*?)#?$|', $name . '#', $matches)) {
                [, $_key, $_rule, $alias] = $matches;
                if (in_array($_rule, ['value', 'default', 'query'])) {
                    if ($_rule === 'value') {
                        $data[$_key] = $message;
                    } elseif ($_rule === 'default') {
                        $data[$_key] = $input[($alias ?: $_key)] ?? $message;
                    } elseif ($_rule === 'query') {
                        if (isset($input[($alias ?: $_key)]) && $input[($alias ?: $_key)] != '') {
                            $data[$_key] = $input[($alias ?: $_key)];
                        }
                    }
                } else {
                    $info[explode(':', $name)[0]] = $message;
                    if (isset($input[($alias ?: $_key)])) {
                        $data[$_key] = $data[$_key] ?? ($input[($alias ?: $_key)] ?? null);
                    }
                    $rule[$_key] = isset($rule[$_key]) ? ($rule[$_key] . '|' . $_rule) : $_rule;
                }
            }
        }

        $validate = new Validate();
        if ($validate->rule($rule)->message($info)->check($data)) {
            return $data;
        } elseif (is_callable($callable)) {
            return call_user_func($callable, lang($validate->getError()), $data);
        } else {
            throw new ValidateException($validate->getError());
        }
    }


}
