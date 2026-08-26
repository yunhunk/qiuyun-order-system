<?php

return [
    [
        'label'   => '邮件编码',
        'field'   => 'charset',
        'type'    => 'select',
        'options' => [ //select 和radio,checkbox的子选项
            [
                'label' => 'utf-8(推荐)',
                'value' => 'utf-8',
            ],
            [
                'label' => '8bit',
                'value' => '8bit',
            ],
            [
                'label' => '7bit',
                'value' => '7bit',
            ],
            [
                'label' => 'binary',
                'value' => 'binary',
            ],
            [
                'label' => 'base64',
                'value' => 'base64',
            ],
            [
                'label' => 'quoted-printable',
                'value' => 'quoted-printable',
            ],
        ],
        'value'   => 'utf-8',
        'tips'    => '<span style="color:red"><b>SMTP邮箱使用注意</b>:如出现不能使用建议使用阿里云邮箱</span><br/><b>SMTP邮箱使用教程</b>: <a href="https://doc.apipost.net/docs/detail/2b71787dc064000?target_id=4701bda" target="_blank">点我查看教程</a>',
    ],
    [
        'label' => 'SMTP 端口',
        'field' => 'port',
        'type'  => 'string',
        'value' => '465',
        'tips'  => '协议类型是ssl时通常填465、是tls时通常填587',
    ],
    [
        'label' => 'SMTP 主机名',
        'type'  => 'string',
        'field' => 'host',
        'value' => 'smtp.qq.com',
        'tips'  => 'QQ邮箱: smtp.qq.com 网易: smtp.163.com 搜狐: smtp.souhu.com 新浪: smtp.sina.com ',
    ],
    [
        'label'   => 'SMTP 用户名',
        'type'    => 'string',
        'field'   => 'username',
        'encrypt' => 1,
        'value'   => '',
        'tips'    => '',
    ],
    [
        'label'   => ' SMTP 密码',
        'type'    => 'string',
        'field'   => 'password',
        'encrypt' => 1,
        'value'   => '',
        'tips'    => '注意, 不是邮箱密码哦! 具体可以看上方的入口教程',
    ],
    [
        'label'   => 'SMTP SSL类型',
        'type'    => 'select',
        'field'   => 'smtpsecure',
        'options' => [ //select 和radio,checkbox的子选项
            [
                'label' => 'tls(推荐)',
                'value' => 'tls',
            ],
            [
                'label' => 'ssl',
                'value' => 'ssl',
            ],
        ],
        'value'   => 'tls',
        'tips'    => '推荐先使用tls协议, 如果发送不了再用ssl尝试',
    ],
    [
        'label' => '系统发送人名称',
        'field' => 'fromname',
        'type'  => 'string',
        'value' => '星河云',
        'tips'  => '',
    ],
    [
        'label' => '系统发送邮箱',
        'type'  => 'string',
        'field' => 'systememail',
        'value' => '',
        'tips'  => '',
    ],
];
