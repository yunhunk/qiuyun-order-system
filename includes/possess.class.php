<?php

use core\Card;

if (!defined('IN_CRONLITE')) {
    exit(0);
}

if (!defined("authcode")) {
    exit(0);
}

if (!isset($isLogin) || !$isLogin) {
    exit(0);
}

if (!function_exists('checkFileSize')) {
    exit(0);
}

checkFileSize();

function getGoodsPrice_jiuwu($config)
{
    $url  = $config['url'] . 'index.php?m=home&c=api&a=user_get_goods_lists_details&Api_UserName=' . urlencode($config['username']) . '&Api_UserMd5Pass=' . md5(strSafeEnCode($config['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1'));
    $data = get_curl($url);
    $arr  = json_decode($data, true);
    if (@array_key_exists('status', $arr) && $arr['status'] == 1) {
        $result = array();
        foreach ($arr['user_goods_lists_details'] as $v) {
            $result[] = array(
                "id"      => $v['id'],
                "type"    => $v['goods_type'],
                "price"   => $v['user_unitprice'],
                "shopimg" => $v['user_unitprice'],
                "minnum"  => $v['minbuynum_0'],
                "maxnum"  => $v['maxbuynum_0'],
            );
        }
        $ret = array('code' => 0, "msg" => 'succ', "data" => $result);
    } elseif (stripos($data, "密码") !== false) {
        $ret = array('code' => -1, "msg" => "账号或密码错误，请确认后再试！", "data" => $data);
    } else {
        $data = str_replace(array("\r\n", "\r", "\n"), "", $data);
        $ret  = array('code' => -1, "msg" => "获取玖伍价格失败，请稍后重试！<br>" . htmlspecialchars($data), "data" => $data);
    }
    return $ret;
}

function do_orders_all($orderid, $is_djorder = false)
{
    global $DB;

    // 开始事务
    $DB->transaction();

    try {
        $srow = $DB->get_row("SELECT * from cmy_orders where `id`='{$orderid}' limit 1");
        $time = intval(time());
        if (!$srow) {
            throw new \Exception("该订单正在处理中, 稍后再试！[404]");
        } elseif (!isset($srow['id'])) {
            throw new \Exception("该订单正在处理中, 稍后再试！[405]");
        } elseif (($time - intval($srow['uptime'])) < 3) {
            throw new \Exception('订单' . $srow['id'] . '提交速度过快，请稍后~上次提交：' . date('Y-m-d H:i:s', $srow['uptime']) . '; 当前时间: ' . date('Y-m-d H:i:s', $time));
        }

        if (!$DB->query("UPDATE `pre_orders` SET `uptime`='{$time}' where `id`= '{$orderid}'")) {
            throw new \Exception(" 该订单更新状态失败，请尝试更新最新版！");
        }

        if ($srow['status'] > 0 && !$is_djorder) {
            if ($srow['djorder']) {
                $tool  = $DB->get_row("SELECT * from cmy_tools where tid='" . $srow["tid"] . "' limit 1");
                $shequ = $DB->get_row("SELECT * from cmy_shequ where id='" . $tool["shequ"] . "' limit 1");
                throw new \Exception("该订单已对接到" . $shequ['url'] . "，订单号" . $srow['djorder']);
            } else {
                throw new \Exception("该订单已处理过了~");
            }
        }

        $tool   = $DB->get_row("SELECT * from cmy_tools where tid='" . $srow["tid"] . "' limit 1");
        $status = 0;
        $input  = array($srow["input"], $srow["input2"], $srow["input3"], $srow["input4"], $srow["input5"]);
        if ($tool["value"] < 1) {
            $tool["value"] = 1;
        }

        if ($srow["value"] < 1) {
            $srow["value"] = 1;
        }

        $num = $tool["value"] * $srow["value"];
        if ($tool["is_curl"] == 1) {
            $ret = do_curl($tool, $input, $num, $srow);
            if (stripos($ret, 'ErrorResult') === false && !empty($ret)) {
                $status = 1;
                $djzt   = 5;
            } else {
                $status = 0;
                $djzt   = 6;
            }
            $post = $tool['goods_param'];
            if (!empty($tool['goods_param'])) {
                $post = str_replace("[input]", urlencode($input[0]), $tool['goods_param']);
                $post = str_replace("[input2]", urlencode($input[1]), $post);
                $post = str_replace("[input3]", urlencode($input[2]), $post);
                $post = str_replace("[input4]", urlencode($input[3]), $post);
                $post = str_replace("[input5]", urlencode($input[4]), $post);
            }
            log_result('URL访问', $srow['zid'], 'url：' . $tool["curl"] . '；Data：' . $post, '模拟访问后返回：' . $ret, 0, $orderid);
            $DB->query("update `pre_orders` set `djzt`='" . $djzt . "',`status`='" . $status . "'  where id='" . $orderid . "'");
        } elseif ($tool["is_curl"] == 4) {
            $result = do_orders_km($srow, $tool, $num);
            if (stripos($result, '成功') !== false) {
                $status = 1;
                $djzt   = 3;
            } else {
                $status = 0;
                $djzt   = 4;
            }
            $DB->query("update `pre_orders` set `djzt`='" . $djzt . "',`status`='" . $status . "'  where id='" . $orderid . "'");
        } else {
            if ($tool["is_curl"] == 2) {
                if ($srow['djorder'] && !$is_djorder) {
                    $shequ  = $DB->get_row("SELECT * from cmy_shequ where id='" . $tool["shequ"] . "' limit 1");
                    $status = "该订单已对接到" . $shequ['url'] . "，订单号" . $srow['djorder'];
                } else {
                    $status = do_orders_shequ($srow, $tool, $num);
                }
            } else {
                $status = "该订单未配置对接，请手动处理";
            }
        }
        // 提交事务
        $DB->commit();
        return $status;
    } catch (\Exception $th) {
        // 回滚事务
        $DB->rollback();
        return $th->getMessage();
    }
}

function do_orders_km($srow, $tool, $num)
{
    global $DB, $date, $conf;
    $status    = 0;
    $djzt      = 4;
    $result    = "";
    $orderid   = $srow['id'];
    $Fakacount = intval($num) > 0 ? $num : 1;
    $isFakaNum = $DB->count("SELECT count(*) FROM cmy_faka WHERE `tid`='" . $srow['tid'] . "' and `orderid`='" . $orderid . "'");
    $kmdata    = '';
    if ($isFakaNum == $Fakacount) {
        $rs = $DB->query("SELECT * FROM cmy_faka WHERE tid='" . $srow['tid'] . "' and orderid='" . $orderid . "' LIMIT " . $Fakacount);
        while ($res = $DB->fetch($rs)) {
            if (!empty($res['pw'])) {
                $kmdata .= '卡号：' . $res['km'] . ' 密码：' . $res['pw'] . '<br/>';
            } else {
                $kmdata .= $res['km'] . '<br/>';
            }
        }
    } else {
        $FakaKucNum = $DB->count("SELECT count(*) FROM cmy_faka WHERE tid='" . $srow['tid'] . "' and orderid<1  order by kid asc");
        if ($FakaKucNum < $Fakacount) {
            $djzt = 4;
            $DB->query("UPDATE `pre_orders` set `status`='0',`endtime`='" . $date . "',`djzt`='" . $djzt . "' where `id`='" . $orderid . "'");
            return '该卡密商品库存不足，请先加卡后再补单操作！';
        } else {
            $rs = $DB->query("SELECT * FROM cmy_faka WHERE tid='" . $srow['tid'] . "' and orderid<1  order by kid asc LIMIT " . $Fakacount);
            while ($res = $DB->fetch($rs)) {
                if (!empty($res['pw'])) {
                    $kmdata .= '卡号：' . $res['km'] . ' 密码：' . $res['pw'] . '<br/>';
                } else {
                    $kmdata .= $res['km'] . '<br/>';
                }
                $DB->query("UPDATE `pre_faka` set `usetime`='" . $date . "',`orderid`='" . $orderid . "' WHERE kid='" . $res['kid'] . "'");
            }
        }
    }

    $kmdata = trim($kmdata, "<br/>");
    if ($kmdata == '') {
        $result = '**补单失败！发货份数：' . $Fakacount . '  发货已处理份数' . $isFakaNum;
    } else {
        $status = 1;
        $djzt   = 3;
        $result = "订单" . $orderid . "重新发货成功！";
        // if ($conf['mail_cloud'] >= 0) {
        //     $msg         = getFakaMod($srow, $tool, $kmdata);
        //     $sub         = "**商品自动发货提醒";
        //     $mail_name   = $srow['input'];
        //     $emailResult = send_mail($mail_name, $sub, $msg);
        //     if ($emailResult === true) {
        //         $result = "订单" . $orderid . "重新发货成功！发送邮件成功）";
        //     } else {
        //         $sub       = "自动**处理失败提醒";
        //         $msg       = " 订单ID为" . $orderid . "自动**失败！失败原因：" . $emailResult . "<br/><br/>----------<br/>Time:" . $date;
        //         $mail_name = $conf["mail_recv"] ? $conf["mail_recv"] : $conf["mail_name"];
        //         send_mail($mail_name, $sub, $msg);
        //         $DB->query("update `pre_orders` set `status`='" . $status . "',`djzt`='" . $djzt . "',`bz`='邮件提醒失败：" . addslashes($emailResult) . "',`endtime`='" . $date . "',`djzt`='3',`result`='您的卡密信息如下：<br>" . $kmdata . "' where `id`='" . $orderid . "'");
        //         $result = "订单" . $orderid . "重新发货成功！发送邮件失败（" . $emailResult . "）";
        //     }
        // } else {
        //     $DB->query("update `pre_orders` set `status`='" . $status . "',`djzt`='" . $djzt . "',`endtime`='" . $date . "',`djzt`='3',`result`='您的卡密信息如下：<br>" . $kmdata . "' where `id`='" . $orderid . "'");
        //     $result = "订单" . $orderid . "重新发货成功！发送邮件失败（未配置好邮箱）";
        // }
    }
    $DB->query("UPDATE `pre_orders` set `status`='" . $status . "',`djzt`='" . $djzt . "',`endtime`='" . $date . "',`result`='您的卡密信息如下：<br>" . addcslashes($kmdata, "'") . "' where `id`='" . $orderid . "'");
    return $result;
}

function do_orders_shequ($row, $tool, $num)
{
    global $DB, $date, $conf;
    $orderid     = $row['id'];
    $status      = 0;
    $djzt        = 0;
    $goods_param = explode("|", $tool["goods_param"]);

    $inputData = $row['input'] . ($row['input2'] ? '|' . $row['input2'] : null) . ($row['input3'] ? '|' . $row['input3'] : null) . ($row['input4'] ? '|' . $row['input4'] : null) . ($row['input5'] ? '|' . $row['input5'] : null);
    $input     = explode("|", $inputData);
    ob_clean();

    $i = 0;
    foreach ($goods_param as $value) {
        if ($input[$i] != "") {
            $data[$value] = $input[$i];
        }
        $i = $i + 1;
    }

    if ($row["stock_id"] > 0) {
        $stock_row = $DB->get_row("SELECT * FROM `pre_stock` where id='" . $row["stock_id"] . "'");
        $num       = $stock_row['value'] > 1 ? intval($stock_row["value"] * $row["num"]) : $row["num"];
    } else {
        $num = intval($tool["value"] * $row["value"]);
    }

    $shequ = $DB->get_row("SELECT * FROM cmy_shequ where id='" . $tool["shequ"] . "' limit 1");
    if ($shequ) {
        if (!preg_match('/^(https|http):\/\/[\w\.\-]+\.[\w\:\/]+\/$/', $shequ["url"])) {
            preg_match('/[\w\.\-]+\.[\w\:]+/', $shequ["url"], $arr);
            if ($shequ["ssl"] == 1) {
                $shequ["url"] = 'https://' . $arr[0] . '/';
            } else {
                $shequ["url"] = 'http://' . $arr[0] . '/';
            }
        }
        $djzt = 2;
        $DB->query("UPDATE `pre_orders` set `djzt`='2' where id='" . $orderid . "'");
        $result = do_orders_extend($row, $shequ, $tool, $num);
        $param  = "shequ:" . $tool["shequ"] . " goods_id:" . $tool["goods_id"] . " goods_type:" . $tool["goods_type"] . " num:" . $num . " data:" . http_build_query($data);

        if (stripos($result, "success") === false && stripos($result, "成功") === false) {
            $djzt = 2;
            $DB->query("UPDATE `pre_orders` SET `djzt`='{$djzt}',`status`='0' where `id`='" . $orderid . "'");
            if ($conf["shequ_tixing"] == 1 && $conf['mail_cloud'] >= 0) {
                $sub       = "自动下单到对接站失败提醒";
                $msg       = ($tool["inputname"] ? $tool["inputname"] : "账号：") . $input[0] . " 下单商品: " . $tool["name"] . "<br/><b>提交参数：</b>" . $param . "<br/><b>返回结果：</b>" . $result . "<br/>----------<br/>" . $_SERVER["HTTP_HOST"] . "<br/>" . $date;
                $mail_name = $conf["mail_recv"] ? $conf["mail_recv"] : $conf["mail_name"];
                send_mail($mail_name, $sub, $msg);
            }

            if ($conf['mail_rmb'] == 1) {
                if (stripos($result, "余额") !== false || stripos($result, "充值") !== false || stripos($result, "点数") !== false || stripos($result, "需要") !== false) {
                    sendlackRmbEmail($tool, $shequ);
                }
            }
        }
    } else {
        $status = 0;
        $result = "下单失败，对接社区/**不存在或账号密码未填写";
        log_result($shequ["type"], $row['zid'], http_build_query($data), $result, 0, $orderid);
    }

    if ($status > 0) {
        if ($tool["result"] != "" && $tool["is_curl"] != 2) {
            $DB->query("UPDATE `pre_orders` set `result`='" . addslashes($tool["result"]) . "',`status`='" . $status . "',`endtime`='" . $date . "' where `id`='" . $orderid . "'");
        } else {
            $DB->query("UPDATE `pre_orders` set `status`='" . $status . "',`endtime`='" . $date . "' where `id`='" . $orderid . "'");
        }
    }
    return $result;
}

function sendlackRmbEmail($tool, $shequ)
{
    global $DB, $date, $conf, $CACHE;
    $key = substr(md5($shequ['url']), 6, 12);
    if (time() - $conf['tixing_' . $key] > 0) {
        saveSetting('tixing_' . $key, time() + 14400); //4小时间隔
        $ad = $CACHE->clear();
        if ($ad) {
            $sub       = "下.单余额不足提醒";
            $msg       = "检测到 " . $shequ['url'] . " 余额不足<br/>请及时充值余额<br/>Time：" . $date;
            $mail_name = $conf["mail_recv"] ? $conf["mail_recv"] : $conf["mail_name"];
            send_mail($mail_name, $sub, $msg);
        }
    }
}

if (!function_exists("getkayixingoods")) {
    function getkayixingoods($config)
    {
        $cookie_file = ROOT . "other/" . md5($config["url"] . $config["username"]) . ".txt";

        $url     = $config["url"] . "front/inter/dirList.htm";
        $cookies = file_get_contents($cookie_file);
        if (!file_exists($cookie_file) || $cookies == "") {
            $cookies = login_kayixin($config);
            if (strpos($cookies, "失败")) {
                return $cookies;
            }
            file_put_contents($cookie_file, $cookies);
        }

        $handle   = chenm_curl($url, 0, 0, $cookies, 0, 0, 0, $config['proxy'], 20);
        $data     = curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);
        if (!empty($data) || $httpCode == 200 || $httpCode == 301 || $httpCode == 302) {
            if (strstr($data, "须重新登录系统")) {
                $cookies = login_kayixin($config);
                if (strpos($cookies, "失败")) {
                    return $cookies;
                }
                file_put_contents($cookie_file, $cookies);

                $data = shequ_get_curl($url, 0, 0, $cookies, 1, 0, 0, 1);
            }
        } else {
            $data = '网站打开失败[' . $httpCode . ']<br/>访问url：' . $url . '<br/>提交cookie：' . $cookies;
        }

        return $data;
    }
}

