
<?php
/**
 * 卡密管理
 **/

use core\Db;

include dirname(__DIR__) . "/common.php";

$title = '置顶排名';

if ($act == 'getData') {
    $cid    = intval(input('cid'));
    $search = input('search2');
    $where  = [
        'active' => 1,
    ];
    $where2 = "`zid`='{$masterrow['zid']}' OR deposit>0";
    $list   = Db::name('tools')->field('zid,name,like_up,deposit')->where($where)->where($where2)->limit('100')->order('deposit DESC,tid DESC')->select();
    $total  = count($list);

    if ($list) {
        foreach ($list as $key => $value) {
            $value['is_self'] = $value['zid'] == $masterrow['zid'] ? 1 : 0;
            $list[$key]       = $value;
        }
    }
    success('成功', [
        'rows'  => $list,
        'total' => $total,
    ]);
} elseif ($act == 'uprank') {
    $tid     = intval(input('tid'));
    $deposit = floatval(input('deposit'));
    if (!isset($tid) || !$tid) {
        error('商品ID不能为空');
    }

    $row = Db::name('tools')->find(['tid' => $tid]);
    if (!$row) {
        error('该卡密记录不存在');
    }

    if ($row['zid'] != $masterrow['zid']) {
        error('无权限操作或卡密不存在');
    }

    $deposit = floatval(input('deposit'));

    if (preg_match('/^[\d\.]+$/', $deposit) != 1 || $deposit <= 0) {
        error('押金排名金额格式不正确');
    }

    if ($masterrow['income'] < $deposit) {
        error('金额不足' . $deposit . '元, 请先充值');
    }

    Db::name('tools')->where(['tid' => $tid])->update([
        'deposit' => $row['deposit'] + $deposit,
    ]);

    Db::name('master')->where(['zid' => $masterrow['zid']])->update([
        'income' => $masterrow['income'] - $deposit,
    ]);

    addMasterPointLogs($masterrow['zid'], $deposit, '商品置顶', '通过缴纳押金增加商品<b>' . $row['name'] . '</b>的排名');

    success('置顶商品排名成功, 花费' . $deposit . '元', [
        'tid'     => $tid,
        'deposit' => $deposit,
    ]);
}