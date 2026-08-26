<?php

return [
    [
        'label'   => '阿里云accessKeyId',
        'field'   => 'accessKeyId',
        'type'    => 'string',
        'value'   => '',
        'encrypt' => 1,
        'tips'    => '数据已隐藏，如需修改请重新填写!<br/><b>阿里邮件推送控制台:</b><a href="https://dm.console.aliyun.com/" target="_blank">https://dm.console.aliyun.com/</a><br/><b>阿里accessKeyId获取地址:</b><a href="https://ram.console.aliyun.com/manage/ak" target="_blank">https://ram.console.aliyun.com/manage/ak</a>',
    ],
    [
        'label'   => '阿里云accessKeySecret',
        'type'    => 'string',
        'field'   => 'accessKeySecret',
        'value'   => '',
        'encrypt' => 1,
        'tips'    => '为避免泄露数据已隐藏，如需修改请重新填写',
    ],
    [
        'label'   => '系统发送人名称',
        'field'   => 'fromname',
        'type'    => 'string',
        'value'   => '星河云',
        'encrypt' => 0,
        'tips'    => '',
    ],
    [
        'label'   => '阿里发信邮箱',
        'type'    => 'string',
        'field'   => 'systememail',
        'value'   => '',
        'encrypt' => 1,
        'tips'    => '为避免泄露数据已隐藏，如需修改请重新填写',
    ],
    [
        'label'   => '邮件编码',
        'field'   => 'charset',
        'type'    => 'select',
        'encrypt' => 0,
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
        'tips'    => '',
    ],
];