if (!function_exists("getkayixinlist")) {
    //商品列表
    function getkayixinlist($config, $post, $page)
    {
        $cookie_file = ROOT . "other/" . md5($config["url"] . $config["username"]) . ".txt";
        $url         = $config["url"] . "front/inter/cutPageGoodsList.htm?nowPage=" . $page;
        $cookies     = file_get_contents($cookie_file);
        if (!file_exists($cookie_file) || $cookies == "") {
            $cookies = login_kayixin($config);
            if (strpos($cookies, "失败")) {
                return $cookies;
            }
            file_put_contents($cookie_file, $cookies);
        }
        $curl     = chenm_curl($url, $post, 0, $cookies, 0, 0, 0, $config['proxy'], 20);
        $data     = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if (!empty($data) || $httpCode == 200 || $httpCode == 301 || $httpCode == 302) {
            if (strstr($data, "须重新登录系统")) {
                $cookies = login_kayixin($config);
                if (strpos($cookies, "失败")) {
                    return $cookies;
                }
                file_put_contents($cookie_file, $cookies);

                $data = shequ_get_curl($url, $post, 0, $cookies, 1, 0, 0, 1);
            }
        } else {
            $data = '网站打开超时，已等待20秒无响应！[' . $httpCode . ']';
        }

        return $data;
    }
}
if (!function_exists("getkayixingoodsinfo")) {
    //商品详情
    function getkayixingoodsinfo($config, $url)
    {
        $cookie_file = ROOT . "other/" . md5($config["url"] . $config["username"]) . ".txt";
        $cookies     = file_get_contents($cookie_file);
        if (!file_exists($cookie_file) || $cookies == "") {
            $cookies = login_kayixin($config);
            if (strpos($cookies, "失败")) {
                return $cookies;
            }
            file_put_contents($cookie_file, $cookies);
        }
        $curl     = chenm_curl($url, 0, 0, $cookies, 0, 0, 0, $config['proxy'], 20);
        $data     = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if (!empty($data) || $httpCode == 200 || $httpCode == 301 || $httpCode == 302) {
            if (strstr($data, "须重新登录系统")) {
                $cookies = login_kayixin($config);
                if (strpos($cookies, "失败")) {
                    return $cookies;
                }
                file_put_contents($cookie_file, $cookies);

                $data = shequ_get_curl($url, 0, 0, $cookies, 1, 0, 0, 1);
            }
        } else {
            $data = '网站打开失败[' . $httpCode . ']';
        }
        return $data;
    }
}

