<?php
/*
 * @Author: 星河 andywickshum@gmail.com 
 * @Date: 2022-08-08 20:35:06
 * @LastEditors: 星河 andywickshum@gmail.com 
 * @LastEditTime: 2022-10-21 12:23:12
 * @FilePath: \开发版\other\epay_return.php
 * @Description:
 *
 * Copyright (c) 2022 by 星河科技, All Rights Reserved.
 */
/* *
 * 功能：彩虹易支付页面跳转同步通知页面
 * 版本：3.3
 * 日期：2012-07-23
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。
 * 该代码仅供学习和研究支付宝接口使用，只是提供一个参考。

 *************************页面功能说明*************************
 * 该页面可在本机电脑测试
 * 可放入HTML等美化页面的代码、商户业务逻辑程序代码
 * 该页面可以使用PHP开发工具调试，也可以使用写文本函数logResult，该函数已被默认关闭，见alipay_notify_class.php中的函数verifyReturn
 */

define('CLASS_AUTOLOAD', false);

include_once __DIR__ . "/inc.php";

if (!defined('SYSTEM_ROOT')) {
    exit('fail，当前环境异常导致程序不完整，请检查该站点是否开启缓存加速或CDN加速，如有请先关闭后再试！');
}

if (isset($webConfig['debug']) && $webConfig['debug'] == 1) {
    @addWebLog('易支付同步回调参数', '', 'Pay');
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

require_once SYSTEM_ROOT . "epay/epay.config.php";
require_once SYSTEM_ROOT . "epay/epay_notify.class.php";

//计算得出通知验证结果
$alipayNotify = new AlipayNotify($alipay_config);

$verify_result = $alipayNotify->verifyReturn();

if (isset($webConfig['debug']) && $webConfig['debug'] == 1) {
    @addWebLog(
        '易支付同步回调结果',
        '订单号【' . daddslashes($_GET['out_trade_no']) . '】验证结果 => ' . $verify_result ? '成功' : '失败',
        'Pay'
    );
}

if ($verify_result && ($conf['alipay_api'] == 2 || $conf['qqpay_api'] == 2 || $conf['wxpay_api'] == 2 || $conf['tenpay_api'] == 2)) {
    //商户订单号

    // if ($out_trade_no == '20240727013305188044') {
    //     die('测试');
    // }

    //支付宝交易号

    $trade_no = $_GET['trade_no'];

    //交易状态
    $trade_status = $_GET['trade_status'];

    //金额
    $money = $_GET['money'];
    if (!preg_match('/^[0-9\.]+$/', $money)) {
        showalert('验证失败，金额格式不正确！', 4, 'shop');
    } else {
        // $DB->transaction();
        try {

            if ($srow) {
                if ($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS') {
                    if ($srow['status'] == 0 && $srow['money'] == $money) {
                        $sql = "UPDATE `pre_pay` set `status` ='1' where `trade_no`=:trade limit 1";
                        if ($DB->exec($sql, [':trade' => $out_trade_no]) > 0) {
                            $sql2        = "UPDATE `pre_pay` set `endtime` = :endtime where `trade_no`=:trade_no limit 1";
                            $sql2_params = [
                                ':endtime'  => $date,
                                ':trade_no' => $out_trade_no,
                            ];
                            $DB->query($sql2, $sql2_params);
                            $orderid = processOrderAll($srow);
                        }
                    }
                }
            }
            // $DB->commit();
            switch ($srow['tid']) {
                case -4:
                    showalert('在线充值成功, 感谢支持!', 1, $out_trade_no, intval($srow['tid']));
                    break;
                case -1:
                    showalert('在线充值成功, 感谢支持!', 1, $out_trade_no, intval($srow['tid']));
                    break;
                case -2:
                    showalert('开通分站成功, 感谢支持!', 1, $out_trade_no, intval($srow['tid']));
                    break;
                default:
                    showalert('您所购买的商品已付款成功，感谢支持！', 1, $out_trade_no, intval($srow['tid']));
                    break;
            }

        } catch (\Exception $th) {
            // $DB->rollback();
            showalert('您所购买的商品生产失败,' . $th->getMessage(), 1, $out_trade_no, intval($srow['tid']));
        }
    }

} else {
    //验证失败
    showalert('验证失败！', 4, 'shop');
}
