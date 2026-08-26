<?php
require './inc.php';

$trade_no = isset($_GET['trade_no']) ? input('get.trade_no') : exit('{"code":-1,"msg":"No trade_no"}');

@header('Content-Type: text/html; charset=UTF-8');

$row = $DB->get_row("SELECT * FROM cmy_pay WHERE trade_no= ? limit 1 for UPDATE", [$trade_no]);

if ($row['tid'] == -1) {
    $link = $baseurl . '/user/';
} elseif ($row['tid'] == -2) {
    $link = $baseurl . '/user/regok.php?orderid=' . $trade_no;
} else {
    $link = $baseurl . '/?buyok=1';
}
$status = 0;
if ($row['status'] >= 1) {
    $status = 1;
}

if ($status !== 1 && addslashes($_GET['type']) == 'qqpay' && $conf['qqpay_api'] == 1) {
    include_once './qqpay/qpayQuery.class.php';
    $QpayQuery         = new QpayQuery($trade_no);
    $result            = $QpayQuery->check();
    $result['backurl'] = $link;
    $result['date']    = $date;
    if ($result['code'] == 1) {
        if ($row['status'] == 0) {
            if ($DB->exec("UPDATE `pre_pay` set `status` ='1' where `trade_no`=:trade_no limit 1", [':trade_no' => $trade_no])) {
                $DB->query("UPDATE `pre_pay` set `endtime` = ? where `trade_no`= ? limit 1", [$date, $trade_no]);
                if ($row['tid'] > 0) {
                    $row2 = $DB->get_row("SELECT * FROM `pre_orders` WHERE `payorder`= ? limit 1", [$out_trade_no]);
                    if (!is_array($row2)) {
                        $orderid = processOrderAll($row);
                    }
                } else {
                    processOrderAll($row);
                }
            }
        }
    }
    @pay_update($trade_no, $date . '：' . json_encode($result));
    exit(json_encode($result));
} elseif ($status !== 1 && addslashes($_GET['type']) == 'alipay' && $conf['alipay_api'] == 3) {
    require_once SYSTEM_ROOT . "f2fpay/AlipayTradeService.php";
    try {
        //通过商户订单号查询支付宝官方，百分百准确
        $alipaySevice = new AlipayTradeService($config);
        $arr          = $alipaySevice->orderQueryOut($trade_no);
        if (is_object($arr)) {
            if ($arr->code == 10000) {
                if ($arr->trade_status == 'TRADE_SUCCESS') {
                    //已付款
                    if ($DB->exec("UPDATE `pre_pay` set `status` ='1' where `trade_no`=:trade_no limit 1", [':trade_no' => $trade_no])) {
                        $DB->query("UPDATE `pre_pay` set `endtime` = ? where `trade_no`= ? limit 1", [$date, $trade_no]);
                        if ($row['tid'] > 0) {
                            $row2 = $DB->get_row("SELECT * FROM `pre_orders` WHERE `payorder`= ? limit 1", [$out_trade_no]);
                            if (!is_array($row2)) {
                                $orderid = processOrderAll($row);
                            }
                        } else {
                            processOrderAll($row);
                        }
                    }
                    $result = ['code' => 1, 'msg' => '已扫码，付款成功', 'backurl' => $link];
                } else {
                    //待付款
                    $result = ['code' => 0, 'msg' => '已扫码，等待付款中'];
                }

            } else {
                //待扫码
                $result = ['code' => 0, 'msg' => '未扫码，等待扫码中'];
            }
        } else {
            //错误
            $result = ['code' => 0, 'msg' => '查询订单错误，请联系开发者处理', 'type' => gettype($arr)];
        }
        if ($webConfig['debug']) {
            addWebLog('当面付主动查询', "result：" . json_encode($arr));
        }

        exit(json_encode($result));
    } catch (\Exception $e) {
        @pay_update($trade_no, $e->getMessage());
        if ($webConfig['debug']) {
            addWebLog('当面付主动查询', "查询异常：" . $e->getMessage());
        }
        $result = ['code' => 0, 'msg' => '错误：' . $e->getMessage()];
        exit(json_encode($result));
    }

} else {
    $row = $DB->get_row("SELECT * FROM cmy_pay WHERE trade_no= ? limit 1", array($trade_no));
    if ($row['domain'] && $row['domain'] != $_SERVER['HTTP_HOST'] && strpos($row['domain'], '.') !== false) {
        $baseurl = 'http://' . $row['domain'] . '/';
    } else {
        $baseurl = '../';
    }

    if ($row['status'] >= 1) {
        exit('{"code":1,"msg":"付款成功","backurl":"' . $link . '"}');
    } else {
        exit('{"code":-1,"msg":"未付款"}');
    }
}
