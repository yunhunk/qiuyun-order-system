<?php
include "../includes/common.php";

if ($isLogin !== 1) {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

if ($_GET['act'] == 'kms') {
    $tid = intval($_GET['tid']);
    if (isset($_GET['use']) && $_GET['use'] == 1) {
        $sql = " and orderid>0";
    } elseif (isset($_GET['use']) && $_GET['use'] == 0) {
        $sql = " and orderid<1";
    } else {
        $sql = "";
    }

    $rs   = $DB->query("SELECT * FROM pre_faka WHERE tid='$tid'{$sql} order by kid asc");
    $data = '';
    while ($res = $DB->fetch($rs)) {
        $data .= ($res['pw'] ? $res['km'] . '----' . $res['pw'] : $res['km']) . "\r\n";
    }

} else {
    $tid     = intval($_GET['tid']);
    $status  = intval($_GET['status']);
    $sign    = intval($_GET['sign']);
    $orderby = ($_GET['orderby'] == 1) ? "desc" : "asc";

    $tool  = $DB->get_row("SELECT * FROM pre_tools WHERE tid='$tid' limit 1");
    $value = $tool['value'] > 0 ? $tool['value'] : 1;

    $date = date("Y-m-d");
    $data = '';

    $rs = $DB->query("SELECT * FROM pre_orders WHERE tid='{$tid}' and status={$status} order by addtime {$orderby} limit 1000");

    while ($row = $DB->fetch($rs)) {
        $data .= $row['input'] . ($row['input2'] ? '----' . $row['input2'] : null) . ($row['input3'] ? '----' . $row['input3'] : null) . ($row['input4'] ? '----' . $row['input4'] : null) . ($row['input5'] ? '----' . $row['input5'] : null) . '----' . $row['value'] * $value . "\r\n";
        if ($sign > 0) {
            $DB->query("UPDATE `pre_orders` SET status={$sign} where `id`='{$row['id']}'");
        }

    }
}

$file_name = 'output_' . $tid . '_' . $date . '__' . time() . '.txt';
$file_size = strlen($data);
header("Content-Description: File Transfer");
header("Content-Type:application/force-download");
header("Content-Length: {$file_size}");
header("Content-Disposition:attachment; filename={$file_name}");
echo $data;
