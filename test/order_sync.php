<?php

include_once '../includes/common.php';

$data = $DB->get_row("SELECT * from `pre_pay` where `trade_no`='20221112140103236950'");

include_once '../includes/ajax.class.php';

if ($data) {
    $ret = processOrderAll($data);
    echo '返回：' . $ret . "\n";

    echo '时间：' . msectime() . "\n";
} else {
    echo '订单不存在！' . "\n";
}

function msectime()
{
    list($usec, $sec) = explode(" ", microtime());
    return sprintf('%.0f', (floatval($usec) + floatval($sec)) * 1000);
}
