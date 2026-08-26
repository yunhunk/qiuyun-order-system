<?php
/**
 * @author 星河
 * @desc   屏蔽腾讯电脑管家网址安全检测
 * @version 1.12
 */
if ($nosecu == true) {
    return;
}
//IP屏蔽
$iptables     = '1872850689~1872850943|1700480345|2073511781|3082860377|989548909|992246182|1882984123|3419255553~3419255807|3419274679|3419274497~3419274751|236000768~236001023|992312699|3419245824~3419246079|1728519168~1728520191';
$remoteiplong = bindec(decbin(ip2long(x_real_ip())));
foreach (explode('|', $iptables) as $iprows) {
    if ($remoteiplong == $iprows) {
        exit('欢迎访问！');
    }
    $ipbanrange = explode('~', $iprows);
    if ($remoteiplong >= $ipbanrange[0] && $remoteiplong <= $ipbanrange[1]) {
        exit('欢迎使用！');
    }
}
if (strpos($_SERVER['HTTP_REFERER'], 'urls.tr.com') !== false) {
    $_SESSION['txprotectblock'] = true;
}

//HEADER特征屏蔽
if (!isset($_SERVER['HTTP_ACCEPT']) || preg_match("/manager/", strtolower($_SERVER['HTTP_USER_AGENT'])) || isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] == '' || strpos($_SERVER['HTTP_USER_AGENT'], 'ozilla') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla') === false || preg_match("/Windows NT 6.1/", $_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_ACCEPT'] == '*/*' || preg_match("/Windows NT 5.1/", $_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_ACCEPT'] == '*/*' || preg_match("/vnd.wap.wml/", $_SERVER['HTTP_ACCEPT']) && preg_match("/Windows NT 5.1/", $_SERVER['HTTP_USER_AGENT']) || isset($_COOKIE['ASPSESSIONIDQASBQDRC']) || empty($_SERVER['HTTP_USER_AGENT']) || preg_match("/Alibaba.Security.Heimdall/", $_SERVER['HTTP_USER_AGENT']) || strpos($_SERVER['HTTP_USER_AGENT'], 'wechatdevtools/') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'libcurl/') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'python') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Go-http-client') !== false || $_SESSION['txprotectblock'] == true) {
    exit('欢迎使用！');
}

//HEADER特征屏蔽

if ($_SESSION['txprotectblock'] == true) {
    if (isset($_GET['key'])) {
        if (addslashes($_GET['key']) != $conf['cronkey']) {
            exit('欢迎光临~');
        }
    } else {
        exit('欢迎光临！');
    }
}