if (!function_exists("getkayixinmoney")) {
    function getkayixinmoney($config)
    {
        $cookie_file = ROOT . "other/" . md5($config["url"] . $config["username"]) . ".txt";
        $url         = $config["url"] . "front/inter/refreshmoney.htm";
        $cookies     = file_get_contents($cookie_file);
        if (!file_exists($cookie_file) || $cookies == "") {
            $cookies = login_kayixin($config);
            if (strpos($cookies, "失败")) {
                return $cookies;
            }
            file_put_contents($cookie_file, $cookies);
        }

        $data = shequ_get_curl($url, 0, 0, $cookies, 1, 0, 0, 1);

        if (strstr($data, "须重新登录系统")) {
            $cookies = login_kayixin($config);
            if (strpos($cookies, "失败")) {
                return $cookies;
            }
            file_put_contents($cookie_file, $cookies);

            $data = shequ_get_curl($url, 0, 0, $cookies, 1, 0, 0, 1);
        }
        return $data;
    }
}

if (!function_exists("getkayixinorder")) {
    function getkayixinorder($config, $noewpage, $starttime, $endtime, $orderState = "3,4")
    {
        //获取已完成和取消的订单
        $starttime   = $starttime ? $starttime : date('Y-m-d');
        $endtime     = $endtime ? $endtime : date('Y-m-d', strtotime('-15 day'));
        $post        = "nowPage=" . $noewpage . "&everyPage=100&orderState=$orderState&goodType=0&startTime=$starttime&endTime=$endtime";
        $cookie_file = ROOT . "other/" . md5($config["url"] . $config["username"]) . ".txt";
        $url         = $config["url"] . "front/inter/salelist.htm";

        $cookies = file_get_contents($cookie_file);
        if (!file_exists($cookie_file) || $cookies == "") {
            $cookies = login_kayixin($config);
            if (strpos($cookies, "失败")) {
                return $cookies;
            }
            file_put_contents($cookie_file, $cookies);
        }

        $data = shequ_get_curl($url, $post, 0, $cookies, 1, 0, 0, 1);

        if (strstr($data, "须重新登录系统")) {
            $cookies = login_kayixin($config);
            if (strpos($cookies, "失败")) {
                return $cookies;
            }
            file_put_contents($cookie_file, $cookies);

            $data = shequ_get_curl($url, $post, 0, $cookies, 1, 0, 0, 1);
        }
        return $data;
    }
}

function setKjToolSort($gid, $sort)
{
    global $DB, $date;
    $tool = $DB->get_row("SELECT * from cmy_kjtools where gid='" . $gid . "' limit 1");
    if ($sort == 0) {
        $tool1 = $DB->get_row("SELECT * from cmy_kjtools where 1 order by sort asc limit 1");
        if ($tool1['sort'] == 0) {
            $rs = $DB->query("SELECT * from cmy_kjtools where gid!='" . $gid . "'");
            while ($res = $DB->fetch($rs)) {
                $DB->query('UPDATE `pre_kjtools` SET `sort`=`sort`+1 WHERE `gid`=' . $res['gid'] . '');
            }
            $DB->query('UPDATE `pre_kjtools` SET `sort`=0 WHERE `gid`=' . $gid . '');
        } else {
            $DB->query('UPDATE `pre_kjtools` SET `sort`=' . $tool1['sort'] . '-1 WHERE `gid`=' . $gid . '');
        }
    } elseif ($sort == 1) {
        $tool1 = $DB->get_row("SELECT * from cmy_kjtools where `sort` < " . $tool['sort'] . " order by sort desc limit 1");
        $DB->query('UPDATE `pre_kjtools` SET `sort`=' . $tool1['sort'] . ' WHERE `gid`=' . $gid . '');
        $DB->query("update cmy_kjtools set `sort`=" . $tool['sort'] . " where gid='" . $tool1['gid'] . "'");
    } elseif ($sort == 2) {

        $tool1 = $DB->get_row("SELECT * from cmy_kjtools where  `sort`>" . $tool['sort'] . " order by sort asc limit 1");
        $DB->query('UPDATE `pre_kjtools` SET `sort`=' . $tool1['sort'] . ' WHERE `gid`=' . $gid . '');
        $DB->query("update cmy_kjtools set `sort`=" . $tool['sort'] . " where gid='" . $tool1['gid'] . "'");
    } else {
        $tool1 = $DB->get_row("SELECT * from cmy_kjtools where 1 order by sort desc limit 1");
        $rs    = $DB->query("SELECT * from cmy_kjtools where gid!='" . $gid . "'");
        while ($res = $DB->fetch($rs)) {
            $DB->query('UPDATE `pre_kjtools` SET `sort`=`sort`+1 WHERE `gid`=' . $res['gid'] . '');
        }
        $DB->query('UPDATE `pre_kjtools` SET `sort`=' . $tool1['sort'] . ' WHERE `gid`=' . $gid . '');
    }
    return true;
}

function setTgToolSort($sid, $sort)
{
    global $DB, $date;
    $tool = $DB->get_row("SELECT * from cmy_invitetools where sid='" . $sid . "' limit 1");
    if ($sort == 0) {
        $tool1 = $DB->get_row("SELECT * from cmy_invitetools where 1 order by sort asc limit 1");
        if ($tool1['sort'] == 0) {
            $rs = $DB->query("SELECT * from cmy_invitetools where sid!='" . $sid . "'");
            while ($res = $DB->fetch($rs)) {
                $DB->query('UPDATE `pre_invitetools` SET `sort`=`sort`+1 WHERE `sid`=' . $res['sid'] . '');
            }
            $DB->query('UPDATE `pre_invitetools` SET `sort`=0 WHERE `sid`=' . $sid . '');
        } else {
            $DB->query('UPDATE `pre_invitetools` SET `sort`=' . $tool1['sort'] . '-1 WHERE `sid`=' . $sid . '');
        }
    } elseif ($sort == 1) {
        $tool1 = $DB->get_row("SELECT * from cmy_invitetools where `sort` < " . $tool['sort'] . " order by sort desc limit 1");
        $DB->query('UPDATE `pre_invitetools` SET `sort`=' . $tool1['sort'] . ' WHERE `sid`=' . $sid . '');
        $DB->query("update cmy_invitetools set `sort`=" . $tool['sort'] . " where sid='" . $tool1['sid'] . "'");
    } elseif ($sort == 2) {

        $tool1 = $DB->get_row("SELECT * from cmy_invitetools where  `sort`>" . $tool['sort'] . " order by sort asc limit 1");
        $DB->query('UPDATE `pre_invitetools` SET `sort`=' . $tool1['sort'] . ' WHERE `sid`=' . $sid . '');
        $DB->query("update cmy_invitetools set `sort`=" . $tool['sort'] . " where sid='" . $tool1['sid'] . "'");
    } else {
        $tool1 = $DB->get_row("SELECT * from cmy_invitetools where 1 order by sort desc limit 1");
        $rs    = $DB->query("SELECT * from cmy_invitetools where sid!='" . $sid . "'");
        while ($res = $DB->fetch($rs)) {
            $DB->query('UPDATE `pre_invitetools` SET `sort`=`sort`+1 WHERE `sid`=' . $res['sid'] . '');
        }
        $DB->query('UPDATE `pre_invitetools` SET `sort`=' . $tool1['sort'] . ' WHERE `sid`=' . $sid . '');
    }
    return true;
}

