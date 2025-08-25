<?php
// 全局中间件定义文件
use app\adminapi\middleware\AllowCrossDomain;
use think\middleware\LoadLangPack;


$middleware_config = [
    //跨域请求中间件
    AllowCrossDomain::class,
    //语言中间件
    LoadLangPack::class,
];

$addon_dir = root_path() . 'addon';
$addons = array_diff(scandir($addon_dir), ['.', '..']);
foreach ($addons as $addon) {
    $route = $addon_dir . DIRECTORY_SEPARATOR . $addon . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'adminapi' . DIRECTORY_SEPARATOR . 'middleware.php';
    if (file_exists($route)) {
        $middleware_config = array_merge($middleware_config, include $route);
    }
}

return $middleware_config;
