<?php
/* *
 * 支付宝当面付异步通知页面
 */

require_once "./inc.php";
require_once SYSTEM_ROOT . "f2fpay/config.php";
require_once SYSTEM_ROOT . "f2fpay/AlipayTradeService.php";

//计算得出通知验证结果
$alipaySevice = new AlipayTradeService($config);
//$alipaySevice->writeLog(var_export($_POST,true));
$verify_result = $alipaySevice->check($_POST);

if (isset($webConfig['debug']) && $webConfig['debug'] == 1) {
    @addWebLog(
        '当面付异步同步回调结果',
        '订单号【' . daddslashes($_GET['out_trade_no']) . '】验证结果 => ' . $verify_result ? '成功' : '失败',
        'Pay'
    );
}

if ($verify_result && $conf['alipay_api'] == 3) {
    //验证成功
    //商户订单号

    $out_trade_no = daddslashes($_POST['out_trade_no']);

    //支付宝交易号

    $trade_no = $_POST['trade_no'];

    //交易状态
    $trade_status = daddslashes($_POST['trade_status']);

    //交易金额
    $total_amount = floatval($_POST['total_amount']);

    $srow = $DB->get_row("SELECT * FROM cmy_pay WHERE trade_no= ? limit 1 for update", [$out_trade_no]);
    if ($trade_status == 'TRADE_SUCCESS' && preg_match('/^[\d\.]+$/', $total_amount) && $srow['money'] == $total_amount) {
        //付款完成后，支付宝系统发送该交易状态通知
        if ($srow['status'] == 0) {
            $sql = "UPDATE `pre_pay` set `status` ='1' where `trade_no`=:trade_no limit 1";
            if ($DB->exec($sql, [':trade_no' => $out_trade_no]) > 0) {
                $sql2  = "UPDATE `pre_pay` set `endtime` = :endtime where `trade_no`=:trade_no limit 1";
                $data2 = [
                    ':endtime'  => $date,
                    ':trade_no' => $out_trade_no,
                ];
                $DB->query($sql2, $data2);
                if ($srow['tid'] > 0) {
                    //加一重检测避免重复生成订单
                    $row = $DB->get_row("SELECT * FROM `pre_orders` WHERE `payorder`= ? limit 1", [$out_trade_no]);
                    if (!is_array($row)) {
                        $orderid = processOrderAll($srow);
                    }
                } else {
                    $orderid = processOrderAll($srow);
                }
            }
        }
    }
    echo "success";
} else {
    //验证失败
    echo "fail";
}