function setToolSort($cid, $tid, $sort)
{
    global $DB, $conf, $date;
    $tool = $DB->get_row("SELECT * from cmy_tools where tid='" . $tid . "' limit 1");

    //  $conf['goods_sort_type'] == 1 是从小到大 反之从大到小
    $orderBy  = $conf['goods_sort_type'] == 1 ? "ASC" : "DESC";
    $orderBy2 = $conf['goods_sort_type'] == 1 ? "DESC" : "ASC";
    if ($sort == 0) {
        // 置顶
        $tool1 = $DB->get_row("SELECT * from cmy_tools where 1 order by sort {$orderBy} limit 1");
        if ($tool1['sort'] == 0) {
            $rs = $DB->query("SELECT * from cmy_tools where tid!='" . $tid . "' and cid='" . $tool['cid'] . "'");
            if (!$rs) {
                $rs = $DB->query("SELECT * from cmy_tools where tid!='" . $tid . "' and find_in_set('" . $tool['cid'] . "',cids)");
            }
            while ($res = $DB->fetch($rs)) {
                if ($orderBy == 'ASC') {
                    $DB->query('UPDATE `pre_tools` SET `sort`=`sort`+1 WHERE `tid`=' . $res['tid'] . '');
                } else {
                    $DB->query('UPDATE `pre_tools` SET `sort`=`sort`-1 WHERE `tid`=' . $res['tid'] . '');
                }
            }
            $DB->query('UPDATE `pre_tools` SET `sort`=0 WHERE `tid`=' . $tid . '');
        } else {
            $DB->query('UPDATE `pre_tools` SET `sort`=' . $tool1['sort'] . '-1 WHERE `tid`=' . $tid . '');
        }
    } elseif ($sort == 1) {
        // 上移
        $tool1 = $DB->get_row("SELECT * from cmy_tools where  cid='" . $tool['cid'] . "' and `sort` < " . $tool['sort'] . " order by sort {$orderBy2} limit 1");
        if (!$tool1) {
            $cids = explode("|", $tool['cids']);
            foreach ($cids as $cid) {
                $tool1 = $DB->get_row("SELECT * from cmy_tools where (cid='" . $cid . "' or find_in_set('" . $cid . "',cids)) and `sort` < " . $tool['sort'] . " order by sort desc limit 1");
                if ($tool1) {
                    break;
                }
            }
        }
        $DB->query('UPDATE `pre_tools` SET `sort`=' . $tool1['sort'] . ' WHERE `tid`=' . $tid . '');
        $DB->query("UPDATE cmy_tools set `sort`=" . $tool['sort'] . " where tid='" . $tool1['tid'] . "'");
    } elseif ($sort == 2) {
        // 下移
        $tool1 = $DB->get_row("SELECT * from cmy_tools where  cid='" . $tool['cid'] . "' and `sort`>" . $tool['sort'] . " order by sort {$orderBy} limit 1");
        if (!$tool1) {
            $cids = explode("|", $tool['cids']);
            foreach ($cids as $cid) {
                $tool1 = $DB->get_row("SELECT * from cmy_tools where (cid='" . $cid . "' or find_in_set('" . $cid . "',cids)) and `sort`>" . $tool['sort'] . " order by sort asc limit 1");
                if ($tool1) {
                    break;
                }
            }
        }
        $DB->query('UPDATE `pre_tools` SET `sort`=' . $tool1['sort'] . ' WHERE `tid`=' . $tid . '');
        $DB->query("UPDATE cmy_tools set `sort`=" . $tool['sort'] . " where tid='" . $tool1['tid'] . "'");
    } else {
        // 置底
        $tool1 = $DB->get_row("SELECT * from cmy_tools where 1 order by sort {$orderBy2} limit 1");
        $rs    = $DB->query("SELECT * from cmy_tools where tid!='" . $tid . "' and cid='" . $tool['cid'] . "'");
        if (!$rs) {
            $rs = $DB->query("SELECT * from cmy_tools where tid!='" . $tid . "' and find_in_set('" . $tool['cid'] . "',cids)");
        }
        while ($res = $DB->fetch($rs)) {
            if ($orderBy == 'ASC') {
                $DB->query('UPDATE `pre_tools` SET `sort`=`sort`-1 WHERE `tid`=' . $res['tid'] . '');
            } else {
                $DB->query('UPDATE `pre_tools` SET `sort`=`sort`+1 WHERE `tid`=' . $res['tid'] . '');
            }
        }
        $DB->query('UPDATE `pre_tools` SET `sort`=' . $tool1['sort'] . ' WHERE `tid`=' . $tid . '');
    }
    return true;
}

function setClassSort($cid, $sort)
{
    global $DB, $date;
    $row  = $DB->get_row("SELECT * from cmy_class where cid='$cid' limit 1");
    $sql  = "1";
    $sql2 = "";
    if ($row['upcid'] != null && $row['upcid'] > 0) {
        $sql  = " upcid='" . $row['upcid'] . "'";
        $sql2 = " and upcid='" . $row['upcid'] . "'";
    }

    if ($sort == 0) {
        $row2 = $DB->get_row("SELECT * from cmy_class where " . $sql . " order by sort asc limit 1");
        $DB->query("update cmy_class set `sort`=`sort`+'1' where cid!='$cid' and sort<{$row['sort']}" . $sql2 . "");
        $DB->query('UPDATE `pre_class` SET `sort`=' . $row2['sort'] . ' WHERE `cid`=' . $cid . '');
    } elseif ($sort == 1) {
        $row2 = $DB->get_row("SELECT * from cmy_class where cid!='$cid' and sort<{$row['sort']}" . $sql2 . " order by sort desc limit 1");
        $DB->query('UPDATE `pre_class` SET `sort`=' . $row2['sort'] . ' WHERE `cid`=' . $cid);
        $DB->query("update cmy_class set `sort`={$row['sort']} where cid={$row2['cid']}");
    } elseif ($sort == 2) {
        $row2 = $DB->get_row("SELECT * from cmy_class where cid!='$cid' and sort>{$row['sort']}" . $sql2 . " order by sort asc limit 1");
        $DB->query('UPDATE `pre_class` SET `sort`=' . $row2['sort'] . ' WHERE `cid`=' . $cid);
        $DB->query("update cmy_class set `sort`={$row['sort']} where cid={$row2['cid']}");
    } else {
        $row2 = $DB->get_row("SELECT * from cmy_class where " . $sql . " order by sort desc limit 1");
        $DB->query("update cmy_class set `sort`=`sort`-'1' where cid!='$cid' and sort>{$row['sort']}" . $sql2 . "");
        $DB->query('UPDATE `pre_class` SET `sort`=' . $row2['sort'] . ' WHERE `cid`=' . $cid);
    }

    return true;
}

