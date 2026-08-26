<?php

use core\Db;

include "./includes/common.php";
define('AJAX_VERSION', '2.8.3');
define('AJAX_BUILD', 1043);
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
        $type = input('get.type', 1, 1);
        $type = is_string($type) && $type ? $type : 'freebuy';
        if ($conf['captcha_open'] == 1) {
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
        } else {
            if ($conf['captcha_type'] == 2) {
                $time  = time();
                $useZh = ($time % 3 == 0 || $time % 2 == 0 || $time % 6 == 0 || $time % 8 == 0) ? true : false;
            } else {
                $useZh = $conf['captcha_type'] == 1 ? true : false;
            }

            $captcha = captcha_doimg($type, [
                // 使用中文
                'useZh' => $useZh,
            ]);
            // 输出验证码
            $captcha->outPut();
        }
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
    case 'upload':
        $type = input('post.type', 1);
        $key  = input('post.key', 1);
        if (!in_array($type, ['workorder'])) {
            json_error('上传类型不受支持');
        } elseif ($type == 'workorder' && $conf['workorder_image'] != 1) {
            json_error('未开启图片上传');
        }

        if ($isLogin2 === 1) {
            $cookiesid = $userrow['zid'];
        }

        if ($type == 'workorder') {
            $count = $DB->count("SELECT count(*) `pre_workorder_file` WHERE `cookiesid`='{$cookiesid}'");
            if ($count >= 10) {
                json_error('最多添加10张图片！');
            } else {
                $data   = uploadFile_workorder($key);
                $fileid = 0;
                if ($data['code'] == 0) {
                    $sql    = "INSERT INTO `pre_workorder_file` (`workid`,`path`,`size`,`cookiesid`,`addtime`) VALUES (?, ?, ?, ?, ?)";
                    $fileid = $DB->insert($sql, [0, $data['path'], $data['size'], $cookiesid, time()]);
                }
                $result           = $data;
                $result['fileid'] = $fileid;
                unset($result['frompath']);
            }
        }

        break;
    case 'payrmb':
        if (!$isLogin2) {
            exit('{"code":-4,"msg":"您还未登录，无法使用余额付款！"}');
        }

        $orderid = isset($_POST['orderid']) ? input('post.orderid', 1) : exit('{"code":-1,"msg":"订单号未知"}');
        $srow    = $DB->get_row("SELECT * FROM cmy_pay WHERE trade_no= ? limit 1", [$orderid]);
        if (!$srow['trade_no'] || $srow['tid'] == -1) {
            exit('{"code":-1,"msg":"订单号不存在！"}');
        }

        if ($srow['money'] == '0') {
            exit('{"code":-1,"msg":"当前商品为免费商品，不需要支付"}');
        }

        if (!preg_match('/^[0-9.]+$/', $srow['money'])) {
            exit('{"code":-1,"msg":"订单金额不合法"}');
        }

        if ($srow['status'] == 0) {
            if ($srow['money'] > $userrow['money']) {
                exit('{"code":-3,"msg":"您的余额不足，请充值！"}');
            }

            if ($DB->query("UPDATE `pre_site` set `money`=`money`- ? where `zid`= ?", [$srow['money'], $userrow['zid']])) {
                $sql = "UPDATE `pre_pay` set `status` ='1',`endtime` = ?,userid= ? where `trade_no`= ?";
                if ($DB->query($sql, [$date, $userrow['zid'], $orderid])) {
                    try {
                        include SYSTEM_ROOT . 'ajax.class.php';
                        $srow['type'] = 'rmb';
                        $orderid      = processOrderAll($srow);
                        if ($orderid) {
                            addPointLogs($userrow['zid'], $srow['money'], '消费', '使用余额支付购买 ' . $srow['name'] . ' (' . $orderid . '),当前余额' . sprintf('%.5f', $userrow['money'] - $srow['money']) . '元', $orderid);
                            $tool = $DB->get_row("SELECT is_curl FROM cmy_tools WHERE tid= ? limit 1", [$srow['tid']]);
                            if ($tool['is_curl'] == 4) {
                                $orderrow = $DB->get_row("SELECT * FROM `pre_orders` WHERE id= ? LIMIT 1", [$orderid]);
                                exit('{"code":2,"msg":"您所购买的商品已付款成功，感谢购买！","orderid":"' . $orderid . '","skey":"' . getOrderSkey($orderrow, 'get') . '"}');
                            } else {
                                exit('{"code":1,"msg":"您所购买的商品已付款成功，感谢购买！","orderid":"' . $orderid . '"}');
                            }
                        } else {
                            $DB->query("UPDATE `pre_site` set `money`=`money`+ ? where `zid`= ?", array($srow['money'], $userrow['zid']));
                            exit(json_encode(['code' => -1, 'msg' => '下单失败, ' . $DB->error()], 256));
                        }
                    } catch (\Exception $th) {
                        $DB->query("UPDATE `PRE_site` set `money`=`money`+ ? where `zid`= ?", [$srow['money'], $userrow['zid']]);
                        exit(json_encode(['code' => -1, 'msg' => '下单失败, 系统错误 =>' . $th->getMessage() . '[' . $th->getLine() . ']'], 256));
                    }
                } else {
                    $DB->query("UPDATE `pre_site` set `money`=`money`+ ? where `zid`= ?", [$srow['money'], $userrow['zid']]);
                    exit('{"code":-1,"msg":"下单失败！' . $DB->error() . '"}');
                }
            } else {
                exit('{"code":-1,"msg":"下单时扣款失败！' . $DB->error() . '"}');
            }
        } else {
            exit('{"code":-2,"msg":"当前订单已付款过，请勿重复提交"}');
        }
        break;
    case 'getclass':
        $classhide = explode(',', $siterow['class']);
        $rs        = $DB->query("SELECT * FROM `pre_class` WHERE active=1 order by sort asc");
        $data      = array();
        while ($res = $DB->fetch($rs)) {
            if ($is_fenzhan && in_array($res['cid'], $classhide)) {
                continue;
            }
            $data[] = $res;
        }
        json_success('succ', $data);
        break;
    case 'login':
        $user = input('user', 1);
        $pass = input('pass', 1);
        if ($user == "") {
            exit('{"code":-1,"msg":"用户名不能为空！"}');
        } elseif ($pass == "") {
            exit('{"code":-1,"msg":"登录密码不能为空！"}');
        } else {
            $row = $DB->get_row("SELECT * FROM cmy_site WHERE user=:user limit 1", [':user' => $user]);
            if ($row) {
                if ($row['status'] != 1) {
                    exit('{"code":-1,"msg":"该账户已被封禁，无法登录！"}');
                } elseif ($row['pwd'] == $pass) {
                    $session = md5($user . $pass . $password_hash);
                    $token   = authcode($row['zid'] . "\t" . $session, 'ENCODE', SYS_KEY);
                    setcookie("user_token", $token, time() + 604800, '/');
                    $DB->query("UPDATE `pre_site` set `loginIp`= ? where zid= ? limit 1", array($clientip, $row['zid']));
                    $DB->query("UPDATE `pre_orders` set `zid`= ? where `userid`= ?", [$row['zid'], $cookiesid]);
                    fzlog_result($row['zid'], '分站登录', '用户名：' . $user . '；登录IP：' . $clientip, '用户通过网站首页登录成功', 1);
                    exit('{"code":1,"msg":"登陆成功！欢迎回来"}');
                } else {
                    exit('{"code":-1,"msg":"用户名或密码不正确！","user":"' . $user . '","pwd":"' . $row['pwd'] . '"}');
                }
            } else {
                exit('{"code":-1,"msg":"该用户不存在！"}');
            }
        }
        break;
    case 'reg':
        $user = input('post.user', 1);
        $pwd  = input('post.pwd', 1);
        $qq   = input('post.qq', 1);
        $code = input('post.code', 1);
        if ($user == "") {
            exit('{"code":-1,"msg":"要注册的登录账号不能为空！"}');
        } elseif ($pwd == "") {
            exit('{"code":-1,"msg":"要注册的登录密码不能为空！"}');
        } elseif ($qq == "") {
            exit('{"code":-1,"msg":"要注册的联系QQ不能为空！"}');
        } elseif ($DB->get_row("SELECT * FROM cmy_site WHERE user=:user limit 1", [':user' => $user])) {
            exit('{"code":-1,"msg":"该用户名已被注册"}');
        } else {

            if ($conf['captcha_open'] == 1 && $conf['captcha_open_reg'] == 1) {
                if (isset($_POST['geetest_challenge']) && isset($_POST['geetest_validate']) && isset($_POST['geetest_seccode'])) {

                    $GtSdk = new \core\GeetestLib($conf['captcha_id'], $conf['captcha_key']);
                    $data  = array(
                        'user_id'     => $cookiesid,
                        'client_type' => "web",
                        'ip_address'  => $clientip,
                    );
                    if ($_SESSION['gtserver'] == 1) {
                        $result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $data);
                        if (!$result) {
                            exit('{"code":-1,"msg":"验证失败，请重新验证"}');
                        }
                    } else {
                        if ($GtSdk->fail_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'])) {
                        } else {
                            exit('{"code":-1,"msg":"验证失败，请重新验证"}');
                        }
                    }
                } else {
                    exit('{"code":2,"msg":"请先完成验证"}');
                }
            }

            // if (!$code || strtolower($code) != $_SESSION['vc_code']) {
            //     unset($_SESSION['vc_code']);
            //     exit('{"code":2,"msg":"验证码错误！"}');
            // }

            $endtime = date("Y-m-d H:i:s", strtotime("+365 day"));
            $sql     = "INSERT into `pre_site` (`upzid`,`user`,`pwd`,`sitename`,`qq`,`power`,`status`,`addtime`) values (:upzid,:user,:pwd,'QQ业务商城',:qq,:power,:status,:addtime)";
            $data    = [
                ":upzid"   => $siterow['zid'] ? $siterow['zid'] : 1,
                ":user"    => $user,
                ":pwd"     => $pwd,
                ":qq"      => $qq,
                ":power"   => 0,
                ":status"  => 1,
                ":addtime" => $date,
            ];
            $zid = $DB->insert($sql, $data);
            if ($zid) {
                $session = md5($user . $pwd . $password_hash);
                $token   = authcode($zid . "\t" . $session, 'ENCODE', SYS_KEY);
                setcookie("user_token", $token, time() + 604800, '/');
                $DB->query("UPDATE `pre_site` set `loginIp`= ? where zid= ? limit 1", [$clientip, $zid]);
                $DB->query("UPDATE `pre_orders` set `userid`= ?,`zid`= ? where `userid`= ?", [$zid, $zid, $cookiesid]);
                $DB->query("UPDATE `pre_pay` set `userid`= ?,`zid`= ? where `userid`= ?", [$zid, $zid, $cookiesid]);
                fzlog_result($row['zid'], '分站登录', '用户名：' . $user . '；登录IP：' . $clientip, '用户通过网站首页快捷注册成功', 1);
                $result = array('code' => 1, 'msg' => "注册成功并已登陆！欢迎使用", 'user' => $user);
            } else {
                $result = array('code' => -1, 'msg' => "注册失败，请联系客服" . $conf['kfqq'] . "处理！[531]" . $DB->error(), 'user' => $user);
            }
        }
        break;
    case 'reguser':
        $user = input('post.user', 1);
        $pwd  = isset($_POST['pwd']) ? input('post.pwd', 1) : input('post.pass', 1);
        $qq   = input('post.qq', 1);
        $code = input('post.code', 1);
        if (empty($user)) {
            exit('{"code":-1,"msg":"要注册的登录账号不能为空！"}');
        } elseif (empty($pwd)) {
            exit('{"code":-1,"msg":"要注册的登录密码不能为空！"}');
        } elseif (!preg_match('/^[1-9]{1}[0-9]{4,10}$/', $qq)) {
            exit('{"code":-1,"msg":"要注册的联系QQ格式不正确！"}');
        } elseif ($DB->get_row("SELECT * FROM `pre_site` WHERE user=:user limit 1", [':user' => $user])) {
            exit('{"code":-1,"msg":"该用户名已被注册"}');
        } else {
            if ($conf['captcha_open'] == 1 && $conf['captcha_open_reg'] == 1) {
                if (isset($_POST['geetest_challenge']) && isset($_POST['geetest_validate']) && isset($_POST['geetest_seccode'])) {

                    $GtSdk = new \core\GeetestLib($conf['captcha_id'], $conf['captcha_key']);
                    $data  = array(
                        'user_id'     => $cookiesid,
                        'client_type' => "web",
                        'ip_address'  => $clientip,
                    );
                    if ($_SESSION['gtserver'] == 1) {
                        $result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $data);
                        if ($result) {
                        } else {
                            exit('{"code":-1,"msg":"验证失败，请重新验证"}');
                        }
                    } else {
                        if ($GtSdk->fail_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'])) {
                        } else {
                            exit('{"code":-1,"msg":"验证失败，请重新验证"}');
                        }
                    }
                } else {
                    exit('{"code":2,"msg":"请先完成验证"}');
                }
            } elseif (!$code || strtolower($code) != $_SESSION['vc_code']) {
                unset($_SESSION['vc_code']);
                exit('{"code":2,"msg":"验证码错误！"}');
            }

            $endtime = date("Y-m-d H:i:s", strtotime("+365 day"));
            $sqlData = [
                ':upzid'    => $siterow['zid'],
                ':user'     => $user,
                ':pwd'      => $pwd,
                ':qq'       => $qq,
                ':addtime'  => $date,
                ':lasttime' => $date,
            ];
            $sql = "INSERT INTO `pre_site` (`upzid`,`power`,`siteurl`,`user`,`pwd`,`money`,`qq`,`addtime`,`lasttime`,`status`) values (:upzid,'0','',:user,:pwd,'0',:qq,:addtime,:lasttime,'1')";
            $zid = $DB->insert($sql, $sqlData);
            if ($zid) {
                $session = md5($user . $pwd . $password_hash);
                $token   = authcode($zid . "\t" . $session, 'ENCODE', SYS_KEY);
                setcookie("user_token", $token, time() + 604800, '/');
                $DB->query("UPDATE `pre_site` set `loginIp`= ? where zid= ? limit 1", [$clientip, $zid]);
                $DB->query("UPDATE `pre_orders` set `zid`= ? where `userid`= ?", [$zid, $cookiesid]);
                $DB->query("UPDATE `pre_pay` set `userid`= ?,`zid`= ? where `userid`= ?", [$zid, $zid, $cookiesid]);
                fzlog_result($row['zid'], '分站登录', '用户名：' . $user . '；登录IP：' . $clientip, '用户通过网站首页快捷注册成功', 1);
                $result = array('code' => 1, 'msg' => "注册成功，已自动登陆~请手动刷新网页！", 'user' => $user);
            } else {
                $result = array('code' => -1, 'msg' => "注册失败，请联系客服" . $conf['kfqq'] . "处理！[531]" . $DB->error(), 'user' => $user);
            }
        }
        break;
    case 'gettool':
        $result['code']  = 0;
        $result['msg']   = 'succ';
        $result['upcid'] = 0;
        $result['class'] = 0;

        if ($conf['master_deposit_sort'] == 1) {
            if ($conf['goods_sort_type'] == 1) {
                $orderBy = "deposit DESC, like_up DESC, sort ASC, like_down ASC";
            } else {
                $orderBy = "deposit DESC, like_up DESC, sort DESC, like_down ASC";
            }
        } else {
            $orderBy = $conf['goods_sort_type'] == 1 ? "sort ASC" : "sort DESC";
        }

        try {
            if (isset($_POST['kw'])) {
                $kw = input('post.kw', 1);
                if ($kw == 'random') {
                    $rs = $DB->query("SELECT * FROM cmy_tools WHERE (`condition`=1 OR `zid`=1 OR `zid`=0) AND `close`=0 order by rand() asc LIMIT 10");
                } else {
                    if ($conf['search_replace_open'] == 2 || ($conf['search_replace_open'] == 1 && $isLogin2 == 1)) {
                        $search_replace = explode(',', $conf['search_replace_list']);
                        foreach ($search_replace as $value) {
                            $arr = explode('|', $value);
                            $kw  = str_replace($arr[0], $arr[1], $kw);
                        }
                    }
                    $rs = $DB->query("SELECT * FROM `pre_tools` WHERE (`condition`=1 OR `zid`=1 OR `zid`=0) AND `name` LIKE ? and `close`=0 order by {$orderBy}", array('%' . $kw . '%'));
                }
            } else {
                if (isset($_GET['tid'])) {
                    $tid = intval(input('get.tid', 1));
                    $rs  = $DB->query("SELECT * FROM `pre_tools` WHERE (`condition`=1 OR `zid`=1 OR `zid`=0)  AND `tid` = :tid", [':tid' => $tid]);
                } else {
                    $cid = intval(input('get.cid', 1));

                    $subClass = $DB->count("SELECT count(*) FROM `pre_class` WHERE active=1 and upcid= ? order by sort asc", [$cid]);
                    if ($cid > 0 && $subClass > 0) {
                        $rs   = $DB->select("SELECT * FROM `pre_class` WHERE active=1 and upcid= ? order by sort asc", [$cid]);
                        $data = $list = [];
                        if ($rs) {
                            $list = $rs;
                        }
                        if (function_exists('hook') && count($list) > 0) {
                            hook('goodsCateAfter', $list, function ($item = null) {
                                global $list;
                                if (is_array($item)) {
                                    $list = $item;
                                }
                            });

                            if (!is_array($list)) {
                                $list = $rs;
                            }
                        }

                        foreach ($list as $res) {
                            if ($is_fenzhan && in_array($res['cid'], $classhide)) {
                                continue;
                            }
                            $data[] = $res;
                        }

                        $result['data']  = $data;
                        $result['class'] = 1;
                    } else {
                        $class = $DB->get_row("SELECT upcid FROM cmy_class WHERE active=1 and cid= ? order by sort asc", array($cid));
                        if ($class['upcid'] > 0) {
                            $result['upcid'] = (int) $class['upcid'];
                        }

                        //$rs = $DB->query("SELECT " . $fields . " FROM cmy_tools WHERE (`cid`= ? OR ? IN (`cids`)) AND `close` != '1' order by sort asc", [$cid, $cid]); //多目录模式，较耗性能一点

                        // if ($conf['index_class_repeat'] == 1) {
                        //     $rs = $DB->query("SELECT * FROM `pre_tools` WHERE  (`condition`=1 OR `zid`=1 OR `zid`=0) AND  ( OR :cids IN (`cids`)) AND `close` != '1' order by {$orderBy}", [':cid' => $cid, ':cids' => $cid]);
                        // } else {
                        //     $rs = $DB->query("SELECT * FROM `pre_tools` WHERE  (`condition`=1 OR `zid`=1 OR `zid`=0) AND `cid`=:cid AND `close` != '1' order by {$orderBy}", [':cid' => $cid]);
                        // }

                        $rs = $DB->query("SELECT * FROM `pre_tools` WHERE  (`condition`=1 OR `zid`=1 OR `zid`=0) AND `cid`=:cid AND `close` != '1' order by {$orderBy}", [':cid' => $cid]);
                    }
                }
                $result['info'] = null;
                if (isset($_GET['info']) && $_GET['info'] == 1) {
                    $info           = $DB->get_row("SELECT * FROM cmy_class WHERE cid= ?", array($cid));
                    $result['info'] = $info;
                }
            }
        } catch (\Exception $th) {
            $result['code'] = -1;
            $result['msg']  = '系统错误，' . $th->getMessage();
        }

        //如果是二级分类
        if ($result['class'] == 1 || $result['code'] == -1) {
            exit(json_encode($result, JSON_UNESCAPED_UNICODE));
        }

        $defaultImg = $cdnserver . 'assets/img/Product/default.png';

        $data = array();
        $rows = [];
        if ($rs) {
            $rows = $DB->fetchAll($rs);
        }

        $result['data'] = $rows;

        $weburl   = str_replace('user/', '', $weburl);
        $unsetarr = ['price1', 'close_alert', 'cost2', 'cost', 'result', 'result_succ', 'check', 'check_val', 'card', 'card_pass', 'attr_id', 'close_login', 'curl', 'title', 'shequ', 'mesh_list', 'pay_alipay', 'pay_qqpay', 'pay_rmb', 'pay_wxpay', 'permission', 'uptime', 'is_email', 'is_curl', 'is_rank'];
        foreach ($rows as $res) {
            $cids = explode(',', $res['cids']);
            if ($conf['hide_rmbpay'] == 1 && !$isLogin2 && $res['pay_rmb'] == 1) {
                continue;
            } elseif ($res['close_login'] == 1 && $isLogin2 !== 1) {
                //登录可见
                continue;
            } elseif ($cid && $res['cid'] != $cid && !in_array($cid, $cids)) {
                continue;
            }

            $stock_count = 0;
            if ($res['prid'] == 0) {
                $stock_count = intval($DB->count("SELECT count(*) FROM `pre_stock` WHERE `tid`=:tid", [$res['tid']]));
            }

            if ($stock_count > 0) {
                $rs2        = $DB->query("SELECT * FROM `pre_stock` WHERE `tid`=:tid order by value ASC", [$res['tid']]);
                $stock_rows = [];
                if ($rs2) {
                    $stock_rows = $DB->fetchAll($rs2);
                    foreach ($stock_rows as $item) {
                        if ($res['is_curl'] == 4) {
                            $isfaka       = 1;
                            $res['input'] = getFakaInput();
                        } else {
                            $isfaka = 0;
                        }

                        $tool           = $res;
                        $tool['price1'] = $item['price1'];
                        $tool['prid']   = $item['prid'];
                        $price_obj->setToolInfo($tool['tid'], $tool);
                        if ($isLogin2 == 1) {
                            $tool['price'] = $price_obj->getBuyPrice($tool['tid']);
                        } else {
                            $tool['price'] = $price_obj->getToolPrice($tool['tid']);
                        }

                        $shopimg = $tool['shopimg'];
                        if ($shopimg) {
                            if (!preg_match('/http/i', $shopimg)) {
                                $shopimg = $weburl . $res['shopimg'];
                            }
                        } else {
                            $shopimg = @getClassImg($tool['cid']);
                            if ($shopimg && !preg_match('/http/i', $shopimg)) {
                                $shopimg = $weburl . $shopimg;
                            } elseif ($shopimg == '') {
                                $shopimg = $weburl . 'assets/img/Product/default.png';
                            }
                        }

                        $tool['shopimg'] = $shopimg;

                        if (empty($tool['shopimg'])) {
                            $tool['shopimg'] = $defaultImg;
                        }

                        if (!empty($tool['close_alert'])) {
                            $tool['close_alert'] = htmlspecialchars_decode($tool['close_alert']);
                        }

                        $tool['isfaka'] = $isfaka;
                        $tool['value']  = $item['value'];
                        if ($item['value'] > 1) {
                            $tool['name'] = $tool['name'] . '☛' . $item['value'] . '个';
                        }
                        $tool['stock_id'] = $item['id'];
                        $tool['desc']     = htmlspecialchars_decode($tool['desc']);
                        $tool['alert']    = htmlspecialchars_decode($tool['alert']);
                        foreach ($unsetarr as $key2 => $value) {
                            unset($tool[$value]);
                        }
                        $data[] = $tool;
                    }
                }
            } else {
                if (isset($_SESSION['gift_id']) && isset($_SESSION['gift_tid']) && $_SESSION['gift_tid'] == $res['tid']) {
                    $price = $conf["cjmoney"] ? $conf["cjmoney"] : 0;
                } else {
                    if (is_object($price_obj)) {
                        $price_obj->setToolInfo($res['tid']);
                        if ($isLogin2 == 1) {
                            if ($userrow['power'] == 0 && $price_obj->getToolDel($res['tid']) == 1) {
                                continue;
                            }
                            $price = $price_obj->getBuyPrice($res['tid']);
                        } else {
                            if ($is_fenzhan == true && $price_obj->getToolDel($res['tid']) == 1) {
                                continue;
                            }
                            $price = $price_obj->getToolPrice($res['tid']);
                        }
                    } else {
                        $price = $res['price'];
                    }
                }

                if ($res['is_curl'] == 4) {
                    $isfaka       = 1;
                    $res['input'] = getFakaInput();
                } else {
                    $isfaka = 0;
                }

                $tool = $res;

                $shopimg = $tool['shopimg'];
                if ($shopimg) {
                    if (!preg_match('/http/i', $shopimg)) {
                        $shopimg = $weburl . $res['shopimg'];
                    }
                } else {
                    $shopimg = @getClassImg($tool['cid']);
                    if ($shopimg && !preg_match('/http/i', $shopimg)) {
                        $shopimg = $weburl . $shopimg;
                    } elseif ($shopimg == '') {
                        $shopimg = $weburl . 'assets/img/Product/default.png';
                    }
                }
                $tool['shopimg'] = $shopimg;

                if (!empty($tool['close_alert'])) {
                    $tool['close_alert'] = htmlspecialchars_decode($tool['close_alert']);
                }

                $tool['price']  = $price;
                $tool['isfaka'] = $isfaka;

                $tool['desc']  = htmlspecialchars_decode($tool['desc']);
                $tool['alert'] = htmlspecialchars_decode($tool['alert']);
                foreach ($unsetarr as $key => $value) {
                    unset($tool[$value]);
                }
                $data[] = $tool;
            }
        }
        $result['data'] = $data;

        break;
    case 'gettoolnew':
        $page  = $_POST['page'] ? intval(input('post.page')) : 1;
        $limit = $_POST['limit'] ? intval(input('post.limit')) : 9;
        if ($limit < 1) {
            $limit = 9;
        }

        if ($limit > 18) {
            $limit = 18;
        }

        $page      = ($page - 1) * $limit;
        $kw        = input('post.kw');
        $cid       = intval(input('post.cid'));
        $sort_type = $_POST['sort_type'] ? input('post.sort_type') : 'sort';
        $sort      = $_POST['sort'] ? input('post.sort') : 'ASC';
        if (!$cid && $sort_type == 'sort') {
            $sort_type = 'tid';
        }

        $sort_type_arr = ['sort', 'price', 'sale'];
        $sort_arr      = ['DESC', 'ASC'];

        if ($conf['template_store_sort_type'] == 1) {
            $orderBy = "deposit DESC, like_up DESC, sort ASC, like_down ASC";
        } else {
            $orderBy = "deposit DESC, like_up DESC, sort DESC, like_down ASC";
        }

        if (in_array($sort_type, $sort_type_arr) && in_array($sort, $sort_arr)) {
            $orderBy = "{$sort_type} {$sort}";
        }

        $where = "close!=1";
        if (!empty($kw)) {
            $where .= " and name LIKE '%{$kw}%'";
        }

        $result = array("code" => 0, "msg" => "succ", "class" => 0, "info" => []);

        if ($cid) {
            $subClass = $DB->count("SELECT count(*) FROM `pre_class` WHERE active=1 and upcid= ? order by sort asc", [$cid]);
            if ($subClass > 0) {
                $rs   = $DB->query("SELECT * FROM `pre_class` WHERE active=1 and upcid= ? order by sort asc", [$cid]);
                $data = array();
                $list = array();
                if ($rs) {
                    $list = $DB->fetchAll($rs);
                }

                foreach ($list as $res) {
                    if ($is_fenzhan && in_array($res['cid'], $classhide)) {
                        continue;
                    }
                    $data[] = $res;
                }
                $result['data']  = $data;
                $result['class'] = 1;
                $result['total'] = intval($subClass);
            } else {
                $info = $DB->get_row("SELECT * FROM cmy_class WHERE `cid`= ?", array($cid));
            }

            $where .= " and cid='{$cid}'";
        }

        //如果是二级分类或出错
        if ($result['class'] == 1) {
            exit(json_encode($result, JSON_UNESCAPED_UNICODE));
        }

        $num       = $DB->get_column("SELECT count(tid) FROM `pre_tools` WHERE {$where}");
        $rs        = $DB->select("SELECT * FROM `pre_tools` WHERE {$where} ORDER BY {$orderBy} LIMIT {$page},{$limit}");
        $data      = array();
        $curr_time = time();

        if (is_array($rs)) {
            $unsetarr = ['price1', 'close_alert', 'cost2', 'cost', 'result', 'result_succ', 'check', 'check_val', 'card', 'card_pass', 'attr_id', 'curl', 'title', 'zid', 'shequ', 'mesh_list', 'pay_alipay', 'pay_qqpay', 'pay_rmb', 'pay_wxpay', 'permission', 'uptime', 'is_email', 'is_curl', 'is_rank'];
            foreach ($rs as $key => $res) {
                if ($res['close_login'] == 1 && !$isLogin2) {
                    continue;
                } elseif ($res['close'] == 1) {
                    continue;
                }

                if (isset($_SESSION['gift_id']) && isset($_SESSION['gift_tid']) && $_SESSION['gift_tid'] == $res['tid']) {
                    $price = $conf["cjmoney"] ? $conf["cjmoney"] : 0;
                } elseif (isset($price_obj)) {
                    if ($isLogin2 == 1) {
                        $price = $price_obj->getBuyPrice($res['tid']);
                    } else {
                        $price_obj->setToolInfo($res['tid'], $res);
                        if ($is_fenzhan == true && $price_obj->getToolDel($res['tid']) == 1) {
                            continue;
                        }
                        $price = $price_obj->getToolPrice($res['tid']);
                    }
                } else {
                    $price = $res['price'];
                }

                $is_stock_err = 0;
                if ($res['is_curl'] == 4) {
                    $isfaka = 1;
                    $count  = intval($DB->count("SELECT count(*) FROM cmy_faka WHERE `tid`='" . $res['tid'] . "' AND `orderid`=0 AND `status`=0"));
                    if ($count == 0) {
                        $is_stock_err = 1;
                    }
                    $res['input'] = getFakaInput();
                } elseif ($res['stock_open'] == 1) {
                    $isfaka = 0;
                    $count  = intval($res['stock']);
                    if ($count == 0) {
                        $is_stock_err = 1;
                    }
                } else {
                    $isfaka = 0;
                    $count  = null;
                }

                if ($res['shopimg'] == '') {
                    $res['shopimg'] = @getClassImg($res['cid']);
                }

                if (!preg_match('/http/', $res['shopimg'])) {
                    $res['shopimg'] = $weburl . ltrim($res['shopimg'], '/');
                }

                foreach ($unsetarr as $key2 => $value) {
                    unset($res[$value]);
                }

                $res['is_stock_err'] = $is_stock_err;
                $res['stock']        = $count;
                $res['active']       = $res['active'] == 1 ? 0 : 1;
                $res['isfaka']       = $isfaka;
                $res['price']        = $price;
                $data[]              = $res;
            }
        }
        $pages  = ceil($num / $limit);
        $result = array("code" => 0, "msg" => "succ", "class" => 0, "data" => $data, "info" => $info, 'pages' => $pages, 'total' => intval($num));

        break;
    case 'getleftcount':
        $tid   = intval($_POST['tid']);
        $count = $DB->count("SELECT count(*) FROM cmy_faka WHERE tid= ? and orderid<=0", array($tid));
        Db::name('tools')->where(['tid' => $tid])->update(['stock' => $count, 'stock_time' => time() + 1200]);
        exit('{"code":0,"count":"' . $count . '"}');
        break;
    case 'getlikecount':
        $tid = intval($_POST['tid']);
        $row = $DB->find("SELECT * FROM cmy_tools WHERE tid= ?", array($tid));
        if ($row) {
            exit('{"code":0,"like_up":' . intval($row['like_up']) . ', "like_down":' . intval($row['like_down']) . '}');
        }
        exit('{"code":-1,"msg":"商品不存在"}');
        break;
    case 'like':
        if ($isLogin2 != 1) {
            exit('{"code":-2,"msg":"需要登录才能操作！"}');
        }

        $tid  = intval($_POST['tid']);
        $type = input('type', 1);
        $row  = $DB->find("SELECT * FROM cmy_tools WHERE tid= ?", array($tid));
        if ($row) {
            $log = $DB->find("SELECT * FROM cmy_tools_like_log WHERE tid= ? AND `uid`=?", array($tid, $userrow['zid']));
            if ($log) {
                exit('{"code":-1,"msg":"该商品您已经顶或踩过, 不能再操作"}');
            }

            if ($type == 'up') {
                Db::name('tools')->where(['tid' => $tid])->update(['like_up' => $row['like_up'] + 1]);
            } else {
                Db::name('tools')->where(['tid' => $tid])->update(['like_down' => $row['like_down'] + 1]);
            }

            // 写入日志
            Db::name('tools_like_log')->insert([
                'uid'     => $userrow['zid'],
                'tid'     => $tid,
                'type'    => $type,
                'addtime' => $date,
            ]);
            exit('{"code":0}');
        }
        exit('{"code":-1,"msg":"商品不存在"}');
        break;
    case 'toollogs':
        if (isset($_GET['page'])) {
            $total  = $DB->getColumn("SELECT count(*) FROM pre_tools_log");
            $limit  = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;
            $pages  = ceil($total / $limit);
            $offset = $limit * ($_GET['page'] - 1);
            $data   = $DB->select("SELECT * FROM pre_tools_log ORDER BY id DESC limit $offset,$limit");
            $result = ['code' => 0, 'msg' => 'success', 'data' => $data, 'total' => $total, 'page' => $pages];
        } else {
            $data   = $DB->select("SELECT * FROM pre_tools_log ORDER BY id DESC");
            $result = ['code' => 0, 'msg' => 'success', 'data' => $data, 'total' => count($data)];
        }

        if ($result['data']) {
            $item['data'] = parse_unique($item['data'], 'tid');
            foreach ($item['data'] as $key => &$value) {
                $value['time'] = date('Y-m-d H:i:s', $value['addtime']);
            }
        }
        exit(json_encode($result, 256));
        break;
    case 'toollogsgroup':
        $limit  = isset($_GET['limit']) ? (int) $_GET['limit'] : 5;
        $page   = isset($_GET['page']) ? (int) $_GET['page'] : 5;
        $result = getToolMessage($limit, $page);
        exit(json_encode($result, 256));
        break;
    case 'order2':
        $id   = input('get.id', 1);
        $rows = $DB->get_row("SELECT * from cmy_orders where id= ? limit 1", array($id));
        if (!$rows) {
            exit('{"code":-1,"msg":"当前订单不存在！"}');
        }

        $tool = $DB->get_row("SELECT * from cmy_tools where tid='{$rows['tid']}' limit 1");
        if ($tool['is_curl'] == 4) {
            $input = getFakaInput();
        } else {
            $input = $tool['input'] ? $tool['input'] : '下单ＱＱ';
        }

        $valArr       = explode('|', $tool['inputs']);
        $inputs       = $valArr;
        $result       = $rows['result'] ? $rows['result'] : $rows['info'];
        $obtn         = '';
        $rand         = '?' . $conf['jsver'];
        $placeholder2 = "补单需要的新内容啥的扔这里";
        $data         = '<div class="form-group text-center"><b style="color:red">异常信息</b><br/><span style="color:blue">' . $result . '</span></div>';
        $data .= '<div class="form-group"><div class="input-group"><div class="input-group-addon" id="edit_inputname">' . $input . '</div><input type="text" id="edit_inputvalue" placeholder="' . $placeholder . '" value="' . $rows['input'] . '" class="form-control" required/><div id="editBtnBox" class="input-group-addon" onclick="checkInput_input()">点我获取</div></div></div>';
        $select = '';
        $z      = 0;
        for ($i = 0; $i < count($valArr); $i++) {
            if (empty($rows['input' . ($z + 2)])) {
                continue;
            }

            $btnName = '';
            if (stripos($valArr[$i], "{") !== false) {
                $btnName   = explode('{', $valArr[$i])[0];
                $selectstr = explode('{', $valArr[$i])[1];
                $selectstr = explode('}', $selectstr)[0];
                $select    = getSelect($selectstr);
                $select    = '<select name="edit_inputvalue' . ($z + 2) . '" id="edit_inputvalue' . ($z + 2) . '" class="form-control">' . $select . '</select>';
                $data .= '<div class="form-group"><div class="input-group"><div class="input-group-addon" id="edit_inputname' . ($z + 2) . '">' . $btnName . '</div>' . $select . '<div id="editBtnBox2" class="input-group-addon" onclick="checkInput_input()">点我获取</div></div></div>';
            } else {
                $btnName = $valArr[$i];

                $data .= '<div class="form-group"><div class="input-group"><div class="input-group-addon" id="edit_inputname' . ($z + 2) . '">' . $btnName . '</div><input type="text" id="edit_inputvalue' . ($z + 2) . '" value="' . $rows['input' . ($z + 2)] . '" class="form-control" required/><div id="editBtnBox2" class="input-group-addon" onclick="checkInput_input()">点我获取</div></div></div>';
            }
            $z++;
            array_push($inputs, $btnName);
        }

        $data .= '<div id="edit_inputsname"></div>';
        $data .= '<div class="form-group"><div class="input-group"><div class="input-group-addon" id="edit_inputname6">备注/说明：</div><textarea type="text" id="edit_inputvalue6" rows="4" placeholder="' . $placeholder2 . '" class="form-control" required></textarea></div></div>';
        $data .= '<input type="submit" id="save" onclick="saveOrder(' . $id . ')" class="btn btn-primary btn-block" value="提交补单">';
        $data .= '<script src="/assets/js/inputorder.js?ver=' . $conf['version'] . rand(1, 999) . '"></script>';
        $result = array("code" => 0, "msg" => "succ", "data" => $data, 'inputs' => $inputs);

        break;
    case 'editOrder':

        $id            = input('id', 1);
        $inputvalue    = input('post.inputvalue', 1);
        $inputvalue2   = input('post.inputvalue2', 1);
        $inputvalue3   = input('post.inputvalue3', 1);
        $inputvalue4   = input('post.inputvalue4', 1);
        $inputvalue5   = input('post.inputvalue5', 1);
        $bz            = input('post.bz', 1) . '（更新时间：' . $date . '）';
        $inputValueArr = array($inputvalue, $inputvalue2, $inputvalue3, $inputvalue4, $inputvalue5);
        $order         = $DB->get_row("SELECT * from cmy_orders where id= ? limit 1", array($id));
        if (!$order) {
            exit('{"code":-1,"msg":"该订单不存在，请确定后再试或联系客服处理！"}');
        }

        $tool = $DB->get_row("SELECT * from cmy_tools where tid='" . $order['tid'] . "' limit 1");
        // if ($tool && $tool['inputs']) {
        //     //检测密码是否相同，避免客户恶意重复填密码补单影响效率，如果不需要自己改
        //     $inputs = getInputsBtn($tool['input'],$tool['inputs']);
        //     $i=1;
        //     foreach ($inputs as $title) {
        //         if ($i==1 && $tool['input'])continue;
        //         $title =strtolower($title);
        //         if (stripos($title, 'qq密码')!==false) {
        //             if($i==2 && !empty($inputvalue2) && $order['input2']===$inputvalue2){
        //                 exit('{"code":-1,"msg":"密码不能与上次相同！"}');
        //             }
        //             elseif($i==3 && !empty($inputvalue3) && $order['input3']===$inputvalue3){
        //                 exit('{"code":-1,"msg":"密码不能与上次相同！"}');
        //             }
        //             elseif($i==4 && !empty($inputvalue4) && $order['input4']===$inputvalue4){
        //                 exit('{"code":-1,"msg":"密码不能与上次相同！"}');
        //             }
        //             elseif($i==5 && !empty($inputvalue5) && $order['input5']===$inputvalue5){
        //                 exit('{"code":-1,"msg":"密码不能与上次相同！"}');
        //             }
        //         }
        //         $i++;
        //     }
        // }
        $checkVal = checkVal($inputValueArr, $tool);
        if ($checkVal['code'] !== 0) {
            exit(json_encode($checkVal, true));
        } else {
            $inputArr   = $checkVal['inputArr'];
            $inputvalue = $inputArr[0];
            if ($inputArr[1]) {
                $inputvalue2 = $inputArr[1];
            }

            if ($inputArr[2]) {
                $inputvalue3 = $inputArr[2];
            }

            if ($inputArr[3]) {
                $inputvalue4 = $inputArr[3];
            }

            if ($inputArr[4]) {
                $inputvalue5 = $inputArr[4];
            }
        }
        $sds = "UPDATE `pre_orders` set `input`= ?,`input2`= ?,`input3`= ?,`input4`= ?,`input5`= ?,`bz`= ?,`result`='',`status`=0 where `id`= ?";
        if ($DB->query($sds, array($inputvalue, $inputvalue2, $inputvalue3, $inputvalue4, $inputvalue5, $bz, $id))) {
            exit('{"code":0,"msg":"修改订单成功！请等待处理","inputvalue":"' . $inputvalue . '"}');
        } else {
            exit('{"code":-1,"msg":"修改订单失败，请稍后重试或联系客服！' . $DB->error() . '"}');
        }

        break;
    case 'pay':
        $method        = input('method', 1);
        $inputvalue    = input('post.inputvalue', 1);
        $inputvalue2   = input('post.inputvalue2', 1);
        $inputvalue3   = input('post.inputvalue3', 1);
        $inputvalue4   = input('post.inputvalue4', 1);
        $inputvalue5   = input('post.inputvalue5', 1);
        $inputattr     = input('post.inputattr', 1);
        $stock_id      = intval(input('post.stock_id', 1));
        $inputValueArr = array($inputvalue, $inputvalue2, $inputvalue3, $inputvalue4, $inputvalue5);
        $num           = intval($_POST['num']) > 0 ? intval(input('post.num', 1)) : 1;
        $hashsalt      = isset($_POST['hashsalt']) ? input('post.hashsalt', 1) : null;
        $tid           = $_POST['tid'] > 0 ? intval(input('post.tid', 1)) : 0;
        if (session_status() < 2) {
            exit('{"code":-1,"msg":"验证失败，该服务器SESSION不支持，请联系网站客服反馈解决"}');
        } elseif ($conf['verify_open'] == 1 && $hashsalt != session_get()) {
            exit('{"code":-1,"msg":"验证错误，请刷新页面重试","addsalt":"' . session_get() . '"}');
        }

        if ($method == 'cart_edit') {
            $cookiesid = $isLogin2 == 1 ? $userrow['zid'] : $cookiesid;
            $shop_id   = intval($_POST['shop_id']);
            $cart_item = $DB->get_row("SELECT * FROM `pre_cart` WHERE `id`= ? LIMIT 1", array($shop_id));
            if (!$cart_item) {
                exit('{"code":-1,"msg":"商品不存在！"}');
            }

            if ($cart_item['userid'] != $cookiesid || $cart_item['status'] > 1) {
                exit('{"code":-1,"msg":"商品权限校验失败"}');
            }
            $tid  = intval($cart_item['tid']);
            $tool = $DB->get_row("SELECT * from cmy_tools where tid= ? limit 1", array($tid));
        } elseif (isset($_SESSION['gift_id']) && $_SESSION['gift_tid'] == $tid) {
            $tid  = $_SESSION['gift_tid'];
            $tool = $DB->get_row("SELECT A.*,B.`hidepays` from cmy_tools A LEFT JOIN cmy_class B ON (A.`cid`=B.`cid`) where A.`tid`= ? limit 1", [$tid]);
        } else {
            $tool = $DB->get_row("SELECT A.*,B.`hidepays` from cmy_tools A LEFT JOIN cmy_class B ON (A.`cid`=B.`cid`) where A.`tid`= ? limit 1", [$tid]);
        }

        if ($tool) {

            if ($num < 1) {
                exit('{"code":-1,"msg":"单次下单份数最低为1份！"}');
            } elseif ($tool['active'] != 1) {
                exit('{"code":-1,"msg":"当前商品维护中，停止下单！"}');
            } elseif ($tool['stock_open'] == 1 && $tool['stock'] < $num) {
                exit('{"code":-1,"msg":"该商品库存已不足' . $num . '份！"}');
            }

            if (checkBlockCity($tool['cid']) === true) {
                exit('{"code":-1,"msg":"当前商品该地区维护中，禁止下单！"}');
            }

            if (!empty($conf['blacklist']) && in_array($inputvalue, explode("|", $conf['blacklist']))) {
                $msg = '您的下单账号已被拉黑，无法下单！';
                if (!empty($conf['blacklisttip'])) {
                    $msg = $conf['blacklisttip'];
                }
                exit('{"code":-1,"msg":"' . $msg . '"}');
            }

            // $vipList = ['超级会员', 'QQ会员', '腾讯视频VIP', '豪华黄钻', '黄钻贵族', '绿钻豪华版', '绿钻', '紫钻', '红钻'];
            // if (in_array($tool["check_val"], $vipList) && preg_match('/^[1-9]{1}[0-9]{4,11}$/', $inputvalue)) {
            //     //验证QQ钻
            //     if (checkVip($inputvalue, $tool["check_val"]) === true) {
            //         exit('{"code":-1,"msg":"该下单账号已开通&nbsp;<span style=\'color:red\'>' . $tool["check_val"] . '</span>&nbsp;，请等到期后再下单哦"}');
            //     }
            // }

            if ($conf['index_regbuy'] == 1 && !$isLogin2) {
                exit('{"code":4,"msg":"你还未登录"}');
            }

            if ($tool['is_curl'] == 4) {
                if (!$isLogin2 && $conf['faka_input'] == 0 && !checkEmail($inputvalue)) {
                    exit('{"code":-1,"msg":"邮箱格式不正确"}');
                }

                $count = $DB->count("SELECT count(*) FROM cmy_faka WHERE tid=:tid and (`orderid`<1 OR `orderid` is null)", [':tid' => $tid]);
                $nums  = ($tool['value'] > 0 ? $tool['value'] : 1) * $num;
                if ($count == 0) {
                    exit('{"code":-1,"msg":"该商品库存卡密不足，请联系站长加卡！"}');
                }

                if ($nums > $count) {
                    exit('{"code":-1,"msg":"你所购买的数量超过库存数量！"}');
                }
            } elseif ($tool['input'] == "") {
                if (!preg_match('/^[1-9]{1}[0-9]{4,11}$/', $inputvalue)) {
                    exit(json_encode(["code" => -1, "msg" => "请输入正确的QQ号码！"]));
                }
            }

            $is_gift = false;
            if (isset($_SESSION['gift_id']) && isset($_SESSION['gift_tid']) && $_SESSION['gift_tid'] == $tool['tid']) {
                $gift_id  = $_SESSION['gift_id'];
                $gift_row = $DB->get_row("SELECT * from cmy_giftlog where id= ? limit 1", array($gift_id));
                if ($gift_row['status'] == 1) {
                    exit('{"code":-1,"msg":"该抽奖奖励已经领取过了！"}');
                } elseif ($gift_row && $gift_row['tid'] == $tool['tid']) {
                    $is_gift = true;
                } else {
                    unset($_SESSION['gift_id']);
                    unset($_SESSION['gift_tid']);
                    exit('{"code":-1,"msg":"该抽奖奖励不存在！"}');
                }
                $price = $conf["cjmoney"] ? $conf["cjmoney"] : 0;
            } else {
                if (isset($stock_id) && $stock_id > 0) {
                    //规格价格处理
                    $stock_row = $DB->get_row("SELECT * from cmy_stock where id=:stock_id limit 1", [':stock_id' => $stock_id]);
                    if ($stock_row) {
                        if ($stock_row['stock'] > 0) {
                            $tool['price1'] = $stock_row['price1'];
                            $tool['prid']   = $stock_row['prid'];
                        } else {
                            exit('{"code":-1,"msg":"您所选版本商品库存已售罄，请重新选购或联系客服咨询下单！"}');
                        }
                    } else {
                        exit('{"code":-1,"msg":"您所选版本商品已下架，请刷新页面重新选购！"}');
                    }
                }
                if ($isLogin2 == 1) {
                    $price_obj->setToolInfo($tool['tid'], $tool);
                    $price = $price_obj->getBuyPrice($tool['tid']);
                } elseif ($is_fenzhan == true) {
                    $price_obj->setToolInfo($tool['tid'], $tool);
                    if ($price_obj->getToolDel($tool['tid']) == 1) {
                        exit('{"code":-1,"msg":"该商品已下架！"}');
                    }
                    $price = $price_obj->getToolPrice($tool['tid']);
                } else {
                    $price_obj->setToolInfo($tool['tid'], $tool);
                    $price = $price_obj->getToolPrice($tool['tid']);
                }
            }

            if ($tool['repeat'] > 1) {
                if ($tool['repeat'] == 2 && $count) {
                    exit('{"code":-1,"msg":"您今天已经买过这个了，明天再来吧~"}');
                } elseif ($tool['repeat'] == 3) {
                    $thtime = date("Y-m-d") . ' 00:00:00';
                    $row    = $DB->get_row("SELECT * from cmy_orders where (userid='" . $cookiesid . "' or input='" . $inputvalue . "') and addtime>'" . $thtime . "' and tid='" . $tool['tid'] . "' limit 1");
                    if ($row) {
                        $daytime  = 86400;
                        $lasttime = strtotime($date) - strtotime($row['addtime']);
                        $day      = floor($lasttime / $daytime);
                        if ($day < 25) {
                            exit('{"code":-1,"msg":"该商品每25天才能买1次哦！距离上次购买已' . $day . '天~"}');
                        }
                    }
                }
            } elseif ($tool['repeat'] == 0) {
                $thtime = date("Y-m-d") . ' 00:00:00';
                $row    = $DB->get_row("SELECT * from cmy_orders where tid=:tid and input=:input order by id desc limit 1", [':tid' => $tid, ':input' => $inputvalue]);
                if ($row['input'] && $row['status'] == 0) {
                    exit('{"code":-1,"msg":"您今天添加的' . $tool['name'] . '正在排队中，请勿重复提交！"}');
                } elseif ($row['addtime'] > $thtime) {
                    exit('{"code":-1,"msg":"您今天已添加过' . $tool['name'] . '，请勿重复提交！"}');
                }

                if ($price <= 0) {
                    $row = $DB->get_row("SELECT * from cmy_orders where (userid=:userid or input=:input) and addtime>:thtime and tid=:tid", [':userid' => $cookiesid, ':input' => $inputvalue, ':thtime' => $thtime, ':tid' => $tool['tid']]);
                    if ($row) {
                        exit('{"code":-1,"msg":"' . $free_maxmsg . '"}');
                    } elseif ($conf['free_max_open'] == 1) {
                        if ($_SESSION['blockfree'] == true || $DB->count("SELECT count(*) FROM `pre_pay` WHERE `tid`=:tid and `money`=0 and `ip`=:ip and `status`=1 and `endtime`>:thtime", [':ip' => $clientip, ':thtime' => $thtime, ':tid' => $tool['tid']]) >= 1) {
                            exit('{"code":-1,"msg":"您今天已领取过，请明天再来哦！<br>' . $free_maxmsg . '"}');
                        }
                    }
                }
            }

            if ($tool['validate'] == 1 && is_numeric($inputvalue)) {
                $validate = true;
                //$validate=validate_qzone($inputvalue);
                if ($validate === false) {
                    exit('{"code":-1,"msg":"您的QQ空间设置了访问权限，无法下单！"}');
                }
            }

            if ($tool['multi'] == 0 || $num < 1) {
                $num = 1;
            }

            if ($tool['multi'] == 1 && $tool['min'] > 0 && $num < $tool['min']) {
                exit('{"code":-1,"msg":"当前商品最小下单份数为' . $tool['min'] . '"}');
            }

            if ($tool['multi'] == 1 && $tool['max'] > 0 && $num > $tool['max']) {
                exit('{"code":-1,"msg":"当前商品最大下单份数为' . $tool['max'] . '"}');
            }

            if ($conf['order_inputcheck'] == 1) {
                //处理不符合下单要求的下单数据
                $checkVal = checkVal($inputValueArr, $tool);
                if ($checkVal['code'] !== 0) {
                    exit(json_encode($checkVal, true));
                } else {
                    $inputArr   = $checkVal['inputArr'];
                    $inputvalue = $inputArr[0];
                    if ($inputArr[1]) {
                        $inputvalue2 = $inputArr[1];
                    }

                    if ($inputArr[2]) {
                        $inputvalue3 = $inputArr[2];
                    }

                    if ($inputArr[3]) {
                        $inputvalue4 = $inputArr[3];
                    }

                    if ($inputArr[4]) {
                        $inputvalue5 = $inputArr[4];
                    }
                }
            }

            if ($tool['price1'] > 0 && $tool['prid'] > 0 && $price < $tool['price1'] && $tool['price'] > 0 && !$is_gift) {
                exit('{"code":-1,"msg":"当前商品价格异常，请稍后重新发起支付试试！"}');
            }

            $need = sprintf('%.5f', floatval($price * $num));

            if ($need > 0) {
                $need = $need * getAllTimes($tool, $inputValueArr);
            }

            if ($need > 100000) {
                exit('{"code":-1,"msg":"单笔订单金额不能超过100000，大订单请联系网站客服会有优惠哦"}');
            }

            if (isset($price_obj) && $tool['prices']) {
                $need = $price_obj->getFinalPrice($need, $num);
            }

            if ($need == 0 && $tid != $_SESSION['gift_tid']) {
                if ($method == 'cart_add' || $method == 'cart_edit') {
                    exit('{"code":-1,"msg":"免费商品请直接点击领取"}');
                }

                $thtime = date("Y-m-d") . ' 00:00:00';
                if ($_SESSION['blockfree'] == true || $DB->count("SELECT count(*) FROM `pre_pay` WHERE `tid`= ? and `money`=0 and `ip`= ? and `status`=1 and `addtime`> ?", [$tid, $clientip, $thtime]) >= 1) {
                    exit('{"code":-1,"msg":"您今天已领取过，请明天再来！"}');
                }

                if (!empty($conf['freeblacklist']) && in_array($inputvalue, explode("|", $conf['freeblacklist']))) {
                    $msg = '您的下单账号已无法领取免费商品！';
                    if (!empty($conf['freeblacklisttip'])) {
                        $msg = $conf['freeblacklisttip'];
                    }
                    exit('{"code":-1,"msg":"' . $msg . '"}');
                }
            }

            if ($need == 0 && $conf['captcha_open'] > 0 && $conf['captcha_open_freebuy'] == 1) {
                if ($conf['captcha_open'] == 1) {
                    if (isset($_POST['geetest_challenge']) && isset($_POST['geetest_validate']) && isset($_POST['geetest_seccode'])) {
                        $GtSdk = new \core\GeetestLib($conf['captcha_id'], $conf['captcha_key']);
                        $data  = array(
                            'user_id'     => $cookiesid,
                            'client_type' => "web",
                            'ip_address'  => $clientip,
                        );

                        if ($_SESSION['gtserver'] == 1) {
                            //服务器正常
                            $result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $data);
                            if ($result) {
                                //echo '{"status":"success"}';
                            } else {
                                exit('{"code":-1,"msg":"验证失败，请重新验证"}');
                            }
                        } else {
                            //服务器宕机,走failback模式
                            if ($GtSdk->fail_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'])) {
                                //echo '{"status":"success"}';
                            } else {
                                exit('{"code":-1,"msg":"验证失败，请重新验证"}');
                            }
                        }
                    } else {
                        exit('{"code":2,"msg":"请先完成验证"}');
                    }
                } else {
                    if ($conf['captcha_open'] == 2) {
                        $code = input('post.code', 1);
                        if (!$code || !captcha_check($code, 'freebuy')) {
                            exit('{"code":4,"msg":"请先完成验证"}');
                        }
                    }
                }
            }

            //同步亿樂社区价格
            // if ($conf['pricejk_time'] < 120) {
            //     $conf['pricejk_time'] = 120;
            // }

            // if ($need > 0 && time() - $tool['uptime'] >= $conf['pricejk_time'] && $tool['shequ'] > 0 && $tool['is_curl'] == 2) {
            //     $change_num = pricejk_yile_one($tool);
            //     if ($change_num > 0) {
            //         exit('{"code":3,"msg":"当前商品价格发生变化，请刷新页面重试","change":"' . $change_num . '"}');
            //     }
            // }

            $trade_no = date("YmdHis") . rand(111111, 999999);
            if ($need == 0) {
                $trade_no = 'free' . $trade_no;
                $num      = 1;
            }

            $input = $inputvalue . ($inputvalue2 ? '|' . $inputvalue2 : null) . ($inputvalue3 ? '|' . $inputvalue3 : null) . ($inputvalue4 ? '|' . $inputvalue4 : null) . ($inputvalue5 ? '|' . $inputvalue5 : null);
            if ($method == 'cart_add') {
                $toolCount = $DB->count("SELECT count(*) FROM cmy_cart WHERE tid='" . $tid . "' and input='" . $input . "' and status=0");

                if ($tool['multi'] == 0 && $toolCount >= 1) {
                    exit('{"code":-1,"msg":"该商品1次最多只能下单1份且已经添加过购物车了！"}');
                }
                $cookiesid = $isLogin2 === 1 ? $userrow['zid'] : $cookiesid;
                $sqlData   = [
                    ':userid'    => $cookiesid,
                    ':zid'       => $sitezid,
                    ':tid'       => $tid,
                    ':input'     => $input,
                    ':inputattr' => $inputattr,
                    ':num'       => $num,
                    ':amount'    => $need,
                    ':stock_id'  => $stock_id,
                    ':addtime'   => $date,
                ];
                $sql = "INSERT INTO `pre_cart` (`userid`,`zid`,`tid`,`input`,`inputattr`,`num`,`money`,`stock_id`,`addtime`,`status`) values (:userid, :zid, :tid, :input, :inputattr, :num, :amount, :stock_id, :addtime, '0')";
                if ($DB->query($sql, $sqlData)) {

                    $cart_count = $DB->count("SELECT count(*) from cmy_cart WHERE `userid`='{$cookiesid}' AND `status`=0");
                    exit('{"code":0,"msg":"加入购物车成功！","need":"' . $need . '","cart_count":"' . $cart_count . '"}');
                } else {
                    exit('{"code":-1,"msg":"加入购物车失败！' . $DB->error() . '"}');
                }
            } elseif ($method == 'cart_edit') {
                $sql = "UPDATE `pre_cart` set `input`= ?,`num`= ?,`money`= ?,`status`='0' where id= ?";
                if ($DB->query($sql, array($input, $num, $need, $shop_id))) {
                    exit('{"code":0,"msg":"编辑订单成功！","need":"' . $need . '"}');
                } else {
                    exit('{"code":-1,"msg":"编辑订单失败！' . $DB->error() . '"}');
                }
            } else {

                if ($need == 0) {
                    require_once SYSTEM_ROOT . 'ajax.class.php';
                    $sqlData = [
                        ':trade_no'  => $trade_no,
                        ':type'      => 'free',
                        ':tid'       => $tid,
                        ':zid'       => $sitezid,
                        ':input'     => $input,
                        ':inputattr' => $inputattr,
                        ':num'       => $num,
                        ':name'      => $tool['name'],
                        ':amount'    => $need,
                        ':ip'        => $clientip,
                        ':userid'    => $isLogin2 === 1 ? $userrow['zid'] : $cookiesid,
                        ':inviteid'  => $invite_id,
                        ':siteurl'   => addslashes($_SERVER['HTTP_HOST']),
                        ':addtime'   => $date,
                        ':stock_id'  => $stock_id,
                    ];
                    if ($is_gift === true) {
                        $sqlData[':type'] = 'gift_free';
                    }
                    $sql = "INSERT INTO `pre_pay` (`trade_no`,`type`,`tid`,`zid`,`input`,`inputattr`,`num`,`name`,`money`,`ip`,`userid`,`inviteid`,`siteurl`,`addtime`,`stock_id`,`status`) values (:trade_no, :type, :tid, :zid, :input, :inputattr, :num, :name, :amount, :ip, :userid, :inviteid, :siteurl, :addtime, :stock_id, '0')";
                    if ($DB->query($sql, $sqlData)) {
                        //session_set('', 0);
                        $_SESSION['blockfree'] = true;
                        if (isset($_SESSION['gift_id'])) {
                            $gift_id = $_SESSION['gift_id'];
                            $DB->query("UPDATE `pre_giftlog` set `status` =1,`tradeno` = ?,`input` = ? where `id`= ?", array($trade_no, $input, $gift_id));
                            unset($_SESSION['gift_id']);
                            unset($_SESSION['gift_tid']);
                        }
                        $result = ['code' => -1, 'msg' => '下单失败，请稍后再试！'];

                        $srow = $DB->get_row("SELECT * from `pre_pay` where trade_no= ? limit 1", [$trade_no]);
                        if ($orderid = processOrderAll($srow)) {
                            $result = [
                                'code'     => 1,
                                'msg'      => $free_okmsg,
                                'orderid'  => $orderid,
                                'trade_no' => $trade_no,
                                'input'    => $input,
                            ];
                        } else {
                            $result = [
                                'code'     => -1,
                                'msg'      => '下单失败！' . $DB->error(),
                                'trade_no' => $trade_no,
                                'input'    => $input,
                            ];
                        }
                    } else {
                        $result = [
                            'code'  => -1,
                            'msg'   => '订单提交失败！' . $DB->error(),
                            'input' => $input,
                        ];
                    }
                } else {
                    $sqlData = [
                        ':trade_no'  => $trade_no,
                        ':tid'       => $tid,
                        ':zid'       => $sitezid,
                        ':input'     => $input,
                        ':inputattr' => $inputattr,
                        ':num'       => $num,
                        ':name'      => $tool['name'],
                        ':amount'    => $need,
                        ':ip'        => $clientip,
                        ':userid'    => $isLogin2 === 1 ? $userrow['zid'] : $cookiesid,
                        ':inviteid'  => $invite_id,
                        ':siteurl'   => addslashes($_SERVER['HTTP_HOST']),
                        ':addtime'   => $date,
                        ':stock_id'  => $stock_id,
                    ];
                    $sql = "INSERT INTO `pre_pay` (`trade_no`,`tid`,`zid`,`input`,`inputattr`,`num`,`name`,`money`,`ip`,`userid`,`inviteid`,`siteurl`,`addtime`,`stock_id`,`status`) values (:trade_no, :tid, :zid, :input, :inputattr, :num, :name, :amount, :ip, :userid, :inviteid, :siteurl, :addtime, :stock_id, '0')";
                    if ($DB->query($sql, $sqlData)) {
                        //session_set('', 0);
                        if (isset($_SESSION['gift_id'])) {
                            $DB->query("UPDATE `pre_giftlog` set `status` =1,`payorder` = ? where `id`= ?", array($trade_no, $gift_id));
                            unset($_SESSION['gift_id']);
                            unset($_SESSION['gift_tid']);
                        }

                        $conf = getPayConf($tool);

                        if ($conf['alipay_api'] == 1 && $conf['alipay_limit_open'] == 1) {
                            $conf['alipay_api'] = $need > floatval($conf['alipay_limit']) ? '1' : '0';
                        }

                        if ($conf['wxpay_api'] == 1 && $conf['wxpay_limit_open'] == 1) {
                            $conf['wxpay_api'] = $need > floatval($conf['wxpay_limit']) ? '1' : '0';
                        }

                        if ($conf['qqpay_api'] == 1 && $conf['qqpay_limit_open'] == 1) {
                            $conf['qqpay_api'] = $need > floatval($conf['qqpay_limit']) ? '1' : '0';
                        }

                        $result = ["code" => 0, "msg" => "提交订单成功！", "trade_no" => $trade_no, "need" => $need, "pay_alipay" => $conf['alipay_api'], "pay_wxpay" => $conf['wxpay_api'], "pay_qqpay" => $conf['qqpay_api'], "pay_rmb" => $conf['rmbpay_api'], "user_rmb" => round($userrow['money'], 5), "inputvalue" => $inputvalue, "trade_no" => $trade_no, "pay_alert" => $conf['pay_alert']];
                    } else {
                        $result = [
                            'code' => -1,
                            'msg'  => '提交订单失败！' . $DB->error(),
                        ];
                    }
                }
            }
        } else {
            exit('{"code":-2,"msg":"该商品不存在"}');
        }
        break;
    case 'cancel':
        /*$orderid=isset($_POST['orderid'])?daddslashes((int)$_POST['orderid']):exit('{"code":-1,"msg":"订单号未知"}');
        $hashsalt=isset($_POST['hashsalt'])?$_POST['hashsalt']:null;
        $srow=$DB->get_row("SELECT * FROM cmy_pay WHERE trade_no= ? limit 1 for update", array($orderid));
        if(!$srow['trade_no'] || $srow['userid']!=$cookiesid)exit('{"code":-1,"msg":"订单号不存在！"}');
        if($srow['status']==0){
        $DB->query("DELETE FROM cmy_pay WHERE trade_no= ?", array($orderid));
        if($conf['verify_open']==1){
        session_set($hashsalt, 300);
        }
        }*/
        exit('{"code":0,"msg":"ok"}');
        break;
    case 'getSpecsInfo':
        $tid               = intval(input('post.tid'));
        $tid > 0 && $count = $DB->count("SELECT count(*) FROM cmy_stock where tid= ? ORDER BY `value` ASC", [$tid]);
        if ($tid > 0 && $count > 0) {
            $rs      = $DB->query("SELECT * FROM cmy_stock where tid= ? ORDER BY `value` ASC", [$tid]);
            $data    = [];
            $options = [];
            if ($rs) {
                $data = $DB->fetchAll($rs);
                foreach ($data as $key => $row) {
                    $options[$key] = getStockInfo($row);
                }
            }
            $result = array("code" => 0, "msg" => "succ", "data" => $options);
        } else {
            $result = array("code" => -1, "msg" => "库存参数错误，请联系客服处理！", "data" => []);
        }

        break;

    case 'checkkm':
        $km    = input('post.km', 1);
        $myrow = $DB->get_row("SELECT * from cmy_kms where km= ? limit 1", [$km]);
        if (!$myrow) {
            exit('{"code":-1,"msg":"此卡密不存在！","km":"' . $km . '"}');
        } elseif ($myrow['usetime'] != null) {
            exit('{"code":-1,"msg":"此卡密已被使用！"}');
        }
        $tool   = $DB->get_row("SELECT * from cmy_tools where tid= ? limit 1", [$myrow['tid']]);
        $result = array("code" => 0, "tid" => $tool['tid'], "cid" => $tool['cid'], "name" => $tool['name'], "alert" => $tool['alert'], "desc" => $tool['desc'], "inputname" => $tool['input'], "inputsname" => $tool['inputs'], "value" => $tool['value'], "close" => $tool['close'], "active" => $tool['active']);

        break;
    case 'card':
        if ($conf['iskami'] == 0) {
            exit('{"code":-1,"msg":"当前站点未开启卡密下单"}');
        }

        $km          = input('post.km', 1);
        $inputvalue  = input('post.inputvalue', 1);
        $inputvalue2 = input('post.inputvalue2', 1);
        $inputvalue3 = input('post.inputvalue3', 1);
        $inputvalue4 = input('post.inputvalue4', 1);
        $inputvalue5 = input('post.inputvalue5', 1);
        $myrow       = $DB->get_row("SELECT * from cmy_kms where km= ? limit 1", [$km]);
        if (!$myrow) {
            exit('{"code":-1,"msg":"此卡密不存在！","km":"' . $km . '"}');
        } elseif (!empty($myrow['usetime']) && !empty($myrow['user'])) {
            exit('{"code":-1,"msg":"此卡密已被使用！"}');
        } else {
            require_once SYSTEM_ROOT . 'ajax.class.php';
            $tid  = $myrow['tid'];
            $tool = $DB->get_row("SELECT * from cmy_tools where tid=:tid limit 1", [':tid' => $tid]);
            if ($tool && $tool['active'] == 1) {
                if (in_array($inputvalue, explode("|", $conf['blacklist']))) {
                    exit('{"code":-1,"msg":"您的下单账号已被拉黑，无法下单！"}');
                }

                if ($tool['repeat'] == 0) {
                    $row    = $DB->get_row("SELECT * from cmy_orders where tid= ? and input= ? order by id desc limit 1", array($tid, $inputvalue));
                    $thtime = date("Y-m-d") . ' 00:00:00';
                    if ($row['input'] && $row['status'] == 0) {
                        exit('{"code":-1,"msg":"您今天添加的' . $tool['name'] . '正在排队中，请勿重复提交！"}');
                    } elseif ($row['addtime'] > $thtime) {
                        exit('{"code":-1,"msg":"您今天已添加过' . $tool['name'] . '，请勿重复提交！"}');
                    }
                }
                if ($tool['validate'] && is_numeric($inputvalue)) {
                    if (validate_qzone($inputvalue) == false) {
                        exit('{"code":-1,"msg":"您的QQ空间设置了访问权限，无法下单！"}');
                    }
                }
                $srow['tid']      = $tid;
                $srow['input']    = $inputvalue . ($inputvalue2 ? '|' . $inputvalue2 : null) . ($inputvalue3 ? '|' . $inputvalue3 : null) . ($inputvalue4 ? '|' . $inputvalue4 : null) . ($inputvalue5 ? '|' . $inputvalue5 : null);
                $srow['num']      = 1;
                $srow['zid']      = $sitezid;
                $srow['userid']   = $cookiesid;
                $srow['trade_no'] = 'kid:' . $myrow['kid'];
                if ($orderid = processOrderAll($srow)) {
                    $DB->query("UPDATE `pre_kms` set `user` =:user,`usetime` =:usetime where `kid`=:kid", [':user' => $inputvalue, ':usetime' => $date, ':kid' => $myrow['kid']]);
                    exit('{"code":0,"msg":"' . $tool['name'] . ' 下单成功！你可以在进度查询中查看进度","orderid":"' . $orderid . '"}');
                } else {
                    exit('{"code":-1,"msg":"' . $tool['name'] . ' 下单失败！' . $DB->error() . '"}');
                }
            } else {
                exit('{"code":-2,"msg":"该商品不存在"}');
            }
        }
        break;
    case 'query':
        $type   = intval(input('type', 1));
        $qq     = input('qq', 1);
        $page   = isset($_POST['page']) ? intval($_POST['page']) : 1;
        $status = isset($_POST['status']) ? intval($_POST['status']) : -1;

        $limit = 10;
        $start = $limit * ($page - 1);
        if (empty($qq)) {
            if ($status >= 0) {
                $query_sql  = "SELECT * FROM cmy_orders WHERE `userid`=:uid AND `status`=:status order by id desc limit :pagestart, :pagesize";
                $query_data = array(
                    ':uid'       => $isLogin2 === 1 ? $userrow['zid'] : $cookiesid,
                    ':status'    => $status,
                    ':pagestart' => $start,
                    ':pagesize'  => $limit,
                );
                $query_data2 = $query_data;
                unset($query_data2[':pagestart']);
                unset($query_data2[':pagesize']);
                $total = $DB->count("SELECT count(*) FROM cmy_orders WHERE `userid`=:uid AND `status`=:status", $query_data2);
            } else {
                $query_sql  = "SELECT * FROM cmy_orders WHERE `userid`=:uid order by id desc limit :pagestart, :pagesize";
                $query_data = array(
                    ':uid'       => $isLogin2 === 1 ? $userrow['zid'] : $cookiesid,
                    ':pagestart' => $start,
                    ':pagesize'  => $limit,
                );
                $query_data2 = $query_data;
                unset($query_data2[':pagestart']);
                unset($query_data2[':pagesize']);
                $total = $DB->count("SELECT count(*) FROM `cmy_orders` WHERE `userid`=:uid", $query_data2);
            }
        } else {
            $query_data = [];
            if ($conf['query_checkcookie'] == 1) {
                $query_data[':uid'] = $isLogin2 === 1 ? $userrow['zid'] : $cookiesid;
            }

            if ($conf['query_orderid'] == 1) {
                //支持订单ID查询
                $query_data = array_merge($query_data, array(
                    ':payorder'  => $qq,
                    ':id'        => $qq,
                    ':input'     => $qq,
                    ':pagestart' => $start,
                    ':pagesize'  => $limit,
                ));
                $sql_where = '(payorder=:payorder OR id=:id OR input=:input)';
            } else {
                if (is_numeric($qq) && $qq < 10000) {
                    json_error("请输入正确的查询条件！");
                }
                $query_data = array_merge($query_data, array(
                    ':payorder'  => $qq,
                    ':input'     => $qq,
                    ':pagestart' => $start,
                    ':pagesize'  => $limit,
                ));
                $sql_where = '(payorder=:payorder OR input=:input)';
            }

            if ($status >= 0) {
                $sql_where .= ' AND `status`=:status';
                $query_data = array_merge($query_data, array(
                    ':status' => $status,
                ));
            }

            $query_data2 = $query_data;
            unset($query_data2[':pagestart']);
            unset($query_data2[':pagesize']);
            if ($conf['query_checkcookie'] == 1) {
                //userid缓存安全验证
                $query_sql = "SELECT * FROM cmy_orders WHERE `userid`=:uid AND {$sql_where} order by id desc limit :pagestart, :pagesize";
                $total     = $DB->count("SELECT count(*) FROM cmy_orders WHERE `userid`=:uid AND {$sql_where}", $query_data2);
            } else {
                $query_sql = "SELECT * FROM cmy_orders WHERE {$sql_where} order by id desc limit :pagestart, :pagesize";
                $total     = $DB->count("SELECT count(*) FROM cmy_orders WHERE {$sql_where}", $query_data2);
            }
        }

        try {
            $rows = $DB->select($query_sql, $query_data);
            if (!is_array($rows)) {
                throw new \Exception($DB->error());
            }
            $data  = array();
            $count = 0;
            if (is_array($rows)) {
                $count  = count($rows);
                $weburl = str_replace('user/', '', $weburl);
                foreach ($rows as $key => $res) {
                    $tool = $DB->get_row("SELECT * FROM cmy_tools where tid=:tid limit 1", array(':tid' => $res['tid']));
                    if ($conf['order_id_type'] == 1) {
                        $index = 'order_' . $res['payorder'];
                        $id    = $res['payorder'];
                    } else {
                        $index = 'order_' . $res['id'];
                        $id    = $res['id'];
                    }
                    $shopimg = $tool['shopimg'];
                    if ($shopimg) {
                        if (!preg_match('/http/i', $shopimg)) {
                            $shopimg = $weburl . $res['shopimg'];
                        }
                    } else {
                        $shopimg = @getClassImg($tool['cid']);
                        if ($shopimg && !preg_match('/http/i', $shopimg)) {
                            $shopimg = $weburl . $shopimg;
                        } elseif ($shopimg == '') {
                            $shopimg = $weburl . 'assets/img/Product/default.png';
                        }
                    }

                    $skey         = getOrderSkey($res, 'get');
                    $data[$index] = array('id' => $id, 'tid' => $res['tid'], 'input' => $res['input'], 'name' => is_array($tool) ? $tool['name'] : '该商品已被删除或不存在', 'value' => $res['value'], 'money' => $res['money'], 'addtime' => $res['addtime'], 'endtime' => $res['endtime'], 'result' => $res['result'], 'status' => $res['status'], 'is_curl' => $tool['is_curl'], 'skey' => $skey, 'payorder' => $res['payorder'], 'shopimg' => $shopimg);
                    if ($isLogin2 === 1 && $userrow['zid'] && $res['userid'] === $cookiesid) {
                        //将订单所属更新为当前已登陆用户
                        $DB->query("UPDATE `pre_orders` SET `userid`=:userid where id=:id", [':userid' => $userrow['zid'], ':id' => $res['id']]);
                    }
                }
                if ($page > 1 && $count == 0) {
                    exit('{"code":-1,"msg":"没有更多订单了"}');
                }
                $result = array("code" => 0, "msg" => "succ", "count" => $count, "total" => intval($total), "content" => $qq, "page" => $page, "isnext" => ($count == $limit ? true : false), "islast" => ($page > 1 ? true : false), "data" => $data, "islogin" => $isLogin2, "query_checkcookie" => $conf['query_checkcookie']);
            } else {
                $result = array("code" => -1, "msg" => "查询订单失败，" . $DB->error(), "content" => $qq, "page" => $page, "isnext" => false, "islast" => false, "data" => []);
            }
        } catch (\PDOException $e) {
            $result = array("code" => -1, "msg" => "查询订单失败，" . $e->getMessage(), "content" => $qq, "page" => $page, "isnext" => false, "islast" => false, "data" => []);
        }

        break;
    case 'order': //订单进度查询
        $id   = input('post.id', 1);
        $skey = input('post.skey', 1);
        if (empty($skey) || strlen($skey) !== 32) {
            exit('{"code":-1,"msg":"验证失败"}');
        }

        $row = $DB->get_row("SELECT * from `pre_orders` where `payorder`= ? OR `id`= ? limit 1", [$id, $id]);

        if (!is_array($row)) {
            exit('{"code":-1,"msg":"当前订单不存在！","id":"' . $id . '"}');
        }

        if (getOrderSkey($row) !== $skey) {
            exit('{"code":-2,"msg":"验证失败"}');
        }

        $tool        = $DB->get_row("SELECT * from `pre_tools` where tid= ? limit 1", [$row['tid']]);
        $list        = null;
        $list_result = null;
        if ($tool['is_curl'] == 2 && $row['djzt'] != 3) {
            $InfoControler = new \core\InfoControler();
            $shequ         = $DB->get_row("SELECT * from cmy_shequ where id= ? limit 1", array($tool['shequ']));
            $shequ["url"]  = shequ_url_parse($shequ);
            $query         = ['code' => -1, 'msg' => '该货源平台不支持查询'];
            if ($shequ['type'] == 4) {
                $list = $InfoControler->query_jiuliu($row, $shequ);
            } elseif ($shequ['type'] == 23) {
                $query = $InfoControler->query_chengzi($row, $shequ);
            } elseif ($shequ['type'] == 24) {
                $query = $InfoControler->query_guakebao($row, $shequ);
            } else {
                $query = $InfoControler->query_extend($row, $shequ);
            }

            if (is_array($query) && $query['code'] == 0) {
                $list = $query['data'];
                if (preg_match('/已完成|成功|已到账/', $list['order_state'])) {
                    if ($row['status'] != 1) {
                        $DB->query("UPDATE `pre_orders` set status=1 where id=:id", [':id' => $id]);
                    }
                    $row['status'] = 1;
                }
                $list_result = ['code' => 0, 'msg' => '查询成功'];
            } else {
                $list_result = $query;
            }
        }

        $input = !empty($tool['input']) ? $tool['input'] : '下单QQ';
        if ($tool['is_curl'] == 4) {
            $input = '联系方式';
        }

        $inputs  = explode('|', $tool['inputs']);
        $usetime = '';
        if ($conf['show_usetime'] == 1 && ($row['status'] == 2 || $row['status'] == 0)) {
            $usetime = time() - strtotime($row['addtime']);
        }

        $row2 = $DB->get_row("SELECT content,status from `pre_workorder` where orderid= ? limit 1", [$id]);
        if (!$row2) {
            $works = 0;
        } else {
            $works = count(explode('*', $row2['content']));
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

        if (!is_array($list)) {
            $count = $DB->count("SELECT count(*) FROM `pre_faka` WHERE `orderid`= ?", [$row['id']]);
            if ($count > 0) {
                if ($count >= 3) {
                    $kmdata = '<center><a href="./?mod=faka&id=' . $id . '&skey=' . $skey . '" target="_blank" class="btn btn-sm btn-primary">点此查看卡密</a></center>';
                } else {
                    $rs     = $DB->select("SELECT * FROM cmy_faka WHERE tid= ? AND orderid= ? ORDER BY kid ASC LIMIT ?", [$row['tid'], $row['id'], $count]);
                    $kmdata = '';
                    if (is_array($rs)) {
                        foreach ($rs as $key => $res) {
                            if ($res['pw']) {
                                $kmdata .= '卡号：' . $res['km'] . ' 密码：' . $res['pw'] . '<br/>';
                            } else {
                                $kmdata .= $res['km'] . '<br/>';
                            }
                            if (strlen($res['km'] . $res['pw']) > 80) {
                                $kmdata = '<center><a href="./?mod=faka&id=' . $id . '&skey=' . $skey . '" target="_blank" class="btn btn-sm btn-primary">点此查看卡密</a></center>';
                                break;
                            }
                        }
                    } else {
                        $kmdata = '<center>**失败，请联系客服处理！[' . $DB->error() . ']</center>';
                    }
                }
            }
        }

        $result = array('code' => 0, 'msg' => 'succ', 'payorder' => $row['payorder'], 'name' => $tool['name'], 'money' => $row['money'], 'date' => $row['addtime'], 'usetime' => $usetime, 'show_usetime' => ($conf['show_usetime'] > 0 ? 1 : 0), 'show_endtime' => ($conf['show_endtime'] > 0 ? 1 : 0), 'endtime' => $row['endtime'], 'inputs' => showInputs($row, $input, $inputs), 'list' => $list, 'list_result' => $list_result, 'kminfo' => $kmdata, 'show_desc' => ($conf['show_desc'] > 0 ? 1 : 0), 'alert' => $tool['alert'], 'desc' => $tool['desc'], 'status' => $row['status'], 'is_curl' => $tool['is_curl'], 'result' => $row['result'], 'expressInfo' => $expressInfo, 'complain' => $complain, 'works' => intval($works), 'workorder_open' => $conf['workorder_open'], 'isLogin2' => $isLogin2, 'islogin' => $isLogin2);

        break;
    case 'order_edit':
        $id   = input('get.id', 1);
        $skey = input('post.skey', 1);
        if (empty($skey) || strlen($skey) !== 32) {
            exit('{"code":-1,"msg":"验证失败"}');
        }

        if ($conf['order_id_type'] == 1) {
            $rows = $DB->get_row("SELECT * from `pre_orders` where `payorder`= ? limit 1", [$id]);
        } else {
            $rows = $DB->get_row("SELECT * from `pre_orders` where `id`= ? limit 1", [$id]);
        }

        if (!$rows) {
            exit('{"code":-1,"msg":"当前订单不存在！"}');
        }

        if ($rows['status'] != 0 && $rows['status'] != 3) {
            exit('{"code":-1,"msg":"该订单暂不支持修改！"}');
        }

        if (getOrderSkey($rows) !== $skey) {
            exit('{"code":-2,"msg":"验证失败"}');
        }

        $tool = $DB->get_row("SELECT * from `pre_tools` where tid='{$rows['tid']}' limit 1");
        $data = getInputsHtml($rows, $tool);
        if ($rows['inputattr']) {
            $data .= '<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname5">类型属性</div><input type="text" id="inputattr" value="' . $rows['inputattr'] . '" class="form-control" required/></div></div>';
        }

        $data .= '<input type="submit" id="save" onclick="saveOrder(' . $id . ',\'' . $skey . '\')" class="btn btn-primary btn-block" value="保存">';
        $result = array("code" => 0, "msg" => "succ", "data" => $data);

        break;
    case 'order_save':
        $id   = input('get.id', 1);
        $skey = input('post.skey', 1);
        if (empty($skey) || strlen($skey) !== 32) {
            exit('{"code":-1,"msg":"验证失败"}');
        }

        if ($conf['order_id_type'] == 1) {
            $rows = $DB->get_row("SELECT * from `pre_orders` where `payorder`= ? limit 1", [$id]);
        } else {
            $rows = $DB->get_row("SELECT * from `pre_orders` where `id`= ? limit 1", [$id]);
        }

        if (!$rows) {
            exit('{"code":-1,"msg":"当前订单不存在！"}');
        }

        if ($rows['status'] != 0 && $rows['status'] != 3) {
            exit('{"code":-1,"msg":"该订单暂不支持修改！"}');
        }

        if (getOrderSkey($rows) !== $skey) {
            exit('{"code":-2,"msg":"验证失败"}');
        }

        $value   = input('post.inputvalue');
        $value2  = input('post.inputvalue2');
        $value3  = input('post.inputvalue3');
        $value4  = input('post.inputvalue4');
        $value5  = input('post.inputvalue5');
        $bz      = '客户自助修改订单。修改时间：' . $date;
        $sqlData = [":input" => $value, ":input2" => $value2, ":input3" => $value3, ":input4" => $value4, ":input5" => $value5, ":bz" => $bz, ":id" => $id];
        if ($conf['order_id_type'] == 1) {
            $sql = "UPDATE `pre_orders` SET `input`=:input,`input2`=:input2,`input3`=:input3,`input4`=:input4,`input5`=:input5,`bz`=:bz where `payorder`=:id";
        } else {
            $sql = "UPDATE `pre_orders` SET `input`=:input,`input2`=:input2,`input3`=:input3,`input4`=:input4,`input5`=:input5,`bz`=:bz where `id`=:id";
        }
        if ($DB->query($sql, $sqlData)) {
            $result = array("code" => 0, "msg" => "修改订单成功！");
        } else {
            $result = array("code" => -1, "msg" => "修改订单失败，" . $DB->error());
        }

    case 'workInfo':
        if ($isLogin2 != 1) {
            exit('{"code":-2,"msg":"需要登录才能操作！"}');
        }

        $orderid = input('orderid', 1);

        if ($conf['order_id_type'] == 1) {
            $res = $DB->get_row("SELECT * from `pre_orders` where `payorder`= ? limit 1", [$idorderid]);
        } else {
            $res = $DB->get_row("SELECT * from `pre_orders` where `id`= ? limit 1", [$orderid]);
        }

        if ($res) {

            if ($res['userid'] != $userrow['zid'] && $res['zid'] != $userrow['zid']) {
                exit('{"code":-2,"msg":"非自己订单, 无权限"}');
            }

            $row = $DB->get_row("SELECT name,content,addtime,endtime,status from cmy_workorder where orderid= ? limit 1", array($orderid));
            if ($row) {
                $data = array();
                if (is_numeric($res['input']) && preg_match('/^[1-9]{1}[0-9]{4,11}$/', $res['input'])) {
                    $myimg = 'http://q4.qlogo.cn/headimg_dl?dst_uin=' . $res['input'] . '&spec=100';
                } else {
                    $myimg = 'http://q4.qlogo.cn/headimg_dl?dst_uin=' . $userrow['qq'] . '&spec=100';
                }
                $result['code'] = -1;
                $data[]         = array('index' => 1, 'addtime' => $row['addtime'], 'isadmin' => 0, 'myimg' => $myimg, 'content' => $row['name']);

                $contents = explode('*', $row['content']);
                $works    = count($contents);
                if ($works > 0) {
                    for ($i = 1; $i < $works; $i++) {
                        $content = explode('^', $contents[$i]);
                        if (count($content) == 3) {
                            $data[] = array('index' => ($i + 1), 'addtime' => $content[1], 'isadmin' => ($content[0] == 1 ? 1 : 0), 'myimg' => $myimg, 'content' => $content[2]);
                        }
                    }
                }

                $result['code']   = 0;
                $result['msg']    = 'succ';
                $result['works']  = $works;
                $result['data']   = $data;
                $result['status'] = $row['status'];
                if ($row['status'] == 1) {
                    $result['ok']   = 1;
                    $result['info'] = "本次售后已于" . $row['endtime'] . "处理完毕";
                }
            } else {
                $result = array("code" => -1, "works" => 0, "msg" => "未找到相关售后工单记录");
            }
        } else {
            $result = array("code" => -1, "works" => 0, "msg" => "订单不存在");
        }

        break;
    case 'orderWork':
        if ($isLogin2 != 1) {
            exit('{"code":-2,"msg":"需要登录才能操作！"}');
        }

        $orderid = input('orderid', 1);

        if ($conf['order_id_type'] == 1) {
            $res = $DB->get_row("SELECT * from `pre_orders` where `payorder`= ? limit 1", [$idorderid]);
        } else {
            $res = $DB->get_row("SELECT * from `pre_orders` where `id`= ? limit 1", [$orderid]);
        }

        if ($res) {
            if ($res['userid'] != $userrow['zid'] && $res['zid'] != $userrow['zid']) {
                exit('{"code":-2,"msg":"非自己订单, 无权限"}');
            }

            $tool = $DB->get_row("SELECT * from `pre_tools` where `tid`= ? limit 1", [$res['tid']]);
            $sid  = 1;
            if ($tool) {
                $sid = $tool['zid'];
            }

            $name = input('post.content', 1);
            $qq   = input('post.qq', 1);
            $type = (int) $_POST['type'];
            $row  = $DB->get_row("SELECT `status` from cmy_workorder where orderid= ? limit 1", array($orderid));
            if ($row && $row['status'] != 1) {
                $result = array("code" => -3, "msg" => "历史已有售后工单且未完结");
            } else {
                $sql  = "INSERT into `pre_workorder` (`zid`,`type`,`orderid`,`qq`,`name`,`addtime`,`status`) values (:zid, :type, :orderid, :qq, :name, :addtime, :status)";
                $data = [
                    ':zid'     => $sitezid,
                    ':sid'     => $sid,
                    ':type'    => $type,
                    ':orderid' => $orderid,
                    ':qq'      => $qq,
                    ':name'    => $name,
                    ':addtime' => $date,
                    ':status'  => 0,
                ];
                $id = $DB->insert($sql, $data);
                if ($id) {
                    $ts = '无需发送给供货商';
                    if ($sid > 1) {
                        $user = $DB->get_row("SELECT * from cmy_master where `zid`='{$sid}' limit 1");
                        if ($user) {
                            // 扣除提成
                            // if ($sid > 1 && conf('master_tousu_rmb_remove') == 1) {
                            //     \core\Db::name('master')->where(['zd' => $sid])->update([
                            //         'income' => round($user['income'] - $res['price1'], 2),
                            //     ]);
                            //     addMasterPointLogs($sid, $res['price1'], '扣除', '该订单收到投诉提成扣除, 处理好投诉后可联系客服重新发放提成', $orderid);
                            // }

                            // 发送通知
                            if (conf('master_notify_workorder_email') == 1 && validateData($user['email'], 'email')) {
                                try {
                                    $ems = new \core\Ems();
                                    if ($ems) {
                                        $send = $ems->sendEmail($user['email'], '您有新的工单新增, 请及时处理', '<b>商品ID: </b>' . $res['tid'] . '<br/><b>工单ID:</b> ' . $workid . '<br/><b>问题类型：</b>' . display_type($type) . '<br/><b>订单金额:</b> ' . $res['money'] . '元<br/><b>订单份数: </b>' . $res['value'] . '<br/><b>订单时间: </b>' . $res['addtime'] . '<br/>');

                                        if ($send === true) {
                                            $ts = '发送通知给供货商' . $user['user'] . '[' . $sid . ']成功';
                                        } else {
                                            $ts = '发送通知给供货商' . $user['user'] . '[' . $sid . ']失败, ' . $send;
                                        }
                                    }
                                } catch (\Throwable $th) {
                                    $ts = '发送通知给供货商' . $user['user'] . '[' . $sid . ']失败, 发送执行错误=> ' . $th->getMessage();
                                }
                            }
                        } else {
                            if (!$user) {
                                $ts = '发送通知给供货商失败, 供货商不存在 ';
                            } else {
                                $ts = '发送通知给供货商失败, 供货商邮箱绑定不正确： ' . $user['email'];
                            }
                        }
                        \core\Db::name('workorder')->where(['id' => $workid])->update(['ts' => $ts]);
                    } else {
                        if (conf('work_notice_email') == 1 && validateData($conf['adm_email'], 'email')) {
                            $ems = new \core\Ems();
                            if ($ems) {
                                $send = $ems->sendEmail($conf['adm_email'], '工单提醒！有来自' . $sitezid . '的新工单, 请及时处理', '<b>订单ID: </b>' . $orderid . '<br/><b>商品ID: </b>' . $res['tid'] . '<br/><b>工单ID:</b> ' . $workid . '<br/><b>问题类型：</b>' . display_type($type) . '<br/><b>订单金额:</b> ' . $res['money'] . '元<br/><b>订单份数: </b>' . $res['value'] . '<br/><b>订单时间: </b>' . $res['addtime'] . '<br/>');

                                if ($send === true) {
                                    $ts = '发送通知给供货商' . $user['user'] . '[' . $sid . ']成功';
                                } else {
                                    $ts = '发送通知给供货商' . $user['user'] . '[' . $sid . ']失败, ' . $send;
                                }
                            }
                        }
                    }
                    $result = array("code" => 0, "msg" => "操作成功");
                } else {
                    $result = array("code" => -1, "msg" => "提交售后工单失败，联系客服" . $conf['zz_zzqq'] . "处理！" . $DB->error());
                }
            }
        } else {
            $result = array("code" => -1, "msg" => "订单不存在");
        }

        break;
    case 'workBack':
        if ($isLogin2 != 1) {
            exit('{"code":-2,"msg":"需要登录才能操作！"}');
        }

        $orderid = input('post.orderid', 1);

        if ($conf['order_id_type'] == 1) {
            $res = $DB->get_row("SELECT * from `pre_orders` where `payorder`= ? limit 1", [$idorderid]);
        } else {
            $res = $DB->get_row("SELECT * from `pre_orders` where `id`= ? limit 1", [$orderid]);
        }

        if ($res) {
            if ($res['userid'] != $userrow['zid'] && $res['zid'] != $userrow['zid']) {
                exit('{"code":-2,"msg":"非自己订单, 无权限"}');
            }

            $tool = $DB->get_row("SELECT * from `pre_tools` where `tid`= ? limit 1", [$res['tid']]);
            $sid  = 1;
            if ($tool) {
                $sid = $tool['zid'];
            }

            $content = trim(daddslashes(strip_tags($_POST['content'])));
            $row     = $DB->get_row("SELECT content,status,id from cmy_workorder where orderid= ? limit 1", array($orderid));
            if (!$row) {
                $result = array("code" => -1, "msg" => "该售后工单不存在！");
            } elseif ($row['status'] == 1) {
                $result = array("code" => -1, "msg" => "该订单售后已处理完成");
            } /*elseif ($row['status'] == 0) {
            $result = array("code" => -1, "msg" => "请等待客服回复后再操作");
            } */else {
                $content = addslashes($row['content']) . '*0^' . $date . '^' . $content;
                if ($DB->query("UPDATE `pre_workorder` set `content`= ?,`status`='0' where id= ?", array($content, $row['id']))) {

                    // 发送通知
                    if ($sid > 1 && conf('master_notify_workorder_email') == 1) {
                        $user = $DB->get_row("SELECT * from cmy_master where `zid`='{$sid}' limit 1");
                        if ($user && validateData($user['email'], 'email')) {
                            try {
                                $ems = new \core\Ems();
                                if ($ems) {
                                    $ems->sendEmail($user['email'], '您有新的工单回复, 请及时处理', '<b>商品ID: </b>' . $res['tid'] . '<br/><b>工单ID:</b> ' . $row['id'] . '<br/><b>问题类型：</b>' . display_type($type) . '<br/><b>订单金额:</b> ' . $res['money'] . '元<br/><b>订单份数: </b>' . $res['value'] . '<br/><b>订单时间: </b>' . $res['addtime'] . '<br/>');
                                }
                            } catch (\Throwable $th) {
                                //throw $th;
                            }
                        }
                    } else {
                        if (conf('work_notice_email') == 1 && validateData($conf['adm_email'], 'email')) {
                            $ems = new \core\Ems();
                            if ($ems) {
                                $send = $ems->sendEmail($conf['adm_email'], '工单提醒！有来自' . $sitezid . '的工单回复, 请及时处理', '<b>订单ID: </b>' . $orderid . '<br/><b>商品ID: </b>' . $res['tid'] . '<br/><b>工单ID:</b> ' . $workid . '<br/><b>问题类型：</b>' . display_type($type) . '<br/><b>订单金额:</b> ' . $res['money'] . '元<br/><b>订单份数: </b>' . $res['value'] . '<br/><b>订单时间: </b>' . $res['addtime'] . '<br/>');

                                if ($send === true) {
                                    $ts = '发送通知给供货商' . $user['user'] . '[' . $sid . ']成功';
                                } else {
                                    $ts = '发送通知给供货商' . $user['user'] . '[' . $sid . ']失败, ' . $send;
                                }
                            }
                        }
                    }

                    saveSetting('work_new_reply', '1');
                    $CACHE->clear();
                    $result = array("code" => 0, "msg" => "操作成功", "content" => $content);
                } else {
                    $result = array("code" => -1, "msg" => "回复售后工单失败，联系客服" . $conf['zz_zzqq'] . "处理！" . $DB->error());
                }
            }
        } else {
            $result = array("code" => -1, "msg" => "订单不存在");
        }

        break;

    case 'changepwd':
        $orderid = (int) input('id', 1);
        if (md5($orderid . SYS_KEY . $orderid) !== $_POST['skey']) {
            exit('{"code":-1,"msg":"验证失败"}');
        }

        $pwd = trim(strip_tags(getParams('pwd')));
        if (strlen($pwd) < 5) {
            exit('{"code":-1,"msg":"请输入正确的密码"}');
        }

        $row = $DB->get_row("SELECT * from cmy_orders where id=:orderid limit 1", [':orderid' => $orderid]);
        if ($row) {
            $bz = "客户自助提交新密码：" . $date;
            if ($DB->query("UPDATE `pre_orders` set `input2` = ?,`bz` = ? where `id`= ?", [$pwd, $bz, $orderid])) {
                $result = array("code" => 0, "msg" => "已成功修改密码");
            } else {
                $result = array("code" => 0, "msg" => "修改密码失败");
            }
        } else {
            $result = array("code" => -1, "msg" => "订单不存在");
        }

        break;
    case 'fill':
        $orderid = (int) input('orderid', 1);
        if (md5($orderid . SYS_KEY . $orderid) !== $_POST['skey']) {
            exit('{"code":-1,"msg":"验证失败"}');
        }

        $row = $DB->get_row("SELECT * from cmy_orders where id=:orderid limit 1", [':orderid' => $orderid]);
        if ($row) {
            if ($row['status'] == 3) {
                $DB->query("UPDATE `pre_orders` set `status` ='0',result=NULL where `id`=:orderid", [':orderid' => $orderid]);
                $result = array("code" => 0, "msg" => "已成功补交订单");
            } else {
                $result = array("code" => 0, "msg" => "该订单不符合补交条件");
            }
        } else {
            $result = array("code" => -1, "msg" => "订单不存在");
        }

        break;
    case 'checklogin':
        if ($isLogin2 == 1) {
            exit('{"code":1}');
        } else {
            exit('{"code":0}');
        }

        break;
    case 'lqq':
        $qq = trim(getParams('qq'));
        if (empty($qq) || empty(session_get()) || $_POST['salt'] != session_get()) {
            exit('{"code":-5,"msg":"非法请求"}');
        }

        get_curl($conf['lqqapi'] . $qq);
        $result = array("code" => 0, "msg" => "succ");

        break;
    case 'getshuoshuo':
        $uin      = trim(daddslashes($_GET['uin']));
        $page     = intval($_GET['page']);
        $hashsalt = isset($_GET['hashsalt']) ? $_GET['hashsalt'] : null;
        if ($conf['verify_open'] == 1 && (empty($hashsalt) || $hashsalt != session_get())) {
            exit('{"code":-1,"msg":"验证失败，请刷新页面再操作！","hashsalt":"' . session_get() . '"}');
        }
        if (empty($uin)) {
            exit('{"code":-5,"msg":"QQ号不能为空"}');
        }

        $result = getshuoshuo($uin, $page);

        break;
    case 'getClientSession':
        $result = array("code" => 0, "hashsalt" => session_get());

        break;
    case 'getkuaishou':
        $url = input('post.url');
        if (empty($url)) {
            exit('{"code":-5,"msg":"url不能为空"}');
        }

        $result = getkuaishou($url);

        break;
    case 'getpipixia':
        $url = input('post.url');
        if (empty($url)) {
            exit('{"code":-5,"msg":"url不能为空"}');
        }

        if (function_exists('getpipixia')) {
            $result = getpipixia($url);
        } else {
            $result = array('code' => -1, 'msg' => '网站版本较低获取失败，请联系平台站长升级网站版本');
        }

        break;
    case 'getbilibili':
        $url = input('post.url');
        if (empty($url)) {
            exit('{"code":-5,"msg":"url不能为空"}');
        }

        $result = getbilibili($url);

    case 'getdouyin':
        $url = input('post.url');
        if (empty($url)) {
            exit('{"code":-5,"msg":"url不能为空"}');
        }

        $result = getdouyin($url);

        break;
    case 'getDouyinUserId':
        $url = input('post.url');
        if (empty($url)) {
            exit('{"code":-5,"msg":"url不能为空"}');
        }
        $result = getDouyinUserId($url);

        break;
    case 'gethuoshan':
        $url = input('post.url');
        if (empty($url)) {
            exit('{"code":-5,"msg":"url不能为空"}');
        }

        $result = gethuoshan($url);

        break;
    case 'getxiaohongshu':
        $url = input('post.url');
        if (empty($url)) {
            exit('{"code":-5,"msg":"url不能为空"}');
        }

        $result = getxiaohongshu($url);

        break;
    case 'getshareurl':
        $url = input('post.url');
        if (empty($url)) {
            exit('{"code":-5,"msg":"要获取url的链接不能为空"}');
        }
        $hashsalt = input('hashsalt') ? input('hashsalt', 1) : null;
        if ($conf['verify_open'] == 1) {
            if (empty($hashsalt) || $hashsalt != session_get()) {
                json_error('验证失败，请刷新页面再操作！', ['hashsalt' => session_get()]);
            }
        }

        $result = getshareurl($url);

        break;
    case 'getshareid':
        $url = input('post.url');
        if (empty($url)) {
            exit('{"code":-5,"msg":"要获取ID的链接不能为空"}');
        }
        $hashsalt = input('hashsalt') ? input('hashsalt', 1) : null;
        if ($conf['verify_open'] == 1) {
            if (empty($hashsalt) || $hashsalt != session_get()) {
                json_error('验证失败，请刷新页面再操作！', ['hashsalt' => session_get()]);
            }
        }

        $result = getshareid($url);

        break;
    case 'getzpid':
        $url = input('post.url');
        if (empty($url)) {
            exit('{"code":-5,"msg":"要获取ID的链接不能为空"}');
        }
        $hashsalt = input('hashsalt') ? input('hashsalt', 1) : null;
        if ($conf['verify_open'] == 1) {
            if (empty($hashsalt) || $hashsalt != session_get()) {
                json_error('验证失败，请刷新页面再操作！', ['hashsalt' => session_get()]);
            }
        }
        $result = getzpid($url);

        break;
    case 'getQqVip':
        $uin = trim(daddslashes($_GET['uin']));
        if (empty($uin)) {
            exit('{"code":-5,"msg":"QQ号不能为空"}');
        }

        $result = getQqVip($uin);
        exit($result);
        break;

    case 'gift_start':
        $action = trim(daddslashes($_GET['action']));
        if ($action == '') {
            if (!$conf['gift_open']) {
                exit('{"code":-2,"msg":"网站未开启抽奖功能"}');
            }

            if (!$conf['cjcishu']) {
                exit('{"code":-2,"msg":"站长未设置每日抽奖次数！"}');
            }

            $thtime  = date("Y-m-d") . ' 00:00:00';
            $cjcount = $DB->count("SELECT count(*) from cmy_giftlog where (userid= ? or ip= ?) and addtime>= ?", array($cookiesid, $clientip, $thtime));
            if ($cjcount >= $conf['cjcishu']) {
                exit('{"code":-1,"msg":"' . $cjmsg . '"}');
            }
            $query = $DB->query("SELECT * from cmy_gift where ok=0");
            while ($row = $DB->fetch($query)) {
                $arr[] = array("id" => $row["id"], "tid" => $row["tid"], "name" => $row["name"]);
            }
            $rateall = $DB->count("SELECT sum(rate) from cmy_gift where ok=0");
            if ($rateall < 100) {
                $arr[] = array("id" => 0, "tid" => 0, "name" => '未中奖');
            }

            if (!$arr) {
                exit('{"code":-2,"msg":"站长未设置奖品"}');
            }
            $result = array("code" => 0, "data" => $arr);
        } else {
            $token = md5($_GET['r'] . SYS_KEY . $_GET['r']);
            exit('{"code":0,"token":"' . $token . '"}');
        }
        break;
    case 'gift_stop':
        if (!$conf['gift_open']) {
            exit('{"code":-2,"msg":"网站未开启抽奖功能"}');
        }

        if (!$conf['cjcishu']) {
            exit('{"code":-2,"msg":"站长未设置每日抽奖次数！"}');
        }

        $hashsalt = isset($_POST['hashsalt']) ? $_POST['hashsalt'] : null;
        $token    = isset($_POST['token']) ? $_POST['token'] : null;
        if ($conf['verify_open'] == 1 && (empty($hashsalt) || $hashsalt != session_get())) {
            exit('{"code":-1,"msg":"验证失败，请刷新页面再操作！"}');
        }
        if (md5($_GET['r'] . SYS_KEY . $_GET['r']) !== $token) {
            exit('{"code":-1,"msg":"请勿重复提交请求"}');
        }

        $thtime  = date("Y-m-d") . ' 00:00:00';
        $cjcount = $DB->count("SELECT count(*) from cmy_giftlog where (userid= ? or ip= ?) and addtime>= ?", array($cookiesid, $clientip, $thtime));
        if ($cjcount >= $conf['cjcishu']) {
            exit('{"code":-1,"msg":"' . $cjmsg . '"}');
        }
        $prize_arr = array();
        $query     = $DB->query("SELECT * from cmy_gift where ok=0");
        $i         = 1;
        $bre       = $DB->count("SELECT count(*) from cmy_gift where ok=0");
        while ($i <= $bre) {
            while ($row = $DB->fetch($query)) {
                $prize_arr[] = array("id" => ($i = $i + 1) - 1, "gid" => $row["id"], "tid" => $row["tid"], "name" => $row["name"], "rate" => $row["rate"], "not" => 0);
            }
        }

        if (!$prize_arr) {
            exit('{"code":-2,"msg":"站长未设置奖品"}');
        }
        $rateall = $DB->count("SELECT sum(rate) from cmy_gift where ok=0");
        if ($rateall < 100) {
            $prize_arr[] = array("id" => ($i = $i + 1) - 1, "gid" => 0, "tid" => 0, "name" => '未中奖', "rate" => 100 - $rateall, "not" => 1);
        }

        foreach ($prize_arr as $key => $val) {
            $arr[$val["id"]] = $val["rate"];
        }
        $prize_id     = get_rand($arr);
        $data['rate'] = $prize_arr[$prize_id - 1]['rate'];
        $data['id']   = $prize_arr[$prize_id - 1]['id'];
        $data['gid']  = $prize_arr[$prize_id - 1]['gid'];
        $data['name'] = $prize_arr[$prize_id - 1]['name'];
        $data['tid']  = $prize_arr[$prize_id - 1]['tid'];
        $data['not']  = $prize_arr[$prize_id - 1]['not'];

        $gift_id = $DB->insert("INSERT INTO `pre_giftlog`(`zid`,`tid`,`gid`,`userid`,`ip`,`addtime`,`status`) VALUES ( ?, ?, ?, ?, ?, ?,'0')", array($sitezid, $data['tid'], $data['gid'], $cookiesid, $clientip, $date));
        if ($gift_id) {
            if ($data['not'] == 1) {
                exit('{"code":-1,"msg":"未中奖，谢谢参与！"}');
            }
            $tool                 = $DB->get_row("SELECT * from cmy_tools where tid= ? limit 1", array($data['tid']));
            $_SESSION['gift_tid'] = $data['tid'];
            $_SESSION['gift_id']  = $gift_id;
            //session_set('', 0);

            $class = $DB->get_row("SELECT upcid FROM cmy_class WHERE active=1 and cid= ? order by sort asc", array($tool['cid']));
            if ((int) $class['upcid'] > 0) {
                $result = array("code" => 0, "msg" => "succ", "cid" => $class['upcid'], "sub_cid" => $tool['cid'], "tid" => $data['tid'], "name" => $data['name']);
            } else {
                $result = array("code" => 0, "msg" => "succ", "cid" => $tool['cid'], "tid" => $data['tid'], "name" => $data['name']);
            }
        } else {
            exit('{"code":-3,"msg":"' . $DB->error() . '"}');
        }
        //UPDATE `pre_site` SET `anounce`='',`modal`='',`bottom`='',`alert`='',`skimg`='' WHERE 1
        break;
    case 'inviteurl':
        $qq       = getParams('userqq');
        $hashsalt = input('hashsalt', 1);
        if (!preg_match('/^[1-9][0-9]{4,11}$/i', $qq)) {
            exit('{"code":0,"msg":"QQ号码格式不正确"}');
        }
        $key    = random(6);
        $qqrow  = $DB->get_row("SELECT * FROM `pre_invite` WHERE `qq`= ? LIMIT 1", array($qq));
        $result = array();
        if ($qqrow) {
            $code = 2;
            $url  = $siteurl . '?i=' . $qqrow['key'];
        } else {
            $iprow = $DB->get_row("SELECT * FROM `pre_invite` WHERE `ip`= ? LIMIT 1", array($clientip));
            if ($iprow) {
                $code = 2;
                $url  = $siteurl . '?i=' . $iprow['key'];
            } else {
                if ($conf['verify_open'] == 1 && (empty($hashsalt) || $hashsalt != session_get())) {
                    exit('{"code":-1,"msg":"验证失败，请刷新页面再操作！"}');
                }
                if ($DB->query("INSERT INTO `pre_invite` (`qq`,`key`,`ip`,`date`) VALUES ( ?, ?, ?, ?)", array($qq, $key, $clientip, $date))) {
                    //session_set('', 0);
                    $code = 1;
                    $url  = $siteurl . '?i=' . $key;
                } else {
                    exit('{"code":-1,"msg":"' . $DB->error() . '"}');
                }
            }
        }
        if ($conf['fanghong_url']) {
            $url = getDwzUrl($url);
        }

        $result = array('code' => $code, 'msg' => 'succ', 'url' => $url);

        break;
    case 'cart_info':
        $cart_count = 0;
        if ($conf['shoppingcart'] == 1) {
            if ($isLogin2 == 1) {
                $cart_count = $DB->count("SELECT count(*) from cmy_cart WHERE userid= ? AND status=0", array($userrow['zid']));
            } else {
                $cart_count = $DB->count("SELECT count(*) from cmy_cart WHERE userid= ? AND status=0", array($cookiesid));
            }
        }
        $result = array('code' => 0, 'msg' => 'succ', 'count' => $cart_count);

        break;
    case 'cart_batch':
        $hashsalt = isset($_POST['hashsalt']) ? input('post.hashsalt', 1) : null;
        if ($conf['verify_open'] == 1 && (empty($hashsalt) || $hashsalt != session_get())) {
            exit('{"code":-1,"msg":"验证失败，请刷新页面再操作！"}');
        }
        $tid      = intval(input('post.tid', 1));
        $data     = input('post.data', 1);
        $stock_id = intval(input('post.stock_id', 1));
        $tool     = $DB->get_row("SELECT * from cmy_tools where tid= ? limit 1", array($tid));
        $count    = count($data);
        if (!is_array($tool)) {
            $result = [
                'code' => -1,
                'msg'  => '该商品不存在！',
            ];
        } elseif ($count == 0) {
            $result = [
                'code' => -1,
                'msg'  => '请确保批量下单数据至少有一行！',
            ];
        } elseif ($tool['active'] == 0 || $tool['active'] != 1) {
            $result = [
                'code' => -1,
                'msg'  => '当前商品维护中，停止下单！',
            ];
        } elseif ($tool['stock_open'] == 1 && $tool['stock'] < $count) {
            $result = [
                'code' => -1,
                'msg'  => '当前商品库存不足，请晚点再来',
            ];
        } elseif (checkBlockCity($tool['cid']) === true) {
            $result = [
                'code' => -1,
                'msg'  => '当前商品维护中，禁止下单！',
            ];
        } elseif ($tool['multi'] == 1 && $tool['min'] > 0 && $count < $tool['min']) {
            $result = [
                'code' => -1,
                'msg'  => '当前商品最小下单份数为' . $tool['min'],
            ];
        } elseif ($tool['multi'] == 1 && $tool['max'] > 0 && $count > $tool['max']) {
            $result = [
                'code' => -1,
                'msg'  => '当前商品最大下单份数为' . $tool['max'],
            ];
        } else {
            if (isset($stock_id) && $stock_id > 0) {
                //规格价格处理
                $stock_row = $DB->get_row("SELECT * from cmy_stock where id=:stock_id limit 1", [':stock_id' => $stock_id]);
                if ($stock_row) {
                    if ($stock_row['stock'] > 0) {
                        $tool['price1'] = $stock_row['price1'];
                        $tool['prid']   = $stock_row['prid'];
                    } elseif ($stock_row['stock'] < $num) {
                        exit('{"code":-1,"msg":"您所选版本商品库存不足' . $num . '个，请重新选购或联系客服咨询下单！"}');
                    } else {
                        exit('{"code":-1,"msg":"您所选版本商品库存已售罄，请重新选购或联系客服咨询下单！"}');
                    }
                } else {
                    exit('{"code":-1,"msg":"您所选版本商品已下架，请刷新页面重新选购！"}');
                }
            }
            $price_obj->setToolInfo($tool['tid'], $tool);

            if ($isLogin2 == 1) {
                $price = $price_obj->getBuyPrice($tool['tid']);
            } else {
                if ($is_fenzhan == true && $price_obj->getToolDel($tool['tid']) == 1) {
                    exit('{"code":-1,"msg":"该商品已下架！"}');
                }
                $price = $price_obj->getToolPrice($tool['tid']);
            }
            $num_count  = 0;
            $ok         = 0;
            $no         = 0;
            $error      = '';
            $need_count = 0;
            foreach ($data as $key => $item) {
                $input = implode('|', $item['data']);
                $num   = intval($item['num']);
                if ($num < 1) {
                    $num = 1;
                }
                $num_count += $num;

                if ($tool['multi'] == 1 && $tool['max'] > 0 && $num_count > $tool['max']) {
                    exit('{"code":-1,"msg":"当前商品最大下单份数为' . $tool['max'] . '"}');
                }

                $need = sprintf('%.2f', floatval($price * $num));
                $need_count += $need;
                $sqlData = [
                    ':userid'    => $isLogin2 === 1 ? $userrow['zid'] : $cookiesid,
                    ':zid'       => $sitezid,
                    ':tid'       => $tid,
                    ':input'     => $input,
                    ':inputattr' => '',
                    ':num'       => $num,
                    ':amount'    => $need,
                    ':stock_id'  => $stock_id,
                    ':addtime'   => $date,
                ];
                $sql = "INSERT INTO `pre_cart` (`userid`,`zid`,`tid`,`input`,`inputattr`,`num`,`money`,`stock_id`,`addtime`,`status`) values (:userid, :zid, :tid, :input, :inputattr, :num, :amount, :stock_id, :addtime, '0')";
                if ($DB->query($sql, $sqlData)) {
                    $ok++;
                } else {
                    $no++;
                    $error .= "<br/>" . $DB->error();
                }
            }

            if ($isLogin2 == 1) {
                $cookiesid = $userrow['zid'];
            }

            if ($ok > 0) {
                $cart_count = $DB->count("SELECT count(*) from cmy_cart WHERE userid='{$cookiesid}' AND status=0");
                $result     = [
                    'code'       => 0,
                    'msg'        => '批量添加到购物车成功，共' . $count . '条，成功' . $ok . '条！',
                    'need'       => round($need_count, 2),
                    'cart_count' => $cart_count,
                ];
            } else {
                $result = [
                    'code' => -1,
                    'msg'  => '共' . $num . '条订单数据，添加到购物车失败，' . $error,
                ];
            }
        }

        break;
    case 'cart_buy':
        $shop_ids_str = input('post.shop_id', 1);
        if (is_array($shop_ids_str)) {
            $shop_ids = $shop_ids_str;
        } else {
            $shop_ids = explode("|", $shop_ids_str);
        }

        $hashsalt = isset($_POST['hashsalt']) ? input('post.hashsalt', 1) : null;
        if ($conf['verify_open'] == 1 && (empty($hashsalt) || $hashsalt != session_get())) {
            exit('{"code":-1,"msg":"验证失败，请刷新页面再操作！"}');
        }

        $allmoney = 0;
        if (count($shop_ids) < 1) {
            exit('{"code":-1,"msg":"您未在购物车添加任何商品"}');
        }

        $ids        = array();
        $alipay_api = 0;
        $wxpay_api  = 0;
        $qqpay_api  = 0;
        $pay_rmb    = 0;
        $cookiesid  = $isLogin2 == 1 ? $userrow['zid'] : $cookiesid;
        foreach ($shop_ids as $shop_id) {
            if (preg_match('/^[\d]+$/', $shop_id)) {
                $cart_item = $DB->get_row("SELECT * FROM `pre_cart` WHERE `id`= ? LIMIT 1", array($shop_id));
                if (!$cart_item) {
                    exit('{"code":-1,"msg":"购物车某订单不存在或已失效，请刷新重新提交！"}');
                } elseif (!$cart_item['tid']) {
                    exit('{"code":-1,"msg":"购物车某订单商品已失效，请刷新重新提交！"}');
                }

                $tool_item = $DB->get_row("SELECT * FROM `pre_tools` WHERE `tid`= ? LIMIT 1", array($cart_item['tid']));

                if (!$tool_item) {
                    exit('{"code":-1,"msg":"购物车某订单商品已失效，请刷新重新提交！"}');
                }

                if ($cart_item['userid'] != $cookiesid || $cart_item['status'] > 1) {
                    exit('{"code":-1,"msg":"商品权限校验失败"}');
                }

                if ($cart_item['money'] == '0' || !preg_match('/^[0-9.]+$/', $cart_item['money'])) {
                    exit('{"code":-1,"msg":"商品金额不合法"}');
                }

                $ids[] = intval($shop_id);
                $allmoney += floatval($cart_item['money']);
                $alipay_api += $conf['alipay_api'] > 0 ? $tool_item['pay_alipay'] : 0;
                $wxpay_api += $conf['wxpay_api'] > 0 ? $tool_item['pay_wxpay'] : 0;
                $qqpay_api += $conf['qqpay_api'] > 0 ? $tool_item['pay_qqpay'] : 0;
                $pay_rmb += $tool_item['pay_rmb'] == 1 ? 1 : 0;
            }
        }
        $count    = count($shop_ids);
        $row      = $DB->get_row("SELECT name from cmy_tools where tid= ? limit 1", array($cart_item['tid']));
        $toolname = $row['name'] . '等多件';

        $input    = implode('|', $ids);
        $trade_no = date("YmdHis") . rand(111, 999);
        $data     = [
            ':trade_no' => $trade_no,
            ':tid'      => '-3',
            ':zid'      => $sitezid,
            ':input'    => $input,
            ':num'      => count($ids),
            ':name'     => $toolname,
            ':money'    => $allmoney,
            ':ip'       => $clientip,
            ':userid'   => $cookiesid,
            ':inviteid' => $invite_id,
            ':siteurl'  => $siteurl,
            ':addtime'  => $date,
            ':status'   => '0',
        ];
        $sql = "INSERT into `pre_pay` (`trade_no`,`tid`,`zid`,`input`,`num`,`name`,`money`,`ip`,`userid`,`inviteid`,`siteurl`,`addtime`,`status`) values (:trade_no,:tid,:zid,:input,:num,:name,:money,:ip,:userid,:inviteid,:siteurl,:addtime,:status)";
        if ($DB->exec($sql, $data)) {
            //session_set('', 0);
            // if ($conf['forcermb'] == 1) {
            //     $conf['alipay_api'] = 0;
            //     $conf['wxpay_api']  = 0;
            //     $conf['qqpay_api']  = 0;
            // }
            $pay['pay_alipay'] = $alipay_api == $count ? 1 : 0;
            $pay['pay_wxpay']  = $wxpay_api == $count ? 1 : 0;
            $pay['pay_qqpay']  = $qqpay_api == $count ? 1 : 0;
            $pay['pay_rmb']    = $pay_rmb > 0 ? 1 : 0;
            $result            = [
                'code'       => 0,
                'msg'        => '提交订单成功！',
                'trade_no'   => $trade_no,
                'need'       => round($allmoney, 2),
                'pay_alipay' => $pay['pay_alipay'],
                'pay_wxpay'  => $pay['pay_wxpay'],
                'pay_qqpay'  => $pay['pay_qqpay'],
                'pay_rmb'    => $pay['pay_rmb'],
                'user_rmb'   => $isLogin2 ? $userrow['money'] : '0',
                'isLogin2'   => intval($isLogin2),
                'ids'        => $input,
                'trade_no'   => $trade_no,
                'name'       => $toolname,
            ];
        } else {
            $result = [
                'code' => -1,
                'msg'  => '提交订单失败，' . $DB->error(),
            ];
        }

        break;
    case 'cart_cancel':
        $orderid   = isset($_POST['orderid']) ? daddslashes((int) $_POST['orderid']) : exit('{"code":-1,"msg":"订单号未知"}');
        $cookiesid = $isLogin2 == 1 ? $userrow['zid'] : $cookiesid;
        $hashsalt  = isset($_POST['hashsalt']) ? $_POST['hashsalt'] : null;
        $srow      = $DB->get_row("SELECT * FROM cmy_pay WHERE trade_no= ? limit 1", array($orderid));
        if (!$srow['trade_no'] || $srow['userid'] != $cookiesid) {
            exit('{"code":-1,"msg":"订单号不存在！"}');
        }

        if ($srow['status'] == 0) {
            $DB->query("DELETE FROM cmy_pay WHERE trade_no= ?", array($orderid));
            $input         = explode('|', $srow['input']);
            $place_holders = implode(',', array_fill(0, count($input), '?'));
            $DB->query("UPDATE cmy_cart SET status=0 WHERE id IN ($place_holders) AND status=1", $input);
            if ($conf['verify_open'] == 1) {
                session_set($hashsalt, 300);
            }
        }
        exit('{"code":0,"msg":"ok"}');
        break;
    case 'cart_empty':
        $cookiesid = $isLogin2 == 1 ? $userrow['zid'] : $cookiesid;
        if ($DB->query("DELETE FROM cmy_cart WHERE userid= ? AND status=0", array($cookiesid))) {
            exit('{"code":0,"msg":"清空购物车成功！"}');
        } else {
            exit('{"code":-1,"msg":"清空购物车失败！' . $DB->error() . '"}');
        }
        break;
    case 'cart_list':
        $cartids = input('get.ids', 1);

        $cookiesid = $isLogin2 == 1 ? $userrow['zid'] : $cookiesid;

        if ($cartids && count($cartids) > 0) {
            $ids = implode(',', $cartids);
            $rs  = $DB->query("SELECT a.*,b.name,b.input AS inputname,b.shopimg,b.multi,b.inputs,b.is_curl FROM cmy_cart AS a LEFT JOIN cmy_tools AS b ON a.tid=b.tid WHERE a.userid=:userid AND a.id IN (:ids) AND a.status<=1 ORDER BY a.id ASC", [':userid' => $cookiesid, ':ids' => $ids]);
        } else {
            $rs = $DB->query("SELECT a.*,b.name,b.input AS inputname,b.shopimg,b.multi,b.inputs,b.is_curl FROM cmy_cart AS a LEFT JOIN cmy_tools AS b ON a.tid=b.tid WHERE a.userid=:userid AND a.status<=1 ORDER BY a.id ASC", [':userid' => $cookiesid]);
        }
        $data = array();
        while ($res = $rs->fetch(PDO::FETCH_ASSOC)) {
            $input      = $res['inputname'] ? $res['inputname'] : '下单账号';
            $inputs     = explode('|', $res['inputs']);
            $inputsdata = explode('|', $res['input']);
            $show       = $input . '：' . $inputsdata[0];
            $i          = 1;
            foreach ($inputs as $input) {
                if (!$input) {
                    continue;
                }

                if (strpos($input, '{') !== false && strpos($input, '}') !== false) {
                    $input = substr($input, 0, strpos($input, '{'));
                }
                if (strpos($input, '[') !== false && strpos($input, ']') !== false) {
                    $input = substr($input, 0, strpos($input, '['));
                }
                $show .= '&nbsp;' . $input . '：' . (strpos($input, '密码') === false ? $inputsdata[$i++] : '********');
            }
            $res['inputsdata'] = $show;
            $data[]            = $res;
        }
        $count  = count($data);
        $result = array("code" => 0, "msg" => "succ", "count" => $count, "data" => $data, "sitename" => $conf['sitename']);

        break;
    case 'cart_shop_del':
        $id        = daddslashes((int) $_POST['id']);
        $cookiesid = $isLogin2 == 1 ? $userrow['zid'] : $cookiesid;
        $cart_item = $DB->get_row("SELECT * FROM `pre_cart` WHERE `id`= ? LIMIT 1", array($id));
        if (!$cart_item) {
            exit('{"code":-1,"msg":"商品不存在！"}');
        }

        if ($cart_item['userid'] != $cookiesid || $cart_item['status'] > 1) {
            exit('{"code":-1,"msg":"商品权限校验失败"}');
        }

        if ($DB->query("DELETE FROM cmy_cart WHERE id='$id'")) {
            exit('{"code":0,"msg":"商品删除成功！"}');
        } else {
            exit('{"code":-1,"msg":"商品删除失败！' . $DB->error() . '"}');
        }
        break;
    case 'cart_shop_item':
        $id        = daddslashes((int) $_POST['id']);
        $cart_item = $DB->get_row("SELECT * FROM `pre_cart` WHERE `id`= ? LIMIT 1", array($id));
        if (!$cart_item) {
            exit('{"code":-1,"msg":"商品不存在！"}');
        }
        $cookiesid = $isLogin2 == 1 ? $userrow['zid'] : $cookiesid;
        if ($cart_item['userid'] != $cookiesid || $cart_item['status'] > 1) {
            exit('{"code":-1,"msg":"商品权限校验失败"}');
        }

        $tool       = $DB->get_row("SELECT * from cmy_tools where tid= ? limit 1", array($cart_item['tid']));
        $input      = $tool['input'] ? $tool['input'] : '下单ＱＱ';
        $inputs     = explode('|', $tool['inputs']);
        $inputvalue = explode('|', $cart_item['input']);
        $data       = '<div class="panel-body">';
        if ($tool['value'] > 1) {
            $data .= '<div class="form-group"><div class="input-group"><div class="input-group-addon">份数总计</div><input type="text" id="shop_count" value="" class="form-control" disabled/></div></div>';
        }

        $data .= '<input type="hidden" id="value" value="' . ($tool['value'] ? $tool['value'] : 1) . '"/><div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname">下单份数</div><input type="text" id="num" value="' . $cart_item['num'] . '" class="form-control" required/></div>';
        if ($tool['max'] > 1) {
            $data .= '<small class="help-block"><i class="fa fa-info-circle"></i>&nbsp;该商品下单份数不能超过<b>' . $tool['max'] . '</b>份</small></div>';
        } else {
            $data .= '</div>';
        }

        $data .= '<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname">' . $input . '</div><input type="text" id="inputvalue" value="' . $inputvalue[0] . '" class="form-control" required/></div></div>';
        if ($inputs[0]) {
            $data .= '<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname2">' . $inputs[0] . '</div><input type="text" id="inputvalue2" value="' . $inputvalue[1] . '" class="form-control" required/></div></div>';
        }

        if ($inputs[1]) {
            $data .= '<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname3">' . $inputs[1] . '</div><input type="text" id="inputvalue3" value="' . $inputvalue[2] . '" class="form-control" required/></div></div>';
        }

        if ($inputs[2]) {
            $data .= '<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname4">' . $inputs[2] . '</div><input type="text" id="inputvalue4" value="' . $inputvalue[3] . '" class="form-control" required/></div></div>';
        }

        if ($inputs[3]) {
            $data .= '<div class="form-group"><div class="input-group"><div class="input-group-addon" id="inputname5">' . $inputs[3] . '</div><input type="text" id="inputvalue5" value="' . $inputvalue[4] . '" class="form-control" required/></div></div>';
        }

        $data .= '<input type="submit" id="save" onclick="cart_shop_save(' . $id . ')" class="btn btn-primary btn-block" value="保存修改"></div>';
        $data .= '<script>$("#num").keyup(function () { var i = parseInt($("#num").val()); if(isNaN(i))return false; if(i<1) $("#num").val(1); var count = parseInt($("#value").val()); count = count * i; $("#shop_count").val(count+"个");});  $("#num").keyup();</script>';
        $result = array("code" => 0, "msg" => "succ", "data" => $data);

        break;
    case 'recharge':
        if (!$isLogin2) {
            exit('{"code":-1,"msg":"未登录"}');
        }

        $value    = daddslashes($_GET['value']);
        $type     = daddslashes($_GET['type']);
        $trade_no = date("YmdHis") . rand(111, 999);
        if (!is_numeric($value) || !preg_match('/^[0-9\.]+$/', $value)) {
            exit('{"code":-1,"msg":"提交参数错误！"}');
        }

        $sql = "insert into `pre_pay` (`trade_no`,`zid`,`type`,`tid`,`input`,`name`,`money`,`ip`,`addtime`,`siteurl`,`status`) values ( ?, ?, ?,'-1', ?,'在线充值余额', ?, ?, ?, ?,'0')";
        if ($DB->query($sql, array($trade_no, $userrow['zid'], $type, $userrow['zid'], $value, $clientip, $date, $_SERVER['HTTP_HOST']))) {
            exit('{"code":0,"msg":"提交订单成功！","trade_no":"' . $trade_no . '","money":"' . $value . '","name":"在线充值余额"}');
        } else {
            exit('{"code":-1,"msg":"提交订单失败！' . $DB->error() . '"}');
        }
        break;
    case 'address_add':
        if ($isLogin2 !== 1 || empty($userrow['zid'])) {
            exit('{"code":-2,"msg":"未登录，请先登录后再操作！"}');
        }
        $zid         = $userrow['zid'];
        $name        = input('post.name', 1);
        $mobile      = input('post.mobile', 1);
        $g_phone     = input('post.g_phone', 1);
        $city        = input('post.city', 1);
        $street      = input('post.street', 1);
        $defaultAddr = intval(input('post.defaultAddr', 1));

        if ($defaultAddr === 1) {
            //重置其他默认
            $DB->query("UPDATE `pre_address` SET `is_default`='0' WHERE `zid`=:zid", [":zid" => $userrow['zid']]);
        }

        $sqlData = array(
            ':zid'        => $zid,
            ':name'       => $name,
            ':mobile'     => $mobile,
            ':g_phone'    => $g_phone,
            ':city'       => $city,
            ':street'     => $street,
            ':is_default' => $defaultAddr,
            ':uptime'     => $date,
        );
        $sql = "INSERT INTO `pre_address` (`zid`,`name`,`mobile`,`g_phone`,`city`,`street`,`is_default`,`uptime`) values (:zid,:name,:mobile,:g_phone,:city,:street,:is_default,:uptime)";
        if ($DB->insert($sql, $sqlData)) {
            $result = array("code" => 0, "msg" => "succ");
        } else {
            $result = array("code" => -1, "msg" => "添加失败，请联系平台客服处理！错误信息：" . $DB->error());
        }

        break;
    case 'address_edit':
        if ($isLogin2 !== 1 || empty($userrow['zid'])) {
            exit('{"code":-2,"msg":"未登录，请先登录后再操作！"}');
        }
        $zid         = $userrow['zid'];
        $id          = intval(input('post.id', 1));
        $name        = input('post.name', 1);
        $mobile      = input('post.mobile', 1);
        $g_phone     = input('post.g_phone', 1);
        $city        = input('post.city', 1);
        $adinfo      = input('post.adinfo', 1);
        $defaultAddr = intval(input('post.defaultAddr', 1));

        if ($defaultAddr === 1) {
            //重置其他默认
            $DB->query("UPDATE `pre_address` SET `is_default`='0' WHERE `zid`=:zid", [":zid" => $userrow['zid']]);
        }

        $sqlData = array(
            ':zid'        => $zid,
            ':name'       => $name,
            ':mobile'     => $mobile,
            ':g_phone'    => $g_phone,
            ':city'       => $city,
            ':street'     => $adinfo,
            ':is_default' => $defaultAddr,
            ':uptime'     => $date,
            ':id'         => $id,
        );
        $sql = "UPDATE `pre_address` SET `name`=:name,`mobile`=:mobile,`g_phone`=:g_phone,`city`=:city,`street`=:street,`is_default`=:is_default,`uptime`=:uptime WHERE `zid`=:zid AND `id`=:id";
        if ($DB->query($sql, $sqlData)) {
            $result = array("code" => 0, "msg" => "succ");
        } else {
            $result = array("code" => -1, "msg" => "修改失败，请联系平台客服处理！错误信息：" . $DB->error());
        }

        break;
    case 'address_info':
        if ($isLogin2 !== 1 || empty($userrow['zid'])) {
            exit('{"code":-2,"msg":"未登录，请先登录后再操作！"}');
        }
        $zid = $userrow['zid'];
        $id  = intval(input('post.id', 1));
        $row = $DB->get_row("SELECT * FROM `pre_address` WHERE zid=:zid AND `id`=:id", [":zid" => $userrow['zid'], ':id' => $id]);
        if ($row) {
            $result = array("code" => 0, "msg" => "succ", "data" => $row);
        } else {
            $result = array("code" => -1, "msg" => "获取信息失败，请联系平台客服处理！错误信息：" . $DB->error());
        }
        break;
    case 'share_link':
        $tid = intval(input('get.tid'));
        if (!$tid) {
            exit('{"code":-1,"msg":"参数不能为空"}');
        }

        $tool = $DB->get_row("SELECT * FROM cmy_tools WHERE tid='$tid' AND active=1 LIMIT 1");
        if (!$tool) {
            exit('{"code":-1,"msg":"商品不存在！"}');
        }
        if (file_exists(TEMPLATE_ROOT . $conf['template'] . '/buy.php')) {
            $url = $weburl . '?mod=buy&cid=' . $tool['cid'] . '&tid=' . $tid;
        } else {
            $url = $weburl . '/?cid=' . $tool['cid'] . '&tid=' . $tid;
        }
        if (isset($price_obj)) {
            $price_obj->setToolInfo($tool['tid'], $tool);
            $price = $price_obj->getToolPrice($tool['tid']);
        } else {
            $price = $tool['price'];
        }

        if (abs($conf['dwz_api']) > 0) {
            $ret = getUrlDwz($url);
            if ($ret['code'] == 0) {
                $url = $ret['url'];
            }
        }
        $content = '【' . $tool['name'] . '】' . $price . '元 下单链接：' . $url;
        $result  = array("code" => 0, "msg" => "succ", "link" => $url, "content" => $content);
        break;
    case 'create_url':
        if (isset($_GET['url']) && $_GET['url'] != "") {
            $url = $_GET['url'];
            if (stripos($url, "//") == false) {
                $url = 'http://' . $url;
            }
        } else {
            $url = 'http://' . $_SERVER['HTTP_HOST'] . '/?' . rand(1, 999);
        }
        $result = getUrlDwz($url);

        break;
    case 'appconfig':
        $footer_menu = true;
        if (in_array($conf['template'], ['store', 'kkyone', 'mall'])) {
            $footer_menu = false;
        }
        $data = [
            'alert'       => $conf['app_client_alert'],
            'anounce'     => $conf['anounce'],
            'bottom'      => $conf['bottom'],
            'kfqq'        => $conf['kfqq'],
            'title'       => $conf['title'],
            'sitename'    => $conf['sitename'],
            'modal'       => $conf['modal'],
            'version'     => $SYSVERSION,
            'build'       => $conf['version'],
            'footer_menu' => $footer_menu,
        ];
        $result = array("code" => 0, "msg" => "succ", "data" => $data);
        break;
    case 'getIpAddress':
        $ipAddress = getIpAddress();
        $result    = array("code" => 0, "ipAddress" => $ipAddress);
        break;
    default:
        exit('{"code":-4,"msg":"No Act","version":"' . AJAX_VERSION . '","build":"' . AJAX_BUILD . '"}');
        break;
}

if (isset($result) && is_array($result)) {
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}

$DB->close();
