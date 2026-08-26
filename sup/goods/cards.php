<?php
/**
 * 卡密管理
 **/

use core\Db;

include dirname(__DIR__) . "/common.php";

$title = '卡密管理';

if (conf('master_open') != 1) {
    exit('{"code":-1,"msg":"当前供货商系统已关闭, 无法操作"}');
}

if ($act == 'getData') {
    $cid    = intval(input('cid'));
    $search = input('search2');
    $active = intval(input('active'));
    $stock  = input('stock');

    $where = ['zid' => $masterrow['zid'], 'is_curl' => 4];
    $sql   = '';
    if ($search) {
        $sql = " `name` LIKE '%{$search}%'";
    } else {

    }

    if ($cid > 0) {
        $where['cid'] = $cid;
    }

    if ($active > -1 && in_array($active, [1, 0])) {
        $where['active'] = $active;
    }

    if ($stock > 0) {
        switch ($stock) {
            case 2:
                $where['stock'] = ['<', 10];
                break;
            case 3:
                $where['stock'] = ['>', 10];
                break;
            case 4:
                $where['stock'] = ['>', 50];
                break;
            default:
                $where['stock'] = 0;
                break;
        }
    }

    $list    = Db::name('tools')->where($where)->field('tid,name,stock,stock_time,sale,zid,`active`')->where($sql)->limit($offset . ',' . $pagesize)->order('`stock` ASC')->select();
    $listSql = Db::getLastSql();
    $total   = Db::name('tools')->where($where)->field('tid,name,stock,stock_time,sale,zid,`active`')->where($sql)->count('tid');

    if ($list) {
        foreach ($list as $key => $value) {
            $value['stock'] = Db::name('faka')->where([
                'zid'     => $masterrow['zid'],
                'tid'     => $value['tid'],
                'orderid' => ['<=', 0],
            ])->count('kid');

            // 刷新库存
            Db::name('tools')->where(['tid' => $value['tid']])->update([
                'stock'      => $value['stock'],
                'stock_time' => time() + 3600,
            ]);
            $value['usenum'] = $value['sale'];
            // $value['usenum'] = Db::name('faka')->where([
            //     'zid'     => $masterrow['zid'],
            //     'tid'     => $value['tid'],
            //     'orderid' => ['>', 0],
            //     'status'  => 1,
            // ])->count('kid');
            $list[$key] = $value;
        }
    }

    success('成功', [
        'rows'     => $list,
        'total'    => $total,
        'page'     => $page,
        'offset'   => $offset,
        'pagesize' => $pagesize,
        'listSql'  => $listSql,
    ]);
} elseif ($act == 'getCardsData') {

    $tid    = intval(input('tid'));
    $search = input('search');

    $status = intval(input('status'));

    $where = ['zid' => $masterrow['zid']];
    $sql   = '';
    if ($search) {
        $sql = " `km` LIKE '%{$search}%' OR `pw` LIKE '%{$search}%' ";
    }

    if ($tid) {
        $where['tid'] = $tid;
    }

    if ($status > -1) {
        switch ($status) {
            case 1:
                $where['orderid'] = ['>', 0];
                break;
            default:
                $where['orderid'] = 0;
                break;
        }
    }

    $list    = Db::name('faka')->where($where)->where($sql)->limit($offset . ',' . $pagesize)->order('kid DESC')->select();
    $listSql = Db::getLastSql();
    $total   = Db::name('faka')->where($where)->where($sql)->count('kid');

    success('成功', [
        'rows'     => $list,
        'total'    => $total,
        'page'     => $page,
        'offset'   => $offset,
        'pagesize' => $pagesize,
        'listSql'  => $listSql,
    ]);
} elseif ($act == 'edit') {
    $post = input('post.', 1, 1);
    if (!$post) {
        error('提交参数缺失');
    }

    if (!isset($post['kid']) || !$post['kid']) {
        error('商品ID不能为空');
    }
    $kid = $post['kid'];
    $row = Db::name('faka')->find(['kid' => $kid]);
    if (!$row) {
        error('该卡密记录不存在');
    }

    if ($row['zid'] != $masterrow['zid']) {
        error('无权限操作或卡密不存在');
    }

    if (!isset($post['km']) || !$post['km']) {
        error('卡号不能为空');
    }

    $update = Db::name('faka')->where(['kid' => $kid])->update($post);
    if ($update !== false) {
        success('成功');
    } else {
        error('修改失败, 数据库错误, ' . Db::error());
    }
} elseif ($act == 'import') {
    // 导卡
    $tid = input('post.tid', 1, 1);
    if (!$tid) {
        error('商品ID不能为空');
    }

    if (!validateData($masterrow['email'], 'email')) {
        error('未绑定邮箱, 请先去“设置”绑定', []);
    }

    $row = Db::name('tools')->find(['tid' => $tid]);
    if (!$row) {
        error('该商品不存在 =>' . $tid);
    }

    if ($row['zid'] != $masterrow['zid']) {
        error('无权限');
    }

    $is_check_repeat = intval(input('post.is_check_repeat', 1, 1));
    $succ            = 0;
    $warn            = 0;

    $kms = input('post.kms', 1, 1);

    if (!$kms) {
        error('卡密内容不能为空');
    }

    $kms = str_replace(array(
        0 => "\r\n",
        1 => "\r",
        2 => "\n",
    ), '[br]', $kms);
    $match = explode('[br]', $kms);
    $c     = 0;
    foreach ($match as $val) {
        $km_arr = explode(' ', $val);
        $km     = trim(addcslashes(xss_filter($km_arr[0]), "'"));
        $pw     = trim(addcslashes(xss_filter($km_arr[1]), "'"));
        if ($km != '') {
            $c++;
            if ($is_check_repeat == 1) {

                $find = Db::name('faka')->where(['km' => $km, 'zid' => $masterrow['zid']])->find();
                if (!$find) {

                    $insert = Db::name('faka')->insert([
                        'tid'     => $tid,
                        'zid'     => $masterrow['zid'],
                        'km'      => $km,
                        'pw'      => $pw,
                        'addtime' => $date,
                    ]);
                    if ($insert) {
                        $succ++;
                    } else {
                        $warn++;
                    }
                }
            } else {
                $insert = Db::name('faka')->insert([
                    'tid'     => $tid,
                    'zid'     => $masterrow['zid'],
                    'km'      => $km,
                    'pw'      => $pw,
                    'addtime' => $date,
                ]);
                if ($insert) {
                    $succ++;
                } else {
                    $warn++;
                }
            }
        }
    }

    if ($succ > 0) {
        $stock = Db::name('faka')->where([
            'tid'     => $tid,
            'zid'     => $masterrow['zid'],
            'orderid' => ['<=', 0],
        ])->count('kid');

        $data = [
            'cardstime'  => time(),
            'stock_open' => 1,
            'stock'      => $stock,
        ];

        // 加卡自动上架
        if ($row['stock'] <= 0 && $row['active'] == 0) {
            $data['active'] = 1;
        }

        Db::name('tools')->where(['tid' => $tid])->update($data);
    }
    success('共' . $c . '条卡密, 成功导入' . $succ . '条卡密到商品' . $row['name']);
} elseif ($act == 'getGoods') {
    $list  = Db::name('tools')->where(['zid' => $masterrow['zid'], 'is_curl' => 4])->select();
    $total = Db::name('tools')->where(['zid' => $masterrow['zid'], 'is_curl' => 4])->count('tid');

    success('成功', [
        'rows'     => $list,
        'total'    => $total,
        'page'     => $page,
        'offset'   => $offset,
        'pagesize' => $pagesize,
    ]);
} elseif ($act == 'class') {
    $list  = Db::name('class')->select();
    $total = Db::name('class')->count('cid');
    $rows  = [];

    foreach ($list as $key => $value) {
        $count = Db::name('tools')->where([
            'zid'     => $masterrow['zid'],
            'cid'     => $value['cid'],
            'is_curl' => 4,
        ])->count('tid');
        if ($count > 0) {
            $value['image'] = cdnurl($value['image'], true);
            $rows[]         = $value;
        }
    }

    success('成功', [
        'rows'     => $rows,
        'total'    => $total,
        'page'     => $page,
        'offset'   => $offset,
        'pagesize' => $pagesize,
    ]);
} elseif ($act == 'del') {

    // if (conf('master_auth_delete_order') != 1) {
    //     error('系统未开放删除订单权限');
    // }
    $ids = input('ids');
    if (!$ids) {
        error('要删除的卡密ID列表不能为空');
    }

    !is_array($ids) && $ids = [intval($ids)];
    $rows                   = Db::name('faka')->where(['kid' => ['in', $ids]])->select();
    if (!$rows) {
        error('该卡密不存在 =>' . implode(',', $ids), [
            // 'sql' => Db::getLastSql(),
        ]);
    }
    $update = Db::name('faka')->where(['kid' => ['in', $ids]])->delete();
    if ($update !== false) {
        success('成功删除' . count($ids) . '条卡密', [
            'sql' => Db::getLastSql(),
        ]);
    } else {
        error('修改商品失败, ' . Db::error());
    }
}
