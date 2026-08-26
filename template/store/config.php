<?php
/*
 * @Author: 星河 andywickshum@gmail.com 
 * @Date: 2022-06-13 21:43:28
 * @LastEditors: 星河 andywickshum@gmail.com 
 * @LastEditTime: 2022-11-04 21:46:27
 * @Description:
 *
 * Copyright (c) 2022 by 星河科技, All Rights Reserved.
 */
$template_info = [
    'name'    => 'H5商城首页模板',
    'version' => '1.3.6',
];

$template_settings = [
    'banner'                  => ['name' => '首页轮播图', 'type' => 'textarea', 'tips' => '填写格式：图片链接*跳转链接|图片链接*跳转链接'],
    'defaultcid'              => ['name' => '默认显示分类ID', 'type' => 'input', 'tips' => '首页默认显示商品的分类ID，不填写则显示所有'],
    'template_showprice'      => [
        'name'    => '商品页面显示代理价格',
        'type'    => 'select',
        'options' => [
            '0' => '关闭',
            '1' => '开启',
        ],
    ],
    'template_store_classbtn' => [
        'name'    => '是否显示分类前进后退按钮',
        'type'    => 'select',
        'options' => [
            '0' => '关闭',
            '1' => '开启',
        ],
        'tips'    => '当多页分类时有效',
    ],
    'template_showsales'      => [
        'name'    => '是否显示商品销量',
        'type'    => 'select',
        'options' => [
            '0' => '关闭',
            '1' => '开启',
        ],
    ],
    'template_store_allbtn'   => [
        'name'    => '是否显示所有商品按钮',
        'type'    => 'select',
        'options' => [
            '0' => '关闭',
            '1' => '开启',
        ],
    ],
    'index_class_num_style'   => ['name' => '首页分类展示几行', 'type' => 'input', 'tips' => '默认不填写为2'],
];
