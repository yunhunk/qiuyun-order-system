<?php
/**
 * 系统基础配置
 */
return [

    //调试模式
    'debug'     => false, // true 开启  false 关闭
    //是否本地环境运行
    'localhost' => false, // true 开启  false 关闭
    //测试模式 作为测试站时开启 将不能修改账号密码等，同时授权站公告也会消失
    'test'      => false, // true 开启  false 关闭
    //自动检查更新 设置关闭后将不会自动检测更新
    'update'    => true, // true 开启  false 关闭

    //数据库配置
    'db'        => [
        //开启Exception错误模式
        'exception_on' => true,
    ],

    //后台推荐接口列表
    'epayList'  => array(
    ),
];
