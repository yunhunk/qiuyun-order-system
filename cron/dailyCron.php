<?php
// * 系统任务监控文件 by 星河云Plus
// * 说明：用于网站的各种需要监控任务的处理接口
// * 监控频率建议5~30分钟一次
// * 支付补单监控地址：/cron/payCron.php?act=payCron&key=监控密钥
// * 订单补单监控地址：/cron/orderCron.php?act=orderCron&key=监控密钥
// * 商品价格监控地址：/cron/priceCron.php?act=priceCron&key=监控密钥
// * 卡商订单监控地址：/cron/kashangCron.php?act=kashangCron&key=监控密钥
// * 排行奖励监控地址：/cron/rankCron.php?act=rankCron&key=监控密钥
// * 日常清理监控地址：/cron/dailyCron.php?act=daily&key=监控密钥
// * 注意：千万不要监控太快或使用多节点监控！！！否则可能会出现问题且占用服务器过多性能

if (preg_match('/spider/', $_SERVER['HTTP_USER_AGENT'])) {
    exit;
}

$is_cron = true;
include "../includes/common.php";

if (function_exists("set_time_limit")) {
    @set_time_limit(0);
}

if (function_exists("ignore_user_abort")) {
    @ignore_user_abort(true);
}

@header('Content-Type: text/html; charset=UTF-8');
if (empty($conf['cronkey'])) {
    exit("请先设置好监控密钥");
}

if ($conf['cronkey'] != $_GET['key']) {
    exit("监控密钥不正确");
}

$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
if ($act == 'daily') {
    $cron_lasttime = $DB->get_column("SELECT v FROM pre_config WHERE k='daily_lasttime' LIMIT 1");
    if (time() - strtotime($cron_lasttime) < 3600 * 12) {
        exit('日常维护任务今天已执行过');
    }

    saveSetting('daily_lasttime', $date);
    $DB->query("DELETE FROM `pre_pay` WHERE addtime<'" . date("Y-m-d H:i:s", strtotime("-30 days")) . "'");
    $sq1 = $DB->affected();
    $DB->query("DELETE FROM `pre_pay` WHERE addtime<'" . date("Y-m-d H:i:s", strtotime("-3 hours")) . "' and status=0");
    $sq2 = $DB->affected();
    $DB->query("DELETE FROM `pre_cart` WHERE addtime<'" . date("Y-m-d H:i:s", strtotime("-30 days")) . "'");
    $sq3 = $DB->affected();
    $DB->query("DELETE FROM `pre_cart` WHERE addtime<'" . date("Y-m-d H:i:s", strtotime("-12 hours")) . "' and status<2");
    $sq4 = $DB->affected();
    $DB->query("OPTIMIZE TABLE `pre_pay`");
    $DB->query("DELETE FROM `pre_giftlog` WHERE addtime<'" . date("Y-m-d H:i:s", strtotime("-1 days")) . "'");
    $sq5 = $DB->affected();
    $DB->query("OPTIMIZE TABLE `pre_giftlog`");
    $DB->query("DELETE FROM `pre_invitelog` WHERE date<'" . date("Y-m-d H:i:s", strtotime("-1 days")) . "'");
    $sq6 = $DB->affected();
    $DB->query("OPTIMIZE TABLE `pre_invitelog`");
    $count = $sq1 + $sq2 + $sq3 + $sq4 + $sq5 + $sq6;
    exit('日常维护任务已成功执行，本次共清理' . $count . '条数据');
} else {
    exit('No act！');
}
