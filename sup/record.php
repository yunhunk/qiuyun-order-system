<?php
/**
 * 余额提现
 **/

use core\Db;

include __DIR__ . "/common.php";

$title = '收支明细';

if ($act == 'getData') {
    $list  = Db::name('master_points')->where(['zid' => $masterrow['zid']])->limit($offset . ',' . $pagesize)->order('id desc')->select();
    $total = Db::name('master_points')->where(['zid' => $masterrow['zid']])->count('id');

    success('成功', [
        'rows'     => $list,
        'total'    => $total,
        'page'     => $page,
        'offset'   => $offset,
        'pagesize' => $pagesize,
    ]);
} elseif ($act == 'chonzhi') {
    $where = [
        'zid'  => $masterrow['zid'],
        'tid'  => '-4',
        'name' => '在线充值余额',
    ];
    $list  = Db::name('pay')->where($where)->limit($offset . ',' . $pagesize)->select();
    $total = Db::name('pay')->where($where)->count('id');
    success('成功', [
        'rows'     => $list,
        'total'    => $total,
        'page'     => $page,
        'offset'   => $offset,
        'pagesize' => $pagesize,
    ]);
}
