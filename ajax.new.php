<?php
include "./includes/common.php";
define('AJAX_VERSION', '2.7.8');
define('AJAX_BUILD', 1040);
$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
@header('Content-Type: application/json; charset=UTF-8');

if (function_exists('hook')) {
    @hook('home_ajax');
}

if ($isLogin2 == 1) {
    $price_obj = new \core\Price($userrow['zid'], $userrow);
    if ($userrow['power'] > 0) {
        $siterow = $userrow;
    }
    $sitezid = $userrow['zid'];
} elseif ($is_fenzhan == true) {
    $price_obj = new \core\Price($siterow['zid'], $siterow);
    $sitezid   = $siterow['zid'];
} else {
    $price_obj = new \core\Price(1);
    $sitezid   = 1;
}

$invite_id = 0;
if (isset($_COOKIE['invite_id']) && $_COOKIE['invite_id'] > 0) {
    $invite_id = input('invite_id', 1);
}

if ($_SESSION['specsJson']) {
    $specsJson = json_decode($_SESSION['specsJson'], true);
} else {
    $sprs      = $DB->query('SELECT * FROM `pre_specs` where 1');
    $specsJson = [];
    if ($rs) {
        $arr1 = $DB->fetchAll($sprs);
        foreach ($arr1 as $key => $value) {
            $specsJson[$value['id']] = $value;
        }
    }
    $_SESSION['specsJson'] = json_encode($specsJson, JSON_UNESCAPED_UNICODE);
}

if ($_SESSION['attributeJson']) {
    $attributeJson = json_decode($_SESSION['attributeJson'], true);
} else {
    $attrrs        = $DB->query('SELECT * FROM `pre_attribute` where 1');
    $attributeJson = [];
    if ($rs) {
        $arr2 = $DB->fetchAll($attrrs);
        foreach ($arr2 as $key => $value) {
            $attributeJson[$value['id']] = $value;
        }
    }
    $_SESSION['attributeJson'] = json_encode($attributeJson, JSON_UNESCAPED_UNICODE);
}

$cjmsg = '您今天的抽奖次数已经达到上限！';
if ($conf['cjmsg'] != '') {
    $cjmsg = $conf['cjmsg'];
}

$free_okmsg = '领取成功，等待到账。请收藏保存本站网址，每天来领取福利哦~';
if ($conf['free_okmsg'] != '') {
    $free_okmsg = $conf['free_okmsg'];
}

$free_maxmsg = '您的新用户免费活动已经结束！';
if ($conf['free_maxmsg'] != '') {
    $free_maxmsg = $conf['free_maxmsg'];
}

