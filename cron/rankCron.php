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
if ($act == 'rankCron') {
    $is_lastDay = isset($_GET['lastDay']) && $_GET['lastDay'] ? 1 : 0;
    if ($conf['fenzhan_rank_open'] != 1) {
        exit('未开启分站排行奖励！');
    }

    $rank_time = $conf['fenzhan_rank_time'];

    if (!preg_match("/^([0-9]{1,2})\:([0-9]{1,2})\:([0-9]{1,2})$/", $rank_time)) {
        exit('奖励发放时间格式不正确，正确格式如=> 23:58:00！');
    }

    if (empty($rank_time)) {
        exit('未设置奖励发放时间！');
    }

    $rank_limit = $conf['fenzhan_rank_limit'] ? $conf['fenzhan_rank_limit'] : '5';
    $rank_rate  = $conf['fenzhan_rank_rate'] > 0 ? $conf['fenzhan_rank_rate'] : '3';
    if ($rank_rate < 1 && $rank_rate > 0) {
        $rank_rate = 1;
    }

    if ($rank_rate == 0) {
        exit('奖励百分比为0，无需发放奖励！');
    }

    preg_match("/^([0-9]{1,2})\:([0-9]{1,2})\:([0-9]{1,2})$/", $rank_time, $matchs);
    if ($matchs[1] < 23) {
        $is_lastDay = true;
    }
    $sql2 = '';
    if ($is_lastDay) {
        $thtime  = date("Y-m-d") . " 00:00:00";
        $thtime2 = date("Y-m-d", strtotime('-1 day')) . ' 00:00:00';
        $sql2    = '  addtime>:thtime2 AND addtime<:thtime';
        $sqlData = [':thtime2' => $thtime2, ':thtime' => $thtime, ':rank_limit' => $rank_limit];
    } else {
        if (date("H:i:s") <= $rank_time) {
            exit('时间未到，请在' . $rank_time . '后再执行发放！');
        }

        $thtime = date("Y-m-d") . " 00:00:00";
        $count  = $DB->count("SELECT count(*) from pre_points where action='奖励' and addtime>'{$thtime}'");
        if ($count > 0) {
            exit('已经发放过奖励了，下次再来~');
        }

        $sql2    = ' addtime>:thtime';
        $sqlData = [':thtime' => $thtime, ':rank_limit' => $rank_limit];
    }

    if ($conf['fenzhan_rank_user'] == 1) {
        $sql = "SELECT A.zid,(SELECT b.sitename FROM pre_site as b where A.zid=b.zid) as sitename,count(*) as count,sum(A.`money`) as cmoney from pre_orders as A where {$sql2} and zid>1 and (SELECT C.is_rank FROM pre_tools AS C where A.tid=C.tid)=1 group by zid order by cmoney desc limit :rank_limit";
    } else {
        $sql = "SELECT a.zid,(SELECT b.sitename FROM pre_site as b where a.zid=b.zid) as sitename,count(*) as count,sum(a.`money`) as cmoney from pre_orders as a where {$sql2} and zid>1 and (select c.power from pre_site as c where a.zid=c.zid)>0 AND  (select `pre_tools`.`is_rank` from pre_tools where a.tid=`pre_tools`.`tid`)=1 group by zid order by cmoney desc limit :rank_limit";
    }

    $rs = $DB->query($sql, $sqlData);
    if ($rs !== false) {
        $data = $DB->fetchAll($rs);
        $i    = 1;
        foreach ($data as $res) {
            $point = 0;
            if ($res['cmoney'] > 0) {
                $point = $res['cmoney'] * $rank_rate / 100;
            }

            if ($point > 0) {
                $DB->query("update pre_site set `money`=`money`+" . $point . " where zid='" . $res['zid'] . "' limit 1");
                $bz = "恭喜您销售额获得今天第" . $i . "名，本站为此奖励您" . $point . "元，已发放到余额，继续加油哦！";
                addPointLogs($res['zid'], $point, '奖励', $bz);
            }
            $i++;
        }
        exit("奖励发放完毕！当前时间：" . $date . "\n请注意，如果本次是发放的是昨天的奖励，请不要再重复访问，否则会重复发放");
    } else {
        exit('奖励获取失败！错误码：' . $DB->error());
    }
} else {
    exit('No act！');
}
