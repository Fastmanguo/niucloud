<?php
// +----------------------------------------------------------------------
// | Niucloud-admin 企业快速开发的多应用管理平台
// +----------------------------------------------------------------------
// | 官方网址：https://www.niucloud.com
// +----------------------------------------------------------------------
// | niucloud团队 版权所有 开源版本可自由商用
// +----------------------------------------------------------------------
// | Author: Niucloud Team
// +----------------------------------------------------------------------

use think\facade\Route;

use app\adminapi\middleware\AdminCheckRole;
use app\adminapi\middleware\AdminCheckToken;
use app\adminapi\middleware\AdminLog;

/**
 * 线上展会
 */
Route::group('online_expo', function () {

    Route::group('goods', function () {
        Route::get('lists', 'addon\online_expo\app\adminapi\controller\Goods@lists');
        Route::get('detail/:goods_id', 'addon\online_expo\app\adminapi\controller\Goods@detail');
    });

    Route::group('goods_enquiry', function () {
        Route::get('lists', 'addon\online_expo\app\adminapi\controller\GoodsEnquiry@lists');
        Route::put('post_bid', 'addon\online_expo\app\adminapi\controller\GoodsEnquiry@postBid');
        Route::put('re_bid', 'addon\online_expo\app\adminapi\controller\GoodsEnquiry@reBid');
    })->middleware(AdminLog::class);

    Route::group('contact', function () {
        Route::put('shop', 'addon\online_expo\app\adminapi\controller\Contact@shop');
        Route::put('site_list_friend', 'addon\online_expo\app\adminapi\controller\Contact@siteListByUid');
        Route::post('im', 'addon\online_expo\app\adminapi\controller\Contact@im');
    });

    Route::group('shop_peer', function () {
        Route::get('info/:site_id', 'addon\online_expo\app\adminapi\controller\ShopPeer@info');
    });

    Route::get('stat/lists', 'addon\online_expo\app\adminapi\controller\Stat@lists');
    Route::get('stat/:type', 'addon\online_expo\app\adminapi\controller\Stat@info');

    // 浏览记录
    Route::get('record/lists', 'addon\online_expo\app\adminapi\controller\Record@lists');

})->middleware([
    AdminCheckToken::class,
    AdminCheckRole::class,
]);
