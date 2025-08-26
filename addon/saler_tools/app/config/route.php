<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2024/11/25 2:47
// +----------------------------------------------------------------------


// 注入根路由

use addon\saler_tools\app\api\middleware\AllowCrossDomain;
use addon\saler_tools\app\api\middleware\ApiMiddleware;
use think\facade\Route;

Route::group('adminapi', function () {

    Route::group('config', function () {
        Route::get('register', 'addon\saler_tools\app\adminapi\controller\Index@registerConfig');
    });
});


Route::rule('tools', function () {
    return view(app()->getRootPath() . 'public/tools/index.html');
})->pattern(['any' => '\w+']);


Route::rule('h5', 'addon\saler_tools\app\api\controller\Index@render')->pattern(['any' => '\.+']);
Route::rule('link', 'addon\saler_tools\app\api\controller\Index@link')->pattern(['any' => '\.+']);

Route::get('ulink/', function () {
    // 展示下载页面
    return 'ok';
});

Route::get('apple-app-site-association', function () {
    return json([
        "applinks" => [
            "apps"    => [],
            "details" => [
                [
                    "appID" => "5EAWK6C5RX.com.84000lookingfor",
                    "paths" => [
                        "/ulink/*"
                    ]
                ]
            ]
        ]
    ]);
})->pattern(['any' => '\w+']);



Route::group('shop', function () {

    Route::get('init', 'addon\saler_tools\app\api\controller\Index@index');
    Route::get('lang/:key', 'addon\saler_tools\app\api\controller\Index@lang')->pattern(['key' => '[a-zA-Z-_]+']);

    Route::group('goods', function () {
        Route::get('lists', 'addon\saler_tools\app\api\controller\Goods@lists');
        Route::get('detail/:goods_id', 'addon\saler_tools\app\api\controller\Goods@detail');
    });

})->middleware([AllowCrossDomain::class, ApiMiddleware::class]);

