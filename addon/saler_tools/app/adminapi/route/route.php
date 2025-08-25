<?php


use think\facade\Route;

use app\adminapi\middleware\AdminCheckRole;
use app\adminapi\middleware\AdminCheckToken;
use app\adminapi\middleware\AdminLog;

Route::group('saler_tools/admin', function () {

    Route::group('category', function () {
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\admin\Category@lists');
        Route::post('add', 'addon\saler_tools\app\adminapi\controller\admin\Category@add');
        Route::put('edit', 'addon\saler_tools\app\adminapi\controller\admin\Category@edit');
        Route::post('del', 'addon\saler_tools\app\adminapi\controller\admin\Category@del');
        Route::put('transfer', 'addon\saler_tools\app\adminapi\controller\admin\Category@transfer');


    });

    Route::group('template', function () {
        Route::get('detail', 'addon\saler_tools\app\adminapi\controller\admin\Template@detail');
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\admin\Template@lists');
        Route::get('attr', 'addon\saler_tools\app\adminapi\controller\admin\Template@templateAttr');
        Route::post('add', 'addon\saler_tools\app\adminapi\controller\admin\Template@add');
        Route::put('edit', 'addon\saler_tools\app\adminapi\controller\admin\Template@edit');
        Route::post('del', 'addon\saler_tools\app\adminapi\controller\admin\Template@del');
    });


    Route::group('brand', function () {
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\admin\Brand@lists');
        Route::post('add', 'addon\saler_tools\app\adminapi\controller\admin\Brand@add');
        Route::put('edit', 'addon\saler_tools\app\adminapi\controller\admin\Brand@edit');
        Route::post('del', 'addon\saler_tools\app\adminapi\controller\admin\Brand@del');
    });

    Route::group('model', function () {
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\admin\Model@lists');
        Route::post('add', 'addon\saler_tools\app\adminapi\controller\admin\Model@add');
        Route::put('edit', 'addon\saler_tools\app\adminapi\controller\admin\Model@edit');
        Route::post('del', 'addon\saler_tools\app\adminapi\controller\admin\Model@del');
    });


    Route::group('series', function () {
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\admin\Series@lists');
        Route::post('add', 'addon\saler_tools\app\adminapi\controller\admin\Series@add');
        Route::put('edit', 'addon\saler_tools\app\adminapi\controller\admin\Series@edit');
        Route::post('del', 'addon\saler_tools\app\adminapi\controller\admin\Series@del');
    });


    Route::group('language', function () {
        Route::get('list', 'addon\saler_tools\app\adminapi\controller\admin\Language@list');
        Route::get('package_lists', 'addon\saler_tools\app\adminapi\controller\admin\Language@packageLists');
        Route::get('build_uniapp_lag', 'addon\saler_tools\app\adminapi\controller\admin\Language@buildUniappLanguage');
        Route::get('build_lag/:key', 'addon\saler_tools\app\adminapi\controller\admin\Language@buildLanguage')->pattern(['key' => '[a-zA-Z-_]+']);
        Route::put('package', 'addon\saler_tools\app\adminapi\controller\admin\Language@editPackage');
        Route::put('check_package', 'addon\saler_tools\app\adminapi\controller\admin\Language@checkPackage');
        Route::post('import_data', 'addon\saler_tools\app\adminapi\controller\admin\Language@importData');

        Route::delete('clear_language/:key', 'addon\saler_tools\app\adminapi\controller\admin\Language@clearTranslate')->pattern(['key' => '[a-zA-Z-_]+']);
        Route::put('translate/:key', 'addon\saler_tools\app\adminapi\controller\admin\Language@translate')->pattern(['key' => '[a-zA-Z-_]+']);

        /** 任务相关 */
        Route::get('task_progress', 'addon\saler_tools\app\adminapi\controller\admin\Language@taskProgress');
        Route::put('task_complete', 'addon\saler_tools\app\adminapi\controller\admin\Language@taskComplete');

        Route::put('flush_version', 'addon\saler_tools\app\adminapi\controller\admin\Language@flushVersion');
    });


    Route::group('exchange_rate', function () {
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\admin\ExchangeRate@lists');
        Route::put('flush', 'addon\saler_tools\app\adminapi\controller\admin\ExchangeRate@flush');

        Route::get('type_list', 'addon\saler_tools\app\adminapi\controller\admin\ExchangeRate@typeList');
        Route::post('type_modify', 'addon\saler_tools\app\adminapi\controller\admin\ExchangeRate@typeModify');
    });

    Route::group('diy', function () {
        // 自定义页面分页列表
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\admin\Diy@lists');

        // 自定义页面分页列表，轮播搜索组件用
        Route::get('carousel_search', 'addon\saler_tools\app\adminapi\controller\admin\Diy@getPageByCarouselSearch');

        // 添加自定义页面
        Route::post('diy', 'addon\saler_tools\app\adminapi\controller\admin\Diy@add');

        // 编辑自定义页面
        Route::put('diy/:id', 'addon\saler_tools\app\adminapi\controller\admin\Diy@edit');

        // 自定义页面详情
        Route::get('diy/:id', 'addon\saler_tools\app\adminapi\controller\admin\Diy@info');

        // 删除自定义页面
        Route::delete('diy/:id', 'addon\saler_tools\app\adminapi\controller\admin\Diy@del');

        Route::get('list', 'addon\saler_tools\app\adminapi\controller\admin\Diy@getList');

        // 页面装修列表
        Route::get('decorate', 'addon\saler_tools\app\adminapi\controller\admin\Diy@getDecoratePage');

        // 切换模板
        Route::put('change', 'addon\saler_tools\app\adminapi\controller\admin\Diy@changeTemplate');
        Route::put('lang', 'addon\saler_tools\app\adminapi\controller\admin\Diy@lang');

        // 页面初始化数据
        Route::get('init', 'addon\saler_tools\app\adminapi\controller\admin\Diy@getPageInit');

        // 获取自定义链接列表
        Route::get('link', 'addon\saler_tools\app\adminapi\controller\admin\Diy@getLink');

        // 设为使用
        Route::put('use/:id', 'addon\saler_tools\app\adminapi\controller\admin\Diy@setUse');

        // 获取页面模板
        Route::get('template', 'addon\saler_tools\app\adminapi\controller\admin\Diy@getTemplate');

        // 获取模板页面列表
        Route::get('template/pages', 'addon\saler_tools\app\adminapi\controller\admin\Diy@getTemplatePages');

        // 自定义路由列表
        Route::get('route', 'diy.DiyRoute/lists');

        // 获取路由列表（存在的应用插件列表）
        Route::get('route/apps', 'diy.DiyRoute/getApps');

        // 获取自定义路由分享内容
        Route::get('route/info', 'diy.DiyRoute/getInfoByName');

        // 编辑自定义路由分享内容
        Route::put('route/share', 'diy.DiyRoute/modifyShare');

        // 编辑自定义页面分享内容
        Route::put('diy/share', 'addon\saler_tools\app\adminapi\controller\admin\Diy@modifyShare');

        // 获取模板页面（存在的应用插件列表）
        Route::get('apps', 'addon\saler_tools\app\adminapi\controller\admin\Diy@getApps');
    });


    Route::group('agreement', function () {
        Route::get('allow', 'addon\saler_tools\app\adminapi\controller\admin\Agreement@allowList');
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\admin\Agreement@lists');
        Route::post('add', 'addon\saler_tools\app\adminapi\controller\admin\Agreement@add');
        Route::put('edit', 'addon\saler_tools\app\adminapi\controller\admin\Agreement@edit');
    });

    Route::group('setting', function () {
        Route::get('email', 'addon\saler_tools\app\adminapi\controller\admin\Setting@getEmail');
        Route::post('email', 'addon\saler_tools\app\adminapi\controller\admin\Setting@updateEmail');

        Route::get('app_config', 'addon\saler_tools\app\adminapi\controller\admin\Setting@getAppConfig');
        Route::post('app_config', 'addon\saler_tools\app\adminapi\controller\admin\Setting@updateAppConfig');

        Route::post('test_email', 'addon\saler_tools\app\adminapi\controller\admin\Setting@testEmail');
    });


    Route::group('notify_template', function () {
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\admin\NotifyTemplate@lists');
        Route::get('detail/:id', 'addon\saler_tools\app\adminapi\controller\admin\NotifyTemplate@detail');
        Route::post('add', 'addon\saler_tools\app\adminapi\controller\admin\NotifyTemplate@add');
        Route::put('edit', 'addon\saler_tools\app\adminapi\controller\admin\NotifyTemplate@update');
        Route::put('modify', 'addon\saler_tools\app\adminapi\controller\admin\NotifyTemplate@modify');
        Route::delete('del/:id', 'addon\saler_tools\app\adminapi\controller\admin\NotifyTemplate@del');
    });


    Route::group('channel', function () {
        Route::get('list', 'addon\saler_tools\app\adminapi\controller\admin\Channel@list');
        Route::get('detail/:key', 'addon\saler_tools\app\adminapi\controller\admin\Channel@detail');
        Route::put('update', 'addon\saler_tools\app\adminapi\controller\admin\Channel@update');
    });


    Route::group('shop', function () {
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\admin\Shop@lists');
        Route::put('edit', 'addon\saler_tools\app\adminapi\controller\admin\Shop@edit');

        Route::get('apply_config', 'addon\saler_tools\app\adminapi\controller\admin\Shop@getApplyConfig');
        Route::put('audit', 'addon\saler_tools\app\adminapi\controller\admin\Shop@audit');
        Route::put('apply_config', 'addon\saler_tools\app\adminapi\controller\admin\Shop@setApplyConfig');
    });

    Route::group('version', function () {

        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\admin\Version@lists');
        Route::get('detail/:id', 'addon\saler_tools\app\adminapi\controller\admin\Version@detail');
        Route::post('add', 'addon\saler_tools\app\adminapi\controller\admin\Version@add');
        Route::put('edit', 'addon\saler_tools\app\adminapi\controller\admin\Version@edit');
        Route::delete(':id', 'addon\saler_tools\app\adminapi\controller\admin\Version@del');
    });

    Route::group('charge', function () {
        Route::get('type', 'addon\saler_tools\app\adminapi\controller\admin\SiteCharge@type');
        Route::get('list', 'addon\saler_tools\app\adminapi\controller\admin\SiteCharge@list');
        Route::post('add', 'addon\saler_tools\app\adminapi\controller\admin\SiteCharge@add');
        Route::put('edit', 'addon\saler_tools\app\adminapi\controller\admin\SiteCharge@edit');
        Route::delete('del/:id', 'addon\saler_tools\app\adminapi\controller\admin\SiteCharge@del');
    });


    Route::group('im', function () {
        Route::get('config', 'addon\saler_tools\app\adminapi\controller\admin\ImConfig@getConfig');
        Route::post('config', 'addon\saler_tools\app\adminapi\controller\admin\ImConfig@setConfig');
    });


    // 鉴定商品
    Route::group('identify', function () {
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\admin\Identify@lists');
        Route::get('log_lists', 'addon\saler_tools\app\adminapi\controller\admin\Identify@logLists');
        Route::put('edit', 'addon\saler_tools\app\adminapi\controller\admin\Identify@edit');
    });

    // 鉴定配置
    Route::group('identify_config', function () {
        Route::get('list', 'addon\saler_tools\app\adminapi\controller\admin\IdentifyConfig@list');
        Route::post('save', 'addon\saler_tools\app\adminapi\controller\admin\IdentifyConfig@save');
    });

    // 鉴定师
    Route::group('identify_user', function () {
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\admin\IdentifyUser@lists');
        Route::put('edit', 'addon\saler_tools\app\adminapi\controller\admin\IdentifyUser@edit');
    });

    // 运营国家管理
    Route::group('country', function () {
        Route::get('list', 'addon\saler_tools\app\adminapi\controller\admin\Country@list');
        Route::post('save', 'addon\saler_tools\app\adminapi\controller\admin\Country@save');
    });


    // 公价查询
    Route::group('goods_pool', function () {
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\GoodsPool@lists');
        Route::get('detail/:id', 'addon\saler_tools\app\adminapi\controller\GoodsPool@detail');
        Route::post('save', 'addon\saler_tools\app\adminapi\controller\admin\GoodsPool@save');
        Route::delete('del', 'addon\saler_tools\app\adminapi\controller\admin\GoodsPool@del');
    });

    Route::post('safe/upload', 'addon\saler_tools\app\adminapi\controller\admin\SafeFile@upload');

})->middleware([
    AdminCheckToken::class,
    AdminCheckRole::class,
    AdminLog::class
]);


