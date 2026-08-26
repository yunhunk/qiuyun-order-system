<?php

use core\Card;

if (!defined('IN_CRONLITE')) {exit(0);}
if (!defined("authcode")) {exit(0);}
if (!defined("SYS_KEY")) {exit(0);}

if (!function_exists('getPayType')) {
    function getPayType($type)
    {
        global $DB, $date;
        if (strstr($type, 'alipay') !== false) {
            $str = '支付宝';
        } elseif (strstr($type, 'qqpay') !== false) {
            $str = 'QQ钱包';
        } elseif (strstr($type, 'wxpay') !== false) {
            $str = '微信钱包';
        } elseif (strstr($type, 'tenpay') !== false) {
            $str = '财付通';
        } else {
            $str = '';
        }

        if (strstr($type, 'rmb') !== false) {
            return $str != "" ? $str . '在线支付' : '余额支付';
        } elseif (strstr($type, 'free') !== false) {
            return '免费领取';
        } elseif (strstr($type, 'free') !== false) {
            return '免费领取';
        } else {
            if (empty($str)) {
                $DB->query("INSERT INTO `pre_points` (`zid`, `action`, `point`, `bz`, `addtime`, `orderid`) VALUES ( ?, ?, ?, ?, ?, ?)", [1, '日志', '0', '某订单的支付类型解析异常，请提交工单给开发者！类型原始数据：' . $type, $date, '']);
            }
            return $str;
        }
    }
}

if (!function_exists('getSiteBuyPrice')) {
    function getSiteBuyPrice($tid, $zid)
    {
        global $DB;
        if ($zid > 1 && $tid > 0) {
            $row       = $DB->get_row("SELECT * FROM cmy_site WHERE zid= ? limit 1", array($zid));
            $price_obj = new \core\Price($zid, $row);
            $price_obj->setToolInfo($tid);
            $price_obj->setSuper(1);
            $price = $price_obj->getBuyPrice($tid);
            if ($price <= 0) {
                $price = 0;
            }
        }
        return round($price, 2);
    }
}

/**
 * 发放供货商提成
 * @param  array   $srow    订单信息
 * @param  array   $tool    商品信息
 * @param  array   $row     站点信息
 * @param  float   $money   订单金额
 * @param  int     $orderid 订单ID
 */
function processMasterUp($srow = [], $tool, $row, $money = 0.00, $orderid, $subname = '')
{
    global $DB;

    if ($tool['zid'] > 0) {
        $userrow = $DB->get_row("SELECT * FROM `cmy_master` WHERE `zid`= ? limit 1", [$tool['zid']]);

        if ($userrow['status'] == 1) {
            $point = 0;
            if ($tool['price1'] > 0) {
                $point = $tool['price1'];
            } elseif ($tool['price'] > 0) {
                $point = $tool['price'];
            }

            $goods_display = '';
            if ($tool['is_curl'] == 4) {
                $goods_display = '卡密';
            } elseif ($tool['is_curl'] == 2) {
                $goods_display = $tool['goods_type'] == 1 ? '卡密' : '代充';
            } elseif ($tool['is_curl'] == 1) {
                $goods_display = '代充';
            } else {
                $goods_display = '网盘';
            }

            if ($point > 0) {
                $point = round($point, 5);
                // 计算多份
                $point  = round($point * $srow['num'], 2);
                $income = $userrow['income'] + $point;
                $DB->exec("UPDATE `cmy_master` SET `income`='{$income}' WHERE `zid`= ? limit 1", [$tool['zid']]);
                if (is_numeric($srow['zid']) && $srow['zid'] > 0) {
                    addMasterPointLogs($tool['zid'], $point, '提成', '网站用户[' . $srow['zid'] . ']下单<b>' . $tool['name'] . '</b>, 共<b>' . $srow['num'] . '份</b> [' . $goods_display . ']', $srow['id']);
                } else {
                    addMasterPointLogs($tool['zid'], $point, '提成', '网站游客下单<b>' . $tool['name'] . '</b>, 共<b>' . $srow['num'] . '份</b> [' . $goods_display . ']', $srow['id']);
                }
            }
        }
        unset($row['price']);
        addWebLog('供货商提成日志', "[" . $srow['trade_no'] . "]提成处理发放成功", 'PointLog');
    } else {
        unset($row['price']);
        addWebLog('供货商提成日志', "[" . $srow['trade_no'] . "]提成处理发放失败, 不是供货商订单", 'PointLog');
    }
}

/**
 * 发放上级提成
 * @param  array   $srow    订单信息
 * @param  array   $tool    商品信息
 * @param  array   $row     站点信息
 * @param  float   $money   订单金额
 * @param  int     $orderid 订单ID
 */
function processPointUp($srow = [], $tool, $row, $money = 0.00, $orderid, $subname = '')
{
    global $DB;
    $point = 0;
    if ($row['upzid'] > 0) {
        $row2 = $DB->get_row("SELECT * FROM cmy_site WHERE `zid`= ? limit 1", [$row['upzid']]);
        if (is_array($row2) && $row2['power'] > $row['power']) {
            $num      = intval($srow['num']) > 0 ? $srow['num'] : 1;
            $buyPrice = getSiteBuyPrice($tool['tid'], $row2['zid']);
            $point    = sprintf('%.2f', $money - ($buyPrice * $num));
            $a        = $DB->count("SELECT count(*) FROM cmy_points WHERE `orderid`= ? and `action`='提成' and zid = ?", [$orderid, $row2['zid']]);
            if ($a == 0 && $point > 0) {
                $sql  = "UPDATE `pre_site` SET `point`=`point`+:point where `zid`=:zid";
                $data = [
                    ':point' => $point,
                    ':zid'   => $row2['zid'],
                ];
                $DB->query($sql, $data);
                if ($subname == '') {
                    $subname = '下级[' . $row['zid'] . ']';
                }
                $bz = '您的' . $subname . '消费' . $srow['money'] . '元购买 ' . $tool["name"] . ' 共' . $srow["num"] . '份（' . $orderid . '），获得 ' . $point . ' 元提成。 当前提成账户：' . sprintf('%.2f', $row2['point'] + $point) . '元';
                addPointLogs($row2['zid'], $point, '提成', $bz, $orderid);
            }
            if ($point >= 0) {
                //继续发放上级的上级
                $subname = '下级[' . $row2['zid'] . ']的' . $subname;
                processPointUp($srow, $tool, $row2, $money - $point, $orderid, $subname);
            }
        }
    }
    unset($row['price']);
    addWebLog('上级提成日志', "[" . $srow['trade_no'] . "]提成处理发放成功", 'PointLog');
}

