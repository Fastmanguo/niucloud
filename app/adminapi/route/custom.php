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
    
    Route::group('saler_tools/user', function () {
        // 更新头像
            Route::post('head_img', 'addon\saler_tools\app\adminapi\controller\User@updateHeadImg');
        
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
    Route::get('get_images', 'addon\saler_tools\app\adminapi\controller\sys\Diy@getImages');

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
    Route::post('mobile_register', 'addon\saler_tools\app\adminapi\controller\Register@mobileRegister');
    Route::put('send_captcha', 'addon\saler_tools\app\adminapi\controller\Register@sendCaptcha');
    Route::post('login', 'addon\saler_tools\app\adminapi\controller\Register@login');
});

// 用户找回密码
Route::group('saler_tools/user_forget', function () {
    Route::get('captcha', 'addon\saler_tools\app\adminapi\controller\UserForget@captcha');
    Route::put('send_code', 'addon\saler_tools\app\adminapi\controller\UserForget@sendCode');
    Route::post('reset', 'addon\saler_tools\app\adminapi\controller\UserForget@resetPassword');
    Route::post('verify_email', 'addon\saler_tools\app\adminapi\controller\UserForget@verifyEmail');
    Route::post('real_name_edit', 'addon\saler_tools\app\adminapi\controller\UserForget@realNameEdit');
});

// 收货地址
Route::group('saler_tools/user_address', function () {
    Route::post('select_region', 'addon\saler_tools\app\adminapi\controller\UserAddress@selectRegion');
    Route::post('add', 'addon\saler_tools\app\adminapi\controller\UserAddress@add');
    Route::post('del', 'addon\saler_tools\app\adminapi\controller\UserAddress@del');
    Route::post('find', 'addon\saler_tools\app\adminapi\controller\UserAddress@find');
    Route::post('edit', 'addon\saler_tools\app\adminapi\controller\UserAddress@edit');
    Route::post('list', 'addon\saler_tools\app\adminapi\controller\UserAddress@list');
    Route::post('recognize_text', 'addon\saler_tools\app\adminapi\controller\UserAddress@recognizeText');
    
}); 

// 客户管理
Route::group('saler_tools/customer', function () {
    Route::post('payment_recognize_text', 'addon\saler_tools\app\adminapi\controller\Customer@paymentRecognizeText');
    Route::post('add_payment', 'addon\saler_tools\app\adminapi\controller\Customer@addPayment');
    Route::post('payment_list', 'addon\saler_tools\app\adminapi\controller\Customer@paymentList');
    Route::post('payment_del', 'addon\saler_tools\app\adminapi\controller\Customer@paymentDel');
    Route::post('payment_find', 'addon\saler_tools\app\adminapi\controller\Customer@paymentFind');
    Route::post('payment_edit', 'addon\saler_tools\app\adminapi\controller\Customer@paymentEdit');
    Route::post('address_recognize_text', 'addon\saler_tools\app\adminapi\controller\Customer@addressRecognizeText');
    Route::post('receipt_add', 'addon\saler_tools\app\adminapi\controller\Customer@receiptAdd');
    Route::post('receipt_list', 'addon\saler_tools\app\adminapi\controller\Customer@receiptList');
    Route::post('receipt_del', 'addon\saler_tools\app\adminapi\controller\Customer@receiptDel');
    Route::post('receipt_find', 'addon\saler_tools\app\adminapi\controller\Customer@receiptFind');
    Route::post('receipt_edit', 'addon\saler_tools\app\adminapi\controller\Customer@receiptEdit');
    Route::post('customer_add', 'addon\saler_tools\app\adminapi\controller\Customer@customerAdd');
    Route::post('customer_edit', 'addon\saler_tools\app\adminapi\controller\Customer@customerEdit');
    Route::post('customer_find', 'addon\saler_tools\app\adminapi\controller\Customer@customerFind');
    Route::post('customer_list', 'addon\saler_tools\app\adminapi\controller\Customer@customerList');
    Route::post('customer_del', 'addon\saler_tools\app\adminapi\controller\Customer@customerDel');
    Route::post('customer_details', 'addon\saler_tools\app\adminapi\controller\Customer@customerDetails');
    Route::post('get_code', 'addon\saler_tools\app\adminapi\controller\Customer@getCode');
    Route::post('customer_tj', 'addon\saler_tools\app\adminapi\controller\Customer@customerTj');
    Route::post('logistics_find', 'addon\saler_tools\app\adminapi\controller\Customer@logisticsFind');
    Route::post('logistics_track', 'addon\saler_tools\app\adminapi\controller\Customer@logisticsTrack');
    Route::post('to_price', 'addon\saler_tools\app\adminapi\controller\Goods@toPrice');
    Route::post('to_price_details', 'addon\saler_tools\app\adminapi\controller\Goods@toPriceDetails');
    Route::post('getck_type_price', 'addon\saler_tools\app\adminapi\controller\Goods@getCkTypePrice');
    Route::post('complaint_add', 'addon\saler_tools\app\adminapi\controller\Customer@ComplaintAdd');
    Route::post('feed_back', 'addon\saler_tools\app\adminapi\controller\Customer@FeedBack');
    Route::post('goods_cl', 'addon\saler_tools\app\adminapi\controller\Goods@goodsCl');
    Route::post('generate_qr_code', 'addon\saler_tools\app\adminapi\controller\Customer@generateQrCode');

        
});

