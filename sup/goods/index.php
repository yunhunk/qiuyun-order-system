<?php
/**
 * 余额提现
 **/

use core\Db;
use core\Ems;

include dirname(__DIR__) . "/common.php";

$title = '商品管理';

if (conf('master_open') != 1) {
    exit('{"code":-1,"msg":"当前供货商系统已关闭, 无法操作"}');
}
$master_vip_prid = intval(conf('master_vip_prid'));

$act = isset($_GET['act']) ? $_GET['act'] : null;
if ($act == 'getData') {

    $search = input('search');
    $cid    = input('cid');
    $where  = ['zid' => $masterrow['zid'], 'is_curl' => 4];
    $sql    = '';
    if ($search) {
        $sql = " `name` LIKE '%{$search}%'";
    }

    if ($cid > 0) {
        $where['cid'] = $cid;
    }

    $list  = Db::name('tools')->where($where)->where($sql)->limit($offset . ',' . $pagesize)->order('stock ASC,tid desc')->select();
    $total = Db::name('tools')->where($where)->where($sql)->count('tid');
    if ($list) {
        $rs           = $DB->query("SELECT * FROM pre_class WHERE upcid is null OR upcid=0 order by sort asc");
        $pre_class[0] = '未分类';
        while ($res = $DB->fetch($rs)) {
            $pre_class[$res['cid']] = $res['name'];
            $subClass               = $DB->count("SELECT count(*) FROM pre_class WHERE upcid='" . $res['cid'] . "' order by sort asc");
            if ($subClass > 0) {
                $rs2 = $DB->query("SELECT * FROM pre_class WHERE active=1 and upcid='" . $res['cid'] . "' order by sort asc");
                while ($res2 = $DB->fetch($rs2)) {
                    $pre_class[$res2['cid']] = $res2['name'];
                }
            }
        }
        foreach ($list as $key => $value) {
            $value['classname'] = $pre_class[$value['cid']];
            // 查询缓存60分钟
            $value['stock'] = Db::name('faka')->where([
                'zid'     => $masterrow['zid'],
                'tid'     => $value['tid'],
                'orderid' => 0,
            ])->count('kid');
            // 刷新库存
            Db::name('tools')->where(['tid' => $value['tid']])->update([
                'stock'      => $value['stock'],
                'stock_time' => time() + 3600,
            ]);
            $list[$key] = $value;
        }
    }

    success('成功', [
        'rows'     => $list,
        'total'    => $total,
        'page'     => $page,
        'offset'   => $offset,
        'pagesize' => $pagesize,
    ]);
} elseif ($act == 'change') {

    // if (conf('master_auth_change_order') != 1) {
    //     error('系统未开放处理订单权限');
    // }

    $active = intval(input('active'));
    $ids    = input('ids');
    if (!$ids) {
        error('参数ID不能为空');
    }
    !is_array($ids) && $ids = [intval($ids)];
    $rows                   = Db::name('tools')->where(['tid' => ['in', $ids]])->get();
    if (!$rows) {
        error('该商品不存在 =>' . implode(',', $ids), [
            'sql' => Db::getLastSql(),
        ]);
    }

    $update = Db::name('tools')->where(['tid' => ['in', $ids]])->update([
        'active' => $active,
    ]);

    if ($update !== false) {
        success('修改商品状态成功', [
            'active' => $active,
        ]);
    } else {
        error('修改商品失败, ' . Db::error());
    }
} elseif ($act == 'class') {
    $list  = $DB->select("SELECT * FROM `pre_class` where `upcid` IS NULL OR upcid<1 order by sort asc");
    $total = Db::name('class')->count('cid');
    $rows  = [];

    !is_array($list) && $list = [];
    foreach ($list as $key => $value) {
        $value['image'] = cdnurl($value['image'], true);
        $rows[]         = $value;
        $sub            = Db::name('class')->where(['upcid' => $value['cid']])->select();
        if ($sub) {
            foreach ($sub as $key2 => $item) {
                $item['name']  = '|----' . $item['name'];
                $item['image'] = cdnurl($item['image'], true);
                $rows[]        = $item;
            }
        }
    }

    success('成功', [
        'rows'     => $list,
        'total'    => $total,
        'page'     => $page,
        'offset'   => $offset,
        'pagesize' => $pagesize,
    ]);
} elseif ($act == 'add') {
    // 添加
    $post = input('post.', 1, 1);
    if (!$post) {
        error('提交参数缺失');
    }

    if (Ems::checkIsRun() && !validateData($masterrow['email'], 'email')) {
        error('未绑定账户邮箱, 请先去“设置”绑定', []);
    }

    if (!isset($post['name']) || !$post['name']) {
        error('商品名称不能为空');
    }

    if (!isset($post['price1']) || !$post['price1']) {
        error('供货价格不能为空');
    }

    // if (!isset($post['price2']) || !$post['price2']) {
    //     error('成本价格不能为0');
    // }

    if ($post['price2'] <= 0) {
        $post['price2'] = $post['price1'];
    }
    $post['priceold'] = $post['priceold'] > 0 ? $post['priceold'] : $post['price'];

    $post['desc'] = addcslashes(xss_filter(input('desc', 0, 0), false), "'");

    // 默认加价模板
    $post['prid'] = $master_vip_prid;

    if ($master_vip_prid > 0) {
        $pridrow = Db::name('price')->get(['id' => $master_vip_prid]);
        if ($pridrow) {
            if ($pridrow['kind'] == 2) {
                $post['price'] = round($post['price1'] + $post['price1'] * $pridrow['p_0'] / 100, 2);
                $post['cost']  = round($post['price1'] + $post['price1'] * $pridrow['p_1'] / 100, 2);
                $post['cost2'] = round($post['price1'] + $post['price1'] * $pridrow['p_2'] / 100, 2);
            } else {
                $post['price'] = round($post['price1'] + $pridrow['p_0'], 2);
                $post['cost']  = round($post['price1'] + $pridrow['p_1'], 2);
                $post['cost2'] = round($post['price1'] + $pridrow['p_2'], 2);
            }
        } else {
            unset($post['price']);
            unset($post['cost']);
            unset($post['cost2']);
        }
    } else {
        unset($post['price']);
        unset($post['cost']);
        unset($post['cost2']);
    }

    if ($post['prid'] <= 0) {
        $post['price'] = $post['price1'] + 1.5;
        $post['cost']  = $post['price1'] + 1.2;
        $post['cost2'] = $post['price1'] + 1.0;
    }

    unset($post['hashsalt']);

    // 保证金检测
    if ($masterrow['master_price'] < conf('master_price')) {
        error('保证金不足，请先再缴纳' . round(conf('master_price') - $masterrow['master_price'], 2) . '元后再试');
    }

    // 免审核检测
    if (conf('master_vip_goods') == 1 && $masterrow['master_price'] >= conf('master_vip_price')) {
        $post['condition'] = 1;
    } else {
        $post['condition'] = 0;
    }
    $post['active'] = 1;

    // 卡密商品
    $post['is_curl']    = 4;
    $post['zid']        = $masterrow['zid'];
    $post['stock_time'] = time();
    $post['addtime']    = $date;
    $post['updatetime'] = $date;

    $tid = Db::name('tools')->insert($post);
    if ($tid !== false) {
        // 重置排序
        resetGoodsSort($tid);
        // 通知管理员
        if (conf('master_notify_goods_email') > 0) {
            $is_send = true;
            if ($post['condition'] == 1 && conf('master_notify_goods_email') == 1) {
                $is_send = false;
            }

            if ($is_send === true && Ems::checkIsRun() && validateData(conf('adm_email'), 'email')) {
                $ems = new Ems();
                $ems->sendEmail(conf('adm_email'), '通知! 有新的供货商商品需要审核', '<b>供货商UID: </b>' . $masterrow['zid'] . '<br/><b>商品ID: </b>' . $tid . '<br/><b>供货价格: </b>' . round($post['price1'], 2) . '元<br/>提交时间: </br>' . $res['addtime'] . '<br/>');
            }
        }

        success('成功', [
            'tid' => $tid,
        ]);
    } else {
        error('添加失败, 数据库错误, ' . Db::error());
    }

} elseif ($act == 'edit') {
    // 编辑
    $post = input('post.', 1, 1);
    if (!$post) {
        error('提交参数缺失');
    }

    if (!isset($post['tid']) || !$post['tid']) {
        error('商品ID不能为空');
    }
    $tid = $post['tid'];
    $row = Db::name('tools')->find(['tid' => $tid]);
    if (!$row) {
        error('该商品不存在');
    }

    if ($row['zid'] != $masterrow['zid']) {
        error('无权限操作或商品不存在');
    }

    if (Ems::checkIsRun() && !validateData($masterrow['email'], 'email')) {
        error('未绑定账户邮箱, 请先去“设置”绑定', []);
    }

    if (!isset($post['name']) || !$post['name']) {
        error('商品名称不能为空');
    }

    if (!isset($post['price1']) || !$post['price1']) {
        error('供货价格不能为空');
    }

    // if (!isset($post['price2']) || !$post['price2']) {
    //     error('成本价格不能为0');
    // }

    if ($post['price2'] <= 0) {
        $post['price2'] = $post['price1'];
    }

    $post['priceold'] = $post['priceold'] > 0 ? $post['priceold'] : $post['price'];

    // 保证金检测
    // if ($masterrow['master_price'] < conf('master_price')) {
    //     error('保证金不足，请先再缴纳' . round(conf('master_price') - $masterrow['master_price'], 2) . '元后再试');
    // }

    // 免审核
    if (conf('master_vip_goods') == 1 && $masterrow['master_price'] >= conf('master_vip_price')) {
        $post['condition'] = 1;
    } else {
        $post['condition'] = 0;
    }

    // 卡密商品
    $post['is_curl'] = 4;
    $post['zid']     = $masterrow['zid'];

    if ($row['prid'] > 0) {
        $pridrow = Db::name('price')->get(['id' => $row['prid']]);
        if ($pridrow) {
            if ($pridrow['kind'] == 2) {
                $post['price'] = round($post['price1'] + $post['price1'] * $pridrow['p_0'] / 100, 2);
                $post['cost']  = round($post['price1'] + $post['price1'] * $pridrow['p_1'] / 100, 2);
                $post['cost2'] = round($post['price1'] + $post['price1'] * $pridrow['p_2'] / 100, 2);
            } else {
                $post['price'] = round($post['price1'] + $pridrow['p_0'], 2);
                $post['cost']  = round($post['price1'] + $pridrow['p_1'], 2);
                $post['cost2'] = round($post['price1'] + $pridrow['p_2'], 2);
            }
        } else {
            unset($post['price']);
            unset($post['cost']);
            unset($post['cost2']);
        }
    } else {
        unset($post['price']);
        unset($post['cost']);
        unset($post['cost2']);
    }
    $post['desc'] = addcslashes(xss_filter(input('desc', 0, 0), false), "'");
    unset($post['tid']);
    unset($post['prid']);
    unset($post['classname']);
    unset($post['hashsalt']);

    $insert = Db::name('tools')->where(['tid' => $tid])->update($post);
    if ($insert !== false) {

        // 通知管理员
        if (conf('master_notify_goods_email') > 0) {
            $is_send = true;
            if ($post['condition'] == 1 && conf('master_notify_goods_email') == 1) {
                $is_send = false;
            }

            if ($is_send === true && Ems::checkIsRun() && validateData(conf('adm_email'), 'email')) {
                $ems = new Ems();
                $ems->sendEmail(conf('adm_email'), '通知! 有新的供货商商品需要审核', '<b>供货商UID: </b>' . $masterrow['zid'] . '<br/><b>商品ID: </b>' . $tid . '<br/><b>供货价格: </b>' . round($post['price1'], 2) . '元<br/>提交时间: </br>' . $res['addtime'] . '<br/>');
            }
        }
        success('成功');
    } else {
        error('修改失败, 数据库错误, ' . Db::error());
    }

} elseif ($act == 'del') {

    if (conf('master_auth_delete_order') != 1) {
        error('系统未开放删除订单权限');
    }

    $active = intval(input('active'));
    $ids    = input('ids');
    $result = input('result');
    if (!$ids) {
        error('参数ID不能为空');
    }
    !is_array($ids) && $ids = [intval($ids)];
    $rows                   = Db::name('tools')->where([
        'tid' => ['in', $ids],
        'zid' => $masterrow['zid'],
    ])->get();
    if (!$rows) {
        error('该商品不存在 =>' . implode(',', $ids), [
            'sql' => Db::getLastSql(),
        ]);
    }

    $update = Db::name('tools')->where([
        'tid' => ['in', $ids],
        'zid' => $masterrow['zid'],
    ])->delete();
    if ($update !== false) {
        success('删除商品成功', []);
    } else {
        error('修改商品失败, ' . Db::error());
    }
}
