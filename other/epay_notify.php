<?php
/* *
 * 功能：彩虹易支付服务器异步通知页面
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 该页面调试工具请使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyNotify
 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
 */
error_reporting(0);

define('CLASS_AUTOLOAD', false);

include_once __DIR__ . "/inc.php";

if (!defined('SYSTEM_ROOT')) {
    exit('fail，当前环境异常导致程序不完整，请检查该站点是否开启缓存加速或CDN加速，如有请先关闭后再试！');
}

$out_trade_no = daddslashes($_GET['out_trade_no']);

$srow = $DB->get_row("SELECT * FROM cmy_pay WHERE trade_no='{$out_trade_no}' limit 1");
if (!$srow) {
    showalert('该支付订单不存在', 4, 'shop');
}

if ($srow['channel_id'] > 0) {
    $channel = \core\Db::name('channel')->where([
        'id' => $srow['channel_id'],
    ])->find();
    if ($channel) {
        $conf['epay_pid'] = $channel['appid'];
        $conf['epay_key'] = $channel['appkey'];
        $payapi           = $channel['appurl'];
    }
} elseif ($conf['codepay_type'] == 1) {
    $type = strtolower(input('get.type', 1));
    if ($type && $conf[$type . '_api'] == 5) {
        //码支付兼容模式
        $payapi           = $codepayapi;
        $conf['epay_pid'] = $conf['codepay_id'];
        $conf['epay_key'] = $conf['codepay_key'];
    }
}

if (isset($webConfig['debug']) && $webConfig['debug'] == 1) {
    @addWebLog('易支付异步回调参数', '', 'Pay');
}

require_once SYSTEM_ROOT . "epay/epay.config.php";
require_once SYSTEM_ROOT . "epay/epay_notify.class.php";

//计算得出通知验证结果
$alipayNotify  = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();

if (isset($webConfig['debug']) && $webConfig['debug'] == 1) {
    @addWebLog(
        '易支付异步回调结果',
        '订单号【' . daddslashes($_GET['out_trade_no']) . '】验证结果 => ' . ($verify_result ? '成功' : '失败'),
        'Pay'
    );
}
if ($verify_result) {
    //验证成功
    //商户订单号
    // if ($conf['alipay_api'] == 2 || $conf['qqpay_api'] == 2 || $conf['wxpay_api'] == 2) {

    // }

    //支付宝交易号

    $trade_no = $_GET['trade_no'];

    //交易状态
    $trade_status = $_GET['trade_status'];

    //金额
    $money = $_GET['money'];
    if (!preg_match('/^[0-9\.]+$/', $money)) {
        echo "success";
    } else {
        // $DB->transaction();
        try {
            if (is_array($srow) && $_GET['trade_status'] == 'TRADE_SUCCESS') {
                if ($srow['status'] == 0 && $srow['money'] == $money) {
                    //付款完成后，支付宝系统发送该交易状态通知
                    $sql        = "UPDATE `pre_pay` set `status` ='1' where `trade_no`=:trade_no";
                    $sql_params = [':trade_no' => $out_trade_no];
                    if ($DB->exec($sql, $sql_params)) {
                        $sql2        = "UPDATE `pre_pay` set `endtime` = :endtime where `trade_no`=:trade_no limit 1";
                        $sql2_params = [
                            ':endtime'  => $date,
                            ':trade_no' => $out_trade_no,
                        ];
                        $DB->query($sql2, $sql2_params);
                        try {
                            $orderid = processOrderAll($srow);
                        } catch (\Exception $e) {
                            $callInfo = '订单号【' . $out_trade_no . '】验证成功 => 创建订单失败，' . $e->getMessage();
                        }
                    }
                } else {
                    $callInfo = '订单号【' . $out_trade_no . '】验证成功 => 创建订单失败，订单已回调！';
                }

            } elseif (!is_array($srow)) {
                $callInfo = '订单号【' . $out_trade_no . '】验证成功 => 创建订单失败，订单号不存在！';
            } else {
                $callInfo = '订单号【' . $out_trade_no . '】验证成功 => 创建订单失败，订单付款未成功！';
            }

            echo "success";
        } catch (\Throwable $th) {
            // $DB->rollback();
            echo '系统错误,' . $th->getMessage();
        }
    }
} else {
    //验证失败
    echo "fail";
}
