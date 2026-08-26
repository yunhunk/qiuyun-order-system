<?php

include "./includes/common.php";
if (function_exists("set_time_limit")) {
    @set_time_limit(0);
}

if (function_exists("ignore_user_abort")) {
    @ignore_user_abort(true);
}

@header('Content-Type: text/html; charset=UTF-8');
if (empty($conf['cronkey'])) {
    exit("请先设置好监控密钥");
}

$num  = 0;
$succ = 0;
$warn = 0;

$rs = $DB->query("SELECT tid FROM pre_tools WHERE name like '%筷手%'");
while ($res = $DB->fetch($rs)) {
    $num++;
    $row = $DB->query("delete from `pre_orders` where `tid`='" . $res['tid'] . "'");
    if ($row) {
        $succ++;
    } else {
        $warn++;
    }

    $row2 = $DB->query("delete from `pre_pay` where `tid`='" . $res['tid'] . "'");
    if ($row2) {
        $succ++;
    } else {
        $warn++;
    }
}
exit('共需删除' . $num . '个订单，成功' . $succ . '个订单');