/**
 * 发放提成
 * @param  array   $srow    订单信息
 * @param  array   $tool    商品信息
 * @param  int     $orderid 订单ID
 */
function processPoint($srow = [], $tool = [], $orderid = 0)
{
    global $DB;
    $point = 0;
    addDebugLog('断点12', json_encode($srow), 'ProcessOrder', 1);
    if ($srow['zid'] > 1) {
        //addDebugLog('断点13', '', 'ProcessOrder', 1);
        $row2 = $DB->get_row("SELECT * FROM cmy_site WHERE zid= ? limit 1", [$srow['zid']]);
        if (is_array($row2) && $row2['power'] > 0) {
            addDebugLog('断点13', '', 'ProcessOrder', 1);
            $num      = $srow['num'] > 0 ? intval($srow['num']) : 1;
            $buyPrice = getSiteBuyPrice($tool['tid'], $row2['zid']);
            $point    = sprintf('%.2f', $srow['money'] - ($buyPrice * $num));
            $a        = intval($DB->count("SELECT count(*) FROM cmy_points WHERE `orderid`= ? and `action`='提成' and zid = ?", [$orderid, $row2['zid']]));
            addDebugLog('断点14', '1份成本：' . $buyPrice . "\n记录数量：" . $a . "\n提成：" . $point, 'ProcessOrder', 1);
            if ($a == 0 && $point > 0) {
                $sql  = "UPDATE `pre_site` SET `point`=`point`+:point where `zid`=:zid";
                $data = [
                    ':point' => $point,
                    ':zid'   => $row2['zid'],
                ];
                $DB->query($sql, $data);
                $bz = '您的下级游客消费' . $srow['money'] . '元购买 ' . $tool["name"] . ' 共' . $srow["num"] . '份（' . $orderid . '），获得 ' . $point . ' 元提成。 当前提成账户：' . sprintf('%.2f', $row2['point'] + $point) . '元';
                addPointLogs($row2['zid'], $point, '提成', $bz, $orderid);
            }
        }
    }
    return $point;
}

function sendPoints($srow, $orderid, $tool)
{
    global $DB, $date, $conf, $webConfig;
    $is_cart = "";
    if (isset($srow['cart']) && $srow['cart'] == 1) {
        $is_cart = "通过购物车";
    }

    // addDebugLog('断点2', '', 'ProcessOrder', 1);
    $buyType = getPayType($srow['type']);
    if ($srow['zid'] > 0) {
        try {
            if ($srow['zid'] > 1) {
                $money    = round($srow["money"], 2);
                $tc_point = 0;
                $siterow  = $DB->get_row("SELECT * FROM pre_site WHERE zid= ? limit 1", [$srow['zid']]);
                if (empty($siterow['power'])) {
                    $siterow['power'] = 0;
                }
                $srow['type'] = strtolower($srow['type']);
                $point        = 0;
                // addDebugLog('断点3', '', 'ProcessOrder', 1);
                if (preg_match('/rmb/i', $srow['type'])) {
                    //已登录用户 但不是余额付款的写入一下明细
                    if ($srow['type'] != "rmb") {
                        //余额支付的已写入明细
                        if ($money > 0) {
                            $bz = '您消费' . $money . '元使用' . $buyType . '购买 ' . $tool["name"] . ' 共' . $srow["num"] . '份！当前余额' . $siterow['money'] . '元！';
                        } else {
                            $bz = '您免费领取了 ' . $tool["name"] . ' 共' . $srow["num"] . '份！当前余额' . $siterow['money'] . '元！';
                        }
                        addPointLogs($srow['zid'], $money, '消费', $bz, $orderid);
                    }
                } else {
                    // addDebugLog('断点4', '', 'ProcessOrder', 1);
                    //购买者是游客且站点等级大于0就发放提成
                    if ($siterow['power'] > 0) {
                        addDebugLog('断点5', '', 'ProcessOrder', 1);
                        $point = processPoint($srow, $tool, $orderid);
                    }
                }
                // addDebugLog('断点6', '', 'ProcessOrder', 1);
                //当前站点等级小于旗舰版就发放上级提成
                if ($siterow['power'] < 2 && $siterow['upzid'] > 1) {
                    if ($point > 0) {
                        //如果分站已经吃了游客的提成，这部分金额就要减掉，避免分站的上级提成过高
                        $money = round($srow['money'] - $point, 2);
                        processPointUp($srow, $tool, $siterow, $money, $orderid, '下级[' . $srow['zid'] . ']的游客');
                    } else {
                        processPointUp($srow, $tool, $siterow, $srow['money'], $orderid);
                    }
                }
            }
        } catch (\Exception $e) {
            $bz = $srow['zid'] . '的订单 ' . $tool["name"] . ' 共' . $srow["num"] . '份（' . $orderid . '）写入提成失败，' . $e->getMessage();
            addWebLog('订单提成错误', $bz, 'PointLog');
            addPointLogs(1, $srow["money"], '错误记录[分站]', $bz, $orderid);
        }

        try {
            // 发放供货商提成
            processMasterUp($srow, $tool, $siterow, $srow['money'], $orderid);
            addDebugLog('断点7', '', 'ProcessOrder', 1);
        } catch (\Exception $th) {
            $bz = $srow['zid'] . '的订单 （' . $orderid . '）写入发放供货商提成失败，' . $e->getMessage();
            addWebLog('订单供货商提成错误', $bz, 'PointLog');
            addPointLogs(1, $srow["money"], '错误记录[供货商]', $bz, $orderid);
        }
    } else {
        if ($srow["money"] > 0) {
            $bz = '您的主站游客' . $is_cart . '消费' . $srow["money"] . '元使用' . $buyType . '购买 ' . $tool["name"] . ' 共' . $srow["num"] . '份！（' . $orderid . '）';
        } else {
            $bz = '您的主站游客' . $is_cart . '免费领取了 ' . $tool["name"] . ' 共' . $srow["num"] . '份！（' . $orderid . '）';
        }
        addPointLogs($srow['zid'], $srow["money"], '购买', $bz, $orderid);
    }

    addWebLog('系统日志', '[' . $orderid . ']单所有提成已处理', 'PointLogAll');

    return true;
}

