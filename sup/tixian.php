<?php
/**
 * 余额提现
 **/

use core\Db;

include __DIR__ . "/common.php";

$title = '提现管理';

if ($act == 'getData') {

    $search = input('search');
    $where  = '';
    if ($search) {
        $where = " `money`='{$search}' OR `pay_account` LIKE '%{$search}%' OR `pay_name` LIKE '%{$search}%'";
    }

    $list  = Db::name('master_tixian')->where(['zid' => $masterrow['zid']])->where($where)->limit($offset . ',' . $pagesize)->order('id desc')->select();
    $total = Db::name('master_tixian')->where(['zid' => $masterrow['zid']])->where($where)->count('id');

    success('成功', [
        'rows'     => $list,
        'total'    => $total,
        'page'     => $page,
        'offset'   => $offset,
        'pagesize' => $pagesize,
    ]);
} elseif ($act == 'add') {
    // 新增提现

    if ($conf['master_tixian_open'] != 1) {
        exit('{"code":-1,"msg":"当前系统未开启供货商提现功能"}');
    }

    $money = floatval(input('post.money', 1));

    if (preg_match('/^[\d\.]+$/', $money) != 1) {
        error('提现金额格式不正确', []);
    }

    if (!validateData($masterrow['email'], 'email')) {
        error('未绑定邮箱, 请先去“设置”绑定', []);
    }

    if (\core\Ems::checkIsRun() && conf('ems_check_change_info') == 1 && validateData($masterrow['email'], 'email')) {

        $event = 'safecheck';
        $code  = input('code2');
        if (!$code) {
            json('邮箱验证码不能为空', 406);
        }

        $ems   = new \core\Ems();
        $check = $ems->check($masterrow['email'], $code ?: null, $event);
        if ($check !== true) {
            json($check, 406, [
                'code'  => $code,
                'event' => $event,
            ]);
        }
    }

    if ($money > $masterrow['income']) {
        error('提现金额不能超出账户余额' . $masterrow['income'] . '元', []);
    }

    if (!$masterrow['pay_account']) {
        error('未设置收款账户, 请先设置', []);
    }

    if (!$masterrow['pay_name']) {
        error('未设置收款姓名, 请先设置', []);
    }

    // if (!$masterrow['skimg']) {
    //     error('未设置收款二维码, 请先设置', []);
    // }

    $count = Db::name('tools')->where([
        'zid'        => $masterrow['zid'],
        'stock'      => 0,
        'active'     => 1,
        'condition'  => 1,
        'stock_time' => ['>=', time() - 600],
    ])->count('tid');
    if ($count > 0) {
        error('当前存在0库存的商品, 请先加卡再提现哦', [
            'sql' => Db::getLastSql(),
        ]);
    }

    $realmoney = round($money * $conf['master_tixian_rate'] / 100, 2);

    $data = [
        'zid'         => $masterrow['zid'],
        'money'       => $money,
        'realmoney'   => $realmoney,
        'pay_type'    => $masterrow['pay_type'],
        'pay_account' => $masterrow['pay_account'],
        'pay_name'    => $masterrow['pay_name'],
        'status'      => 0,
        'addtime'     => $date,
        'note'        => '',
    ];

    $insert = Db::name('master_tixian')->insert($data);
    if ($insert !== false) {
        Db::name('master')->where(['zid' => $masterrow['zid']])->update([
            'income' => $masterrow['income'] - $money,
        ]);

        addMasterPointLogs($masterrow['zid'], $money, '提现', '供货账户余额提现' . $money . '元, 实际到账预计为' . $realmoney . '元');
        success('成功', []);
    } else {
        error('数据库错误, ' . Db::error(), []);
    }
} elseif ($act == 'cancel') {

    if ($conf['master_tixian_cancel'] != 1) {
        error('系统未开启该操作', []);
    }

    $id = input('id');

    if (!$id) {
        error('缺少参数id', []);
    }

    $row = Db::name('master_tixian')->find(['id' => $id]);

    if (!$row) {
        throw new \Exception("该提现记录不存在", 1);
    }

    if ($row['zid'] != $masterrow['zid']) {
        throw new \Exception("该笔提现无权限或不存在", 1);
    }

    if ($row['status'] == 1) {
        throw new \Exception("该笔提现已经完成，无法再撤销", 1);
    } elseif ($row['status'] == 2) {
        throw new \Exception("该笔提现已退回，无法再撤销", 1);
    } elseif ($row['status'] == 4) {
        throw new \Exception("该笔提现已撤销，无法再撤销", 1);
    } elseif ($row['status'] == 3) {
        throw new \Exception("该笔提现处理中，无法再撤销", 1);
    }

    $money = $row['money'];

    $update = Db::name('master_tixian')->where(['id' => $id])->update([
        'status' => 4,
        'note'   => '供货商已主动撤销',
    ]);

    if ($update !== false) {
        Db::name('master')->where(['zid' => $masterrow['zid']])->update([
            'income' => $masterrow['income'] + $money,
        ]);
        addMasterPointLogs($masterrow['zid'], $money, '撤销', '供货账户余额提现主动撤销，金额已返还到账户');
        success('撤销成功', [
            'id'    => $id,
            'money' => $money,
        ]);
    } else {
        error('撤销失败,' . Db::error(), [
            'id'    => $id,
            'money' => $money,
        ]);
    }
}
