<?php

require_once '../includes/common.php';

if ($conf['captcha_type'] == 2) {
    $time  = time();
    $useZh = ($time % 3 == 0 || $time % 2 == 0 || $time % 6 == 0 || $time % 8 == 0) ? true : false;
} else {
    $useZh = $conf['captcha_type'] == 1 ? true : false;
}

$captcha = captcha_doimg('admin', [
    // 使用中文
    'useZh' => $useZh,
]);
// 输出验证码
$captcha->outPut();
