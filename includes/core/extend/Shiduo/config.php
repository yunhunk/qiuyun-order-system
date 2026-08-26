<?php
return [
    //别名
    'alias'    => 'shiduo',
    //类型代号
    'type'     => 16,
    //平台名称
    'name'     => '视多系统',

    //账号配置 不能删除该数据
    'username' => [
        //是否显示
        'show' => true,
        //表单标题
        'text' => 'APPID',
        //提示语
        'tips' => '',
    ],
    //密码，密钥配置 不能删除该数据
    'password' => [
        'show' => true,
        'text' => 'APPKEY',
        'tips' => '',
    ],
    //支付密码，密钥等配置 不能删除该数据
    'paypwd'   => [
        'show' => false,
        'text' => '',
        'tips' => '',
    ],
    //网站类型被选中提示
    'tips'     => '只支持对接卡密，对接成功后卡密内容在处理信息里面',
    //是否支持订单同步
    'cron'     => true,
];
