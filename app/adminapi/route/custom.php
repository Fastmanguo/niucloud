<?php

use app\adminapi\middleware\AdminCheckRole;
use app\adminapi\middleware\AdminCheckToken;
use app\adminapi\middleware\AdminLog;
use think\facade\Route;

/**
 * 这里加载插件不需要登录的接口
 */


/**
 * 无需绑定任何店铺时的接口
 */
Route::group('saler_tools', function () {

    Route::group('user', function () {
        Route::get('info', 'addon\saler_tools\app\adminapi\controller\User@info');
        // 绑定邮箱
        Route::put('bind_email', 'addon\saler_tools\app\adminapi\controller\User@sendBindEmail');
        Route::post('bind_email', 'addon\saler_tools\app\adminapi\controller\User@bindEmail');
        // 更新个人信息
        Route::put('update', 'addon\saler_tools\app\adminapi\controller\User@update');
        // 修改密码
        Route::put('password', 'addon\saler_tools\app\adminapi\controller\User@password');
        // 修改个人信息参数
        Route::put('modify', 'addon\saler_tools\app\adminapi\controller\User@modify');
        // 更新头像
        Route::post('head_img', 'addon\saler_tools\app\adminapi\controller\User@updateHeadImg');
        // 注销账户
        Route::put('cancel', 'addon\saler_tools\app\adminapi\controller\User@cancel');

    });

    // 用户获取店铺列表
    Route::get('shop/list', 'addon\saler_tools\app\adminapi\controller\Shop@list');
    // 店铺申请记录
    Route::get('shop/apply', 'addon\saler_tools\app\adminapi\controller\Shop@applyList');
    // 店铺申请
    Route::post('shop/apply', 'addon\saler_tools\app\adminapi\controller\Shop@apply');

    // 获取店铺到期时间
    Route::get('shop/expire', 'addon\saler_tools\app\adminapi\controller\Shop@expire');
    // 获取套餐价格
    Route::get('charge/:charge_type', 'addon\saler_tools\app\adminapi\controller\Charge@list');

    // IM登录
    Route::get('im/login', 'addon\saler_tools\app\adminapi\controller\Im@login');

})->middleware(AdminCheckToken::class, true);


Route::group('saler_tools/sys', function () {

    // 获取页面布局
    Route::get('diy', 'addon\saler_tools\app\adminapi\controller\sys\Diy@index');

    // 获取语言列表
    Route::get('language/list', 'addon\saler_tools\app\adminapi\controller\sys\Language@list');
    // 获取语言包内容
    Route::get('language/:key', 'addon\saler_tools\app\adminapi\controller\sys\Language@package')->pattern(['key' => '[a-zA-Z-_]+']);
    Route::get('language/version/:key', 'addon\saler_tools\app\adminapi\controller\sys\Language@version')->pattern(['key' => '[a-zA-Z-_]+']);

    // 获取页面配置
    Route::get('diy', 'addon\saler_tools\app\adminapi\controller\sys\Diy@index');

    // 获取字典
    Route::get('dict/:key', 'addon\saler_tools\app\adminapi\controller\sys\Dict@getDict');
});


/**
 * 用户注册
 */
Route::group('saler_tools/register', function () {
    Route::post('index', 'addon\saler_tools\app\adminapi\controller\Register@index');
    Route::put('send_captcha', 'addon\saler_tools\app\adminapi\controller\Register@sendCaptcha');
    Route::post('login', 'addon\saler_tools\app\adminapi\controller\Register@login');
});

// 用户找回密码
Route::group('saler_tools/user_forget', function () {
    Route::get('captcha', 'addon\saler_tools\app\adminapi\controller\UserForget@captcha');
    Route::put('send_code', 'addon\saler_tools\app\adminapi\controller\UserForget@sendCode');
    Route::post('reset', 'addon\saler_tools\app\adminapi\controller\UserForget@resetPassword');
});

// 鉴定师
Route::group('saler_tools/identify_user', function () {

    Route::get('check', 'addon\saler_tools\app\adminapi\controller\identify\IdentifyUser@check');
    Route::post('apply', 'addon\saler_tools\app\adminapi\controller\identify\IdentifyUser@apply');
    Route::get('goods_lists', 'addon\saler_tools\app\adminapi\controller\identify\IdentifyUser@goodsLists');
    Route::get('goods/:id', 'addon\saler_tools\app\adminapi\controller\identify\IdentifyUser@goodsDetail');
    Route::put('goods', 'addon\saler_tools\app\adminapi\controller\identify\IdentifyUser@editIdentify');

})->middleware(AdminCheckToken::class);

Route::get('saler_tools/agreement/:key', 'addon\saler_tools\app\adminapi\controller\sys\Agreement@index')->pattern(['key' => '[a-zA-Z-_]+']);


Route::get('saler_tools/check_update', 'addon\saler_tools\app\adminapi\controller\Index@checkUpdate');
Route::get('saler_tools/app_config', 'addon\saler_tools\app\adminapi\controller\Index@getAppConfig');
Route::get('saler_tools/country_list', 'addon\saler_tools\app\adminapi\controller\Index@getCountryList');


// 获取系统支持的货币类型
Route::get('saler_tools/currency/type', 'addon\saler_tools\app\adminapi\controller\Index@currencyList');

// 获取分类配置
Route::get('saler_tools/category_config', 'addon\saler_tools\app\adminapi\controller\Index@getCategoryAndTemplate');


/**
 * 平台支付相关
 */
Route::group('payment', function () {

    /** 获取支付参数支付 */
    Route::get('pay', 'addon\saler_tools\app\adminapi\controller\pay\Payment@info');
    Route::post('pay', 'addon\saler_tools\app\adminapi\controller\pay\Payment@pay');
    /** 获取支付类型 */
    Route::post('type', 'addon\saler_tools\app\adminapi\controller\pay\Payment@type');

})->middleware([
    AdminCheckToken::class,
    AdminCheckRole::class,
    AdminLog::class
]);