function setArticleSort($id, $sort)
{
    global $DB, $date;
    $row = $DB->get_row("SELECT * from cmy_message where `id`=:id limit 1", [':id' => $id]);
    if ($row) {
        if ($sort == 0) {
            $row2 = $DB->get_row("SELECT * from cmy_message order by sort asc limit 1");
            if ($row2) {
                $DB->query("update cmy_message set `sort`=`sort`+'1' where id!='" . $id . "' and sort<" . $row['sort']);
                $DB->query('UPDATE `pre_message` SET `sort`=' . $row2['sort'] . ' WHERE `id`=' . $id . '');
            } else {
                $DB->query("update cmy_message set `sort`=`sort`-1 where id!='" . $row['id'] . "'");
            }
        } elseif ($sort == 1) {
            $row2 = $DB->get_row("SELECT * from cmy_message where id!='{$id}' and sort<" . $row['sort'] . " order by sort desc limit 1");
            if ($row2) {
                $DB->query('UPDATE `pre_message` SET `sort`=' . $row2['sort'] . ' WHERE `id`=' . $id);
                $DB->query("update cmy_message set `sort`={$row['sort']} where id='" . $row2['id'] . "'");
            } else {
                $DB->query("update cmy_message set `sort`=`sort`+1 where id!='" . $row['id'] . "'");
            }
        } elseif ($sort == 2) {
            $row2 = $DB->get_row("SELECT * from cmy_message where id!='{$id}' and sort>" . $row['sort'] . " order by sort asc limit 1");
            $DB->query('UPDATE `pre_message` SET `sort`=' . $row2['sort'] . ' WHERE `id`=' . $id);
            $DB->query("update cmy_message set `sort`={$row['sort']} where id='" . $row2['id'] . "'");
        } else {
            $row2 = $DB->get_row("SELECT * from cmy_message order by sort desc limit 1");
            $DB->query("update cmy_message set `sort`=`sort`-'1' where id!='{$id}' and sort>" . $row['sort']);
            $DB->query('UPDATE `pre_message` SET `sort`=' . $row2['sort'] . ' WHERE `id`=' . $id . '');
        }
        if ($DB->affected()) {
            return true;
        }
        return false;
    }
    return false;
}

function getCloudRmb()
{
    global $conf;
    if ($conf['cloud_api'] == 2) {
        return array('code' => -1, "msg" => '该接口暂不支持查询');
    }

    $url = 'http://api01.monyun.cn:7901/sms/v2/std/get_balance';
    if (!$conf['cloud_user']) {
        return array('code' => -1, "msg" => '未配置账号');
    }

    if (!$conf['cloud_pwd']) {
        return array('code' => -1, "msg" => '未配置密码');
    }
    $times = time();
    $post  = array(
        'userid'    => $conf['cloud_user'],
        'pwd'       => md5(strtoupper($conf['cloud_user']) . '00000000' . $conf['cloud_pwd'] . $times),
        'timestamp' => $times,
    );
    $result = get_curl($url, http_build_query($post));
    $data   = json_decode($result, true);
    if (is_array($data)) {
        if ($data['result'] == 0) {
            $ret = array('code' => -1, "msg" => '短信余额剩余：' . round($data['money'], 2) . '元<br>短信额度剩余：' . $data['balance'] . '条<br>当前计费方式：' . ($data['chargetype'] == 0 ? '短信计费' : '余额计费'));
        } elseif ($data['result'] == (-100001)) {
            $ret = array('code' => -1, "msg" => '查询余额失败，账号或密码错误！', "data" => $data);
        } elseif ($data['result'] == (-100003)) {
            $ret = array('code' => -1, "msg" => '账户余额已欠费，请及时充值！', "data" => $data);
        } elseif ($data['result'] == (-100999)) {
            $ret = array('code' => -1, "msg" => '验证平台内部出现错误，请等待恢复！', "data" => $data);
        } elseif ($data['result'] == (-100056)) {
            $ret = array('code' => -1, "msg" => '用户账号登录的连接数超限', "data" => $data);
        } else {
            $ret = array('code' => -1, "msg" => '查询余额失败，可能是访问超时或被拦截', "result" => $result);
        }
    } else {
        $ret = array('code' => -1, "msg" => '查询余额失败，可能是访问超时或被拦截', "result" => $result);
    }
    return $ret;
}

