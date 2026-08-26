<?php
/**
 * 余额提现
 **/

use core\Db;

include __DIR__ . "/common.php";

$title = '保证金管理';

$act = isset($_GET['act']) ? $_GET['act'] : null;
if ($act == 'joinPrice') {
    // 缴纳
    $money = input('money');
    if (!$money) {
        error('要缴纳的金额不能为空');
    } elseif (preg_match('/^[\d\.]+$/', $money) != 1) {
        error('要缴纳的金额格式不正确');
    }

    if ($masterrow['income'] < $money) {
        error('余额不足' . $money . '元, 需充值' . round($money - $masterrow['income'], 2) . '元');
    }

    $update = Db::name('master')->where(['zid' => $masterrow['zid']])->update([
        'income'       => $masterrow['income'] - $money,
        'master_price' => $masterrow['master_price'] + $money,
    ]);

    if ($update !== false) {
        addMasterPointLogs($masterrow['zid'], $money, '缴纳', '缴纳保证金到冻结余额, 当前保证金:' . round($masterrow['master_price'] + $money, 2) . '元');
        success('成功');
    } else {
        error('缴纳失败,' . Db::error(), [
            'zid'   => $masterrow['zid'],
            'money' => $money,
        ]);
    }
} elseif ($act == 'removePrice') {
    // 解冻
    $money = input('money');
    if (!$money) {
        error('要解冻的金额不能为空');
    } elseif (preg_match('/^[\d\.]+$/', $money) != 1) {
        error('要解冻的金额格式不正确');
    }

    if ($masterrow['master_price'] < $money) {
        error('保证金不足' . $money . '元, 无法解冻');
    }

    $update = Db::name('master')->where(['zid' => $masterrow['zid']])->update([
        'income'       => $masterrow['income'] + $money,
        'master_price' => $masterrow['master_price'] - $money,
    ]);

    if ($update !== false) {
        addMasterPointLogs($masterrow['zid'], $money, '解冻', '解冻保证金到余额, 当前余额:' . round($masterrow['income'] + $money, 2) . '元');
        success('成功');
    } else {
        error('解冻失败,' . Db::error(), [
            'zid'   => $masterrow['zid'],
            'money' => $money,
        ]);
    }
}
