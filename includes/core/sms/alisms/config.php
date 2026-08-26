<?php
/*
 * @author 星河
 * @description 阿里云接口配置信息
 *
 */

return [
    [
        'label'   => 'AccessKey ID',
        'field'   => 'AccessKeyId',
        'type'    => 'string',
        'encrypt' => 1,
        'tips'    => '前往【<a href="https://ram.console.aliyun.com/manage/ak" target="_blank">登录阿里云控制台->右上角菜单点开->AccessKey管理</a>】 页面可获得',
    ],
    [
        'label'   => 'AccessKey Secret',
        'field'   => 'AccessKeySecret',
        'type'    => 'string',
        'encrypt' => 1,
        'tips'    => '',
    ],
    [
        'label' => '短信签名',
        'field' => 'SignName',
        'type'  => 'string',
        'tips'  => '一版都是公司名称简写，如：星河科技',
    ],
];
