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

if (isset($_POST['outTradeNo']) || isset($_POST['outTradeNO'])) {
    $queryArr = $_POST;
} elseif (isset($_GET['outTradeNo']) || isset($_GET['outTradeNO'])) {
    $queryArr = $_GET;
} else {
    $queryArr = json_decode(file_get_contents("php://input"), true);
}

if (isset($queryArr['outTradeNo'])) {
    $out_trade_no = addslashes($queryArr['outTradeNo']);
} elseif (isset($queryArr['outTradeNO'])) {
    $out_trade_no = addslashes($queryArr['outTradeNO']);
}

if (isset($out_trade_no)) {
    $srow = $DB->get_row("SELECT * FROM `pre_pay` WHERE `trade_no`=:trade_no limit 1", [':trade_no' => $out_trade_no]);
}

if ($webConfig['debug'] == true) {
    addWebLog('码支付异步回调', "订单数据：" . json_encode($srow));
}

if ($conf['alipay_api'] != 5 && $conf['qqpay_api'] != 5 && $conf['wxpay_api'] != 5) {
    $result = [
        'code' => 101,
        'msg'  => '通知无效',
    ];
} else {
    //合法的数据
    if (isset($out_trade_no) && is_array($srow)) {
        $result = [
            'code' => 100,
            'msg'  => 'succ',
        ];
    } else {
        $result = [
            'code' => 101,
            'msg'  => '支付验证失败，缺少参数或不正确',
        ];
    }
}

if ($result['code'] == 100 && $srow['tid'] > 0 || $srow['tid'] == -3) {
    showalert('您所购买的商品已付款成功，感谢购买！', 1, $out_trade_no, $srow['tid']);
} elseif ($result['code'] == 100 && $srow['tid'] == -1) {
    showalert('充值' . round($srow['money'], 2) . '元成功，感谢支持与信任！', 1, $out_trade_no, $srow['tid']);
} elseif ($result['code'] == 100 && $srow['tid'] == -2) {
    showalert('开通分站成功，感谢支持与信任！', 1, $out_trade_no, $srow['tid']);
} else {
    showalert($result['msg'], 4, 'shop');
}