function getGoods_chengzi($config)
{

    $url = $config['url'] . "api/index/products";
    $key = strSafeEnCode($config['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
    $arr = array(
        "key" => $key,
    );
    $text = shequ_get_curl($url, http_build_query($arr), 0, 0, 0, 0, 0, $config['proxy']);
    $json = json_decode($text, true);
    if (is_array($json)) {
        if ($json['code'] == 1) {
            $data = [];
            foreach ($json['data'] as $row) {
                $data[] = array(
                    'tid'    => $row['pid'],
                    'name'   => $row['name'],
                    'price'  => $row['price'],
                    'min'    => $row['min'],
                    'max'    => $row['max'],
                    'active' => $row['maintain'] == 1 ? '1' : '0',
                    'inputs' => !empty($row['input_extend_1']) ? $row['input_extend_1'] : '',
                    'param'  => !empty($row['input_extend_1_active']) ? $row['input_extend_1_active'] : '',
                );
            }
            $ret = array('code' => 0, "msg" => $json['msg'], "data" => $data, "type" => getShequType($config['type']));
        } else {
            $ret = array('code' => -1, "msg" => $json['msg'], "data" => null, "type" => getShequType($config['type']));
        }
    } else {
        $ret = array('code' => -1, "msg" => "获取卡卡云商品失败，请稍后重试！", "data" => $text, "type" => getShequType($config['type']));
    }
    return $ret;
}

function getJiuwuImgHost($url)
{
    if (empty($url)) {
        return '';
    }
    $host = '';
    $regx = "/([a-zA-Z0-9\-]+\.[a-zA-Z]+)$/";
    if (preg_match($regx, $url, $matchs)) {
        if (is_array($matchs[1])) {
            $host = $matchs[1][0];
        } else {
            $host = $matchs[0];
        }
    }

    return 'pics.' . $host;
}

if (!function_exists('processRefundByUser')) {
    /**
     * 订单退款手动
     * @param  array  $srow    订单信息
     * @param  array  $tool    商品信息
     * @param  array  $siterow 站点信息
     * @param  float  $refund  实时订单金额
     * @return [type]          [description]
     */
    function processRefundByUser($row = [], $tool = [], $siterow = [], $refund = 0.0)
    {
        global $DB;
        $orderid = $row['id'];
        $message = '状态已改成【已退单】，当前需要你手动退款给客户' . $refund . '元';
        if ($siterow['upzid'] > 1) {
            $message .= processRefundUp($row, $tool, $siterow['upzid']);
        } else {
            $message .= '由于该订单所属用户/站点无上级，无需扣除相应提成';
        }
        return $message;
    }
}

if (!function_exists('processRefund')) {
    /**
     * 订单退款到分站
     * @param  array  $srow    订单信息
     * @param  array  $tool    商品信息
     * @param  array  $siterow 站点信息
     * @param  float  $refund  实时订单金额
     * @return [type]          [description]
     */
    function processRefund($row = [], $tool = [], $siterow = [], $refund = 0.0)
    {
        global $DB;
        $orderid  = $row['id'];
        $site_rmb = sprintf('%.2f', $siterow['money'] + $refund);
        $sql1     = $DB->query("UPDATE pre_site set `money`=`money`+ ? where zid= ?", [$refund, $siterow['zid']]);
        if ($sql1) {
            $message = '退款操作成功，已退给分站' . $refund . '元';
            addPointLogs($row['zid'], $refund, '退款', "您收到订单（" . $orderid . "）的退款" . $refund . "元，当前可用余额" . $site_rmb . "元！", $orderid);
            if ($siterow['upzid'] > 1) {
                $message .= processRefundUp($row, $tool, $siterow['upzid']);
            }
        } else {
            $message = '退给分站' . $refund . '元失败，错误信息：' . $DB->error();
        }
        return $message;
    }
}

if (!function_exists('processRefundUp')) {
    /**
     * 订单退款上级提成处理
     * @param  array  $srow    订单信息
     * @param  array  $tool    商品信息
     * @param  array  $upzid   站点ID
     * @return [type]          [description]
     */
    function processRefundUp($row = [], $tool = [], $upzid = 1)
    {
        global $DB;
        $orderid = $row['id'];
        $row1    = [];
        if ($upzid > 0) {
            $row1 = $DB->get_row("SELECT * FROM cmy_site WHERE `zid`= ? limit 1", [$upzid]);
        }
        $message = '';
        if (isset($row1['zid'])) {
            $row2 = $DB->get_row("SELECT * FROM cmy_points WHERE zid='{$upzid}' AND `action`='提成' AND `orderid`= ? limit 1", [$row['id']]);
            if ($row2 && isset($row2['point'])) {
                if ($row1['point'] > $row1['money']) {
                    $sql = $DB->query("UPDATE pre_site set `point`=`point`- ? where zid= ?", [$row2['point'], $upzid]);
                } else {
                    $sql = $DB->query("UPDATE pre_site set `money`=`money`- ? where zid= ?", [$row2['point'], $upzid]);
                }
                if ($sql) {
                    addPointLogs($upzid, $row2['point'], '扣款', "订单（" . $orderid . "）管理员已退款，扣除对应提成" . $row2['point'] . "元", $row['id']);
                    $message = '<br/>同时已扣除上级站点[' . $upzid . ']的相应提成' . $row2['point'] . '元';
                } else {
                    $message = '<br/>扣除上级站点[' . $upzid . ']的相应提成失败，' . $DB->error();
                }
            } else {
                $message = '<br/>由于未找到上级站点[' . $upzid . ']相应提成记录，无需扣除';
            }
            if ($row1['upzid'] > 0) {
                $message .= processRefundUp($row, $tool, $row1['upzid']);
            }
        } else {
            $message = '<br/>由于上级站点[' . $upzid . ']记录不存在，无需扣除';
        }
        return $message;
    }
}

// ----------------------------分割线分割线分割线分割线分割线分割线分割线分割线---------------
// addOrder function start
// ----------------------------分割线分割线分割线分割线分割线分割线分割线分割线---------------
function extend_autoload($class)
{
    $class = str_replace('\\', '/', $class);
    $file  = ROOT . 'includes/' . trim($class, '/') . '.php';
    if (file_exists($file)) {
        include_once $file;
    }
}

function do_orders_kalegou($row, $config, $goods_id, $orderurl, $num = 1, $data = array())
{
    global $DB, $date;
    if ($row['status'] == -1) {
        $DB->query("UPDATE `pre_orders` set status='0', djzt='2' where id=:id", [':id' => $row['id']]);
    } elseif ($row['status'] > 0) {
        return '该订单已对接处理！';
    }
    $orderid  = $row['id'];
    $password = strSafeEnCode($config['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
    $pwd      = "";
    $i        = 0;
    while ($i < strlen($password)) {
        $pwd .= ord($password[$i]) . ",";
        $i = $i + 1;
    }
    $username = urlencode($config["username"]);
    $url      = $config["url"] . "webnew/Customer/CustomerProcess/CheckCustomerLogin.aspx?UserName=" . $username . "&pwd=" . $pwd . "&CheckCode=&DynamicCode=&FengYunlingCode=&EmailCode=&IsSafe=0&rki=undefined&rk=undefined&pwd1=" . $pwd . "&_=" . time() . "000";
    $data1    = get_curl($url, 0, $config["url"] . "", 0, 1);
    $data2    = strstr($data1, "{");
    $json     = json_decode($data2, true);
    $params   = "num=" . $num . "&url=" . http_build_query($data);
    if ($json["Status"]["Code"] == "success") {

        $cookies = "";
        preg_match_all("/Set-Cookie: (.*);/iU", $data1, $matchs);
        foreach ($matchs[1] as $val) {
            if ($val != "") {
                $cookies .= $val . "; ";
            }
        }

        preg_match("/PID=(\\d+)&TPID=(\\d+)&StockID=(.*)&/i", $orderurl, $match);
        $PID     = $match[1];
        $TPID    = $match[2];
        $StockID = $match[3];
        $url     = $config["url"] . "Templates/CustomTemplate.aspx?PID=" . $PID . "&TPID=" . $TPID . "&StockID=" . $StockID;
        $params  = "num=" . $num . "&url=" . $url;
        $data1   = get_curl($url, 0, 0, $cookies);
        preg_match("!id=\"__VIEWSTATE\" value=\"(.*?)\"!i", $data1, $VIEWSTATE);
        preg_match("!id=\"__VIEWSTATEGENERATOR\" value=\"(.*?)\"!i", $data1, $VIEWSTATEGENERATOR);
        preg_match("!id=\"HFOrderNo\" value=\"(.*?)\"!i", $data1, $HFOrderNo);
        preg_match("!id=\"HFGameCompanyID\" value=\"(.*?)\"!i", $data1, $HFGameCompanyID);
        preg_match("!id=\"HFParvalue\" value=\"(.*?)\"!i", $data1, $HFParvalue);
        preg_match("!id=\"HFSupOrderNo\" value=\"(.*?)\"!i", $data1, $HFSupOrderNo);
        if ($data[1]) {
            $addstr = "&txtChargeWay=" . urlencode($data[1]);
        }
        $post = "ScriptManager1=UpdatePanel2|ImageButtonBuyCheck&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=" . urlencode($VIEWSTATE[1]) . "&__VIEWSTATEGENERATOR=" . $VIEWSTATEGENERATOR[1] . "&HFProductID=" . $PID . "&HFOrderNo=" . $HFOrderNo[1] . "&HFGameCompanyID=" . $HFGameCompanyID[1] . "&HFTemplateID=" . $TPID . "&HFParvalue=" . $HFParvalue[1] . "&HFSupOrderNo=" . $HFSupOrderNo[1] . "&txtAccountName=" . urlencode($data[0]) . "&txtAccountName1=" . urlencode($data[0]) . $addstr . "&DrCount=" . $num . "&txtComment=&ImageButtonBuyCheck=%E7%A1%AE%E8%AE%A4%E8%B4%AD%E4%B9%B0";
        $data = get_curl($url, $post, $url, $cookies);

        if (strstr($data, "HandTemplateDetail") !== false) {
            $status = 2;
            if ($config['orderstatus']) {
                $status = $config['orderstatus'];
            }

            $djresult = '';

            $tool = $DB->get_row("SELECT * FROM cmy_tools where tid='" . $row['tid'] . "' limit 1");

            if ($tool['result']) {
                $djresult = $tool['result'];
            }

            $DB->query(
                "UPDATE `pre_orders` set result= ?,djorder= ?,endtime= ?,status= ?,djzt='1' where id= ?",
                [$djresult, $HFOrderNo[1], $date, $status, $row['id']]
            );
            $message = "下单成功!订单号为:" . $HFOrderNo[1];
            log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, $message, 1, $row['id']);
            return $message;
        }

        $DB->query("UPDATE `pre_orders` set status='0',djzt='2' where id=:id", [':id' => $orderid]);

        if (preg_match("/alert\\((.*?)\\)/", $data, $msg)) {
            log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, "下单失败!" . $msg[1], 0, $row['id']);
            return $msg[1];
        }

        $data = str_replace(array("\r\n", "\r", "\n"), "", $data);

        log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, "下单失败!" . $data, 0, $row['id']);
        return $data;
    }

    $DB->query("UPDATE `pre_orders` set status='0',djzt='2' where id=:id", [':id' => $orderid]);

    log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, "下单失败!" . $json["Status"]["Msg"], 0, $row['id']);
    return $json["Status"]["Msg"];
}

function updateToolPirce($tid, $money)
{
    global $DB;
    $row = $DB->get_row("SELECT * FROM cmy_tools where tid='" . $tid . "' limit 1");
    if ($row['prid'] > 0) {
        $sql_data = [
            ':price1' => sprintf('%.2f', $money),
            ':tid'    => $tid,
        ];
        $sql = "UPDATE `pre_tools` SET `price1`=:price1 WHERE `tid`=:tid";
    } else {
        $sql_data = [
            ':prid'  => 0,
            ':price' => sprintf('%.2f', $money + $money * 0.3),
            ':cost'  => sprintf('%.2f', $money + $money * 0.25),
            ':cost2' => sprintf('%.2f', $money + $money * 0.20),
            ':tid'   => $tid,
        ];
        $sql = "UPDATE `pre_tools` SET `prid`=:prid, `price`=:price, `price`=:price, `price`=:price WHERE `tid`=:tid";
    }
    return $DB->query($sql, $sql_data);
}

