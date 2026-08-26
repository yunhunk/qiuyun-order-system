<?php

return [
    [
        'label' => '账号',
        'field' => 'user',
        'type'  => 'string',
        'tips'  => '短信宝登录账号，注册地址：<a href="http://smsbao.com/" target="_blank">http://smsbao.com/</a>',
    ],
    [
        'label'   => '密码',
        'field'   => 'pass',
        'type'    => 'string',
        'tips'    => '短信宝登录密码',
        'encrypt' => 1,
    ],
    [
        'label' => '短信签名',
        'field' => 'sign',
        'type'  => 'string',
        'tips'  => '一版都是公司名称简写，如：星河科技',
    ],
];
