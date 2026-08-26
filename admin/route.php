<?php
function getPageFile($var = '')
{
    if (!$var) {
        $var = isset($_SERVER['QUERY_STRIN']) ? $_SERVER['QUERY_STRIN'] : '';
    }
    $arr = explode('#', $var, 2);
    if ($arr[1] && preg_match('/\.php/', $arr[1])) {
        $arr2 = explode('?', $arr[1], 2);
        return $arr2;
    }
    return ['', ''];
}

$pageInfo = getPageFile();

if (is_file($pageInfo[0])) {
    $html = include $pageInfo[0];
    echo $html;
} elseif ($pageInfo[0] == '') {
    $html = include 'index.php';
    echo $html;
} else {
    include '../includes/common.php';
    include 'head.php';
    showmsg('该页面不存在！');
}
