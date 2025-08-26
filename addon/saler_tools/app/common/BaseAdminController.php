<?php
// +----------------------------------------------------------------------
// | campus-procurement-mall
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/11/19 21:37
// +----------------------------------------------------------------------

namespace addon\saler_tools\app\common;

use core\base\BaseController;
use think\App;
use think\exception\ValidateException;
use think\Validate;

/**
 * 基础管理员控制类
 * Class BaseAdminController
 * @package addon\saler_tools\app\common
 */
class BaseAdminController extends BaseController
{

    protected $site_id;

    public function __construct(App $app)
    {
        parent::__construct($app);
    }


    public function initialize()
    {
        parent::initialize();
        $this->site_id = $this->request->adminSiteId();
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
                // 数据索引 # 规则 # 别名
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

    /**
     * 输入排序条件验证
     * @param array $field 排序字段
     * @param array $default 默认排序
     * @param array $necessity 必要的排序规格
     * @param string|null $method 输入方式 ( post | get | put | delete )
     */
    protected function _order(array $field, array $default = [], array $necessity = [], string $method = null)
    {
        $order = $necessity;
        if (empty($method)) $method = $this->request->method();
        $input = request()->$method('__order__', '');
        $input = explode(',', $input);
        if (!empty($input) && in_array($input[0], $field) && in_array($input[1], ['asc', 'desc'])) {
            $order[$input[0]] = $input[1];
        } else {
            if ($default) $order = array_merge($order, $default);
        }
        return array_reverse($order);
    }
}