function do_orders_all($orderid)
{
    global $DB, $date;
    global $date;
    $srow = $DB->get_row("SELECT * FROM cmy_orders where id='" . $orderid . "' limit 1");
    if ($srow['status'] > 0) {
        if ($srow['djorder']) {
            $tool  = $DB->get_row("SELECT * FROM cmy_tools where tid='" . $srow["tid"] . "' limit 1");
            $shequ = $DB->get_row("SELECT * FROM cmy_shequ where id='" . $tool["shequ"] . "' limit 1");
            return "该订单已对接到" . $shequ['url'] . "，订单号" . $srow['djorder'];
        } else {
            return "该订单已处理过了~";
        }
    }

    $tool   = $DB->get_row("SELECT * FROM cmy_tools where tid='" . $srow["tid"] . "' limit 1");
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
        do_curl($tool, $input, $num, $srow);
        $status = "访问指定URL成功";
    } elseif ($tool["is_curl"] == 4) {
        $status = do_orders_km($srow, $tool, $num);
    } else {
        if ($tool["is_curl"] == 2) {
            if ($srow['djorder']) {
                $shequ  = $DB->get_row("SELECT * FROM cmy_shequ where id='" . $tool["shequ"] . "' limit 1");
                $status = "该订单已对接到" . $shequ['url'] . "，订单号" . $srow['djorder'];
            } else {
                $status = do_orders_shequ($srow, $tool, $num);
            }

        } else {
            $status = "该订单未配置对接，请手动处理";
        }
    }

    return $status;

}

function cartOrderMesh($shop_id_arr, $shop_row)
{
    global $DB, $date, $conf;
    $cart_id  = intval($shop_row['id']);
    $input    = $shop_row['input'];
    $tid      = $shop_row['tid'];
    $mesh     = 1;
    $cart_num = count($shop_id_arr);
    for ($i = 0; $i < $cart_num; $i++) {
        $shop_id = intval($shop_id_arr[$i]);
        $row     = $DB->get_row("SELECT * FROM cmy_cart where id='" . $shop_id . "' limit 1");
        if ($cart_id != $shop_id && $row) {
            if ($input == $row['input'] && $tid == $row['tid'] && $row['tid'] > 0) {
                $res = $DB->query("UPDATE `pre_cart` set `num`=`num`+" . $row['num'] . ",`money`=`money`+" . $row['money'] . " where `id`='" . $cart_id . "'");
                if ($res) {
                    $mesh++;
                    $DB->query("DELETE FROM `pre_cart` where `id`='" . $shop_id . "'");
                }
            }
        }
    }
    return array('mesh' => $mesh, 'cart_id' => $cart_id);
}

function getAstr($data = [], $key = '')
{
    if (!is_array($data)) {
        return '';
    } else {
        if (stripos($key, '.') !== false) {
            $keys = explode('.', $key);
            if (count($keys) >= 2) {
                $key1 = $keys[0];
                $key2 = $keys[1];
                if (array_key_exists($key1, $data)) {
                    if (is_numeric($key1) && $key1 = intval($key1)) {
                        $data2 = $data[$key1];
                    } else {
                        $data2 = $data[$key1];
                    }
                    if (is_array($data2) && array_key_exists($key2, $data2)) {
                        if (is_numeric($key2) && $key2 = intval($key2)) {
                            $value = $data[$key2];
                        } else {
                            $value = $data[$key2];
                        }
                        return $value;
                    }
                    return '';
                }
            } else {
                if (array_key_exists($key, $data)) {
                    return (string) $data[$key];
                }
                return '';
            }

        } else {
            if (array_key_exists($key, $data)) {
                return $data[$key];
            }
            return '';
        }
    }
}

