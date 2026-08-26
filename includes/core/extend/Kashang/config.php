<?php
return [
    //别名
    'alias'    => 'kashang',
    //类型代号
    'type'     => 9,
    //平台名称
    'name'     => '卡商网',

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
        'show' => false,
        'text' => '',
        'tips' => '',
    ],
    //网站类型被选中提示
    'tips'     => '接口地址填：http://www.kashangwl.com/（如地址失效请自行联系卡商网客服更换）。 支持对接直冲、卡密、租号商品',
    //是否支持订单同步
    'cron'     => true,
];
