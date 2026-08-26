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

if ($act == 'getData') {

    $status = input('status');
    $where  = '';
    if ($status > -1) {
        $where = " `status`='{$status}'";
    }

    $list  = Db::name('workorder')->where(['sid' => $masterrow['zid']])->where($where)->limit($offset . ',' . $pagesize)->order('id desc')->select();
    $total = Db::name('workorder')->where(['sid' => $masterrow['zid']])->where($where)->count('id');
    if ($list) {
        foreach ($list as $key => $value) {
            $order = Db::name('orders')->find(['id' => $value['orderid']]);
            if ($order) {
                $tool = Db::name('tools')->where(['tid' => $order['tid']])->find();
                if ($tool) {
                    $value['toolname'] = '[' . $tool['tid'] . ']' . $tool['name'];
                }
            }

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
} elseif ($act == 'reply') {
    // 回复
    $id = intval(input('id'));
    if (!$id) {
        error('参数ID不能为空');
    }
    $row = Db::name('workorder')->where(['id' => $id])->get();
    if (!$row) {
        error('该工单记录不存在 =>' . $id);
    }

    if ($row['sid'] != $masterrow['zid']) {
        error('该工单记录无权限 ');
    }

    if (!validateData($masterrow['email'], 'email')) {
        error('未绑定邮箱, 请先去“设置”绑定', []);
    }

    $status = intval(input('status'));

    $content = input('content', 1);

    if (!$content) {
        error('处理回复信息不能为空!');
    }

    if (!in_array($status, [0, 1, 2])) {
        error('工单处理状态不合法!');
    }

    $type    = 1;
    $time    = $date;
    $content = $type . '^' . $date . '^' . $content;

    $content = $row['content'] ? $row['content'] . '*' . $content : $content;

    $update = Db::name('workorder')->where(['id' => $id])->update([
        'status'  => $status,
        'content' => $content,
    ]);

    if ($update !== false) {
        success('处理工单成功', [
            'status'  => $status,
            'content' => $content,
        ]);
    } else {
        error('处理工单失败, ' . Db::error());
    }
} elseif ($act == 'getTaskList') {
    // 获取回话列表
    $id = intval(input('id'));
    if (!$id) {
        error('参数ID不能为空');
    }
    $row = Db::name('workorder')->where(['id' => $id])->get();
    if (!$row) {
        error('该工单记录不存在 =>' . $id);
    }

    if ($row['sid'] != $masterrow['zid']) {
        error('该工单记录无权限 ');
    }

    $list = [];

    $site = Db::name('site')->where(['zid' => $row['zid']])->get();

    $myimg = '//q4.qlogo.cn/headimg_dl?dst_uin=' . $site['qq'] . '&spec=100';

    if ($masterrow['qq']) {
        $kfimg = '//q4.qlogo.cn/headimg_dl?dst_uin=' . $masterrow['qq'] . '&spec=100';
    } else {
        $kfimg = 'https://imgcache.qq.com/open_proj/proj_qcloud_v2/mc_2014/work-order/css/img/custom-service-avatar.svg';
    }

    $list[] = [
        'type'     => 0, //消息来源 1 客服/供货商 0 客户
        'time'     => $row['addtime'], //时间
        'content'  => $row['name'], //回复内容
        'avatar'   => $myimg, //头像
        'nickname' => $site['user'], //用户名
    ];

    $arr = explode('*', $row['content']);
    foreach ($arr as $key => $value) {
        if ($value) {
            $arr2 = explode('^', $value, 3);
            if (count($arr2) == 3) {
                $list[] = [
                    'type'     => intval($arr2[0]), //消息来源 1 客服/供货商 0 客户
                    'time'     => $arr2[1], //时间
                    'content'  => $arr2[2], //回复内容
                    'avatar'   => ($arr2[0] == 1 ? $kfimg : $myimg), //头像
                    'nickname' => ($arr2[0] == 1 ? '供货商' . $masterrow['user'] : $site['user']), //用户名
                ];
            }
        }
    }
    success('成功', [
        'list' => $list,
        'row'  => $row,
    ]);
} elseif ($act == 'getSelectList') {
    $rows = [
        [
            'name'  => '所有状态',
            'type'  => 'warning',
            'count' => Db::name('workorder')->where([
                'sid' => $masterrow['zid'],
            ])->count('id'),
            'id'    => -1,
        ],
        [
            'name'  => '待处理',
            'type'  => 'info',
            'count' => Db::name('workorder')->where([
                'sid'    => $masterrow['zid'],
                'status' => 0,
            ])->count('id'),
            'id'    => 0,
        ],
        [
            'name'  => '处理中',
            'type'  => 'primary',
            'count' => Db::name('workorder')->where([
                'sid'    => $masterrow['zid'],
                'status' => 2,
            ])->count('id'),
            'id'    => 2,
        ],
        [
            'name'  => '已完成',
            'type'  => 'success',
            'count' => Db::name('workorder')->where([
                'sid'    => $masterrow['zid'],
                'status' => 1,
            ])->count('id'),
            'id'    => 1,
        ],
    ];
    success('成功', [
        'rows' => $rows,
    ]);
}
