<?php
return [
    [
        'name'  => '注册账号',
        'var'   => '您正在注册账号, 验证码是${code}。3分钟内有效, 请不要告诉他人',
        'type'  => 'cn',
        'event' => 'register',
    ],
    [
        'name'  => '发送验证码',
        'var'   => '您的验证码是${code}。3分钟内有效, 请不要告诉他人',
        'type'  => 'cn',
        'event' => 'default',
    ],
    [
        'name'  => '登录账号',
        'var'   => '您正在登录账号, 验证码是${code}。3分钟内有效, 请不要告诉他人',
        'type'  => 'cn',
        'event' => 'login',
    ],
    [
        'name'  => '找回密码',
        'var'   => '您正在找回密码, 验证码是${code}。3分钟内有效, 请不要告诉他人',
        'type'  => 'cn',
        'event' => 'findpwd',
    ],
    [
        'name'  => '修改密码',
        'var'   => '您正在修改密码，验证码是${code}。3分钟内有效, 请不要告诉他人',
        'type'  => 'cn',
        'event' => 'change_pwd',
    ],
    [
        'name'  => '修改资料',
        'var'   => '您正在修改资料，验证码是${code}。3分钟内有效, 请不要告诉他人',
        'type'  => 'cn',
        'event' => 'change_info',
    ],
    [
        'name'  => '换绑手机',
        'var'   => '您正在换绑手机, 验证码是${code}。3分钟内有效, 请不要告诉他人',
        'type'  => 'cn',
        'event' => 'change_mobile',
    ],
    [
        'name'  => '换绑邮箱',
        'var'   => '您正在换绑邮箱, 验证码是${code}。3分钟内有效, 请不要告诉他人',
        'type'  => 'cn',
        'event' => 'change_email',
    ],
    [
        'name'  => '异地登录',
        'var'   => '您正在异地登录, 验证码是${code}。3分钟内有效, 请不要告诉他人',
        'type'  => 'cn',
        'event' => 'ydlogin',
    ],
    [
        'name'  => '库存不足',
        'var'   => '您有商品库存不足, 请及时登录后台处理',
        'type'  => 'cn',
        'event' => 'kucun',
    ],
];