// 店铺使用API
Route::group('saler_tools', function () {

    Route::group('base', function () {
        Route::get('category', 'addon\saler_tools\app\adminapi\controller\GoodsBase@category');
        Route::get('brand', 'addon\saler_tools\app\adminapi\controller\GoodsBase@brand');
        Route::get('series', 'addon\saler_tools\app\adminapi\controller\GoodsBase@series');
        Route::get('model', 'addon\saler_tools\app\adminapi\controller\GoodsBase@model');

        Route::get('series/lists', 'addon\saler_tools\app\adminapi\controller\GoodsBase@seriesLists');
        Route::get('model/lists', 'addon\saler_tools\app\adminapi\controller\GoodsBase@modelLists');

        // 获取数据
        Route::get('data/:key', 'addon\saler_tools\app\adminapi\controller\GoodsBase@dataList')->pattern(['key' => '[a-zA-Z-_]+']);
        Route::post('data/:key', 'addon\saler_tools\app\adminapi\controller\GoodsBase@dataAdd')->pattern(['key' => '[a-zA-Z-_]+']);
        Route::put('data/:key', 'addon\saler_tools\app\adminapi\controller\GoodsBase@dataEdit')->pattern(['key' => '[a-zA-Z-_]+']);
        Route::delete('data', 'addon\saler_tools\app\adminapi\controller\GoodsBase@dataDel');
        Route::put('data_sort', 'addon\saler_tools\app\adminapi\controller\GoodsBase@dataSort');


        // 获取员工昵称
        Route::get('realname/:uid', 'addon\saler_tools\app\adminapi\controller\User@getRealName')->pattern(['uid' => '\d+']);
    });


    Route::group('goods', function () {

        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\Goods@lists');
        Route::get('position_statistics', 'addon\saler_tools\app\adminapi\controller\Goods@positionStatistics');
        Route::get('detail/:goods_id', 'addon\saler_tools\app\adminapi\controller\Goods@detail')->pattern(['uid' => '\d+']);

        /********************                  商品修改相关                       ********************/
        Route::post('add', 'addon\saler_tools\app\adminapi\controller\Goods@add');
        Route::put('edit', 'addon\saler_tools\app\adminapi\controller\Goods@edit');
        Route::put('off_sale', 'addon\saler_tools\app\adminapi\controller\Goods@offSale');
        Route::put('on_sale', 'addon\saler_tools\app\adminapi\controller\Goods@onSale');
        Route::put('edit_cost', 'addon\saler_tools\app\adminapi\controller\Goods@editCost');
        Route::put('edit_appraiser', 'addon\saler_tools\app\adminapi\controller\Goods@editAppraiser');
        Route::put('edit_watch_location', 'addon\saler_tools\app\adminapi\controller\Goods@editWatchLocation');
        Route::put('move_goods', 'addon\saler_tools\app\adminapi\controller\Goods@moveGoods');


        /********************                  删除商品                    ********************/
        Route::delete('del/:goods_id', 'addon\saler_tools\app\adminapi\controller\Goods@del');
        Route::delete('batch_del', 'addon\saler_tools\app\adminapi\controller\Goods@batchDel');


        /****************************  统计相关 *************************/
        Route::get('store_stat', 'addon\saler_tools\app\adminapi\controller\Goods@storeStatistics');
    });


    Route::group('store', function () {
        Route::get('list', 'addon\saler_tools\app\adminapi\controller\Store@list');
        Route::get('detail/:store_id', 'addon\saler_tools\app\adminapi\controller\Store@detail');
        Route::post('add', 'addon\saler_tools\app\adminapi\controller\Store@add');
        Route::put('edit', 'addon\saler_tools\app\adminapi\controller\Store@edit');
        Route::delete('del', 'addon\saler_tools\app\adminapi\controller\Store@delete');
    });


    Route::group('order', function () {
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\Order@lists');
        Route::get('add', 'addon\saler_tools\app\adminapi\controller\Order@preAdd');
        Route::get('detail/:order_id', 'addon\saler_tools\app\adminapi\controller\Order@detail');

        Route::post('add', 'addon\saler_tools\app\adminapi\controller\Order@add');
        Route::put('edit', 'addon\saler_tools\app\adminapi\controller\Order@edit');
        Route::put('un_lock', 'addon\saler_tools\app\adminapi\controller\Order@unLock');
        Route::put('lock', 'addon\saler_tools\app\adminapi\controller\Order@lock');
        Route::put('paid', 'addon\saler_tools\app\adminapi\controller\Order@paid');
        Route::put('refund', 'addon\saler_tools\app\adminapi\controller\Order@refund');
        Route::put('close', 'addon\saler_tools\app\adminapi\controller\Order@close');
        Route::put('send', 'addon\saler_tools\app\adminapi\controller\Order@send');
        // 删除订单
        Route::delete('del', 'addon\saler_tools\app\adminapi\controller\Order@deleted');
        // 统计相关
        Route::get('lock_stat', 'addon\saler_tools\app\adminapi\controller\Order@lockStat');

        // 赎回商品开单
        Route::post('ransom', 'addon\saler_tools\app\adminapi\controller\Order@ransom');
    });


    Route::group('auth', function () {

        Route::get('user/list', 'addon\saler_tools\app\adminapi\controller\Auth@list');
        Route::get('user/stat', 'addon\saler_tools\app\adminapi\controller\Auth@stat');
        Route::get('user/:uid', 'addon\saler_tools\app\adminapi\controller\Auth@userDetail');
        Route::post('user/add', 'addon\saler_tools\app\adminapi\controller\Auth@addUser');
        Route::put('user/edit', 'addon\saler_tools\app\adminapi\controller\Auth@editUser');
        Route::delete('user/del/:uid', 'addon\saler_tools\app\adminapi\controller\Auth@delUser');
        Route::put('lock/:uid', 'addon\saler_tools\app\adminapi\controller\Auth@userLock');
        Route::put('unlock/:uid', 'addon\saler_tools\app\adminapi\controller\Auth@userUnlock');

        Route::get('role', 'addon\saler_tools\app\adminapi\controller\Auth@roleList');
        Route::get('role/:role_id', 'addon\saler_tools\app\adminapi\controller\Auth@roleDetail');
        Route::post('role', 'addon\saler_tools\app\adminapi\controller\Auth@addRole');
        Route::put('role', 'addon\saler_tools\app\adminapi\controller\Auth@editRole');
        Route::delete('role', 'addon\saler_tools\app\adminapi\controller\Auth@delRole');

    });


    Route::group('shop', function () {
        Route::get('detail', 'addon\saler_tools\app\adminapi\controller\Shop@detail');
        Route::get('panel', 'addon\saler_tools\app\adminapi\controller\Shop@shopPanel');

        Route::put('edit', 'addon\saler_tools\app\adminapi\controller\Shop@edit');
        Route::put('modify', 'addon\saler_tools\app\adminapi\controller\Shop@modify');
        Route::post('verify', 'addon\saler_tools\app\adminapi\controller\Shop@verify');
    });

    Route::group('collect', function () {
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\Collect@lists');
        Route::get('check', 'addon\saler_tools\app\adminapi\controller\Collect@check');
        Route::put('modify', 'addon\saler_tools\app\adminapi\controller\Collect@modify');
    });

    Route::group('order_service', function () {
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\OrderService@lists');
        Route::get('stat', 'addon\saler_tools\app\adminapi\controller\OrderService@stat');
        Route::get('detail/:service_id', 'addon\saler_tools\app\adminapi\controller\OrderService@detail');
        Route::post('add', 'addon\saler_tools\app\adminapi\controller\OrderService@add');
        Route::put('edit', 'addon\saler_tools\app\adminapi\controller\OrderService@edit');
        Route::put('operate', 'addon\saler_tools\app\adminapi\controller\OrderService@operate');
    });


    Route::group('identify', function () {
        Route::get('config', 'addon\saler_tools\app\adminapi\controller\identify\Identify@config');
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\identify\Identify@lists');
        Route::get('detail/:id', 'addon\saler_tools\app\adminapi\controller\identify\Identify@detail');
        Route::post('add', 'addon\saler_tools\app\adminapi\controller\identify\Identify@add');
        Route::delete('del/:id', 'addon\saler_tools\app\adminapi\controller\identify\Identify@del');
    });


    Route::group('shop_share', function () {
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\ShopShare@lists');
        Route::post('create', 'addon\saler_tools\app\adminapi\controller\ShopShare@create');
        Route::delete('/:share_id', 'addon\saler_tools\app\adminapi\controller\ShopShare@del');
    });


    Route::group('shop_bill', function () {
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\ShopBill@lists');
        Route::get('detail', 'addon\saler_tools\app\adminapi\controller\ShopBill@detail');
        Route::get('record_lists', 'addon\saler_tools\app\adminapi\controller\ShopBill@recordLists');
        Route::post('create', 'addon\saler_tools\app\adminapi\controller\ShopBill@create');
        Route::get('query_stat', 'addon\saler_tools\app\adminapi\controller\ShopBill@queryStat');
        Route::delete(':bill_id', 'addon\saler_tools\app\adminapi\controller\ShopBill@del');
    });


    Route::group('inventory', function () {
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\Inventory@lists');
        Route::get('detail/:inventory_id', 'addon\saler_tools\app\adminapi\controller\Inventory@detail');
        Route::post('create', 'addon\saler_tools\app\adminapi\controller\Inventory@create');
        Route::put('save/:inventory_id', 'addon\saler_tools\app\adminapi\controller\Inventory@save');
        Route::delete('del/:inventory_id', 'addon\saler_tools\app\adminapi\controller\Inventory@del');
        Route::get('goods_lists', 'addon\saler_tools\app\adminapi\controller\Inventory@inventoryGoodsLists');
        Route::put('goods_modify', 'addon\saler_tools\app\adminapi\controller\Inventory@inventoryModifyGoods');
    });


    Route::group('address', function () {
        Route::get('list', 'addon\saler_tools\app\adminapi\controller\Address@list');
        Route::get('detail/:address_id', 'addon\saler_tools\app\adminapi\controller\Address@detail');
        Route::post('add', 'addon\saler_tools\app\adminapi\controller\Address@add');
        Route::put('edit', 'addon\saler_tools\app\adminapi\controller\Address@edit');
        Route::delete('del/:address_id', 'addon\saler_tools\app\adminapi\controller\Address@del');
    });


    Route::group('shop_contact', function () {
        Route::get('list', 'addon\saler_tools\app\adminapi\controller\ShopContact@list');
        Route::get('detail/:contact_id', 'addon\saler_tools\app\adminapi\controller\ShopContact@detail');
        Route::post('add', 'addon\saler_tools\app\adminapi\controller\ShopContact@add');
        Route::put('edit', 'addon\saler_tools\app\adminapi\controller\ShopContact@edit');
        Route::delete('del/:contact_id', 'addon\saler_tools\app\adminapi\controller\ShopContact@del');
    });

    // 获取我的统计
    Route::get('user/stat', 'addon\saler_tools\app\adminapi\controller\User@stat');

    // 公价查询
    Route::group('goods_pool', function () {
        Route::get('lists', 'addon\saler_tools\app\adminapi\controller\GoodsPool@lists');
        Route::get('detail/:id', 'addon\saler_tools\app\adminapi\controller\GoodsPool@detail');
    });

    // 报表
    Route::group('stat', function () {
        Route::get('manage', 'addon\saler_tools\app\adminapi\controller\stat\StatManage@getStat');
    });


})->middleware([
    AdminCheckToken::class,
    AdminCheckRole::class,
    AdminLog::class
]);

