<?php
return [
    //别名
    'alias'    => 'zhike',
    //类型代号
    'type'     => 25,
    //平台名称
    'name'     => '直客SUP',

    //账号配置 不能删除该数据
    'username' => [
        //是否显示
        'show' => true,
        //表单标题
        'text' => 'AppId',
        //提示语
        'tips' => '',
    ],
    //密码，密钥配置 不能删除该数据
    'password' => [
        'show' => true,
        'text' => 'AppSecret',
        'tips' => '',
    ],
    //支付密码，密钥等配置 不能删除该数据
    'paypwd'   => [
        'show' => false,
        'text' => '',
        'tips' => '',
    ],
    //网站类型被选中提示
    'tips'     => 'AppId和AppSecret登陆直客平台后可获取',
    //是否支持订单同步
    'cron'     => true,
];
