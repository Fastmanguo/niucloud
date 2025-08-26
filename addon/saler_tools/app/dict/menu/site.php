<?php

return  [
    [
        'menu_name' => 'saler-tools',
        'menu_key' => 'saler_tools',
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
                'menu_name' => 'saler-tools',
                'menu_key' => 'saler_tools_hello_world',
                'menu_type' => 1,
                'icon' => '',
                'api_url' => 'saler_tools/hello_world',
                'router_path' => 'saler_tools/hello_world',
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
