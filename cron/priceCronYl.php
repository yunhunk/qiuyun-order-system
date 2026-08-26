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
if ($act == "priceCron") {
    if (empty($conf['PriceCronList'])) {
        exit("当前监控分类列表为空！无需同步价格");
    }

    $cron_lasttime = $conf['pricejk_lasttime_yile'];
    $pricejk_time  = $conf['pricejk_time_yile'] >= 60 && $conf['pricejk_time_yile'] <= 120 ? $conf['pricejk_time'] : 60; //监控间隔时间
    if (!isset($_GET['test']) && time() - $cron_lasttime < $pricejk_time) {
        exit('上次更新时间:' . date("Y-m-d H:i:s", $cron_lasttime) . "，监控间隔不能低于{$pricejk_time}秒");
    }

    $cid     = (int) daddslashes($_GET['cid']);
    $success = 0;
    $is_need = 0;

    //单次访问最大更新商品数量
    $maxnum = intval($conf['pricejk_maxnum_yile']);
    if ($maxnum < 120 || $maxnum > 800) {
        $maxnum = 300;
    }

    $shequ_type = [];

    if (isset($_GET['cid']) && $cid > 0) {
        $rs          = $DB->select("SELECT * FROM `pre_tools` WHERE `is_curl`=2 AND `cid`=:cid GROUP BY `shequ`", [':cid' => $cid]);
        $nowCronList = [];
        if ($rs) {
            foreach ($rs as $key => $value) {
                $nowCronList[] = $value['shequ'];
            }
        }

    } else {
        $nowCronList = explode(",", $conf['nowCronListShequ_yile']);
        if (count($nowCronList) == 0 || intval($nowCronList[0]) == 0) {
            $uptime      = time() - 120;
            $rs          = $DB->select("SELECT * FROM `pre_tools` WHERE `is_curl`=2 GROUP BY `shequ`");
            $nowCronList = [];
            if ($rs) {
                foreach ($rs as $key => $value) {
                    if ($value['shequ']) {
                        $nowCronList[] = $value['shequ'];
                    }
                }
            }
        }
    }

    $uptime = time() - 5;
    saveSetting('pricejk_result', 'error');
    $clist = array();

    $ret = "温馨提示：如出现某些商品未监控，请检查是否加入监控分类列表，和社区对接信息是否正常<br>\n";
    if ($conf['tool_price_open'] == 1) {
        $ret .= "温馨提示：当前已开启未设置加价模板时启用默认加价模板，可能对免费商品有影响<br>\n";
    }
    $ret .= "注意：当商战+直客+卡商对接较多遇到部分商品无法更新时，可多搭建一个做中转站分担一部分商品来更新，商品越多可多搭建几个<br>\n";
    $ret .= "当前设定单次最大更新数量：" . $maxnum . "<br>\n";
    $ret .= "建议设置监控周期：1~2分钟/次<br><br>\n";
    $x          = 0;
    $start_time = $now_time = time();
    $num        = count($nowCronList);

    $cids = implode(',', array_unique(explode('|', $conf['PriceCronList'])));

    $pricejk = new \core\InfoControler();
    $data    = "";
    for ($i = 0; $i < $num; $i++) {
        if (time() - $start_time >= 50) {
            //即将超时，退出执行
            break;
        }

        if ($x >= $maxnum) {
            break;
        }

        $success  = 0;
        $shequ_id = $nowCronList[$i];
        $shequ    = $DB->get_row("SELECT * FROM cmy_shequ WHERE `id`= ? ", [$shequ_id]);
        $clist[]  = $shequ_id;
        if (!$shequ) {
            $data .= '【社区ID：' . $shequ_id . '】' . "数据不存在，已跳过\n<br>";
            continue;
        }

        $uptime = $now_time - (isset($shequ['crontime']) && $shequ['crontime'] >= 10 ? $shequ['crontime'] : 60);
        $count  = $DB->count("SELECT count(*) FROM `pre_tools` WHERE `is_curl`=2 AND `uptime`<='{$uptime}' AND `shequ`='{$shequ_id}' and `cid` IN ({$cids})");
        $x      = $x + $count;
        if ($count > 0) {
            try {
                $shequ["url"] = function_exists('shequ_url_parse') ? shequ_url_parse($shequ) : $shequ["url"];
                if ($shequ['type'] == 9 && method_exists($pricejk, 'pricejk_kashang')) {
                    $results = $pricejk->pricejk_kashang($shequ, $cids);
                } elseif (($shequ['type'] == 25 || $shequ['type'] == 26) && method_exists($pricejk, 'pricejk_zhike')) {
                    $results = $pricejk->pricejk_zhike($shequ, $cids);
                } elseif ($shequ['alias']) {
                    $results = $pricejk->pricejk_extend($shequ, $cids);
                } else {
                    $results = "【" . getShequTypeName($shequ['type']) . " =>" . $shequ['url'] . "】该系统不支持该方式更新商品，其他系统请使用通用监控地址";
                }

                if (is_string($results) && strpos($results, '该系统不支持该方式') !== false) {
                    $data .= $results . "\n<br>";
                } else {
                    if (is_array($results)) {
                        if ($results['code'] == 0) {
                            $success += $results['success'];
                        }
                        if (preg_match('/Domain name not found/', $results['msg'])) {
                            $results['msg'] = '该网站已经迁移或已失效，无法访问 ' . $results['msg'];
                        } elseif (preg_match('/timed/', $results['msg'])) {
                            $results['msg'] = '该网站打开超时，无法更新价格' . $results['msg'];
                        }
                        $data .= "【" . getShequTypeName($shequ['type']) . " =>" . $shequ['url'] . "】更新信息： 查询到{$count}个商品，" . $results['msg'] . ($results['warnlist'] ? "\n<br>错误信息：" . htmlspecialchars($results['warnlist']) : "") . "\n<br>";
                    } else {
                        $data .= "【" . getShequTypeName($shequ['type']) . " =>" . $shequ['url'] . "】更新信息： 更新失败\n<br>错误信息：" . htmlspecialchars($results) . "\n<br>";
                    }
                    // $DB->query("UPDATE `pre_tools` SET `uptime`='" . time() . "' WHERE `is_curl`=2 AND `uptime`<='{$uptime}' AND `shequ`='{$shequ_id}' LIMIT 5");
                }

            } catch (\Exception $e) {
                $data .= "【" . getShequTypeName($shequ['type']) . " =>" . $shequ['url'] . "】更新信息： 更新失败\n<br>价格监控出错，" . $e->getMessage() . "\n<br>";
            }
        } else {
            $data .= "【" . getShequTypeName($shequ['type']) . " =>" . $shequ['url'] . "】更新信息：查询到需要更新的商品为0，无需更新（商品监控间隔：{$pricejk_time}秒）\n<br>";
        }
    }

    foreach ($clist as $v) {
        $index = array_search($v, $nowCronList);
        array_splice($nowCronList, $index, 1);
    }

    if (!$cid) {
        saveSetting('nowCronListShequ_yile', implode(',', $nowCronList));
        saveSetting('pricejk_lasttime_yile', time());
    }
    saveSetting('pricejk_result_yile', '正常！上次执行时间：' . $date);

    $CACHE->clear();
    if ($_GET['cid']) {
        $ret .= $data;
        $ret .= "程序执行完毕~执行耗时：" . (time() - $start_time) . "秒";
    } else {
        if (count($nowCronList) != 0) {
            $ret .= $data;
            $ret .= "当前已执行对接站：" . implode(',', $clist) . "\n<br>";
            $ret .= "请再次刷新访问一次，以更新剩余对接站：" . implode(',', $nowCronList) . "\n<br>本次执行耗时：" . (time() - $start_time) . "秒！商品监控间隔：{$pricejk_time}秒";
        } else {
            $ret .= $data;
            $ret .= "当前已执行对接站：" . implode(',', $clist) . "\n<br>";
            $ret .= "共查询到{$x}个商品需要更新，当前已更新完毕，等待下轮更新！\n<br>本次执行耗时：" . (time() - $start_time) . "秒！商品监控间隔：{$pricejk_time}秒";
        }
    }
    echo $ret;
    addPricejkLogs($ret);
} else {
    exit('No act！');
}
