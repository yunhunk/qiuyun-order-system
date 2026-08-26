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
if ($act == "orderCron") {
    $orderjk_lasttime = $conf['orderjk_lasttime'];
    if (time() - $orderjk_lasttime < 120) {
        exit('ok');
    }

    require_once SYSTEM_ROOT . 'ajax.class.php';
    $succ  = 0;
    $warn  = 0;
    $num   = 0;
    $limit = isset($_GET['limit']) ? $_GET['limit'] : 10;
    $DB->transaction();
    try {
        $rs = $DB->select("SELECT * FROM `pre_orders` WHERE `status`=0 order by id desc limit {$limit} for update");
        if (false !== $rs) {
            foreach ($rs as $key => $res) {
                $tool = $DB->get_row("SELECT * from cmy_tools where `tid`='" . $res['tid'] . "' limit 1");
                if ($tool['is_curl'] == 2) {
                    $shequ = $DB->get_row("SELECT * from cmy_shequ where `id`='" . $tool['shequ'] . "' limit 1");
                    $num++;
                    if ($res['djzt'] == 1) {
                        continue;
                    }
                    $data = do_orders_all($res['id']);
                    if (stripos($data, '成功') !== false || stripos($data, 'success') !== false) {
                        $succ++;
                    } else {
                        $warn++;
                    }
                }
            }
        }
        $DB->commit();
        saveSetting("orderjk_lasttime", time());
        exit('成功补单' . $succ . '个待处理的对接订单，失败' . $warn . '个,共' . $num . '个');
    } catch (\Throwable $th) {
        //throw $th;
        $DB->rollback();
        exit('补单失败, ' . $th->getMessage());
    }
} else {
    exit('No act！');
}
