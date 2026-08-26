<?php

$serverIp = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['SERVER_NAME'];

return [
    //别名
    'alias'    => 'youyunbao',
    //类型代号
    'type'     => 15,
    //平台名称
    'name'     => '优云宝系统',

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
        'show' => true,
        'text' => '二级密码或对接密码',
        'tips' => '如果没有二级/对接密码，或对方是旧版优云宝请务必留空',
    ],
    //网站类型被选中提示
    'tips'     => '1、账号和网站都需要先开通Api权限！需要代充和卡密订单自动同步时对接后动作请选择【进行中】。<span style="color:red">注意：如果对接提示密码错误，请在【登录密码】处填二级密码</span><br>2、如果Api是新版2.0且你服务器不是国内请开启代理服务器并将如下IP添加到优云宝-&gt;Api配置-&gt;IP白名单中：<span style="color:red">' . $serverIp . '</span>',
    //是否支持订单同步
    'cron'     => true,
];
