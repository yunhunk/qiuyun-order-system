<?php
/**
 * 余额提现
 **/

use core\Db;

include dirname(__DIR__) . "/common.php";

$title = '订单管理';

if (conf('master_open') != 1) {
    exit('{"code":-1,"msg":"当前供货商系统已关闭, 无法操作"}');
}

function getEnInputStr($value = '')
{
    if ($value) {
        if (strlen($value) > 9) {
            return '***' . substr($value, 5);
        } else {
            return '***' . substr($value, 3);
        }
    }
    return $value;
}

if ($act == 'getData') {
    $search = input('search');
    $where  = '';
    if ($search) {
        $tids = Db::name('tools')->where(['name' => ['like', '%' . $search . '%']])->column('tid');
        if ($tids) {
            var_dump($tids);
            die;
        }

        !is_array($tids) && $tids = [];

        if ($tids) {
            $where = " `id`='{$search}' OR `input`='{$search}' OR `input2`='{$search}' OR `zid`='{$zid}' OR `tid` IN (" . implode(',', $tids) . ")";
        } else {
            $where = " `id`='{$search}' OR `input`='{$search}' OR `input2`='{$search}' OR `zid`='{$zid}'";
        }
    }

    $list = Db::name('orders')->field('input,input2,input3,input4,value,input5,`addtime`,`tid`,`id`,`result`,`status`,bz')->where(['sid' => $masterrow['zid']])->where($where)->limit($offset . ',' . $pagesize)->select();
    // $list  = Db::name('orders')->where(['sid' => $masterrow['zid']])->where($where)->limit($offset . ',' . $pagesize)->select();
    $sql   = Db::getLastSql();
    $total = Db::name('orders')->where(['sid' => $masterrow['zid']])->where($where)->count('id');
    $rows  = [];
    if ($list) {
        $list = array_reverse($list);
        foreach ($list as $key => $value) {
            $value['input']  = getEnInputStr($value['input']);
            $value['input2'] = getEnInputStr($value['input2']);
            $value['input3'] = getEnInputStr($value['input3']);
            $value['input4'] = getEnInputStr($value['input4']);
            $value['input5'] = getEnInputStr($value['input5']);
            $value['goods']  = Db::name('tools')->where(['tid' => $value['tid']])->find();
            if (!$value['goods']) {
                $value['goods'] = [
                    // 'name' => '商品不存在 =>' . $value['tid'] . '[SQL:' . Db::getLastSql() . ']',
                    'name' => '商品已被删除 =>' . $value['tid'],
                ];
            }
            $rows[] = $value;
        }
    }

    success('成功', [
        'rows'     => $rows,
        'total'    => $total,
        'page'     => $page,
        'offset'   => $offset,
        'pagesize' => $pagesize,
        // 'sql'      => $sql,
    ]);
} elseif ($act == 'change') {

    if (conf('master_auth_change_order') != 1) {
        error('系统未开放处理订单权限');
    }

    $status = intval(input('status'));
    $id     = intval(input('id'));
    $result = input('result');
    if (!$id) {
        error('参数ID不能为空');
    }
    $row = Db::name('orders')->where(['id' => $id])->get();
    if (!$row) {
        error('该订单不存在 =>' . $id);
    }

    $update = Db::name('orders')->where(['id' => $id])->update([
        'status' => $status,
        'result' => $result,
    ]);

    if ($update !== false) {
        success('修改订单状态成功', [
            'status' => $status,
            'result' => $result,
        ]);
    } else {
        error('修改订单状态失败, ' . Db::error());
    }
} elseif ($act == 'del') {

    if (conf('master_auth_delete_order') != 1) {
        error('系统未开放删除订单权限');
    }

    $id     = intval(input('id'));
    $result = input('result');
    if (!$id) {
        error('参数ID不能为空');
    }

    Db::transaction();
    try {
        $row = Db::name('orders')->where(['id' => $id])->lock(true)->get();
        if (!$row) {
            throw new \Exception('该订单不存在 =>' . $id, 1);
        }

        $update = Db::name('orders')->where(['id' => $id])->update([
            'status' => $status,
            'result' => $result,
        ]);

        Db::commit();
        success('修改订单状态成功', [
            'status' => $status,
            'result' => $result,
        ]);
    } catch (\PDOException $th) {
        Db::rollback();
        error('修改订单状态失败, ' . $th->getMessage());
    } catch (\Throwable $th) {
        Db::rollback();
        error('修改订单状态失败, ' . $th->getMessage());
    }
}
