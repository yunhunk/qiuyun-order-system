<?php
// 提现csv下载

include "../includes/common.php";

$table = input('table');

if ($_GET['type'] == 'alipay') {

    $date      = str_replace("/", "-", $_GET['date']);
    $startTime = $date . ' 00:00:00';
    $endTime   = date("Y-m-d", strtotime($date . " +1 day")) . ' 00:00:00';
    $data      = '';
    $rs        = $DB->query("SELECT pay_account,pay_name,realmoney from pre_{$table} where status=0 and addtime>='$startTime' and addtime<'$endTime' and pay_type=0 order by id asc");
    $i         = 0;
    while ($row = $rs->fetch(PDO::FETCH_ASSOC)) {
        $i++;
        $data .= $i . ',' . $row['pay_account'] . ',' . mb_convert_encoding($row['pay_name'], "GB2312", "UTF-8") . ',' . $row['realmoney'] . ',' . $remark . "\r\n";
    }

    $date = date("Ymd");
    $file = mb_convert_encoding('支付宝批量付款文件模板', "GB2312", "UTF-8") . "\r\n";
    $file .= mb_convert_encoding('序号（必填）,收款方支付宝账号（必填）,收款方姓名（必填）,金额（必填，单位：元）,备注（选填）', "GB2312", "UTF-8") . "\r\n";
    $file .= $data;
    $DB->exec("UPDATE pre_{$table} set `status`=3 where `status`=0 and addtime>='$startTime' and addtime<'{$endTime}' and pay_type=0");
}

$file_name = $table . '_' . $date . '.csv';
$file_size = strlen($file);
header("Content-Description: File Transfer");
header("Content-Type:application/force-download");
header("Content-Length: {$file_size}");
header("Content-Disposition:attachment; filename={$file_name}");
echo $file;
