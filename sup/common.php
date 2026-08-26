<?php

// 公共文件

include dirname(__DIR__) . "/includes/common.php";

if (!$isLogin3) {
    if (IS_AJAX) {
        exit('{"code":-1,"msg":"未登录"}');
    } else {
        showmsg('未登录', 4, true);
    }
}

if ($masterrow['master_open'] != 1) {

    if (IS_AJAX) {
        exit('{"code":-1,"msg":"未开通供货商权限"}');
    } else {
        showmsg('未开通供货商权限', 3, true);
    }
}

// 处理公共参数
$page = intval(input('page'));
$page = $page > 0 ? $page : 1;

$pagesize = 15;
if (isset($_GET['pagesize']) || isset($_POST['pagesize'])) {
    $pagesize = intval(input('pagesize'));
} elseif (isset($_GET['pageSize']) || isset($_POST['pageSize'])) {
    $pagesize = intval(input('pageSize'));
}

$offset = 0;
if ($page > 1) {
    $offset = ($page - 1) * $pagesize;
}

$act = isset($_GET['act']) ? addslashes($_GET['act']) : null;

/**
 * 输出成功json数据
 *
 * @param string $msg
 * @param array $data
 * @return void
 */
function success($msg = '', $data = [])
{
    exit(json_encode([
        'code'      => 0,
        'message'   => $msg,
        'msg'       => $msg,
        'data'      => $data,
        'timestamp' => time(),
    ], 256));
}

/**
 * 输出失败json数据
 *
 * @param string $msg
 * @param array $data
 * @return void
 */
function error($msg = '', $data = [])
{
    exit(json_encode([
        'code'      => -1,
        'message'   => $msg,
        'msg'       => $msg,
        'data'      => $data,
        'timestamp' => time(),
    ], 256));
}
