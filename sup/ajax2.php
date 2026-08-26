<?php

include "../includes/common.php";

$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;

@header('Content-Type: text/html; charset=UTF-8');

switch ($act) {
    case 'fastlogin':
        $user = input('user', 1);
        $pwd  = input('pwd', 1);
        $row  = $DB->get_row("SELECT * FROM `pre_master` WHERE user= ? limit 1", array($user));
        if ($row && $user === $row['user'] && checkPwd($pwd, $row['pwd'], $row['salt'])) {

            if ($row['status'] == 0 && $row['user'] == $user) {
                @header('Content-Type: text/html; charset=UTF-8');
                $result = array('code' => -1, "msg" => "当前账户已封禁，无法登陆！<br>关闭原因：" . ($row['closure'] != '' ? $row['closure'] : '账户异常临时封禁处理') . "<br>如有疑问, 请联系站长QQ" . $conf['zzqq'] . "处理");
                setcookie("master_token", "", time() - 604800, '/');
                echo '<meta charset="utf-8"/><script>alert("当前账户已封禁，无法登陆！"); window.history.back();</script>';
                exit;
            }

            $session = md5($user . getEncodePwd($pwd, $row['salt'], $row['pwd']) . $password_hash);
            $token   = authcode($row['zid'] . "\t" . $session, 'ENCODE', SYS_KEY);
            $query   = 'token=' . $token . '&expiretime=' . (time() + 604800) . '&username=' . $user;
            echo '<meta charset="utf-8"/><script>window.location.href="./?mod=fastlogin&' . $query . '";</script>';
        } else {
            echo '<meta charset="utf-8"/><script>alert("用户名或密码错误"); window.history.back();</script>';
        }
        break;
    default:
        exit('{"code":-4,"msg":"No Act"}');
        break;
}
