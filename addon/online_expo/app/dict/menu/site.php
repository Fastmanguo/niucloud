<?php

return  [
    [
        'menu_name' => '线上展会',
        'menu_key' => 'online_expo',
        'menu_type' => 0,
        'icon' => '',
        'api_url' => '',
        'router_path' => '',
        'view_path' => '',
        'methods' => '',
        'sort' => 100,
        'status' => 1,
        'is_show' => 1,
        'children' => [
            [
                'menu_name' => '线上展会',
                'menu_key' => 'online_expo_hello_world',
                'menu_type' => 1,
                'icon' => '',
                'api_url' => 'online_expo/hello_world',
                'router_path' => 'online_expo/hello_world',
                'view_path' => 'hello_world/index',
                'methods' => 'get',
                'sort' => 100,
                'status' => 1,
                'is_show' => 1,
                'children' => []
            ],
        ]
    ]
];
