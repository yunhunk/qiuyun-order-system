<?php
return [
    //别名
    'alias'    => 'Kayixinnew',
    //类型代号
    'type'     => 30,
    //平台名称
    'name'     => '卡易信新版',

    //账号配置 不能删除该数据
    'username' => [
        //是否显示
        'show' => true,
        //表单标题
        'text' => '客户编号',
        //提示语
        'tips' => '',
    ],
    //密码，密钥配置 不能删除该数据
    'password' => [
        'show' => true,
        'text' => '接口密钥(token)',
        'tips' => '',
    ],
    //支付密码，密钥等配置 不能删除该数据
    'paypwd'   => [
        'show' => false,
        'text' => '',
        'tips' => '',
    ],
    //附加选项
    'options'   => [
        // 获取一级分类
        'getclass' => true,
        // 获取二级分类
        'getclass' => false,
    ],
    //网站类型被选中提示
    'tips'     => '支持同步商品价格、订单状态、商品基本信息，接口密钥(token)请前往网站前台登录服获得',
    //是否支持订单同步
    'cron'     => true,
];
