<?php
return [
    //别名
    'alias'    => 'jiuwu',
    //类型代号
    'type'     => 0,
    //平台名称
    'name'     => '玖伍系统',

    //账号配置 不能删除该数据
    'username' => [
        //是否显示
        'show' => true,
        //表单标题
        'text' => '登录账号',
        //提示语
        'tips' => '',
    ],
    //密码，密钥配置 不能删除该数据
    'password' => [
        'show' => true,
        'text' => '登录密码',
        'tips' => '',
    ],
    //支付密码，密钥等配置 不能删除该数据
    'paypwd'   => [
        'show' => false,
        'text' => '',
        'tips' => '',
    ],
    //网站类型被选中提示
    'tips'     => '',
    //是否支持订单同步
    'cron'     => true,
];
