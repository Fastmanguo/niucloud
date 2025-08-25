<?php
// +----------------------------------------------------------------------
// | 门店管理saas
// +----------------------------------------------------------------------
// | Author  : 琦森 admin@musp.cn
// | DateTime: 2025/2/11 15:45
// +----------------------------------------------------------------------
return [
    [
        'name'   => '微信小程序',
        'key'    => 'weapp',
        'fields' => [
            [
                'key'   => 'wx_id',
                'label' => '原始id',
                'type'  => 'text'
            ],
            [
                'key'   => 'appid',
                'label' => 'AppID',
                'type'  => 'text'
            ],
            [
                'key'   => 'secret',
                'label' => 'secret',
                'type'  => 'text'
            ],
            [
                'key'   => 'upload_key',
                'label' => '上传密钥',
                'type'  => 'file'
            ],
        ]
    ],
    [
        'name'   => '支付宝支付',
        'key'    => 'alipay',
        'fields' => [
            [
                'key'   => 'app_id',
                'label' => '支付宝AppId',
                'type'  => 'text'
            ],
            [
                'key'   => 'app_secret_cert',
                'label' => '应用私钥',
                'type'  => 'text'
            ],
            [
                'key'   => 'app_public_cert_path',
                'label' => '应用公钥证书',
                'type'  => 'file'
            ],
            [
                'key'   => 'alipay_public_cert_path',
                'label' => '支付宝公钥证书',
                'type'  => 'file'
            ],
            [
                'key'   => 'alipay_root_cert_path',
                'label' => '支付宝根证书',
                'type'  => 'file'
            ]
        ]
    ],
    [
        'name'   => '微信支付',
        'key'    => 'wechat_pay',
        'fields' => [
            [
                'key'   => 'appid',
                'label' => 'APPID',
                'type'  => 'text'
            ],[
                'key'   => 'mch_id',
                'label' => '商户号',
                'type'  => 'text'
            ],
            [
                'key'   => 'mch_secret_key',
                'label' => 'V3商户秘钥',
                'type'  => 'text'
            ],
            [
                'key'   => 'mch_secret_key_v2',
                'label' => 'V2商户秘钥',
                'type'  => 'text'
            ],
            [
                'key'   => 'mch_secret_cert',
                'label' => 'private_key证书',
                'type'  => 'file'
            ],
            [
                'key'   => 'mch_public_cert_path',
                'label' => 'certificate证书',
                'type'  => 'file'
            ]
        ]
    ],
    [
        'name'   => '微信公众号',
        'key'    => 'wechat',
        'fields' => [
            [
                'key'   => 'appid',
                'label' => 'AppID'
            ],
            [
                'key'   => 'secret',
                'label' => 'secret'
            ],
        ]
    ],
    [
        'name'   => '谷歌',
        'key'    => 'google',
        'fields' => [

        ]
    ],
    [
        'name'   => 'Facebook',
        'key'    => 'facebook',
        'fields' => [

        ]
    ],
    [
        'name'   => 'X',
        'key'    => 'x',
        'fields' => [

        ]
    ],
];
