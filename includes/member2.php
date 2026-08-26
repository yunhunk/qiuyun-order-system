<?php
if (!defined('IN_CRONLITE')) {
    exit();
}
$account    = [];
$userrow    = [];
$isLogin    = 0;
$isLogin2   = 0;
$is_admin   = 0;
$is_account = 0;
$is_user    = 0;
if (isset($_COOKIE["user_token"])) {
    $token           = authcode(daddslashes($_COOKIE['user_token']), 'DECODE', SYS_KEY);
    list($zid, $sid) = explode("\t", $token);
    if ($userrow = $DB->get_row("SELECT * from `pre_site` where zid=:zid limit 1", [':zid' => addslashes($zid)])) {
        $session = md5($userrow['user'] . $userrow['pwd'] . $password_hash);
        if ($session === $sid && $userrow['status'] == 1) {
            $isLogin2 = 1;
            $is_user  = 1;
        } else {
            $userrow = [];
            unset($_COOKIE['user_token']);
        }
    }
}
