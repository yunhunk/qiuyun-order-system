<?php
return [
    //别名
    'alias'    => 'shangzhan',
    //类型代号
    'type'     => 22,
    //平台名称
    'name'     => '商战网',

    //账号配置 不能删除该数据
    'username' => [
        //是否显示
        'show' => true,
        //表单标题
        'text' => '商家编号',
        //提示语
        'tips' => '',
    ],
    //密码，密钥配置 不能删除该数据
    'password' => [
        'show' => true,
        'text' => '接口密钥',
        'tips' => '',
    ],
    //支付密码，密钥等配置 不能删除该数据
    'paypwd'   => [
        'show' => true,
        'text' => '支付密码',
        'tips' => '默认123456',
    ],
    //网站类型被选中提示
    'tips'     => '对接Key请联系该平台站长或客服获得，平台地址：http://www.qqkami.com/',
    //是否支持订单同步
    'cron'     => true,
];
