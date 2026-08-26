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

if (!function_exists('getTimeToDayInFloat')) {
    /**
     * 时间戳转天数
     * @param  integer $time 时间戳
     * @return float
     */
    function getTimeToDayInFloat($time = 0)
    {
        if ($time <= 0) {
            return 0;
        }
        return sprintf('%.2f', $time / 86400);
    }
}

@header('Content-Type: text/html; charset=UTF-8');
if (empty($conf['cronkey'])) {
    exit("请先设置好监控密钥");
}

if ($conf['cronkey'] != $_GET['key']) {
    exit("监控密钥不正确");
}

$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
if ($act == 'kashangCron') {
    $num                = 0;
    $succ               = 0;
    $warn               = 0;
    $datalist           = "";
    $cron_order_timeout = $conf['cron_order_timeout'] > 0 ? intval($conf['cron_order_timeout']) : 7;
    $cron_order_zt      = intval($conf['cron_order_zt']);

    try {
        $InfoControler = new \core\InfoControler();
    } catch (Exception $e) {
        exit('同步订单状态失败，' . $e->getMessage());
    }
    $page = intval($conf['status_cron_page']);
    // $offset = $page * 10;
    // if ($page * 10 > $count) {
    //     saveSetting("status_cron_page", '0');
    // } else {
    //     saveSetting("status_cron_page", $page + 1);
    // }

    $count = 0;
    $CACHE->clear();
    $uptime = time() - 60;
    $rs     = $DB->select("SELECT * FROM `pre_orders` WHERE `status`=2 AND (`uptime`<='{$uptime}' OR `uptime` IS NULL) ORDER BY rand() LIMIT 10");
    $sql    = "SELECT * FROM `pre_orders` WHERE `status`=2 AND (`uptime`<='{$uptime}' OR `uptime` IS NULL) ORDER BY rand() LIMIT 10";
    if (is_array($rs)) {
        $count    = count($rs);
        $datalist = "监控总数：" . $count . "；\n<br>";
        foreach ($rs as $key => $res) {
            $orderid = $res['id'];
            $nowTime = time();
            if (!empty($res['djorder'])) {
                $tool = $DB->get_row("SELECT * from pre_tools where tid= ? limit 1", [$res['tid']]);
                if ($tool && $tool['is_curl'] == 2) {
                    $shequ        = $DB->get_row("SELECT * from pre_shequ where id= ? limit 1", [$tool['shequ']]);
                    $shequ["url"] = shequ_url_parse($shequ);

                    if ($shequ && $shequ['type'] == 9) {
                        $num++;
                        $data = $InfoControler->orderjk_kashang($res, $shequ);
                    } else {
                        $num++;
                        $data = $InfoControler->query_extend($res, $shequ);
                    }

                    if (is_array($data)) {
                        if ($data['code'] == 0) {
                            $succ++;
                        } else {
                            $warn++;
                        }
                        $datalist .= "【" . getShequTypeName($shequ['type']) . "】订单号" . $res['id'] . "的结果：" . $data['msg'] . "\n<br>";
                    } else {
                        $datalist .= "【" . getShequTypeName($shequ['type']) . "】订单号" . $res['id'] . "的结果：" . (string) $data . "\n<br>";
                    }
                } else {
                    $datalist .= "订单号" . $res['id'] . "的结果：该订单对应商品未对接无法同步\n<br>";
                }
            } else {
                $datalist .= "订单号" . $res['id'] . "的结果：该订单无对接订单号\n<br>";
            }
            $DB->query("UPDATE `pre_orders` set `uptime`='{$nowTime}' WHERE `id`='{$orderid}'");
            if ($res['uptime'] > 0 && time() - $res['uptime'] < 3600) {
                $orderDay = getTimeToDayInFloat(time() - strtotime($res['addtime']));
                if ($orderDay > $cron_order_timeout) {
                    $succ++;
                    if ($cron_order_zt == 1) {
                        $DB->query("UPDATE `pre_orders` set `status`='1',`bz`='{$date}：超过{$cron_order_timeout}天处理中，自动改为已完成' WHERE `id`='{$orderid}'");
                        $datalist .= "【订单异常】订单号" . $res['id'] . "超过{$cron_order_timeout}天仍然处理中，已自动改为已完成\n<br>";
                        continue;
                    } elseif ($cron_order_zt == 0) {
                        $DB->query("UPDATE `pre_orders` set `status`='3',`bz`='{$date}：超过{$cron_order_timeout}天处理中，自动改为异常' WHERE `id`='{$orderid}'");
                        $datalist .= "【订单异常】订单号" . $res['id'] . "超过7天仍然处理中，已自动改为异常中\n<br>";
                        continue;
                    }
                }
            }
        }
    }

    $msg = "共监控" . $count . "个处理中的订单，已完成" . $succ . "个，未完成" . $warn . "个<br>\n执行SQL:" . $sql . "<br>\n" . $datalist;
    exit($msg);

} else {
    exit('No act！');
}