// 定金找货
Route::group('saler_tools/sourcing', function () {
    Route::post('sourcing_add', 'addon\saler_tools\app\adminapi\controller\Sourcing@sourcingAdd');
    Route::post('sourcing_lists', 'addon\saler_tools\app\adminapi\controller\Sourcing@sourcingLists');
    Route::post('sourcing_details', 'addon\saler_tools\app\adminapi\controller\Sourcing@sourcingDetails');
    Route::post('sourcing_goods_add', 'addon\saler_tools\app\adminapi\controller\Sourcing@sourcingGoodsAdd');
    Route::post('sourcing_end', 'addon\saler_tools\app\adminapi\controller\Sourcing@sourcingEnd');
    Route::post('sourcing_again', 'addon\saler_tools\app\adminapi\controller\Sourcing@sourcingAgain');
    Route::post('sourcing_del', 'addon\saler_tools\app\adminapi\controller\Sourcing@sourcingDel');
    Route::post('sourcing_edit', 'addon\saler_tools\app\adminapi\controller\Sourcing@sourcingEdit');
    Route::post('sourcing_billing', 'addon\saler_tools\app\adminapi\controller\Sourcing@sourcingBilling');
    Route::post('sourcing_count', 'addon\saler_tools\app\adminapi\controller\Sourcing@sourcingCount');
});

//锁单
Route::group('saler_tools/lock_order', function () {
    Route::post('lock', 'addon\saler_tools\app\adminapi\controller\Order@lock');
    Route::post('lock_edit', 'addon\saler_tools\app\adminapi\controller\Order@lockEdit');
    Route::post('lock_cancel', 'addon\saler_tools\app\adminapi\controller\Order@lockCancel');
    Route::post('lock_statistics', 'addon\saler_tools\app\adminapi\controller\Order@lockStatistics');
    Route::post('lock_proportion', 'addon\saler_tools\app\adminapi\controller\Order@lockProportion');
    Route::post('get_person_info', 'addon\saler_tools\app\adminapi\controller\Order@getPersonInfo');
});

//记账
Route::group('saler_tools/bookkeeping', function () {
    Route::post('add_type', 'addon\saler_tools\app\adminapi\controller\Order@addType');
    Route::post('del_type', 'addon\saler_tools\app\adminapi\controller\Order@delType');
    Route::post('type_list', 'addon\saler_tools\app\adminapi\controller\Order@typeList');
    Route::post('add', 'addon\saler_tools\app\adminapi\controller\UserBookkeeping@add');
    Route::post('list', 'addon\saler_tools\app\adminapi\controller\UserBookkeeping@list');
    Route::post('details', 'addon\saler_tools\app\adminapi\controller\UserBookkeeping@details');
});



//登录后未注册的用户用到的接口
Route::group('saler_tools/get_all_goods', function () {
    Route::post('goods_list', 'addon\saler_tools\app\adminapi\controller\UserAddress@goodsList');
    Route::post('brand_list', 'addon\saler_tools\app\adminapi\controller\UserAddress@brandList');
    Route::post('goods_details', 'addon\saler_tools\app\adminapi\controller\UserAddress@goodsDetails');
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

/**
 * 短信验证码相关
 */
Route::group('sms', function () {
    Route::put('sendIphoneSms', 'app\adminapi\controller\SmsController@sendIphoneSms');
    Route::post('verify', 'app\adminapi\controller\SmsController@verifySms');
});

