<?php
/**
 * 余额提现
 **/

use core\Db;

include __DIR__ . "/common.php";

$title = '商品管理';

if (conf('master_open') != 1) {
    exit('{"code":-1,"msg":"当前供货商系统已关闭, 无法操作"}');
}

$act = isset($_GET['act']) ? $_GET['act'] : null;
if ($act == 'index') {
    $days    = [];
    $key     = 0;
    $moneys  = [];
    $orders  = [];
    $tusous  = [];
    $profits = [];
    for ($i = 6; $i >= 0; $i--) {
        $starttime = strtotime('-' . $i . ' days');
        $days[]    = $start    = date('Y-m-d', $starttime);
        $thtime    = date('Y-m-d H:i:s', $starttime);
        $end       = strtotime($start . ' 23:59:59');
        $endtime   = date('Y-m-d H:i:s', $end);
        $tusous[]  = Db::name('workorder')->where([
            'sid'     => $masterrow['zid'],
            'status'  => ['in', [0, 2]],
            'addtime' => ['between', $thtime, $endtime],
        ])->count('id');

        $moneys[] = Db::name('orders')->where([
            'sid'     => $masterrow['zid'],
            'addtime' => ['between', $thtime, $endtime],
        ])->sum('money');

        $orders[] = Db::name('orders')->where([
            'sid'     => $masterrow['zid'],
            'addtime' => ['between', $thtime, $endtime],
        ])->count('id');

        $profits[] = Db::name('orders')->where([
            'sid'     => $masterrow['zid'],
            'addtime' => ['between', $thtime, $endtime],
        ])->sum('`money`-`price2`');
        $key++;
    }

    $kucunlist = Db::name('tools')->where([
        'zid'        => $masterrow['zid'],
        'active'     => 1,
        'stock'      => 0,
        'stock_time' => ['>=', time() - 1200],
    ])->field('name,tid,stock,active')->select();

    // $kucunlist = [];
    // if ($kucunRs) {
    //     foreach ($kucunRs as $key => $value) {
    //         $stock = Db::name('faka')->where([
    //             'tid'    => $value['tid'],
    //             'zid'    => $masterrow['zid'],
    //             'status' => 0,
    //         ])->count('kid');
    //         if ($stock == 0) {
    //             $kucunlist[] = $value;
    //         }
    //     }
    // }

    $master_nostock_close_day = intval(conf('master_nostock_close_day'));
    $master_nostock_close_day = $master_nostock_close_day > 0 ? $master_nostock_close_day : 3;
    // 自动下架3天不加卡的
    Db::name('tools')->where([
        'zid'       => $masterrow['zid'],
        'cardstime' => ['<=', strtotime('-' . $master_nostock_close_day . ' day')],
        'stock'     => 0,
    ])->select();

    $notifylist = [];

    $count1 = Db::name('workorder')->where(['sid' => $masterrow['zid']])->count('id');
    if ($count1 > 0) {
        $notifylist[] = [
            'type'    => 'warning',
            'alert'   => 0,
            'message' => '您有' . $count1 . '条投诉待处理, 有投诉将影响提现等操作',
            'path'    => '/orders/tousu',
        ];
    }

    if (!$masterrow['pay_account'] || !$masterrow['pay_name'] || !$masterrow['skimg']) {
        $notifylist[] = [
            'type'    => 'error',
            'alert'   => 0,
            'message' => '您收款信息设置不完整, 请及时设置避免影响提现',
            'path'    => '/information/index',
        ];
    }

    if (conf('master_price') > 0 && $masterrow['master_price'] < conf('master_price')) {
        $notifylist[] = [
            'type'    => 'error',
            'alert'   => 0,
            'message' => '您当前保证金不够' . conf('master_price') . '元, 将无法上架商品',
            'path'    => '/master/increase',
        ];
    }

    if (!validateData($masterrow['email'], 'email')) {
        $notifylist[] = [
            'type'    => 'warning',
            'alert'   => 1,
            'message' => '<b style="color:red;">您未绑定有效的邮箱, 请设置好以保证及时通知和账户安全</b>',
            'path'    => '/information/index',
            'btn'     => '去绑定',
        ];
    }

    success('成功', [
        'sevenDays'  => $days,
        'tusous'     => $tusous,
        'moneys'     => $moneys,
        'orders'     => $orders,
        'profits'    => $profits,
        'kucunlist'  => $kucunlist,
        'notifylist' => $notifylist,
    ]);
}
