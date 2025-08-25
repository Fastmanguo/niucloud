<?php

return [
    [
        'key'      => 'online_expo_stat',
        'name'     => '线上展会统计',
        'desc'     => '',
        'time'     => [
            'type' => 'day',
            'day'  => 1,
            'hour' => 0,
            'min'  => 0
        ],
        'class'    => 'addon\online_expo\app\job\CronStatJob',
        'function' => ''
    ]
];
