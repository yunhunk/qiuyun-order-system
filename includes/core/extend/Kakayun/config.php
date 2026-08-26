<?php
return [
    //别名
    'alias'    => 'kakayun',
    //类型代号
    'type'     => 18,
    //平台名称
    'name'     => '卡卡云系统',

    //账号配置 不能删除该数据
    'username' => [
        //是否显示
        'show' => true,
        //表单标题
        'text' => '商户ID',
        //提示语
        'tips' => '',
    ],
    //密码，密钥配置 不能删除该数据
    'password' => [
        'show' => true,
        'text' => '商户KEY',
        'tips' => '',
    ],
    //支付密码，密钥等配置 不能删除该数据
    'paypwd'   => [
        'show' => false,
        'text' => '',
        'tips' => '',
    ],
    //网站类型被选中提示
    'tips'     => '支持对接直冲/卡密，注册账号后需联系站长开通对接权限后刷新才能看到商户ID、商户KEY',
    //是否支持订单同步
    'cron'     => true,
];