$PriceCronList = explode("|", $conf['PriceCronList']);
switch ($act) {
    case 'captcha':
        $GtSdk = new \core\GeetestLib($conf['captcha_id'], $conf['captcha_key']);
        $data  = array(
            'user_id'     => $cookiesid, # 网站用户id
            'client_type' => "web", # web:电脑上的浏览器；h5:手机上的浏览器，包括移动应用内完全内置的web_view；native：通过原生SDK植入APP应用的方式
            'ip_address'  => $clientip, # 请在此处传输用户请求验证时所携带的IP
        );
        $status               = $GtSdk->pre_process($data, 1);
        $_SESSION['gtserver'] = $status;
        $_SESSION['user_id']  = $cookiesid;
        echo $GtSdk->get_response_str();
        break;
    case 'getcount':
        if ($conf['tongji_time'] > 0 && $conf['tongji_cachetime'] - time() > 0) {
            $row = $CACHE->read("tongji");
            if (is_array($row) && $row['expire'] > time()) {
                $result             = json_decode($row['v'], true);
                $result['is_cache'] = 1;

            }
        }

        $strtotime = strtotime($conf['build']); //获取开始统计的日期的时间戳
        $now       = time(); //当前的时间戳
        $yxts      = ceil(($now - $strtotime) / 86400); //取相差值然后除于24小时(86400秒)
        if ($conf['hide_tongji'] == 1) {
            $result = array("code" => 0, "yxts" => $yxts, "orders" => 0, "orders1" => 0, "orders2" => 0, "money" => 0, "money1" => 0, "gift" => $gift);
        } else {

            if ($conf['gift_log'] == 1 && $conf['gift_open'] == 1) {
                $gift = array();
                $list = $DB->query("SELECT a.*,(select b.name from cmy_gift as b where a.gid=b.id) as name FROM cmy_giftlog as a WHERE status=1 ORDER BY id DESC");
                while ($cjlist = $DB->fetch($list)) {
                    if (!$cjlist['input']) {
                        continue;
                    }

                    $gift[$cjlist['input']] = $cjlist['name'];
                }
            }

            $time   = date("Y-m-d") . ' 00:00:01';
            $count1 = $DB->count("SELECT count(*) from cmy_orders");
            $count2 = $DB->count("SELECT count(*) from cmy_orders where status>=1");
            $count3 = $DB->count("SELECT sum(money) from cmy_pay where status=1");
            $count4 = round($count3, 2);
            $count5 = $DB->count("SELECT count(*) from `pre_orders` WHERE  `addtime` > ?", [$time]);
            $count6 = $DB->count("SELECT sum(money) FROM `pre_pay` WHERE `addtime` > ? AND `status` = 1", [$time]);
            $count7 = round($count6, 2);
            $count8 = $DB->count("SELECT count(*) from cmy_site");
            $count9 = $DB->count("SELECT count(*) from cmy_tools");
            if ($conf['shoppingcart'] == 1) {
                $cart_count = $DB->count(
                    "SELECT count(*) from cmy_cart WHERE userid= ? AND status=0",
                    [$isLogin2 == 1 ? $sitezid : $cookiesid]
                );
            }

            if ($conf['invented_open'] == 1) {
                //虚拟数据
                $result = array("code" => 0, "yxts" => $yxts, "orders" => $count1 + $conf['invented_orders'], "orders1" => $count2 + $conf['invented_orders'], "orders2" => $count5 + $conf['invented_order'], "money" => round($count4 + $conf['invented_moneys'], 2), "money1" => round($count7 + $conf['invented_money'], 2), "site" => $count8 + $conf['invented_fenzhan'], "tool" => $count9, "gift" => $gift, "cart_count" => $cart_count);
            } else {
                $result = array("code" => 0, "yxts" => $yxts, "orders" => $count1, "orders1" => $count2, "orders2" => $count5, "money" => round($count4, 2), "money1" => round($count7, 2), "site" => $count8, "tool" => $count9, "gift" => $gift, "cart_count" => $cart_count);
            }
        }

        if ($conf['tongji_time'] > 0) {
            if ($CACHE->save('tongji', @json_encode($result), time() + $conf['tongji_time'], 'string')) {
                $result['savecache'] = 1;
            } else {
                $result['savecache'] = 0;
            }
        }

        break;
    case 'otherpay':
        $orderid = input('orderid', 1);
        $type    = input('type', 1);
        if ($isLogin2 == 1) {
            if ($type != "rmb") {
                $DB->query("UPDATE `pre_pay` set `type` = ?,userid= ? where `trade_no`= ?", ['rmb_' . $type, $userrow['zid'], $orderid]);
            } else {
                $DB->query("UPDATE `pre_pay` set `type` ='rmb',userid= ? where `trade_no`= ?", [$userrow['zid'], $orderid]);
            }
        } else {
            $DB->query("UPDATE `pre_pay` set `type` = ? where `trade_no`= ?", array($type, $orderid));
        }
        exit('{"code":0,"msg":"succ","orderid":"' . $orderid . '","type":"' . $type . '"}');
        break;
    case 'userSiteInfo':
        if (!$isLogin2) {
            json_error('未登录');
        }
        $data = array(
            'logo'         => $userrow['logo'],
            'sitename'     => $userrow['sitename'],
            'keywords'     => $userrow['keywords'],
            'description'  => $userrow['description'],
            'anounce'      => $userrow['anounce'],
            'modal'        => $userrow['modal'],
            'bottom'       => $userrow['bottom'],
            'alert'        => $userrow['alert'],
            'template'     => $userrow['template'],
            'ktfz_price'   => $userrow['ktfz_price'],
            'ktfz_price2'  => $userrow['ktfz_price2'],
            'ktfz_siteurl' => $userrow['ktfz_siteurl'],
            'siteurl'      => $userrow['siteurl'],
        );
        json_success('succ', $data);
        break;
    case 'userSiteInfoSave':
        if (!$isLogin2) {
            json_error('未登录');
        }
        if ($conf['fenzhan_gonggao_type'] == 2) {
            //支持html模式
            $postArr = input('post.', 0);
        } else {
            $postArr = input('post.', 1);
        }
        $param = [];
        $sql   = "UPDATE `pre_site` SET ";
        foreach ($postArr as $key => $value) {
            $param[':' . $key] = $value;
            $sql .= "`{$key}`=:{$key},";
        }
        if (isset($_FILES['logo'])) {
            $logoPath = 'logo_' . $userrow['zid'] . '_' . substr(md5(time() . " 11111111"), 0, 16) . '.png';
            $scsk     = uploadFile_fenzhan('logo', $logoPath, 'logo');
            $logo     = '';
            if ($scsk) {
                if ($conf['file_type'] == 1 && stripos($conf['file_ftp_url'], '://') !== false && $conf['file_ftp_server'] != "" && $conf['file_ftp_username'] != "") {
                    $logo = $conf['file_ftp_url'] . 'images/' . $logoPath;
                } else {
                    $logo = 'assets/img/logo/' . $logoPath;
                }
            }
            $param[':logo'] = $logo;
            $sql .= "`logo`=:logo,";
        }
        $param[':zid'] = $userrow['zid'];
        $sql           = rtrim($sql, ',');
        $sql .= " WHERE `zid`=:zid";
        try {
            $DB->exec($sql, $param);
            json_success('保存成功！');
        } catch (\PDOException $e) {
            json_error('保存失败，' . $e->getMessage());
        }
        break;
    case 'saveUserInfo':
        if (!$isLogin2) {
            json_error('未登录');
        }
        $postArr = input('post.', 1);
        $param   = [];
        $sql     = "UPDATE `pre_site` SET ";
        foreach ($postArr as $key => $value) {
            if ($key != 'skimg') {
                $param[':' . $key] = $value;
                $sql .= "`{$key}`=:{$key},";
            }
        }

        if (isset($_FILES['skimg'])) {
            $logoPath = 'skimg_' . $userrow['zid'] . '_' . substr(md5(time() . " 11111111"), 0, 16) . '.png';
            $scsk     = uploadFile_fenzhan('skimg', $logoPath, 'skimg');
            $skimg    = '';
            if ($scsk) {
                if ($conf['file_type'] == 1 && stripos($conf['file_ftp_url'], '://') !== false && $conf['file_ftp_server'] != "" && $conf['file_ftp_username'] != "") {
                    $skimg = $conf['file_ftp_url'] . 'images/' . $logoPath;
                } else {
                    $skimg = 'assets/img/skimg/' . $logoPath;
                }
            }
            $param[':skimg'] = $skimg;
            $sql .= "`skimg`=:skimg,";
        }
        $param[':zid'] = $userrow['zid'];
        $sql           = rtrim($sql, ',');
        $sql .= " WHERE `zid`=:zid";

        if ($DB->query($sql, $param)) {
            $pwd = input('post.pwd', 1);
            if (!empty($pwd)) {
                $sql2  = "UPDATE `pre_site` SET `pwd`=:pwd WHERE `zid`=:zid";
                $data2 = [
                    ':pwd' => $pwd,
                    ':zid' => $userrow['zid'],
                ];
                if (!$DB->query($sql2, $data2)) {
                    json_success('保存失败，' . $DB->error());
                }
            }
            json_success('保存成功');
        } else {
            json_success('保存失败，' . $DB->error(), [], [$param]);
        }
        break;
    case 'userClassList':
        if (!$isLogin2) {
            json_error('未登录');
        }

        $rs = $DB->select("SELECT * FROM `pre_class` WHERE active=1 order by sort asc");
        if ($rs) {
            $classhide = explode(',', $userrow['class']);
            $data      = array();
            foreach ($rs as $key => $res) {
                if (in_array($res['cid'], $classhide)) {
                    $res['is_show'] = false;
                    $res['active']  = 0;
                } else {
                    $res['is_show'] = $res['active'] == 1 ? true : false;
                }
                $data[] = $res;
            }
            json_success('succ', $data);
        } else {
            json_error('获取失败，' . $DB->error());
        }

        break;
    case 'userOrderInfo':
        if (!$isLogin2) {
            json_error('未登录');
        }

        $id  = intval(input('id', 1));
        $key = input('key', 1);
        if (empty($key) || strlen($key) !== 32) {
            exit('{"code":-1,"msg":"验证失败"}');
        }

        $row = $DB->get_row("SELECT * from `pre_orders` where `payorder`= ? OR `id`= ? limit 1", [$id, $id]);

        if (getOrderSkey($row) !== $key) {
            exit('{"code":-2,"msg":"验证失败"}');
        }
        $list_result = null;
        $list        = null;

        if ($row['userid'] == $userrow['zid']) {
            $tool        = $DB->get_row("SELECT * FROM `pre_tools` WHERE `tid`='{$row['tid']}'");
            $row['name'] = $tool ? $tool['name'] : '该商品已被删除或不存在';
            $tool        = $DB->get_row("SELECT * from cmy_tools where tid= ? limit 1", [$row['tid']]);
            if ($tool['is_curl'] == 2 && $row['djzt'] != 3) {
                $InfoControler = new \core\InfoControler();
                $shequ         = $DB->get_row("SELECT * from cmy_shequ where id= ? limit 1", array($tool['shequ']));
                $shequ["url"]  = shequ_url_parse($shequ);
                $query         = ['code' => -1, 'msg' => '该货源平台不支持查询'];
                if ($shequ['alias'] != "") {
                    $query = $InfoControler->query_extend($row, $shequ);
                } elseif ($shequ['type'] == 4) {
                    $query = $InfoControler->query_jiuliu($row, $shequ);
                } elseif ($shequ['type'] == 13) {
                    $query = $InfoControler->query_this($row, $shequ);
                } elseif ($shequ['type'] == 23) {
                    $query = $InfoControler->query_chengzi($row, $shequ);
                } elseif ($shequ['type'] == 24) {
                    $query = $InfoControler->query_guakebao($row, $shequ);
                }

                if (is_array($query) && $query['code'] == 0) {
                    $list = $query['data'];
                    if ($row['status'] == 2 && preg_match('/已完成|成功|已到账/', $list['order_state'])) {
                        $row['status'] = 1;
                        $DB->query("UPDATE `pre_orders` set status=1 where id=:id", [':id' => $id]);
                    }
                    $list_result = ['code' => 0, 'msg' => '查询成功'];
                } else {
                    $list_result = $query;
                }
            }

            $usetime = '';
            if ($conf['show_usetime'] == 1 && ($row['status'] == 2 || $row['status'] == 0)) {
                $usetime = time() - strtotime($row['addtime']);
            }

            if ($conf['show_complain']) {
                $times    = 3600 * 24;
                $orderDay = time() - strtotime($row['addtime']);
                $orderDay = floor($orderDay / $times);
                if ($orderDay > $conf['complain_limit'] && $conf['complain_limit'] > 0) {
                    $complain = 0;
                } else {
                    $complain = 1;
                }
            } else {
                $complain = 0;
            }

            $expressInfo = [];
            if (!empty($row['exporder'])) {
                $InfoControler = new \core\InfoControler();
                $expressInfo   = $InfoControler->query_express($row);
            }

            $input = !empty($tool['input']) ? $tool['input'] : '下单QQ';
            if ($tool['is_curl'] == 4) {
                $input = '联系方式';
            }

            $inputs = explode('|', $tool['inputs']);

            $count = $DB->count("SELECT count(*) FROM `pre_faka` WHERE `orderid`= ?", [$id]);

            if ($count > 0) {
                if ($count >= 3) {
                    $kmdata = '<center><a href="/?mod=faka&id=' . $id . '&skey=' . $_POST['skey'] . '" target="_blank" class="btn btn-sm btn-primary">点此查看卡密</a></center>';
                } else {
                    $rs     = $DB->query("SELECT * FROM cmy_faka WHERE tid= ? AND orderid= ? ORDER BY kid ASC LIMIT  ?", [$row['tid'], $id, $count]);
                    $kmdata = '';
                    while ($res = $DB->fetch($rs)) {
                        if (!empty($res['pw'])) {
                            $kmdata .= '卡号：' . $res['km'] . ' 密码：' . $res['pw'] . '<br/>';
                        } else {
                            $kmdata .= $res['km'] . '<br/>';
                        }
                        if (strlen($res['km'] . $res['pw']) > 80) {
                            $kmdata = '<center><a href="./?mod=faka&id=' . $id . '&skey=' . $_POST['skey'] . '" target="_blank" class="btn btn-sm btn-primary">点此查看卡密</a></center>';
                            break;
                        }
                    }
                }
            }

            $result = array('name' => $tool['name'], 'money' => $row['money'], 'date' => $row['addtime'], 'usetime' => $usetime, 'show_usetime' => ($conf['show_usetime'] > 0 ? 1 : 0), 'show_endtime' => ($conf['show_endtime'] > 0 ? 1 : 0), 'endtime' => $row['endtime'], 'inputs' => showInputs($row, $input, $inputs), 'list' => $list, 'list_result' => $list_result, 'kminfo' => $kmdata, 'show_desc' => ($conf['show_desc'] > 0 ? 1 : 0), 'alert' => $tool['alert'], 'desc' => $tool['desc'], 'status' => $row['status'], 'is_curl' => $tool['is_curl'], 'result' => $row['result'], 'expressInfo' => $expressInfo, 'complain' => $complain, 'works' => intval($works), 'isLogin' => $isLogin2);
            json_success('succ', $result);
        } elseif ($row) {
            json_error('只能查看自己下单的订单详情');
        } else {
            json_error('该订单不存在，请确定后再试或联系客服处理！');
        }
        break;
    case 'userOrderList':
        if (!$isLogin2) {
            json_error('未登录');
        }
        $page      = isset($_GET['page']) ? intval(input('get.page')) : 0;
        $page_size = isset($_GET['page_size']) ? intval(input('get.page_size')) : 10;
        $kw        = isset($_GET['kw']) ? intval(input('get.kw')) : '';
        if ($page > 0) {
            $page = $page - 1;
        }
        $offset = $page * $page_size;
        try {
            $rsfl      = $DB->select("SELECT * FROM `pre_tools`");
            $toolsname = [];
            foreach ($rsfl as $key => $tool) {
                $toolsname[$tool['tid']] = $tool['name'];
            }
            $kw = input('kw', 1);
            if (!empty($kw)) {
                $sql   = "SELECT * FROM `pre_orders` WHERE `zid`=:zid AND (`input`=:input OR `input2`=:input2 OR `input3`=:input3 OR `input4`=:input4 OR `input5`=:input5 OR `payorder`=:payorder) order by id DESC LIMIT :offset,:page_size";
                $param = array(
                    ':zid'       => $userrow['zid'],
                    ':input'     => $kw,
                    ':input2'    => $kw,
                    ':input3'    => $kw,
                    ':input4'    => $kw,
                    ':input5'    => $kw,
                    ':payorder'  => $kw,
                    ':offset'    => $offset,
                    ':page_size' => $page_size,
                );
            } else {
                $sql   = "SELECT * FROM `pre_orders` WHERE `zid`=:zid order by id DESC LIMIT :offset,:page_size";
                $param = array(
                    ':zid'       => $userrow['zid'],
                    ':offset'    => $offset,
                    ':page_size' => $page_size,
                );
            }

            $rs = $DB->select($sql, $param);
            foreach ($rs as $key => $res) {
                $res['name']       = $toolsname[$res['tid']] ? $toolsname[$res['tid']] : '商品已下架或已删除';
                $res['order_type'] = $userrow['zid'] == $res['userid'] ? 1 : 0;
                $res['order_key']  = md5($res['id'] . SYS_KEY . $res['id']);
                $rs[$key]          = $res;
            }
            $clz = $DB->count("SELECT count(*) FROM `pre_orders` WHERE `zid`=:zid AND `status`=2", [':zid' => $userrow['zid']]);
            $yc  = $DB->count("SELECT count(*) FROM `pre_orders` WHERE `zid`=:zid AND `status`=3", [':zid' => $userrow['zid']]);
            $ywc = $DB->count("SELECT count(*) FROM `pre_orders` WHERE `zid`=:zid AND `status`=1", [':zid' => $userrow['zid']]);
            $ytk = $DB->count("SELECT count(*) FROM `pre_orders` WHERE `zid`=:zid AND `status`=4", [':zid' => $userrow['zid']]);
            $dcl = $DB->count("SELECT count(*) FROM `pre_orders` WHERE `zid`=:zid AND `status`=0", [':zid' => $userrow['zid']]);
            json_success(
                'succ',
                ['list' => $rs, 'clz' => $clz, 'yc' => $yc, 'ywc' => $ywc, 'ytk' => $ytk, 'dcl' => $dcl]
            );
        } catch (\Exception $e) {
            json_error('订单获取失败，' . $e->getMessage());
        }

        break;

    case 'userMsgList':
        if (!$isLogin2) {
            json_error('未登录');
        }
        if ($userrow['power'] == 2) {
            $type = '0,2,4';
        } elseif ($userrow['power'] == 1) {
            $type = '0,2,3';
        } else {
            $type = '0,1';
        }
        //$msgcount = $DB->count("SELECT count(*) FROM cmy_message WHERE type IN ($type) AND active=1");
        $msgread = explode(',', $userrow['msgread']);
        $limit   = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        $rs      = $DB->query("SELECT * FROM `pre_message` WHERE type IN ($type) AND active=1 ORDER BY id DESC LIMIT 0,:limit", [':limit' => $limit]);
        $msgrow  = array();
        while ($res = $DB->fetch($rs)) {
            if (in_array($res['id'], $msgread)) {
                $res['read'] = true;
            } else {
                $res['read'] = false;
            }

            $msgrow[] = $res;
        }
        json_success('succ', $msgrow);
        break;

    case 'userWorkOrderInfo':
        if (!$isLogin2) {
            json_error('未登录');
        }
        $id  = intval(input('get.id'));
        $row = $DB->get_row("SELECT * FROM `pre_workorder` WHERE `id`='{$id}' LIMIT 1");
        if ($row) {
            $messages = [];
            $myimg    = '//q2.qlogo.cn/headimg_dl?bs=qq&dst_uin=' . $userrow['qq'] . '&src_uin=' . $userrow['qq'] . '&fid=' . $userrow['qq'] . '&spec=100&url_enc=0&referer=bu_interface&term_type=PC';
            $kfimg    = 'https://imgcache.qq.com/open_proj/proj_qcloud_v2/mc_2014/work-order/css/img/custom-service-avatar.svg';
            $arr1     = explode('*', $row['content']);
            foreach ($arr1 as $key => $value) {
                $arr2           = explode('^', $value);
                $messages[$key] = array(
                    'name'    => $arr2[0] == 1 ? '官方客服' : '自己',
                    'img'     => $arr2[0] == 1 ? $kfimg : $myimg,
                    'addtime' => $arr2[1],
                    'content' => $arr2[2],
                );
            }
            $row['messages'] = $messages;
            json_success('succ', $row);
        } else {
            json_error('该工单数据不存在！');
        }

        break;
    case 'userWorkOrderComplete':
        if (!$isLogin2) {
            json_error('未登录');
        }
        $id  = intval(input('post.id'));
        $row = $DB->get_row("SELECT * FROM `pre_workorder` WHERE `id`='{$id}' LIMIT 1");
        if ($row) {
            if ($row['status'] == 1) {
                json_error('此工单已经结单了~');
            } else {
                try {
                    $sql   = "UPDATE `pre_workorder` SET status=1 where id=:id";
                    $param = [
                        ':id' => $id,
                    ];
                    $DB->exec($sql, $param);
                    json_success('关闭成功！');
                } catch (\PDOException $e) {
                    json_error('关闭失败，' . $e->getMessage());
                }
            }
        } else {
            json_error('该工单数据不存在！');
        }

        break;

    case 'userReplyWorkOrder':
        if (!$isLogin2) {
            json_error('未登录');
        }
        $id  = intval(input('post.id'));
        $row = $DB->get_row("SELECT * FROM `pre_workorder` WHERE `id`='{$id}' LIMIT 1");
        if ($row) {
            if ($row['status'] == 1) {
                json_error('此工单已经结单');
            } elseif ($row['zid'] == $userrow['zid']) {
                $content = str_replace(array('*', '^', '|'), '', input('post.content', 1));
                $content = addslashes($row['content']) . '*0^' . $date . '^' . $content;
                try {
                    $sql   = "UPDATE `pre_workorder` SET content=:content,status=0 where id=:id";
                    $param = [
                        ':content' => $content,
                        ':id'      => $id,
                    ];
                    $DB->exec($sql, $param);
                    json_success('回复成功！');
                } catch (\PDOException $e) {
                    json_error('回复失败，' . $e->getMessage());
                }
            } else {
                json_error('只能回复自己的工单！');
            }
        } else {
            json_error('该工单数据不存在！');
        }

        break;

    case 'userWorkOrderList':
        if (!$isLogin2) {
            json_error('未登录');
        }
        try {
            $sql   = "SELECT * FROM `pre_workorder` WHERE `zid`=:zid order by id DESC";
            $param = [
                ':zid' => $userrow['zid'],
            ];
            $rs = $DB->select($sql, $param);
            json_success('succ', $rs);
        } catch (\PDOException $e) {
            json_error('加载数据失败，' . $e->getMessage());
        }
        break;
    case 'userWorkOrderAdd':
        if (!$isLogin2) {
            json_error('未登录');
        }
        $orderid = intval(input('post.orderid'));
        $type    = intval(input('post.type'));
        $content = input('post.content');
        if (empty($content)) {
            json_error('描述信息不能为空！');
        } elseif ($DB->get_row("SELECT id from `pre_workorder` where orderid= ? and status<2 order by id desc limit 1", [$orderid])) {
            json_error('请勿重复提交工单！');
        } else {
            $sql   = "INSERT into `pre_workorder` (`zid`,`type`,`orderid`,`name`,`addtime`,`status`) values (:zid,:type,:orderid,:name,:addtime,'0')";
            $param = [
                ':zid'     => $userrow['zid'],
                ':type'    => $type,
                ':orderid' => $orderid,
                ':name'    => $content,
                ':addtime' => $date,
            ];
            if ($DB->query($sql, $param)) {
                if ($conf['workorder_mail'] == 1) {
                    $content   = mb_substr($name, 0, 16, 'utf-8');
                    $sub       = '用户提交工单提醒';
                    $msg       = '<b>' . $userrow['user'] . '</b>（UID:' . $userrow['zid'] . '）于 ' . $date . ' 提交工单，请及时进入网站后台工单列表处理。<br/><b>问题类型：</b>' . display_type($type) . '<br/><b>工单标题：</b>' . $content . '<br/>----------<br/>' . $_SERVER['HTTP_HOST'] . '<br/>' . $date;
                    $mail_name = $conf['mail_recv'] ? $conf['mail_recv'] : $conf['mail_name'];
                    send_mail($mail_name, $sub, $msg);
                }
                json_success('提交工单成功');
                showmsg('提交工单成功！请等待管理员处理。<br/><br/><a href="./workorder.php">>>返回工单列表</a>', 1);
            } else {
                json_success('提交工单失败！' . $DB->error());
            }
        }
        break;
    case 'userEditPirce':
        if (!$isLogin2) {
            json_error('未登录');
        }
        $tid = intval(input('post.tid'));
        $row = $DB->get_row("SELECT * FROM `pre_tools` WHERE `tid`='{$tid}' LIMIT 1");
        if (!$row) {
            json_error('该商品不存在！');
        }

        $price_obj->setToolInfo($tid, $rows);
        $price = round(input('post.price', 1), 2);
        $del   = intval(input('post.del', 1));
        if (!is_numeric($price) || !preg_match('/^[0-9.]+$/', $price)) {
            json_error('销售价格输入不规范');
        }

        $price_arr = @unserialize($userrow['price']);

        $buyPrice                       = $price_obj->getBuyPrice($tid);
        $up                             = sprintf("%.5f", ($price - $buyPrice) / $buyPrice);
        $up                             = 1 + $up;
        $price_arr[$tid]['up']['price'] = $up;
        if ($userrow['power'] == 2) {
            $cost = round(input('post.cost', 1), 2);
            if ($userrow['power'] == 2 && $cost < $buyPrice) {
                json_error('下级代理价格不能低于成本价格！');
            } elseif ($price < $cost) {
                json_error('销售价格不能低于下级代理价格！');
            }
            if (!is_numeric($cost) || !preg_match('/^[0-9.]+$/', $cost)) {
                showmsg('下级价格输入不规范', 3);
            }
            $up2                           = sprintf("%.5f", ($cost - $buyPrice) / $buyPrice);
            $up2                           = 1 + $up2;
            $price_arr[$tid]['up']['cost'] = $up2;
        }

        if ($conf['fenzhan_price_open'] == 1) {
            $c = ($price - $buyPrice) * 100;
            if ($conf['fenzhan_price_max'] > 0 && $c >= $conf['fenzhan_price_max']) {
                showmsg('该商品销售价格不能高于成本价格的' . $c . '%！', 3);
            }
        }
        $price_arr[$tid]['del'] = $del;

        $price_data = @serialize($price_arr);
        try {
            $sql   = "UPDATE `pre_site` SET `price`=:price WHERE  `zid`=:zid LIMIT 1";
            $param = [
                ':price' => $price_data,
                ':zid'   => $userrow['zid'],
            ];
            $DB->exec($sql, $param);
            json_success('修改成功');
        } catch (\PDOException $e) {
            json_error('修改失败，' . $e->getMessage());
        }
        break;
    case 'getUserTools':
        if (!$isLogin2) {
            json_error('未登录');
        }
        $cid = intval(input('get.cid'));
        if ($cid > 0) {
            $rs = $DB->select("SELECT * FROM `pre_tools` WHERE `cid`='{$cid}' order by sort ASC");
        } else {
            $rs = $DB->select("SELECT * FROM `pre_tools` order by tid DESC");
        }
        $data = [];
        if (false !== $rs) {
            foreach ($rs as $index => $row) {
                $price_obj->setToolInfo($row['tid'], $row);
                $del = $price_obj->getToolDel($row['tid']);
                if ($del == 1) {
                    $del = 0;
                } else {
                    $del = 1;
                }
                $data[$index] = array(
                    'cid'     => $row['cid'],
                    'tid'     => $row['tid'],
                    'price1'  => $price_obj->getBuyPrice($row['tid']),
                    'price'   => $price_obj->getToolPrice($row['tid']),
                    'cost'    => $userrow['power'] >= 2 ? $price_obj->getToolCost($row['tid']) : false,
                    'name'    => $row['name'],
                    'shopimg' => $row['shopimg'],
                    'del'     => $del,
                );

            }
            json_success('succ', $data);
        } else {
            json_error('加载数据失败，' . $e->getMessage());
        }
        break;
    case 'getClassAll':
        try {
            $rs = $DB->select("SELECT * FROM `pre_class` WHERE active=1 order by sort asc");
            json_success('succ', $rs);
        } catch (\PDOException $e) {
            json_error('加载分类数据失败，' . $e->getMessage());
        }
        break;
    case 'resetPirce':
        if (!$isLogin2) {
            json_error('未登录');
        }
        try {
            $sql   = "UPDATE `pre_site` SET `price`='' WHERE  `zid`=:zid LIMIT 1";
            $param = [
                ':zid' => $userrow['zid'],
            ];
            $DB->exec($sql, $param);
            json_success('恢复价格成功');
        } catch (\PDOException $e) {
            json_error('操作失败，' . $e->getMessage());
        }
        break;
    case 'setUpPrice':
        if (!$isLogin2) {
            json_error('未登录');
        }
        try {
            $up = intval(input('post.up', 1));

            if ($up <= 0) {
                json_error('输入值不正确');
            }

            $price_arr = [];
            $rs        = $DB->query("SELECT * from cmy_tools");
            $a         = floatval($up / 100);
            $a2        = floatval($up2 / 100);
            $data      = [];
            if ($rs) {
                $data = $DB->fetchAll($rs);
            }

            if ($conf['fenzhan_price_open'] == 1) {
                if ($a * 100 >= $conf['fenzhan_price_max']) {
                    $a = $conf['fenzhan_price_max'] / 100;
                }
            }

            $fenzhan_price_class = [];
            if ("" != $conf['fenzhan_price_class']) {
                $fenzhan_price_class = explode(',', $conf['fenzhan_price_class']);
            }

            if ($conf['fenzhan_price_max'] < 1) {
                $conf['fenzhan_price_open'] = 0;
            }
            //只需要记住加价倍数，前台需要时再实时计算，既提升性能又保证价格实时性
            foreach ($data as $row) {
                if ($row['price'] <= 0) {
                    continue;
                }

                if ($conf['fenzhan_price_open'] == 1 && in_array($row['cid'], $fenzhan_price_class)) {
                    if ($a * 100 > $conf['fenzhan_price_max']) {
                        $a = sprintf('%.2f', $conf['fenzhan_price_max'] / 100);
                    }
                }

                $price_arr[$row['tid']]['up']['price'] = sprintf("%.5f", $a + 1);
            }

            $array_data = @serialize($price_arr);
            $sql        = "UPDATE `pre_site` SET `price`=:price WHERE  `zid`=:zid LIMIT 1";
            $param      = [
                ':price' => $array_data,
                ':zid'   => $userrow['zid'],
            ];
            $DB->exec($sql, $param);
            json_success('succ');
        } catch (\PDOException $e) {
            json_error('操作失败，' . $e->getMessage());
        }
        break;
    case 'setClassActive':
        if (!$isLogin2) {
            json_error('未登录');
        }
        $cid       = intval(input('post.cid'));
        $active    = intval(input('post.active'));
        $classhide = explode(',', $userrow['class']);
        if ($active == 1 && in_array($cid, $classhide)) {
            $classhide = array_diff($classhide, array($cid));
        } elseif ($active == 0 && !in_array($cid, $classhide)) {
            array_push($classhide, $cid);
        }
        $class = implode(',', $classhide);
        $class = trim($class, ',');
        if ($DB->query("UPDATE `pre_site` set `class`= ? where zid= ?", [$class, $userrow['zid']])) {
            json_success('修改成功');
        } else {
            json_error('修改失败，' . $DB->error());
        }
        break;
    case 'getUserPointDesc':
        if (!$isLogin2) {
            json_error('未登录');
        }
        $day = input('get.date', 1);
        if ($day == date("Y-m-d")) {
            $thtime = date("Y-m-d") . ' 00:00:00';
            $sql    = "SELECT sum(`point`) from `pre_points` WHERE  `addtime`>:thtime AND `zid`=:zid AND `action`='消费'";
            $param  = [
                ':thtime' => $thtime,
                ':zid'    => $userrow['zid'],
            ];
            $buyMoney = $DB->count($sql, $param);

            $sql2       = "SELECT sum(`point`) from `pre_points` WHERE  `addtime`>:thtime AND `zid`=:zid AND `action`='提成'";
            $pointMoney = $DB->count($sql2, $param);
        } else {
            $thtime  = date("Y-m-d") . ' 00:00:00';
            $thtime2 = date("Y-m-d", strtotime('-1 day')) . ' 00:00:00';
            $sql     = "SELECT sum(`point`) from `pre_points` WHERE  `addtime`<:thtime AND `addtime`>:thtime2  AND `zid`=:zid AND `action`='消费'";
            $param   = [
                ':thtime'  => $thtime,
                ':thtime2' => $thtime2,
                ':zid'     => $userrow['zid'],
            ];
            $buyMoney = $DB->count($sql, $param);

            $sql2       = "SELECT sum(`point`) from `pre_points` WHERE `addtime`<:thtime AND `addtime`>:thtime2  AND `zid`=:zid AND `action`='提成'";
            $pointMoney = $DB->count($sql2, $param);
        }
        $data['buyMoney']   = sprintf('%.2f', $buyMoney);
        $data['pointMoney'] = sprintf('%.2f', $pointMoney);

        json_success('succ', $data);

        break;
    case 'userSubList':
        if (!$isLogin2) {
            json_error('未登录');
        }
        $page      = isset($_GET['page']) ? intval(input('get.page')) : 0;
        $page_size = isset($_GET['page_size']) ? intval(input('get.page_size')) : 10;
        $kw        = isset($_GET['kw']) ? intval(input('get.kw')) : '';
        if ($page > 0) {
            $page = $page - 1;
        }
        $offset = $page * $page_size;

        if (!empty($kw)) {
            $sql   = "SELECT zid,user,qq,addtime,money from `pre_site` WHERE `upzid`=:zid AND (`user` LIKE :kw1 OR `qq` LIKE :kw2) ORDER BY `zid` DESC LIMIT :offset,:page_size";
            $param = [
                ':zid'       => $userrow['zid'],
                ':kw1'       => '%' . $kw . '%',
                ':kw2'       => '%' . $kw . '%',
                ':offset'    => $offset,
                ':page_size' => $page_size,
            ];
            $count = $DB->count("SELECT count(*) from `pre_site` WHERE `upzid`=:zid AND (`user` LIKE :kw OR `qq` LIKE :kw) ORDER BY `zid` DESC LIMIT :offset,:page_size", $param);
        } else {
            $sql   = "SELECT zid,user,qq,addtime,money from `pre_site` WHERE `upzid`=:zid ORDER BY `zid` DESC LIMIT :offset,:page_size";
            $param = [
                ':zid'       => $userrow['zid'],
                ':offset'    => $offset,
                ':page_size' => $page_size,
            ];
            $count = $DB->count("SELECT count(*) from `pre_site` WHERE `upzid`=:zid ORDER BY `zid` DESC LIMIT :offset,:page_size", $param);
        }
        $rs = $DB->select($sql, $param);
        if (false !== $rs) {
            json_success('succ', $rs);
        } else {
            json_error('加载数据失败，' . $DB->error());
        }
        break;
    case 'getUserTradeList':
        if (!$isLogin2) {
            json_error('未登录');
        }
        $page      = isset($_GET['page']) ? intval(input('get.page')) : 0;
        $page_size = isset($_GET['page_size']) ? intval(input('get.page_size')) : 10;
        if ($page > 0) {
            $page = $page - 1;
        }
        $offset = $page * $page_size;

        $sql  = "SELECT * from `pre_points` WHERE `zid`=:zid ORDER BY `id` DESC LIMIT :offset,:page_size";
        $data = [
            ':zid'       => $userrow['zid'],
            ':offset'    => $offset,
            ':page_size' => $page_size,
        ];
        $count = $DB->count("SELECT count(*) from `pre_points` WHERE `zid`=:zid ORDER BY `id` DESC LIMIT :offset,:page_size", $data);
        $rs    = $DB->select($sql, $data);
        if (false != $rs || $count == 0) {
            json_success('succ', $rs);
        } else {
            json_error('加载数据失败，' . $DB->error());
        }
        break;
    case 'getCashList':
        if (!$isLogin2) {
            json_error('未登录');
        }
        $page      = isset($_GET['page']) ? intval(input('get.page')) : 0;
        $page_size = isset($_GET['page_size']) ? intval(input('get.page_size')) : 10;
        if ($page > 0) {
            $page = $page - 1;
        }
        $offset = $page * $page_size;

        $sql  = "SELECT * from `pre_tixian` WHERE `zid`=:zid ORDER BY `id` DESC LIMIT :offset,:page_size";
        $data = [
            ':zid'       => $userrow['zid'],
            ':offset'    => $offset,
            ':page_size' => $page_size,
        ];
        $count = $DB->count("SELECT count(*) from `pre_tixian` WHERE `zid`=:zid ORDER BY `id` DESC LIMIT :offset,:page_size", $data);
        $rs    = $DB->select($sql, $data);
        if (false != $rs || $count == 0) {
            json_success('succ', $rs);
        } else {
            json_error('加载数据失败，' . $DB->error());
        }
        break;
    case 'getUserInfo':
        if (!$isLogin2) {
            json_error('未登录');
        }
        json_success('succ', $userrow);
        break;
    default:
        exit('{"code":-4,"msg":"No Act","version":"' . AJAX_VERSION . '","build":"' . AJAX_BUILD . '"}');
        break;
}

if (isset($result) && is_array($result)) {
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}

$DB->close();
