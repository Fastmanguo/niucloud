<?php
return [
    'verify_code' => [
        'key' => 'verify_code',
        'receiver_type' => 0,
        'name' => '手机验证码',
        'title' => '管理端验证码登录',
        'async' => false,
        'variable' => [
            'code' => '验证码'
        ],
    ],
    //手机验证码，站点应用发送
    'member_verify_code' => [
        'key' => 'member_verify_code',
        'receiver_type' => 1,
        'name' => '手机验证码',
        'title' => '前端验证码登录，注册，手机验证',
        'async' => false,
        'variable' => [
            'code' => '验证码'
        ],
    ]
];