function processOrderFenzhan($srow, $input)
{
    global $DB, $date, $conf;

    $type        = $input[0];
    $rmb         = $conf["fenzhan"] ? $conf["fenzhan"] : 0;
    $keywords    = $conf["keywords"];
    $description = $conf["description"];
    $anounce     = addslashes(stripslashes($conf["anounce"]));
    $alert       = addslashes(stripslashes($conf["alert"]));
    $template    = $conf['template_default'] ? $conf['template_default'] : 'default';
    if ($type == 'update') {
        $zid     = intval($input[1]);
        $kind    = intval($input[2]);
        $siteurl = daddslashes($input[3]);
        $name    = $input[4];
        $endtime = $input[5];
        if (strtotime($endtime) <= 0) {
            $endtime = date("Y-m-d H:i:s", strtotime("+365 day", time()));
        }
        $sqlData = [':kind' => $kind, ':siteurl' => $siteurl, ':name' => $name, ':keywords' => $keywords, ':description' => $description, ':template' => $template, ':anounce' => $anounce, ':alert' => $alert, ':endtime' => $endtime, ':zid' => $zid];
        $sql     = "UPDATE `pre_site` set `power`=:kind,`siteurl`=:siteurl,`sitename`=:name,`keywords`=:keywords,`description`=:description,`template`=:template,`anounce`=:anounce,`alert`= :alert,`endtime`=:endtime where `zid`=:zid";
        if (!$DB->query($sql, $sqlData)) {
            $db_error = $DB->error();
            addWebLog('升级分站', "订单号【" . $srow['trade_no'] . "】升级分站失败，" . $db_error . "\nSQL语句：" . $sql . "\nSQL数据：" . @json_encode($sqlData), 'FenzhanUp', $srow['zid']);
        }
    } else {
        $upzid   = intval($srow["zid"]);
        $kind    = intval($input[1]);
        $siteurl = $input[2];
        $user    = daddslashes($input[3]);
        $pwd     = daddslashes($input[4]);
        $salt    = random(6);
        $name    = daddslashes($input[5]);
        $qq      = $input[6];
        $endtime = $input[7];
        if (strtotime($endtime) <= 0) {
            $endtime = date("Y-m-d H:i:s", strtotime("+365 day", time()));
        }
        $sqlData = [':upzid' => $upzid, ':kind' => $kind, ':siteurl' => $siteurl, ':user' => $user, ':pwd' => getEncodePwd($pwd, $salt), ':salt' => $salt, ':qq' => $qq, ':name' => $name, ':keywords' => $keywords, ':description' => $description, ':template' => $template, ':anounce' => $anounce, ':alert' => $alert, ':addtime' => $date, ':endtime' => $endtime];
        $sql     = "INSERT into `pre_site` (`upzid`, `power`, `siteurl`, `user`, `pwd`, `salt`, `money`, `qq`, `sitename`, `keywords`, `description`, `template`, `anounce`, `alert`, `addtime`, `endtime`, `status`) values (:upzid, :kind, :siteurl, :user, :pwd, :salt, '0', :qq, :name, :keywords, :description, :template, :anounce, :alert, :addtime, :endtime,'1')";
        $zid     = $DB->insert($sql, $sqlData);
        if (!$zid) {
            $db_error = $DB->error();
            addWebLog('开通分站', "订单号【" . $srow['trade_no'] . "】开通分站失败，" . $db_error . "\nSQL语句：" . $sql . "\nSQL数据：" . @json_encode($sqlData), 'FenzhanKt', $srow['zid']);
        }
    }

    if (!$zid) {
        addPointLogs(1, $srow['money'], '分站日志', "订单号【" . $srow['trade_no'] . "】开通分站失败，[SQL：" . $sql . "]：" . $db_error);
        return false;
    }

    addPointLogs($zid, $srow['money'], '开通分站', "您花费" . $srow['money'] . "元成功开通" . ($kind == 2 ? '旗舰版' : '专业版') . "分站");

    if ($rmb > 0) {
        $DB->query("UPDATE `pre_site` set `money`=`money`+ ? where `zid`= ?", array($rmb, $zid));
        addPointLogs($zid, $rmb, '赠送', "你首次开通分站获赠" . $rmb . "元余额！");
    }
    if ($type == 'update') {
        //升级
        $row1  = $DB->get_row("SELECT * FROM cmy_site WHERE zid= ? limit 1", [$srow['zid']]);
        $upzid = $row1['upzid'];
        $user  = $row1['user'];
        if ($upzid == $srow["zid"]) {
            $upzid = 0;
        }
    } else {
        $upzid = $srow["zid"];
    }
    if (isset($upzid) && $upzid > 1) {
        $cost = getAddSiteCost($kind);
        if ($kind == 2 && $srow["money"] > $cost) {
            $tc_point = sprintf('%.2f', $srow["money"] - $cost);
            $kind_str = '旗舰分站';
        } else if ($kind == 1 && $srow["money"] > $cost) {
            $tc_point = sprintf('%.2f', $srow["money"] - $cost);
            $kind_str = '专业分站';
        }

        if ($tc_point > 0) {
            $DB->query("UPDATE `pre_site` set `point`=`point`+ ? where `zid`= ?", array($tc_point, $upzid));
            addPointLogs($upzid, $tc_point, '提成', "你网站的用户 " . $user . " 开通" . $kind_str . "你获得" . $tc_point . "元提成！");
        }
    }
    return $zid;
}

function processOrderCheck($srow = [], $tool = [])
{
    global $conf;
    //拦截异常订单
    if (!isset($conf['order_price_check_max']) || $conf['order_price_check_max'] < 50000) {
        $conf['order_price_check_max'] = 100000;
    }

    $payTypeList = ['free', 'gift_free', 'kj_free', 'rmb', 'alipay', 'wxpay', 'qqpay', 'rmb_alipay', 'rmb_wxpay', 'rmb_qqpay'];
    if (!in_array($srow['type'], $payTypeList)) {
        addWebLog('订单创建', "订单号【" . $srow['trade_no'] . "】异常，支付类型不合法，该订单类型为" . $srow['type'], 'ProcessOrder', $srow['zid']);
        throw new Exception("支付类型不合法，该订单类型为" . $srow['type']);
        return 0;
    } elseif ($srow["tid"] > 0 && !preg_match("/^[0-9\.]+$/", $srow['money'])) {
        if ($srow["tid"] > 0) {
            if ($tool["value"] < 1) {
                $tool["value"] = 1;
            }
            if ($srow["num"] < 1) {
                $srow["num"] = 1;
            }
            $money = $tool['price'] * $srow["num"] * $tool["value"];
            addWebLog('订单创建', "订单号【" . $srow['trade_no'] . "】异常，订单金额较可疑，已自动拦截订单，该订单金额为" . $srow['money'] . "，商品单价为" . $tool['price'] . "，该订单价值" . $money . "元", 'ProcessOrder', $srow['zid']);
            throw new Exception("异常，订单金额较可疑，已自动拦截订单，该订单金额为" . $srow['money'] . "，商品单价为" . $tool['price']);
        } else {
            addWebLog('订单创建', "订单号【" . $srow['trade_no'] . "】异常，订单金额较可疑，已自动拦截订单，该订单金额为" . $srow['money'] . "，订单类型为【" . $srow['tid'] . "】", 'ProcessOrder', $srow['zid']);
            throw new Exception("】异常，订单金额较可疑，已自动拦截订单，该订单金额为" . $srow['money']);
        }
        return 0;
    } elseif ($srow['type'] != 'gift_free' && $srow["tid"] > 0 && $srow['money'] == 0 && $tool['price'] != 0) {
        addWebLog('订单创建', "订单号【" . $srow['trade_no'] . "】异常，订单金额较可疑，已自动拦截订单，该订单金额为" . $srow['money'] . "，商品售价为" . $tool['price'], 'ProcessOrder', $srow['zid']);
        throw new Exception("订单金额较可疑，已自动拦截订单，该订单金额为" . $srow['money']);
        return 0;
    } elseif ($srow["tid"] == -1 && !preg_match("/^[0-9\.]+$/", $srow['money']) || $srow['money'] > $conf['order_price_check_max']) {
        addWebLog('代理充值', "订单号【" . $srow['trade_no'] . "】异常，充值金额过大较可疑，已自动拦截订单，该充值金额为" . $srow['money'], 'ProcessOrder', $srow['zid']);
        throw new Exception("充值金额过大较可疑，已自动拦截订单，该充值金额为" . $srow['money']);
        return 0;
    }
    return 1;
}

