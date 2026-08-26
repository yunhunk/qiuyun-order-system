<?php
/* *
 * 码支付异步通知页面
 */

require_once "./inc.php";

require_once SYSTEM_ROOT . "codepay/codepay_config.php";

if ($webConfig['debug'] == true) {
    addWebLog('码支付异步回调', json_encode($_SERVER) . "\n数据：" . file_get_contents("php://input"));
}

function getAppToken($queryString = '', $config)
{
    $s = $config['AppId'] . $config['AppSecret'];
    $s .= $queryString . $config['time'];
    return sha1($s);
}

$queryString = $_SERVER['REQUEST_URI'];
$config      = [
    'AppId'     => $codepay_config['id'],
    'AppSecret' => $codepay_config['key'],
    'time'      => isset($_SERVER['HTTP_APPTIMESTAMP']) ? $_SERVER['HTTP_APPTIMESTAMP'] : time(),
];

$mySign = getAppToken($queryString, $config);
$AppId  = isset($_SERVER['HTTP_APPID']) ? $_SERVER['HTTP_APPID'] : '';
$sign   = isset($_SERVER['HTTP_APPTOKEN']) ? $_SERVER['HTTP_APPTOKEN'] : '';
if ($conf['alipay_api'] != 5 && $conf['qqpay_api'] != 5 && $conf['wxpay_api'] != 5) {
    $result = [
        'code' => 101,
        'msg'  => '通知无效',
    ];
} elseif (empty($AppId) || $AppId != $codepay_config['id'] || $mySign != $sign) {
    //不合法的数据
    $result = [
        'code' => 101,
        'msg'  => '验证失败',
    ];
} else {
    //合法的数据
    if (isset($_POST['outTradeNo']) || isset($_POST['outTradeNO'])) {
        $queryArr = $_POST;
    } elseif (isset($_GET['outTradeNo']) || isset($_GET['outTradeNO'])) {
        $queryArr = $_GET;
    } else {
        $queryArr = json_decode(file_get_contents("php://input"), true);
    }

    if (isset($queryArr['outTradeNo'])) {
        $out_trade_no = daddslashes($queryArr['outTradeNo']);
    } elseif (isset($queryArr['outTradeNO'])) {
        $out_trade_no = daddslashes($queryArr['outTradeNO']);
    }

    if (isset($out_trade_no)) {
        $srow = $DB->get_row("SELECT * FROM cmy_pay WHERE trade_no= ? limit 1 for update", [$out_trade_no]);
        if (is_array($srow) && $srow['status'] == 0) {
            if ($DB->exec("UPDATE `pre_pay` set `status` ='1' where `trade_no`=:trade limit 1", [':trade' => $out_trade_no]) > 0) {
                $DB->query("UPDATE `pre_pay` set `endtime` = ? where `trade_no`= ? limit 1", array($date, $out_trade_no));
                processOrderAll($srow);
            }
        }
        $result = [
            'code' => 100,
            'msg'  => 'succ',
        ];
    } else {
        $result = [
            'code' => 101,
            'msg'  => '缺少商户订单号',
        ];
    }
}

exit(json_encode($result));
