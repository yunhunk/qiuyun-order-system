<?php
if (!defined('IN_CRONLITE')) {
    exit();
}
/** @var $account array */
$account = [];
/** @var $userrow array */
$userrow = [];
/** @var $userrow array */
$masterrow = [];
/** @var $isLogin int */
$isLogin = 0;
/** @var $isLogin2 int */
$isLogin2 = 0;
/** @var $isLogin3 int */
$isLogin3 = 0;
/** @var $is_admin int */
$is_admin = 0;
/** @var $is_account int */
$is_account = 0;
/** @var $is_user int */
$is_user = 0;
if (isset($_COOKIE["admin_token"])) {
    $token            = authcode(daddslashes($_COOKIE['admin_token']), 'DECODE', SYS_KEY);
    list($user, $sid) = explode("\t", $token);
    $session          = md5($conf['adm_user'] . $conf['adm_pwd'] . $password_hash);
    if ($session === $sid) {
        $isLogin  = 1;
        $is_admin = 1;
        //登录IP一致性验证
        if ($conf['adm_login_checkip'] == 1 && $conf['admin_loginip'] !== x_real_ip()) {
            $isLogin  = 0;
            $is_admin = 0;
        }
    } else {
        unset($_COOKIE['admin_token']);
    }
}

if (isset($_COOKIE['account_token'])) {
    $token           = authcode(daddslashes($_COOKIE['account_token']), 'DECODE', SYS_KEY);
    list($aid, $sid) = explode("\t", $token);
    if ($account = $DB->get_row("SELECT * from cmy_admin where aid=:aid limit 1", [':aid' => addslashes($aid)])) {
        $session = md5($account['user'] . $account['pwd'] . $password_hash);
        if ($session === $sid && $account['status'] == 1 && $conf['account_' . $account['uid'] . '_loginip'] === x_real_ip()) {
            $isLogin    = 1;
            $is_account = 1;
        } else {
            $isLogin = 0;
            unset($_COOKIE['account_token']);
        }
    } else {
        unset($_COOKIE['account_token']);
    }
}

$user_token = isset($_COOKIE["user_token"]) ? $_COOKIE["user_token"] : null;

if ($user_token) {
    $token           = authcode(daddslashes($user_token), 'DECODE', SYS_KEY);
    list($zid, $sid) = explode("\t", $token);
    if ($userrow = $DB->get_row("SELECT * from cmy_site where zid=:zid limit 1", [':zid' => addslashes($zid)])) {
        $session = md5($userrow['user'] . $userrow['pwd'] . $password_hash);
        if ($session === $sid && $userrow['status'] == 1) {
            $isLogin2 = 1;
            $is_user  = 1;
        } else {
            unset($_COOKIE['user_token']);
        }
    }
}

$services = array_keys($_SERVER);

if (in_array('HTTP_AUTHORIZATION', $services)) {
    $master_token = trim(str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']));
} else {
    $master_token = isset($_COOKIE["master_token"]) ? $_COOKIE["master_token"] : null;
}

if ($master_token) {
    $token           = authcode(daddslashes($master_token), 'DECODE', SYS_KEY);
    list($zid, $sid) = explode("\t", $token);
    $masterrow       = $DB->get_row("SELECT * from `cmy_master` where zid=:zid limit 1", [':zid' => addslashes($zid)]);
    if ($masterrow) {
        $session = md5($masterrow['user'] . $masterrow['pwd'] . $password_hash);
        if ($session === $sid && $masterrow['status'] == 1) {
            $isLogin3 = 1;
            $is_user  = 1;
        } else {
            unset($_COOKIE['user_token']);
        }
    }
}