/**
 * 处理订单
 *
 * @param array   $srow 支付订单数据
 * @return int|string
 */
function processOrderAll($srow)
{
    global $DB, $date, $conf, $webConfig, $cookiesid;

    foreach ($srow as $key => $value) {
        $srow[$key] = addslashes($value);
    }

    if ($webConfig['debug']) {
        addWebLog('断点-2', $srow, 'ProcessOrder', 1);
    }

    $tool = $DB->get_row("SELECT * FROM cmy_tools where tid='" . $srow["tid"] . "' limit 1");
    if ($srow["tid"] > 0 && !is_array($tool)) {
        return 0;
    }

    if (processOrderCheck($srow, $tool) == 0) {

        return 0;
    }

    $siterow = $DB->get_row("SELECT * FROM cmy_site WHERE zid= ? limit 1", array($srow['zid']));
    if ($siterow && $siterow['regular'] == 1) {
        $DB->query("UPDATE `pre_site` set `money`=0 where `zid`='" . $srow["zid"] . "'");
    }

    $input = explode("|", $srow["input"]);
    if ($srow['zid'] < 1) {
        $srow['zid'] = 1;
    }

    if ($srow["tid"] == (0 - 3)) {
        if (stripos($srow['type'], 'rmb') !== false) {
            $buyType = "消费";
        } else {
            $buyType = "购买";
        }
        $shop_id_arr = explode("|", $srow["input"]);
        foreach ($shop_id_arr as $key => $shop_id) {
            $cart_row = $DB->get_row("SELECT * FROM `pre_cart` where `id`='{$shop_id}' limit 1");
            if ($cart_row && $cart_row['input']) {
                addDebugLog('Dbug', "购物车订单[{$shop_id}]；订单金额[" . $cart_row['money'] . "]；订单数据[" . $cart_row['input'] . "]", 'processOrderAll');
                $cart_row['trade_no'] = $srow["trade_no"];
                $cart_row['type']     = $srow["type"];
                $cart_row['cart']     = $shop_id;
                if (processOrderAll($cart_row)) {
                    $DB->query("UPDATE `pre_cart` set `endtime`='{$date}',`status`='1' where `id`='{$shop_id}'");
                }
            }
            //取消购物车订单合并
            //$MeshResult = cartOrderMesh($shop_id_arr, $shop_row);
            //$cart_id    = intval($MeshResult['cart_id']);
            // if ($cart_id > 0 && $cart_num > 1) {
            //     $cart_row             = $DB->get_row("SELECT * FROM cmy_cart where id='" . $cart_id . "' limit 1");
            //     $cart_row['trade_no'] = $srow["trade_no"];
            //     $cart_row['type']     = $srow["type"];
            //     $cart_row['cart']     = $cart_id;
            //     if (processOrderAll($cart_row)) {
            //         $DB->query("UPDATE `pre_cart` set `endtime`='" . $date . "',`status`='1' where `id`='" . $cart_id . "'");
            //     }
            // } else {

            // }
        }
        return true;
    }

    if ($srow["tid"] == (0 - 4) && intval($srow['input']) > 1) {
        //供货商充值余额
        $buyType = getPayType($srow['type']);
        $DB->query("UPDATE `pre_master` set `income`=`income`+" . $srow["money"] . " where `zid`='" . $srow["input"] . "'");
        addMasterPointLogs($srow["zid"], $srow["money"], '充值', "你通过" . $buyType . "在线充值了" . $srow["money"] . "元余额！", null);
        return true;
    } elseif ($srow["tid"] == (0 - 1) && intval($srow['input']) > 1) {
        //分站和用户充值余额
        $buyType = getPayType($srow['type']);
        $DB->query("UPDATE `pre_site` set `money`=`money`+" . $srow["money"] . " where `zid`='" . $srow["input"] . "'");
        addPointLogs($srow["zid"], $srow["money"], '充值', "你通过" . $buyType . "在线充值了" . $srow["money"] . "元余额！", null);
        if ($conf['fz_fanli_open'] == 1 && $conf['fz_fanli_list'] != "") {
            $fanli_list = explode(',', $conf['fz_fanli_list']);
            $fanli_arr  = array();
            foreach ($fanli_list as $row) {
                $arr                = explode('|', $row);
                $fanli_arr[$arr[0]] = $arr[1];
            }
            krsort($fanli_arr);
            $money = 0;
            foreach ($fanli_arr as $key => $value) {
                if ($srow["money"] >= $key) {
                    $money = sprintf("%.2f", $srow["money"] * $value / 100);
                    break;
                }
            }
            if ($money < $srow["money"] && $money > 0) {
                $DB->query("UPDATE `pre_site` set `money`=`money`+" . $money . " where `zid`='" . $srow["input"] . "'");
                addPointLogs($srow["zid"], $money, '返利', "你刚刚通过" . $buyType . "在线充值了" . $srow["money"] . "，本次返利" . $money . "元已到账，感谢充值！", null);
            }
        }
        return true;
    } elseif ($srow["tid"] == (0 - 2)) {
        //开通分站
        return processOrderFenzhan($srow, $input);
    }

    $row1 = $DB->get_row("SELECT * FROM `pre_orders` where `payorder`=:payorder", [':payorder' => $srow['trade_no']]);
    if ($srow['cart'] < 1 && $row1) {
        return '该订单已处理，' . $row1['id'];
    }

    $buyType = getPayType($srow['type']);

    $status = 0;
    $djzt   = 0;

    $stock_row = array();
    if ($srow["stock_id"] > 0) {
        $stock_row = $DB->get_row("SELECT * FROM `pre_stock` where id='" . $srow["stock_id"] . "'");
    }

    if (stripos($srow["type"], 'rmb') !== false) {
        $fz_price = $srow["money"];
    } else {
        $fz_price = null;
        if ($srow['zid'] > 0) {
            $fz_price = getSiteBuyPrice($srow['tid'], $srow['zid']);
            $fz_price = sprintf('%.2f', $fz_price * $srow["num"]);
        }
    }

    $bz = '';

    if ($srow["kid"] > 0) {
        $srow["type"] = 'kj_' . $srow["type"];
        $bz           = "来自砍价任务ID（" . $srow["kid"] . "）的砍价订单";
    }
    if ($srow["stock_id"] > 0) {
        $price1 = isset($stock_row['price1']) ? $stock_row['price1'] * $srow["num"] : $tool["price1"] * $srow["num"];
        $price1 = sprintf("%.2f", $price1);

    } else {
        $price1 = sprintf("%.2f", $tool["price1"] * $srow["num"]);
    }

    $params = array(
        ":tid"       => $srow["tid"],
        ":sid"       => $tool["zid"],
        ":zid"       => $srow["zid"],
        ":type"      => $srow["type"],
        ":input"     => getAstr($input, '0'),
        ":input2"    => getAstr($input, '1'),
        ":input3"    => getAstr($input, '2'),
        ":input4"    => getAstr($input, '3'),
        ":input5"    => getAstr($input, '4'),
        ":inputattr" => $srow["inputattr"],
        ":stock_id"  => $srow["stock_id"],
        ":bz"        => $bz,
        ":value"     => $srow["num"],
        ":userid"    => $srow["userid"],
        ":addtime"   => $date,
        ":status"    => '-1',
        ":amount"    => $srow["money"],
        ":cost"      => $fz_price,
        ":price1"    => $price1,
        ":price2"    => $tool["price2"] > 0 ? $tool["price2"] : 0,
        ":payorder"  => $srow["trade_no"],
    );

    if ($webConfig['debug']) {
        addWebLog('断点-1', $params, 'ProcessOrder', 1);
    }

    if ($srow['cart'] > 0) {
        $params[':cartorder'] = $srow["cart"];
        $orderid              = $DB->insert("INSERT INTO `pre_orders` (`tid`, `sid`, `zid`, `type`, `input`, `input2`, `input3`, `input4`, `input5`, `inputattr`, `stock_id`, `bz`, `value`, `userid`, `addtime`, `status`, `money`, `cost`, `price1`, `payorder`, `cartorder`) VALUES (:tid, :sid, :zid, :type, :input, :input2, :input3, :input4, :input5, :inputattr, :stock_id, :bz, :value, :userid, :addtime, :status, :amount, :cost, :price1, :payorder, :cartorder)", $params);

    } else {
        $orderid = $DB->insert("INSERT INTO `pre_orders` (`tid`, `sid`, `zid`, `type`, `input`, `input2`, `input3`, `input4`, `input5`, `inputattr`, `stock_id`, `bz`, `value`, `userid`, `addtime`, `status`, `money`, `cost`, `price1`, `payorder`) VALUES (:tid, :sid, :zid, :type, :input, :input2, :input3, :input4, :input5, :inputattr, :stock_id, :bz, :value, :userid, :addtime, :status, :amount, :cost, :price1, :payorder)", $params);
    }

    if (intval($orderid) < 1) {
        addPointLogs('1', $srow["money"], "日志", "订单生成失败，订单号：" . $srow["trade_no"] . "；提交数据：" . $srow["input"] . "！请检查," . $DB->error());
        return false;
    }

    if ($webConfig['debug']) {
        addWebLog('断点0', '', 'ProcessOrder', 1);
    }

    $row = $DB->get_row("SELECT * FROM `pre_orders` where `id`=:id", [':id' => $orderid]);
    if (!$row) {
        return '该订单不存在或正在处理！';
    } elseif ($row['status'] >= 0) {
        return '该订单正在处理！';
    } else {
        if ($tool["value"] < 1) {
            $tool["value"] = 1;
        }

        if ($srow["num"] < 1) {
            $srow["num"] = 1;
        }

        if ($srow["stock_id"] > 0) {
            $num = $stock_row['value'] > 1 ? intval($stock_row["value"] * $srow["num"]) : $srow["num"];
        } else {
            $num = intval($tool["value"] * $srow["num"]);
        }

        $srow['orderid'] = $orderid;

        // if (function_exists('hook')) {
        //     hook('order_before', $srow);
        // }

        //订单生成成功时库存减少
        if ($tool['stock_open'] == 1 || $tool['is_curl'] == 1 && $tool['stock'] > 0) {
            $order_num = $srow['value'] > 0 ? $srow['value'] : 1;
            $sql       = "UPDATE `pre_tools` SET `stock`=`stock`-'{$order_num}' where `tid`=:tid";
            $DB->exec($sql, [':tid' => $tool['tid']]);
        }

        //销量更新
        $sql = "UPDATE `pre_tools` SET `sale`=`sale`+:sale where `tid`=:tid";
        @$DB->exec($sql, [':sale' => $num, ':tid' => $tool['tid']]);

        if ($webConfig['debug']) {
            addWebLog('断点1', '', 'ProcessOrder', 1);
        }

        sendPoints($srow, $orderid, $tool);

        if ($webConfig['debug']) {
            addWebLog('断点10', '', 'ProcessOrder', 1);
        }

        $result = "";

        if ($conf['free_mail_open'] == 1 && $srow["money"] == 0 && preg_match('/[1-9]{1}[0-9]{4,9}/', $input[0]) && ($tool["is_curl"] == 2 || $tool["is_curl"] == 1)) {
            $msg       = getFreeMod($srow, $tool);
            $sub       = $conf['free_mail_title'] ? $conf['free_mail_title'] : "亲，你获得一个新奖品~";
            $mail_name = '';
            if (preg_match('/^[1-9]{1}[\d]{4,10}$/', $input[0])) {
                $mail_name = $input[0] . '@qq.com';
            } elseif (preg_match('/^[\w\.\-]+@[\w\.\-]+$/', $input[0])) {
                $mail_name = $input[0];
            }
            if (!empty($mail_name)) {
                $result = send_mail($mail_name, $sub, $msg);
                if ($result !== true) {
                    $bz = '免费商品邮件发送失败，返回:' . $result;
                    addPointLogs(1, $srow["money"], '日志', $bz);
                }
            }
        }

        if ($tool["is_curl"] == 1) {
            //POST访问
            $result = do_curl($tool, $input, $num, $srow);
            if (stripos($result, 'ErrorResult') === false && !empty($result)) {
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
            log_result('URL访问', $srow['zid'], 'url：' . $tool["curl"] . '；Data：' . $post, '；返回：' . $result, 0, $orderid);
            $DB->query("UPDATE `pre_orders` SET `djzt`='" . $djzt . "',`status`='" . $status . "'  where id='" . $orderid . "'");
        } else if ($tool["is_curl"] == 5) {
            //直接显示内容
            $DB->query("UPDATE `pre_orders` set `bz`='订单处理成功',`result`='" . addslashes($tool['result']) . "',`status`=1  where `id`='" . $orderid . "'");
        } else if ($tool["is_curl"] == 4) {
            //虚拟卡密
            $DB->query("UPDATE `pre_orders` set `status`='0',`djzt`='4' where `id`= ?", [$orderid]);
            $result = do_orders_km($row, $tool, $num);
            if (stripos($result, '成功') !== false) {
                $status = 1;
                $djzt   = 3;
            } else {
                $status = 0;
                $djzt   = 4;
            }
            $DB->query("UPDATE `pre_orders` set `bz`='" . $result . "',`djzt`='" . $djzt . "',`status`='" . $status . "'  where id='" . $orderid . "'");
        } else {
            if ($tool["is_curl"] == 2) {
                $djzt = 2;
                $DB->query("UPDATE `pre_orders` set `djzt`='" . $djzt . "' where id='" . $orderid . "'");

                $goods_param = explode("|", $tool["goods_param"]);
                $i           = 0;
                foreach ($input as $val) {
                    if ($val != "") {
                        $data[$goods_param[$i]] = $val;
                        $i                      = $i + 1;
                    }
                }

                if ($row['djorder']) {
                    $DB->query("UPDATE `pre_orders` set `djzt`='1' where `id`='" . $orderid . "'");
                    return $orderid;
                } else {
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

                        if (function_exists('do_orders_extend')) {
                            $result = do_orders_extend($row, $shequ, $tool, $num, true);
                            if (stripos($result, "success") === false && stripos($result, "成功") === false) {
                                $djzt = 2;

                                $DB->query("UPDATE `pre_orders` SET `djzt`='{$djzt}',`status`='0' where `id`='" . $orderid . "'");

                                if ($conf["shequ_tixing"] == 1 && $conf['mail_cloud'] >= 0) {
                                    $sub       = "自动下单到对接站失败提醒";
                                    $msg       = ($tool["inputname"] ? $tool["inputname"] : "账号：") . $input[0] . " 商品tid: " . $tool["tid"] . "<br/><br/><b>返回结果：</b>请登录后台查看日志<br/>----------<br/>" . $_SERVER["HTTP_HOST"] . "<br/>" . $date;
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
                            $result = "程序文件被破坏或安装更新不完整，处理订单失败~";
                        }
                    } else {
                        $status = 0;
                        $result = "对接货源平台不存在或账号密码未填写";
                        log_result($shequ["type"], $srow['zid'], http_build_query($data), "下单失败，" . $result, 0, $orderid);
                    }
                }
            } else {
                $DB->query("UPDATE `pre_orders` set `status`='0' where `id`=:id", [':id' => $orderid]);
                if ($tool["is_curl"] == 3) {
                    $sub       = $conf["sitename"] . "下单成功提醒";
                    $msg       = ($tool["inputname"] ? $tool["inputname"] : "QQ") . $input[0] . " 已成功下单商品: " . $tool["name"] . "<br/>----------<br/>" . $_SERVER["HTTP_HOST"] . "<br/>" . $date;
                    $mail_name = $conf["mail_recv"] ? $conf["mail_recv"] : $conf["mail_name"];
                    $result    = send_mail($mail_name, $sub, $msg);
                }
            }
        }

        //邮件通知
        if ($conf['email_push'] > 0) {
            $is_send = false;
            if ($tool["is_email"] == 1) {
                if ($conf['email_push'] == 2) {
                    $is_send = true;
                } elseif ($conf['email_push'] == 1 && $tool["is_curl"] == 0) {
                    $is_send = true;
                } elseif ($conf['email_push'] == 3 && $tool["is_curl"] != 2) {
                    $is_send = true;
                } elseif ($conf['email_push'] == 4) {
                    if ($tool["is_curl"] != 2 && $tool["is_curl"] != 4) {
                        $is_send = true;
                    }
                }
            }

            if ($is_send == true) {
                $sub = "您有新的订单！";
                if ($tool["is_curl"] == 4 && $tool["input"] == '' && function_exists('getFakaInput')) {
                    $inputname = getFakaInput();
                } else {
                    $inputname = ($tool["input"] ? $tool["input"] : "QQ");
                }
                $msg       = $inputname . $input[0] . " 已成功下单商品: <br/>" . $tool["name"] . "<br/><br/>来自：" . $_SERVER["HTTP_HOST"] . "<br/>时间：" . $date;
                $mail_name = $conf["mail_recv"] ? $conf["mail_recv"] : $conf["mail_name"];
                $result    = send_mail($mail_name, $sub, $msg);
                if ($result === true) {
                    $bz = "邮件通知：邮件于{$date}发送成功！";
                    $DB->exec("UPDATE `pre_orders` SET `bz`='{$bz}' WHERE `id`=:id", [':id' => $orderid]);
                } else {
                    $bz = "邮件通知：邮件于{$date}发送成功！" . $result;
                    $DB->exec("UPDATE `pre_orders` SET `bz`='{$bz}' WHERE `id`=:id", [':id' => $orderid]);
                }
            } else {
                $DB->exec("UPDATE `pre_orders` SET `bz`='邮件通知：不满足发送条件！' WHERE `id`= ? ", [$orderid]);
            }
        }
    }
    if ($orderid > 0) {
        // @hook('order_after', $srow);
        return $orderid;
    }
    return 0;
}

function do_orders_km($srow, $tool, $num)
{
    global $DB, $date, $conf;
    if ($srow['id'] < 1) {
        return '订单不存在，发货失败！';
    }

    $status    = 0;
    $djzt      = 4;
    $result    = "";
    $orderid   = $srow['id'];
    $Fakacount = intval($num) > 0 ? $num : 1;
    $isFakaNum = $DB->count("SELECT count(*) FROM cmy_faka WHERE `tid`='" . $srow['tid'] . "' and `orderid`='" . $orderid . "'");
    $kmdata    = '';
    if ($isFakaNum == $Fakacount) {
        $rs = $DB->query("SELECT * FROM cmy_faka WHERE tid='{$srow['tid']}' and orderid='{$orderid}' LIMIT " . $Fakacount);
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
            return '该**商品库存不足，请先加卡后再补单操作！';
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
        $djzt = 2;
        $DB->query("UPDATE `pre_orders` set `djzt`='2' where id='" . $orderid . "'");

        if (!preg_match('/^(https|http):\/\/[\w\.\-]+\.[\w\:\/]+\/$/', $shequ["url"])) {
            preg_match('/[\w\.\-]+\.[\w\:]+/', $shequ["url"], $arr);
            if ($shequ["ssl"] == 1) {
                $shequ["url"] = 'https://' . $arr[0] . '/';
            } else {
                $shequ["url"] = 'http://' . $arr[0] . '/';
            }
        }

        $result = do_orders_extend($row, $shequ, $tool, $num, true);

        if (stripos($result, "success") === false && stripos($result, "成功") === false) {
            $djzt = 2;

            $DB->query("UPDATE `pre_orders` SET `djzt`='{$djzt}',`status`='0' where `id`='" . $orderid . "'");

            if ($conf["shequ_tixing"] == 1 && $conf['mail_cloud'] >= 0) {
                $sub       = "自动下单到对接站失败提醒";
                $msg       = ($tool["inputname"] ? $tool["inputname"] : "账号：") . $input[0] . " 下单商品: " . $tool["name"] . "<br/><b>提交参数：</b>" . $input . "<br/><b>返回结果：</b>" . $result . "<br/>----------<br/>" . $_SERVER["HTTP_HOST"] . "<br/>" . $date;
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
    if ($conf['tixing_' . $key] - time() <= 0) {
        saveSetting('tixing_' . $key, time() + 7200); //2小时间隔
        $ad = $CACHE->clear();
        if ($ad) {
            $sub       = "下.单余额不足提醒";
            $msg       = "检测到 " . $shequ['url'] . " 余额不足<br/>请及时充值余额<br/>Time：" . $date;
            $mail_name = $conf["mail_recv"] ? $conf["mail_recv"] : $conf["mail_name"];
            send_mail($mail_name, $sub, $msg);
        }
    }
}

// ----------------------------分割线分割线分割线分割线分割线分割线分割线分割线---------------
// ----------------------------分割线分割线分割线分割线分割线分割线分割线分割线---------------
// ----------------------------分割线分割线分割线分割线分割线分割线分割线分割线---------------
// ----------------------------分割线分割线分割线分割线分割线分割线分割线分割线---------------
// addOrder function start
// ----------------------------分割线分割线分割线分割线分割线分割线分割线分割线---------------
// ----------------------------分割线分割线分割线分割线分割线分割线分割线分割线---------------
// ----------------------------分割线分割线分割线分割线分割线分割线分割线分割线---------------
// ----------------------------分割线分割线分割线分割线分割线分割线分割线分割线---------------

if (!function_exists('extend_function')) {
    function extend_function($extendname)
    {
        $extendname = str_replace('\\', '/', $extendname);
        $file       = ROOT . 'includes/core/extend/' . trim($extendname, '/') . '/function.php';
        if (file_exists($file)) {
            include_once $file;
        }
    }
}

if (!function_exists('extend_autoload')) {
    function extend_autoload($class)
    {
        $class = str_replace('\\', '/', $class);
        $file  = ROOT . 'includes/' . trim($class, '/') . '.php';
        if (file_exists($file)) {
            include_once $file;
        }
    }
}

function do_orders_kayisu($row, $config, $tool, $num = 1)
{
    //卡易速
    return '该平台已不支持对接！';
}

function kayisu_callback($row, $config)
{
    return true;
}

function kayisu_getCookie($config)
{
    $cookie_file = ROOT . "other/" . md5($config["url"] . $config["username"]) . ".txt";
    $cookies     = file_get_contents($cookie_file);
    if (!file_exists($cookie_file) || $cookies == "") {
        $cookies = kayisu_login($config);
        if (strpos($cookies, "失败") === false) {
            file_put_contents($cookie_file, $cookies);
        }
    }
    return $cookies;
}

function kayisu_login($config)
{
    $url      = $config['url'] . "login.html";
    $user     = $config['username'];
    $pwd      = strSafeEnCode($config['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
    $post     = "username=" . $user . "&password=" . $pwd;
    $header[] = "Host:" . $config['url'];
    $header[] = "Origin:http://" . $config['url'];
    //$header[] = "Upgrade-Insecure-Requests:1";
    $header[] = "X-Requested-With:XMLHttpRequest";
    $header[] = "Content-Type:application/x-www-form-urlencoded;";

    $handle   = chenm_curl($url, $post, $config['url'] . "login/", 0, 1, $header, 0, $config['proxy']);
    $result   = curl_exec($handle);
    $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
    curl_close($handle);

    if ($httpCode == 200) {
        $data2 = "{" . getSubstr($result, "{", "}") . "}";
        $Json  = json_decode($data2, true);
        if (is_array($Json)) {
            if ($Json['status'] == '1' && stripos($result, 'front_user_auth_sign') !== false) {
                $cookies = "";
                preg_match_all("/Set-Cookie:(.*);/im", $result, $matchs);
                writeLogs("获取卡易速cookie：\n" . json_encode($matchs));
                foreach ($matchs[1] as $val) {
                    $cookies .= trim($val) . "; ";
                }
                return $cookies;
            } else {
                return '尝试登录失败，' . $Json['info'];
            }
        } else {
            return '尝试登录失败，' . $result;
        }
    } else {
        writeLogs("自动登录卡易速失败：\n" . $result);
        return '对接站访问失败，[' . $httpCode . ']：' . $result;
    }

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
