<?php
include "../includes/common.php";

checkLogin();

$zid = intval($_GET['zid']);

$userrow = $DB->get_row("select * from pre_site where zid= ? limit 1", array($zid));
if (!$userrow) {
    sysmsg('当前用户不存在！');
}

$session = md5($userrow['user'] . $userrow['pwd'] . $password_hash);
$token   = authcode("{$zid}\t{$session}", 'ENCODE', SYS_KEY);
setcookie("user_token", $token, time() + 604800, '/');
exit("<script language='javascript'>window.location.href='../user/';</script>");