function do_orders_jiuliu($row, $config, $goods_id)
{

    global $DB, $date;

    if ($row['status'] == -1) {
        $DB->query("UPDATE `pre_orders` set status='0', djzt='2' where id=:id", [':id' => $row['id']]);
    } elseif ($row['status'] > 0) {
        return '该订单已对接处理！';
    }

    $tool = $DB->get_row("SELECT * FROM cmy_tools where tid='" . $row['tid'] . "' limit 1");

    if (!$tool['card'] || !$tool['card_pass']) {
        return '对接商品缺少卡号或卡密';
    }

    $tool['num'] = $tool['num'] > 0 ? $tool['num'] : 1;
    $num         = $row['number'] * $tool['num'];

    $url  = $config["url"] . "index.php?m=Api&c=User&a=Addorder";
    $post = "card=" . $tool["card"] . "&pass=" . $tool["card_pass"] . "&goodsid=" . $goods_id . "&neednum=" . $num;

    $params = explode("|", $tool['goods_param']);
    $i      = 1;
    foreach ($params as $key) {
        if ($i == 1) {
            $post .= "&" . $key . "=" . $row['input'];
        } else {
            $post .= "&" . $key . "=" . $row['input' . $i];
        }
        $i++;
    }

    $data = get_curl($url . '&' . $post);
    $arr  = json_decode($data, true);
    if (is_array($arr)) {
        if ($arr['status'] == true && isset($arr['orderid'])) {
            $status = 1;
            if ($config['orderstatus']) {
                $status = $config['orderstatus'];
            }
            $djresult = '';
            if ($tool['result']) {
                $djresult = $tool['result'];
            }
            $DB->query("UPDATE `pre_orders` set result='" . $djresult . "',djorder='" . $arr['orderid'] . "',djzt='1',status='" . $status . "' where id='" . $row['id'] . "'");
            log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $post, '下单成功，订单号：' . $arr["orderid"], 1, $row['id']);
            return '下单成功，订单号：' . $arr["orderid"];
        } else {
            $DB->query("UPDATE `pre_orders` set status='0',djzt='2' where id=:id", [':id' => $row['id']]);
        }
        log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $post, '下单失败，' . $arr["info"], 0, $row['id']);
        return "下单失败," . $arr["info"];
    }

    $DB->query("UPDATE `pre_orders` set status='0', djzt='2' where id=:id", [':id' => $row['id']]);

    if (preg_match("/<p\\sclass=\"error\">(.*?)<\\/p>/", $data, $msg)) {
        $DB->query("UPDATE `pre_orders` set status='0',djzt='2' where id=:id", [':id' => $row['id']]);
        log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $post, '下单失败，' . $msg[1], 0, $row['id']);
        return '下单失败,' . $msg[1];
    }

    $DB->query("UPDATE `pre_orders` set status='0',djzt='2' where id=:id", [':id' => $row['id']]);
    log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $post, '下单失败，' . $data, 0, $row['id']);
    return $data;
}

