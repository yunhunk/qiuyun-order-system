<?php

//---------------------------------------------------------
//QQ钱包支付即时到帐支付后台回调示例，商户按照此文档进行开发即可
//---------------------------------------------------------
require_once "./inc.php";
include_once ROOT . 'other/qqpay/qpayNotify.class.php';

@header("HTTP/1.1 885");
$DeBug = 1;

$qpayNotify = new QpayNotify();
$result     = $qpayNotify->getParams();

@header('Content-Type: text/html; charset=UTF-8');
//判断签名
if ($qpayNotify->verifySign() && $conf['qqpay_api'] == 1) {

    //判断签名及结果（即时到帐）
    if ($result['trade_state'] == "SUCCESS") {
        //商户订单号
        $out_trade_no = daddslashes($result['out_trade_no']);
        //------------------------------
        //处理业务开始
        //------------------------------
        $DB->transaction();
        try {
            $srow = $DB->get_row("SELECT * FROM cmy_pay WHERE trade_no= ? limit 1 for update", [$out_trade_no]);
            if ($srow['status'] == 0) {
                if ($DB->exec("UPDATE `pre_pay` set `status` ='1' where `trade_no`=:trade_no limit 1", [':trade_no' => $out_trade_no]) > 0) {
                    $DB->query("UPDATE `pre_pay` set `endtime` = ? where `trade_no`= ? limit 1", [$date, $out_trade_no]);
                    processOrderAll($srow);
                }
            }
            $DB->commit();
            //------------------------------
            //处理业务完毕
            //------------------------------
            echo "<xml>
<return_code>SUCCESS</return_code>
<return_msg>通知成功</return_msg>
</xml>";
        } catch (\Throwable $th) {
            $DB->rollback();
            echo $th->getMessage();
            echo "<xml>
<return_code>SUCCESS</return_code>
<return_msg>系统错误, " . $th->getMessage() . "</return_msg>
</xml>";
        }
    } else {
        @header("HTTP/1.1 403 Forbidden");
        echo "<xml>
<return_code>FAIL</return_code>
<return_code>未成功付款</return_code>
</xml>";
    }

} else {
    //回调签名错误
    @header("HTTP/1.1 500 Internal Server Error");
    echo "<xml>
<return_code>FAIL</return_code>
<return_msg>签名失败</return_msg>
</xml>";
}