function do_orders_guakebao($row, $config)
{
    global $DB, $date;

    if ($row['status'] == -1) {
        $DB->query("UPDATE `pre_orders` set status='0', djzt='2' where id=:id", [':id' => $row['id']]);
    } elseif ($row['status'] > 0) {
        return '该订单已对接处理！';
    }
    if ($row['value'] < 1) {
        $row['value'] = 1;
    }

    $tool = $DB->get_row("SELECT * FROM cmy_tools where tid=:tid limit 1", [':tid' => $row['tid']]);
    if ($tool['value'] < 1) {
        $tool['value'] = 1;
    }
    $goods_id = 0;
    if (preg_match("/busi_type_id=(\d+)/", $tool['goods_param'], $matchs)) {
        $goods_id = $matchs[1];
    } else {
        return '商品详情链接格式错误，正确格式为：http://guakebao.com:808/Busi/BusiTypeInfo.aspx?busi_type_id=100101';
    }
    $apikey = strSafeEnCode($config['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
    $data   = array(
        'action'                     => 'busi_make_order',
        'write_response_do_add_root' => true,
        'exception'                  => true,
        'confirmed'                  => true,
        'target_id'                  => $row['input'],
        'add_amount'                 => intval($row['value'] * $tool['value']),
        'type'                       => $goods_id,

    );
    $execute_content_list = '';
    if ($row['input2']) {
        $execute_content_list .= $row['input2'];
    }

    if ($row['input3']) {
        $execute_content_list .= "
" . $row['input3'];
    }

    if ($row['input4']) {
        $execute_content_list .= "
" . $row['input4'];
    }
    if ($row['input5']) {
        $execute_content_list .= "
" . $row['input5'];
    }

    $url = $config['url'] . "AppApi.ashx";

    $params          = http_build_query($data);
    $data['api_key'] = $apikey;
    $result          = shequ_get_curl($url, http_build_query($data), 0, 0, 0, 0, 0, $config['proxy']);
    //$result=get_curl($url,$post);
    //$json = xmlToArray($result);
    if (stripos($result, '<result_code>') !== false) {
        $result_code = getXmlVal($result, 'result_code');
        if ($result_code == '0') {
            $djorder = getXmlVal($result, 'order.id');
            $message = '下单成功，订单号：' . $djorder;
            $status  = 1;
            if ($config['orderstatus']) {
                $status = $config['orderstatus'];
            }

            $djresult = '';
            if ($tool['result']) {
                $djresult = $tool['result'];
            }
            $sql      = "UPDATE `pre_orders` SET `result`=:result,`djorder`=:djorder,`endtime`=:endtime,`status`=:status,`djzt`='1' where `id`=:id";
            $sql_data = array(
                ':result'  => $djresult,
                ':djorder' => $djorder,
                ':endtime' => $date,
                ':status'  => $status,
                ':id'      => $row['id'],
            );
        } else {
            $status   = 0;
            $sql      = "UPDATE `pre_orders` SET endtime=:endtime,status=:status,djzt=:djzt where id=:id";
            $sql_data = array(
                ':endtime' => $date,
                ':status'  => $status,
                ':djzt'    => '2',
                ':id'      => $row['id'],
            );
            $message = '下单失败，' . getXmlVal($result, 'result_message');
        }
    } else {
        $status   = 0;
        $sql      = "UPDATE `pre_orders` SET endtime=:endtime,status=:status,djzt=:djzt where id=:id";
        $sql_data = array(
            ':endtime' => $date,
            ':status'  => $status,
            ':djzt'    => '2',
            ':id'      => $row['id'],
        );
        $message = '对接站返回数据解析失败，' . $result;
        @writeLogs("下单到社区" . $config['url'] . "失败，返回文本：\n" . $result); //调试日志
    }
    $DB->query($sql, $sql_data);
    log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, $message, 0, $row['id']);
    return $message;
}

function do_orders_chengzi($row, $config)
{
    global $DB, $conf, $date;

    if ($row['status'] == -1) {
        $DB->query("UPDATE `pre_orders` set status='0', djzt='2' where id=:id", [':id' => $row['id']]);
    } elseif ($row['status'] > 0) {
        return '该订单已对接处理！';
    }

    if ($row['value'] < 1) {
        $row['value'] = 1;
    }

    $tool = $DB->get_row("SELECT * FROM cmy_tools where tid=:tid limit 1", [':tid' => $row['tid']]);
    if ($tool['value'] < 1) {
        $tool['value'] = 1;
    }
    $key  = strSafeEnCode($config['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
    $data = array(
        'url' => $row['input'],
        'num' => $row['value'] * $tool['value'],
        'pid' => $tool['goods_id'],
    );

    if (!empty($row['input2'])) {
        $data['url2'] = $row['input2'];
    }

    if (!empty($row['input3'])) {
        $data['url3'] = $row['input3'];
    }

    if (!empty($row['input4'])) {
        $data['url4'] = $row['input4'];
    }

    if (!empty($row['input5'])) {
        $data['url5'] = $row['input5'];
    }

    $url = $config['url'] . "api/index/add";

    $params      = http_build_query($data);
    $data['key'] = $key;

    $handle   = chenm_curl($url, http_build_query($data), 0, 0, 1, 0, 0, $config['proxy']);
    $result   = curl_exec($handle);
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    if ($conf['debug'] == 1) {
        @writeLogs("下单到社区" . $config['url'] . "返回：\n" . $result, $config['url'] . '.txt'); //调试日志
    }
    if ($httpCode == 200) {
        $text = '{' . getSubstr($result, '{', '}') . '}';
        $json = json_decode($text, true);
        if (is_array($json)) {
            if ($json['code'] == '200') {
                $djorder = $json['data'];
                $message = '下单成功，订单号：' . $djorder;
                $status  = 1;
                if ($config['orderstatus']) {
                    $status = $config['orderstatus'];
                }

                $djresult = '';
                if ($tool['result']) {
                    $djresult = $tool['result'];
                }
                $sql      = "UPDATE `pre_orders` SET `result`=:result,`djorder`=:djorder,`endtime`=:endtime,`status`=:status,`djzt`='1' where `id`=:id";
                $sql_data = array(
                    ':result'  => $djresult,
                    ':djorder' => $djorder,
                    ':endtime' => $date,
                    ':status'  => $status,
                    ':id'      => $row['id'],
                );
            } else {
                $status   = 0;
                $sql      = "UPDATE `pre_orders` SET endtime=:endtime,status=:status,djzt=:djzt where id=:id";
                $sql_data = array(
                    ':endtime' => $date,
                    ':status'  => $status,
                    ':djzt'    => '2',
                    ':id'      => $row['id'],
                );
                $message = '下单失败，' . $json['msg'];
            }
        } else {
            $status   = 0;
            $sql      = "UPDATE `pre_orders` SET endtime=:endtime,status=:status,djzt=:djzt where id=:id";
            $sql_data = array(
                ':endtime' => $date,
                ':status'  => $status,
                ':djzt'    => '2',
                ':id'      => $row['id'],
            );
            $message = '对接站返回数据解析失败，' . $result;
        }
    } else {
        $status   = 0;
        $sql      = "UPDATE `pre_orders` SET endtime=:endtime,status=:status,djzt=:djzt where id=:id";
        $sql_data = array(
            ':endtime' => $date,
            ':status'  => $status,
            ':djzt'    => '2',
            ':id'      => $row['id'],
        );
        $message = '对接站打开失败，' . $result;
    }

    $DB->query($sql, $sql_data);
    log_result($config["type"], $row['zid'], 'url：' . $config["url"] . '；shequ：' . $config["id"] . '；Data：' . $params, $message, 0, $row['id']);
    return $message;
}

if (!function_exists('getGoodsPrice_extend')) {
    function getGoodsPrice_extend($config, $goodsid = 0)
    {
        if (extend_class($config)) {
            $className = ucfirst($config['alias']);
            if ($config['password']) {
                $config['password'] = strSafeEnCode($config['password'], "DECODE", '');
            }
            if ($config['paypwd']) {
                $config['paypwd'] = strSafeEnCode($config['paypwd'], "DECODE", '');
            }
            $obj = new $className($config);
            if (method_exists($obj, 'getGoodsParams')) {
                return $obj->getGoodsParams($config, $goodsid);
            } else if (method_exists($obj, 'getGoodsPrice')) {
                return $obj->getGoodsPrice($config, $goodsid);
            } else {
                return [
                    'code' => -1,
                    'msg'  => '该对接网站的所属插件不支持获取详情，请联系插件开发者核实更新',
                ];
            }
        } else {
            extend_function($config['alias']);
            $funcName = 'getGoodsPrice_' . strtolower($config['alias']);
            if (function_exists($funcName)) {
                if ($config['password']) {
                    $config['password'] = strSafeEnCode($config['password'], "DECODE", '');
                }
                if ($config['paypwd']) {
                    $config['paypwd'] = strSafeEnCode($config['paypwd'], "DECODE", '');
                }
                return $funcName($config, $goodsid);
            } else {
                return [
                    'code' => -1,
                    'msg'  => '该对接网站的所属插件不支持，请检查或联系插件开发者更新',
                ];
            }
        }
    }
}

if (!function_exists('getGoods_extend')) {
    /**
     * 获取商品列表
     *
     * @param array $config         对接站数据
     * @param integer $category_id  分类ID
     * @param integer $page         分页页码
     * @return array
     */
    function getGoods_extend($config, $category_id = 0, $page = 1)
    {
        if ($config['password']) {
            $config['password'] = strSafeEnCode($config['password'], "DECODE", '');
        }
        if ($config['paypwd']) {
            $config['paypwd'] = strSafeEnCode($config['paypwd'], "DECODE", '');
        }
        if (extend_class($config)) {
            try {

                $className = ucfirst($config['alias']);
                $obj       = new $className($config);
                if (method_exists($obj, 'getGoodsList')) {
                    return $obj->getGoodsList($config, $category_id, $page);
                } elseif (method_exists($obj, 'getGoods')) {
                    return $obj->getGoods($config, $category_id, $page);
                }
                return [
                    'code' => -1,
                    'msg'  => '该对接网站的所属插件不支持获取商品，请联系插件开发者核实更新',
                ];
            } catch (\Throwable $th) {
                return [
                    'code' => -1,
                    'msg'  => '该插件获取商品遇到错误，' . $th->getMessage(),
                ];
            }
        } else {
            extend_function($config['alias']);
            $funcName = 'getGoods_' . strtolower($config['alias']);
            if (function_exists($funcName)) {
                return $funcName($config, $category_id, $page);
            } else {
                return [
                    'code' => -1,
                    'msg'  => '该对接网站的所属插件不支持获取商品，请联系插件开发者核实更新',
                ];
            }
        }
    }
}

if (!function_exists('getGoodsParams_extend')) {
    /**
     * 获取商品详情
     *
     * @param array $config
     * @return array
     */
    function getGoodsParams_extend($config, $goodsid = 0)
    {
        if ($config['password']) {
            $config['password'] = strSafeEnCode($config['password'], "DECODE", '');
        }
        if ($config['paypwd']) {
            $config['paypwd'] = strSafeEnCode($config['paypwd'], "DECODE", '');
        }
        if (extend_class($config)) {
            try {
                $className = ucfirst($config['alias']);
                $obj       = new $className($config);
                if (method_exists($obj, 'getGoodsParams')) {
                    return $obj->getGoodsParams($config, $goodsid);
                } else if (method_exists($obj, 'getGoodsPrice')) {
                    return $obj->getGoodsPrice($config, $goodsid);
                } else {
                    return [
                        'code' => -1,
                        'msg'  => '该对接网站的所属插件不支持获取详情，请联系插件开发者核实更新',
                    ];
                }
            } catch (\Throwable $th) {
                return [
                    'code' => -1,
                    'msg'  => '该插件获取详情遇到错误，' . $th->getMessage(),
                ];
            }
        } else {
            if (extend_function($config['alias'])) {
                $funcName = 'getGoodsParams_' . strtolower($config['alias']);
                if (function_exists($funcName)) {
                    return $funcName($config, $goodsid);
                }
            }
            return [
                'code' => -1,
                'msg'  => '该对接网站的所属插件不支持获取详情，请检查或联系插件开发者更新',
            ];
        }
    }
}

if (!function_exists('getGoodsCategory_extend')) {
    /**
     * 获取分类列表
     *
     * @param array $config
     * @return array
     */
    function getGoodsCategory_extend($config = [], $upcid = 0)
    {
        if ($config['password']) {
            $config['password'] = strSafeEnCode($config['password'], "DECODE", '');
        }
        if ($config['paypwd']) {
            $config['paypwd'] = strSafeEnCode($config['paypwd'], "DECODE", '');
        }
        if (extend_class($config)) {
            try {
                $className = ucfirst($config['alias']);
                $obj       = new $className($config);
                if (method_exists($obj, 'getCateList')) {
                    return $obj->getCateList($config, $upcid);
                } elseif (method_exists($obj, 'getGoodsClass')) {
                    return $obj->getGoodsClass($config, $upcid);
                } elseif (method_exists($obj, 'getGoodsCategory')) {
                    return $obj->getGoodsCategory($config, $upcid);
                } else {
                    return [
                        'code' => -1,
                        'msg'  => '该对接网站的所属插件不支持获取分类，请联系插件开发者核实更新',
                    ];
                }
            } catch (\Throwable $th) {
                return [
                    'code' => -1,
                    'msg'  => '该插件获取分类遇到错误，' . $th->getMessage(),
                ];
            }
        } else {
            if (extend_function($config['alias'])) {
                $funcName = 'getGoodsParams_' . strtolower($config['alias']);
                if (function_exists($funcName)) {
                    return $funcName($config, $upcid);
                }
            }
            return [
                'code' => -1,
                'msg'  => '该对接网站的所属插件不支持获取详情，请检查或联系插件开发者更新',
            ];
        }
    }
}

if (!function_exists('extend_function')) {
    /**
     * 自动加载插件函数库
     *
     * @param string $extendname
     * @return bool
     */
    function extend_function(string $extendname = '')
    {
        $extendname = str_replace('\\', '/', $extendname);
        $file       = ROOT . 'includes/core/extend/' . trim($extendname, '/') . '/function.php';
        if (file_exists($file)) {
            include_once $file;
            return true;
        }
        return false;
    }
}
