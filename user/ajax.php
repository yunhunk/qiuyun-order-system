<?php

use core\Db;

include "../includes/common.php";

$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;

$fenzhan_siteurl = $conf['fenzhan_siteurl'];
if ($fenzhan_siteurl == "") {
    $fenzhan_siteurl = $conf['fenzhan_domain'];
}

@header('Content-Type: application/json; charset=UTF-8');

if (in_array($act, ['app_add', 'app_upload', 'app_query'])) {
    $config = [
        'key'             => $conf['app_key'],
        'app_create_type' => $conf['app_create_type'],
    ];
    $AppExtend = new \core\AppExtend($config);
    @\core\File::delFiles(__DIR__ . '/temp');
    @\core\File::checkDir(__DIR__ . '/temp');
}

switch ($act) {
    case 'checkdomain':
        $qz     = daddslashes($_GET['qz']);
        $domain = $qz . '.' . daddslashes($_GET['domain']);
        $srow   = $DB->get_row("SELECT * FROM cmy_site WHERE siteurl= ? or siteurl2= ? limit 1", array($domain, $domain));
        if ($srow) {
            exit('1');
        } else {
            exit('0');
        }
        break;
    case 'checkuser':
        $user = daddslashes($_GET['user']);
        $srow = $DB->get_row("SELECT * FROM cmy_site WHERE user= ? limit 1", array($user));
        if ($srow) {
            exit('1');
        } else {
            exit('0');
        }

        break;
    case 'login':
        $user = input('post.user', 1);
        $pwd  = isset($_POST['pass']) ? input('post.pass', 1) : input('post.pwd', 1);
        $pwd  = isset($_POST['pass']) ? input('post.pass', 1) : input('post.pwd', 1);
        $row  = $DB->get_row("SELECT * FROM `pre_site` WHERE user= ? limit 1", array($user));
        if ($row && $user === $row['user'] && checkPwd($pwd, $row['pwd'], $row['salt'])) {
            if ($row['status'] == 0 && $row['user'] == $user) {
                @header('Content-Type: text/html; charset=UTF-8');
                $result = array('code' => -1, "msg" => "当前分站已关闭，无法登陆！<br>关闭原因：" . ($row['closure'] != '' ? $row['closure'] : '站点运行异常临时封禁处理') . "<br>如有疑问，请联系站长QQ" . $conf['zzqq'] . "处理");
                setcookie("user_token", "", time() - 604800, '/');
                exit(json_encode($result));
            }

            if (\core\Template::isNeedCodeCaptcha('login')) {
                if ($conf['captcha_open'] >= 1 && $conf['captcha_id'] && $conf['captcha_key']) {
                    captchaCheck('login');
                } else {
                    $code = isset($_POST['code']) ? input('post.code', 1) : input('get.code', 1);
                    if (!$code || strtolower($code) != $_SESSION['vc_code']) {
                        unset($_SESSION['vc_code']);
                        exit('{"code":3,"msg":"验证码错误"}');
                    }
                }
            }

            $session = md5($user . getEncodePwd($pwd, $row['salt'], $row['pwd']) . $password_hash);
            $token   = authcode($row['zid'] . "\t" . $session, 'ENCODE', SYS_KEY);
            setcookie("user_token", $token, time() + 604800, '/');
            $DB->query("UPDATE `pre_site` set `loginIp`= ? where zid= ? limit 1", array($clientip, $row['zid']));
            fzlog_result($row['zid'], '分站登录', '用户名：' . $user . '；登录IP：' . $clientip, '分站登录成功', 1);
            exit('{"code":0,"msg":"succ"}');
        } else {
            fzlog_result($siterow['zid'] ? $siterow['zid'] : 1, '分站登录', '用户名：' . $user . '；登录IP：' . $clientip, '用户名或密码不正确！', 0);
            exit('{"code":-1,"msg":"用户名或密码不正确！"}');
        }

        break;
    case 'ktfz_user':
        if (!$isLogin2) {
            exit('{"code":-1,"msg":"您还未登录！"}');
        }

        if ($conf['fenzhan_buy'] == 0) {
            exit('{"code":-1,"msg":"系统未开启自助开通分站功能！"}');
        }

        if (!$conf['fenzhan_domain']) {
            exit('{"code":-1,"msg":"系统未配置搭建分站可选域名！"}');
        }

        if ($isLogin2 != 1) {
            exit('{"code":-2,"msg":"请先登陆！"}');
        }

        $kind     = intval(daddslashes($_POST['kind']));
        $sitename = trim(strip_tags(daddslashes($_POST['sitename'])));
        if ($sitename == "") {
            exit('{"code":-1,"msg":"网站名称不能为空！"}');
        }

        if ($conf['zz_fenzhan_siteurl'] == "" && $conf['zz_fenzhan_domain'] == "") {
            exit('{"code":-1,"msg":"未配置可搭建域名，联系站长处理"}');
        }

        if ($kind == 2) {
            $need = $conf['fenzhan_price2'];
        } else {
            $need = $conf['fenzhan_price'];
        }

        if ($userrow['money'] < $need) {
            exit('{"code":-1,"msg":"开通失败，余额不足！"}');
        }

        $domain = getRandUrl(2, 6);
        if (strlen($domain) <= 8) {
            exit('{"code":-1,"msg":"在线开通分站失败，联系客服' . $conf['zzqq'] . '处理！","siteurl":"' . $domain . '"}');
        }

        $fenzhan_expiry = $conf['fenzhan_expiry'] > 0 ? $conf['fenzhan_expiry'] : 12;
        $endtime        = date("Y-m-d H:i:s", strtotime("+ {$fenzhan_expiry} months", time()));

        $keywords    = daddslashes($conf['keywords']);
        $description = daddslashes($conf['description']);
        $template    = daddslashes($conf['template_default'] ? $conf['template_default'] : 'default');
        if ($conf['fenzhan_html'] == 1) {
            $anounce = daddslashes($conf['anounce']);
            $alert   = daddslashes($conf['alert']);
        }

        $zid = intval($userrow['zid']);
        if ($need > 0) {
            $need = round($need, 2);
            $sql2 = $DB->query("UPDATE `pre_site` set `money`=`money`-" . $need . " where `zid`= ?", [$zid]);
            if (!$sql2) {
                exit('{"code":-1,"msg":"开通分站失败！' . $DB->error() . '"}');
            }
        }

        $sqlData = [':power' => $kind, ':siteurl' => $domain, ':sitename' => $sitename, ':keywords' => $keywords, ':description' => $description, ':template' => $template, ':anounce' => $anounce, ':alert' => $alert, ':endtime' => $endtime, ':zid' => $userrow['zid']];
        $sql     = "UPDATE `pre_site` set `power`=:power,`siteurl`=:siteurl,`sitename`=:sitename,`keywords`=:keywords,`description`=:description,`template`=:template,`anounce`=:anounce,`alert`=:alert,`endtime`=:endtime WHERE `zid`=:zid";
        if ($DB->query($sql, $sqlData)) {
            if ($need > 0) {
                addPointLogs($zid, $need, '升级', '使用余额付款升级到' . ($kind == 2 ? '旗舰版' : '专业版') . ' ，当前余额' . ($userrow['money'] - $need) . '元', null);
            } else {
                addPointLogs($zid, 0, '升级', '花费0元升级到' . ($kind == 2 ? '旗舰版' : '专业版') . ' ，当前余额' . $userrow['money'] . '元', null);
            }

            $rmb = $conf["fenzhan"] ? $conf["fenzhan"] : 0;

            if ($rmb > 0) {
                $DB->query("UPDATE `pre_site` set `money`=`money`+ ? where `zid`= ?", array($rmb, $zid));
                addPointLogs($zid, $rmb, '赠送', "你首次开通分站获赠" . $rmb . "元余额！");
            }

            $upzid = $userrow['upzid'];

            if ($upzid > 1 && $conf["fenzhan_cost"] > 0 && $need > $conf["fenzhan_cost"] && $kind == 1) {
                $tc_point = round($need - $conf["fenzhan_cost"], 2);
                $DB->query("UPDATE `pre_site` set `money`=`money`+ ? where `zid`= ?", array($tc_point, $upzid));
                addPointLogs($upzid, $tc_point, '提成', "你网站的用户（" . $userrow['zid'] . "）开通专业分站获得" . $tc_point . "元提成！");
            } elseif ($upzid > 1 && $conf["fenzhan_cost2"] > 0 && $need > $conf["fenzhan_cost2"] && $kind == 2) {
                $tc_point = round($need - $conf["fenzhan_cost2"], 2);
                $DB->query("UPDATE `pre_site` set `money`=`money`+ ? where `zid`= ?", array($tc_point, $upzid));
                addPointLogs($upzid, $tc_point, '提成', "你网站的用户（" . $userrow['zid'] . "）开通旗舰分站获得" . $tc_point . "元提成！");
            }

            $DB->query("UPDATE `pre_orders` set `userid`= ? where `userid`= ?", array($zid, $cookiesid));
            $DB->query("UPDATE `pre_orders` set `zid`=" . $zid . " where `userid`='" . $zid . "'");
            exit('{"code":0,"msg":"开通分站成功","zid":"' . $zid . '"}');

        } else {
            exit('{"code":-1,"msg":"开通分站失败！' . $DB->error() . '"}');
        }

        break;
    case 'reguser':
        if ($isLogin2 == 1) {
            exit('{"code":-1,"msg":"您已登陆！"}');
        } elseif ($conf['user_open'] == 0) {
            exit('{"code":-1,"msg":"当前站点未开启用户注册功能！"}');
        }

        $user     = input('post.user', 1);
        $pwd      = input('post.pwd', 1);
        $qq       = input('post.qq', 1);
        $hashsalt = input('post.hashsalt', 1);
        $code     = isset($_POST['code']) ? input('post.code', 1) : null;

        if ($conf['verify_open'] == 1 && (empty($hashsalt) || $hashsalt != session_get())) {
            exit('{"code":-1,"msg":"验证失败，请刷新页面重试"}');
        }

        if (!preg_match('/^[a-zA-Z0-9]+$/', $user)) {
            exit('{"code":-1,"msg":"用户名只能为英文或数字！"}');
        } elseif (!preg_match('/^[a-zA-Z0-9\.\_\-\@]{6,16}$/', $pwd)) {
            exit('{"code":-1,"msg":"密码只能为6~16位的英文数字下划线小数点！"}');
        } elseif ($DB->get_row("SELECT * FROM cmy_site WHERE user= ? limit 1", array($user))) {
            exit('{"code":-1,"msg":"用户名已存在！"}');
        } elseif (strlen($pwd) < 6) {
            exit('{"code":-1,"msg":"密码不能低于6位"}');
        } elseif (!preg_match('/^([1-9]){1}([0-9]){4,11}$/', $qq)) {
            exit('{"code":-1,"msg":"QQ格式不正确！"}');
        }

        if (\core\Template::isNeedCodeCaptcha('reg')) {
            if ($conf['captcha_open'] == 1 && $conf['captcha_id'] && $conf['captcha_key']) {
                captchaCheck('reg');
            } else {
                if ($conf['captcha_open'] == 2) {
                    $code = isset($_POST['code']) ? input('post.code', 1) : input('get.code', 1);
                    if (!$code || !captcha_check($code, 'user')) {
                        exit('{"code":3,"msg":"验证码错误"}');
                    }
                }
            }
        }

        $salt    = random(6);
        $sqlData = [':upzid' => $is_fenzhan ? $siterow['zid'] : 1, ':user' => $user, ':pwd' => getEncodePwd($pwd, $salt), ':salt' => $salt, ':qq' => $qq, ':addtime' => $date, ':lasttime' => $date];
        $sql     = "INSERT into `pre_site` (`upzid`,`power`,`siteurl`,`user`,`pwd`,`salt`,`money`,`qq`,`addtime`,`lasttime`,`status`) values (:upzid,'0','',:user,:pwd,:salt,'0',:qq,:addtime,:lasttime,'1')";
        $zid     = $DB->insert($sql, $sqlData);
        if ($zid) {
            session_set('', 0);
            $DB->query("UPDATE `pre_orders` set `userid`= ? where `userid`= ?", array($zid, $cookiesid));
            $session = md5($user . $pwd . $password_hash);
            $token   = authcode("{$zid}\t{$session}", 'ENCODE', SYS_KEY);
            setcookie("user_token", $token, time() + 604800, '/');
            fzlog_result($row['zid'], '分站登录', '用户名：' . $user . '；登录IP：' . $clientip, '分站登录成功', 1);
            exit('{"code":1,"msg":"注册用户成功","zid":"' . $zid . '"}');
        } else {
            exit('{"code":-1,"msg":"注册用户失败！' . $DB->error() . '"}');
        }
        break;
    case 'paysite':
        if ($isLogin2 == 1 && $userrow['power'] > 0) {
            exit('{"code":-1,"msg":"您已开通过分站！"}');
        } elseif ($conf['fenzhan_buy'] == 0) {
            exit('{"code":-1,"msg":"当前站点未开启自助开通分站功能！"}');
        }

        $kind     = intval($_POST['kind']);
        $qz       = strtolower(input('post.qz', 1));
        $domain   = strtolower(input('post.domain', 1));
        $user     = input('post.user', 1);
        $pwd      = input('post.pwd', 1);
        $name     = input('post.name', 1);
        $qq       = trim(daddslashes($_POST['qq']));
        $hashsalt = isset($_POST['hashsalt']) ? $_POST['hashsalt'] : null;
        $fzurl    = $qz . '.' . $domain;
        if ($conf['verify_open'] == 1 && (empty($hashsalt) || $hashsalt != session_get())) {
            exit('{"code":-1,"msg":"验证失败，请刷新页面重试"}');
        }

        if (!in_array($domain, explode(',', $fenzhan_siteurl))) {
            exit('{"code":-1,"msg":"该域名后缀无法使用"}');
        } elseif ($kind != 0 && $kind != 1 && $kind != 2) {
            exit('{"code":-1,"msg":"分站类型错误！"}');
        } elseif (strlen($qz) < 2 || strlen($qz) > 10 || !preg_match('/^[a-z0-9\-]+$/', $qz)) {
            exit('{"code":-1,"msg":"域名前缀不合格！"}');
        } elseif (!preg_match('/^[a-zA-Z0-9\_\-\.]+$/', $fzurl)) {
            exit('{"code":-1,"msg":"域名格式不正确！"}');
        } elseif ($DB->get_row("SELECT * FROM cmy_site WHERE siteurl= ? or siteurl2= ? limit 1", [$fzurl, $fzurl]) || $qz == 'www' || $fzurl == $_SERVER['HTTP_HOST']) {
            exit('{"code":-1,"msg":"此前缀已被使用！"}');
        } elseif ($qz == 'www' || $qz == 'wap' || in_array($fzurl, explode(',', $conf['fenzhan_remain']))) {
            exit('{"code":-1,"msg":"此前缀已被使用！"}');
        }

        if (!$isLogin2) {
            if (!preg_match('/^[a-zA-Z0-9]+$/', $user)) {
                exit('{"code":-1,"msg":"用户名只能为英文或数字！"}');
            } elseif (!preg_match('/^[a-zA-Z0-9\.\_\-\@]{6,16}$/', $pwd)) {
                exit('{"code":-1,"msg":"密码只能为6~16位的英文数字下划线小数点！"}');
            } elseif ($DB->get_row("SELECT * FROM cmy_site WHERE user= ? limit 1", array($user))) {
                exit('{"code":-1,"msg":"用户名已存在！"}');
            } elseif (strlen($pwd) < 6) {
                exit('{"code":-1,"msg":"密码不能低于6位"}');
            } elseif (strlen($name) < 2) {
                exit('{"code":-1,"msg":"网站名称太短！"}');
            } elseif (!preg_match('/^([1-9]){1}([0-9]){4,11}$/', $qq)) {
                exit('{"code":-1,"msg":"QQ格式不正确！"}');
            }
        }
        $fenzhan_expiry = $conf['fenzhan_expiry'] > 0 ? $conf['fenzhan_expiry'] : 12;
        $endtime        = date("Y-m-d H:i:s", strtotime("+ {$fenzhan_expiry} months", time()));
        $trade_no       = date("YmdHis") . rand(111, 999);
        if ($kind == 2) {
            $need = sprintf('%.2f', $conf['fenzhan_price2']);
        } else {
            $need = round(addslashes($conf['fenzhan_price']), 2);
        }
        if ($need == 0) {
            if ($conf['captcha_open'] == 1 && $conf['captcha_id'] != "" && $conf['captcha_key'] != "" && $conf['captcha_open_regsite'] == 1 && session_status() == 2) {
                if (isset($_POST['geetest_challenge']) && isset($_POST['geetest_validate']) && isset($_POST['geetest_seccode'])) {

                    $GtSdk = new \core\GeetestLib($conf['captcha_id'], $conf['captcha_key']);

                    $data = array(
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
            }

            if ($conf['captcha_open'] == 2 && $conf['captcha_open_regsite'] == 1) {
                $code = isset($_POST['code']) ? input('post.code', 1) : input('get.code', 1);
                if (!$code || !captcha_check($code, 'user')) {
                    exit('{"code":3,"msg":"验证码错误"}');
                }
            }

            $keywords    = addslashes($conf['keywords']);
            $description = addslashes($conf['description']);
            $template    = addslashes($conf['template_default'] ? $conf['template_default'] : 'default');
            if ($conf['fenzhan_html'] == 1) {
                $anounce = addslashes($conf['anounce']);
                $alert   = addslashes($conf['alert']);
            }
            if ($isLogin2 == 1) {
                $sqlData = [':power' => $kind, ':siteurl' => $fzurl, ':sitename' => $name, ':keywords' => $keywords, ':description' => $description, ':template' => $template, ':anounce' => $anounce, ':alert' => $alert, ':endtime' => $endtime, ':zid' => $userrow['zid']];
                $sql     = "update `pre_site` set `power`=:power,`siteurl`=:siteurl,`sitename`=:sitename,`keywords`=:keywords,`description`=:description,`template`=:template,`anounce`=:anounce,`alert`=:alert,`endtime`=:endtime where `zid`=:zid";
                $DB->query($sql, $sqlData);
                $zid = $userrow['zid'];
            } else {
                $sqlData = [':upzid' => $siterow['zid'], ':power' => $kind, ':siteurl' => $fzurl, ':user' => $user, ':pwd' => $pwd, ':qq' => $qq, ':sitename' => $name, ':keywords' => $keywords, ':description' => $description, ':template' => $template, ':anounce' => $anounce, ':alert' => $alert, ':addtime' => $date, ':endtime' => $endtime];
                $sql     = "INSERT INTO `pre_site` (`upzid`,`power`,`siteurl`,`user`,`pwd`,`money`,`qq`,`sitename`,`keywords`,`description`,`template`,`anounce`,`alert`,`addtime`,`endtime`,`status`) values (:upzid,:power,:siteurl,:user,:pwd,'0',:qq,:sitename,:keywords,:description,:template,:anounce,:alert,:addtime,:endtime,'1')";
                $zid     = $DB->insert($sql, $sqlData);
            }

            if ($zid) {
                $_SESSION['newzid'] = $zid;
                session_set('', 0);
                if (!$isLogin2) {
                    $DB->query("UPDATE `pre_orders` set `userid`= ? where `userid`= ?", array($zid, $cookiesid));
                }
                $DB->query("UPDATE `pre_orders` set `zid`= ? where `userid`= ?", array($zid, $zid));
                exit('{"code":1,"msg":"开通分站成功","zid":"' . $zid . '"}');
            } else {
                exit('{"code":-1,"msg":"开通分站失败！' . $DB->error() . '"}');
            }
        } else {
            if ($isLogin2 == 1) {
                $zid   = $userrow['zid'];
                $input = 'update|' . $userrow['zid'] . '|' . $kind . '|' . $fzurl . '|' . $name . '|' . $endtime;
            } else {
                $input = 'add|' . $kind . '|' . $fzurl . '|' . $user . '|' . $pwd . '|' . $name . '|' . $qq . '|' . $endtime;
                $zid   = $siterow['zid'] ? $siterow['zid'] : 1;
            }

            $sqlData = [':trade_no' => $trade_no, ':zid' => $zid, ':input' => $input, ':need' => $need, ':ip' => $clientip, ':userid' => $cookiesid, ':addtime' => $date];
            $sql     = "INSERT into `pre_pay` (`trade_no`,`tid`,`zid`,`input`,`num`,`name`,`money`,`ip`,`userid`,`addtime`,`status`) values (:trade_no,'-2',:zid, :input,'1','自助开通分站',:need,:ip,:userid,:addtime,'0')";
            if ($DB->query($sql, $sqlData)) {
                session_set('', 0);
                exit('{"code":0,"msg":"提交订单成功！","trade_no":"' . $trade_no . '","need":"' . $need . '","pay_alipay":"' . $conf['alipay_api'] . '","pay_wxpay":"' . $conf['wxpay_api'] . '","pay_qqpay":"' . $conf['qqpay_api'] . '","pay_rmb":"' . $isLogin2 . '","user_rmb":"' . ($isLogin2 == 1 ? $userrow['money'] : 0) . '"}');
            } else {
                exit('{"code":-1,"msg":"提交订单失败！' . $DB->error() . '"}');
            }
        }
        break;
    case 'up_price':
        if (!$isLogin2) {
            exit('{"code":-1,"msg":"未登录"}');
        }

        unset($isLogin2);
        $price_obj = new \core\Price($userrow['zid'], $userrow);
        $up        = intval($_POST['up']);
        if ($up <= 0) {
            exit('{"code":-1,"msg":"输入值不正确"}');
        }

        $sql  = $DB->query("select * from cmy_tools where active=1");
        $data = array();
        while ($row = $DB->fetch($sql)) {
            if ($row['price'] == 0) {
                continue;
            }
            if (strpos($row['name'], '免费') !== false) {
                continue;
            }
            $price_obj->setToolInfo($row['tid'], $row);
            $price                      = $price_obj->getToolPrice($tid);
            $a                          = (float) $up / 100;
            $data[$row['tid']]['price'] = round($price * ($a + 1), 2);
        }
        $array_data = serialize($data);
        $DB->query("UPDATE `pre_site` set `price`= ? where zid= ?", array($array_data, intval($userrow['zid'])));
        exit('{"code":0}');
        break;
    case 'create_url':
        if (!$isLogin2) {
            exit('{"code":-1,"msg":"未登录"}');
        }

        $force = trim(daddslashes($_GET['force']));
        if (!$userrow['siteurl']) {
            exit('{"code":-1,"msg":"当前分站还未绑定域名"}');
        }

        $url    = 'http://' . $userrow['siteurl'] . '/?' . rand(1, 999);
        $result = getUrlDwz($url);
        exit(json_encode($result, JSON_UNESCAPED_UNICODE));
        break;
    case 'qiandao':
        if (!$isLogin2) {
            exit('{"code":-1,"msg":"未登录"}');
        }

        if ($conf['qiandao_open'] != 1) {
            exit('{"code":-1,"msg":"当前站点未开启签到功能"}');
        }

        if (!isset($_SESSION['isqiandao']) || $_SESSION['isqiandao'] != $userrow['zid']) {
            exit('{"code":-1,"msg":"校验失败，请刷新页面重试"}');
        }

        $day     = date("Y-m-d");
        $lastday = date("Y-m-d", strtotime("-1 day"));

        if ($DB->get_row("SELECT * FROM cmy_qiandao WHERE zid= ? and `date`= ? order by id desc limit 1", array($userrow['zid'], $day))) {
            exit('{"code":-1,"msg":"今天已经签到过了, 明天在来吧！"}');
        }

        $reward = round($conf['qiandao_power_' . $userrow['power']], 2);
        if (!$reward || $reward <= 0) {
            exit('{"code":-1,"msg":"未配置好签到奖励余额初始值"}');
        }

        //签到限制
        if ($conf['qiandao_limit_open'] == 1) {
            $addDay      = floor(time() - strtotime($userrow['addtime'])) / 86400;
            $limit_day   = $conf['qiandao_limit_day'] ? $conf['qiandao_limit_day'] : 3;
            $limit_point = $conf['qiandao_limit_point'] ? $conf['qiandao_limit_point'] : 0.1;
            if ($addDay >= $limit_day) {
                $thtime2 = date("Y-m-d H:i:s", strtotime('-' . $limit_day . ' day', time()));
                if ($userrow['power'] > 0) {
                    $point = $DB->count("SELECT sum(point) FROM cmy_points WHERE addtime>= ? and zid= ? and (action='消费' OR action='提成')", array($thtime2, $userrow['zid']));
                } else {
                    $point = $DB->count("SELECT sum(point) FROM cmy_points WHERE addtime>= ? zid= ? and action='消费'", array($thtime2, $userrow['zid']));
                }

                if ($point == 0 || $point < $limit_point) {
                    exit('{"code":-1,"msg":"当前账户状态异常，签到已被限制！"}');
                }
            }
        }

        if ($row = $DB->get_row("SELECT * FROM cmy_qiandao WHERE zid= ? and `date`= ? order by id desc limit 1", array($userrow['zid'], $day))) {
            $continue = $row['continue'] + 1;
        } else {
            $continue = 1;
        }

        $sql = "insert into `pre_qiandao` (`zid`,`qq`,`reward`,`date`,`time`,`continue`) values ( ?, ?, ?, ?, ?, ?)";
        if ($DB->insert($sql, array($userrow['zid'], $userrow['qq'], $reward, $day, $date, $continue))) {
            unset($_SESSION['isqiandao']);
            $DB->query("UPDATE cmy_site set money=money+{$reward} where zid= ?", array($userrow['zid']));
            addPointLogs($userrow['zid'], $reward, '签到', '您今天签到获得了' . $reward . '元奖励', null);
            $result = array('code' => 0, 'msg' => '签到成功，获得' . $reward . '元现金奖励！');
        } else {
            $result = array('code' => -1, 'msg' => '签到失败' . $DB->error());
        }
        exit(json_encode($result));
        break;
    case 'qdcount':
        if (!$isLogin2) {
            exit('{"code":-1,"msg":"未登录"}');
        }

        $day         = date("Y-m-d");
        $lastday     = date("Y-m-d", strtotime("-1 day"));
        $count1      = $DB->count("SELECT count(*) FROM cmy_qiandao WHERE `date`= ?", array($day));
        $count2      = $DB->count("SELECT count(*) FROM cmy_qiandao WHERE `date`= ?", array($lastday));
        $count3      = $DB->count("SELECT count(*) FROM cmy_qiandao");
        $rewardcount = $DB->count("SELECT sum(reward) FROM cmy_qiandao WHERE zid='{$userrow['zid']}'");
        $result      = array("count1" => $count1, "count2" => $count2, "count3" => $count3, "rewardcount" => round($rewardcount, 2));
        exit(json_encode($result));
        break;
    case 'msg':
        if (!$isLogin2) {
            exit('{"code":-1,"msg":"未登录"}');
        }

        if ($userrow['power'] == 2) {
            $type = '0,2,4';
        } elseif ($userrow['power'] == 1) {
            $type = '0,2,3';
        } else {
            $type = '0,1';
        }
        $msgread = trim($userrow['msgread'], ',');
        if (empty($msgread)) {
            $msgread = '0';
        }

        $count        = $DB->count("SELECT count(*) FROM cmy_message WHERE id NOT IN ({$msgread}) and cid!=2 and type IN ({$type})");
        $count2       = $DB->count("SELECT count(*) FROM cmy_workorder WHERE zid= ? AND status=2", [$userrow['zid']]);
        $thtime       = date("Y-m-d") . ' 00:00:00';
        $income_today = $DB->count("SELECT sum(point) FROM cmy_points WHERE zid= ? AND action='提成' AND addtime> ?", [$userrow['zid'], $thtime]);
        exit('{"code":0,"count":' . $count . ',"count2":' . $count2 . ',"income_today":"' . round($income_today, 2) . '"}');
        break;
    case 'msginfo':
        if (!$isLogin2) {
            exit('{"code":-1,"msg":"未登录"}');
        }

        if ($userrow['power'] == 2) {
            $type = array(0, 2, 4);
        } elseif ($userrow['power'] == 1) {
            $type = array(0, 2, 3);
        } else {
            $type = array(0, 1);
        }
        $id  = intval($_GET['id']);
        $row = $DB->get_row("select * from cmy_message where id= ? and active=1 limit 1", [$id]);
        if (!$row) {
            exit('{"code":-1,"msg":"当前消息不存在！"}');
        }

        if (!in_array($row['type'], $type)) {
            exit('{"code":-1,"msg":"你没有权限查看此消息内容"}');
        }

        if (!in_array($id, explode(',', $userrow['msgread']))) {
            $msgread_n = $userrow['msgread'] . $id . ',';
            $DB->query("UPDATE cmy_message SET count=count+1 WHERE id= ?", [$id]);
            $DB->query("UPDATE cmy_site SET msgread= ? WHERE zid= ?", [$msgread_n, $userrow['zid']]);
        }
        $result = array("code" => 0, "msg" => "succ", "title" => $row['title'], "type" => $row['type'], "content" => $row['content'], "date" => $row['addtime']);
        exit(json_encode($result));
        break;
    case 'recharge':
        if (!$isLogin2) {
            exit('{"code":-1,"msg":"未登录"}');
        }

        $value = floatval(input('get.value'));
        $type  = strtolower(input('get.type'));

        if (!is_numeric($value) || !preg_match('/^[0-9.]+$/', $value)) {
            exit('{"code":-1,"msg":"金额格式不正确！"}');
        }

        switch ($type) {
            case 'alipay':
                if ($conf['alipay_limit_open'] == 1 && $value < floatval($conf['alipay_limit'])) {
                    exit('{"code":-1,"msg":"支付宝单笔最低充值' . floatval($conf['alipay_limit']) . '元"}');
                }
                break;
            case 'wxpay':
                if ($conf['wxpay_limit_open'] == 1 && $value < floatval($conf['wxpay_limit'])) {
                    exit('{"code":-1,"msg":"微信单笔最低充值' . floatval($conf['wxpay_limit']) . '元"}');
                }
                break;
            case 'qqpay':
                if ($conf['qqpay_limit_open'] == 1 && $value < floatval($conf['qqpay_limit'])) {
                    exit('{"code":-1,"msg":"QQ钱包单笔最低充值' . floatval($conf['qqpay_limit']) . '元"}');
                }
                break;
            default:
                break;
        }

        if ($conf['fenzhan_recharge_min'] > 0 && $value < floatval($conf['fenzhan_recharge_min'])) {
            exit('{"code":-1,"msg":"单笔最低充值' . floatval($conf['fenzhan_recharge_min']) . '元"}');
        }

        $trade_no = date("YmdHis") . rand(111, 999);

        $sql = "INSERT into `pre_pay` (`trade_no`,`zid`,`type`,`tid`,`input`,`name`,`money`,`ip`,`addtime`,`siteurl`,`status`) values ( ?, ?, ?,'-1', ?,'在线充值余额', ?, ?, ?, ?,'0')";
        if ($DB->query($sql, array($trade_no, $userrow['zid'], $type, $userrow['zid'], $value, $clientip, $date, $_SERVER['HTTP_HOST']))) {
            exit('{"code":0,"msg":"提交订单成功！","trade_no":"' . $trade_no . '","money":"' . $value . '","name":"在线充值余额"}');
        } else {
            exit('{"code":-1,"msg":"提交订单失败！' . $DB->error() . '"}');
        }
        break;
    case 'sendCode': //获取验证码

        $tel = trim(daddslashes(strip_tags($_POST['tel'])));
        if ($tel == "") {
            $result = array("code" => -1, "msg" => "手机号不能为空！");
            exit(json_encode($result));
        } else {
            if ($conf['tel'] == "") {
                $type = 1;
            } else {
                $type = 2;
            }

            $result = sendCode($tel, $type);

            if (!is_array($result)) {
                $result = array('code' => -1, 'msg' => '系统错误，发送验证码失败！');
            }

        }
        exit(json_encode($result));
        break;
    case 'checkCode': //验证验证码
        $code = trim(daddslashes(strip_tags($_POST['code'])));
        $tel  = trim(daddslashes(strip_tags($_POST['tel'])));
        if ($isLogin2 == 1) {
            $tel = $userrow['tel'];
        }

        if (empty($code)) {
            $result = array('code' => -1, 'msg' => '验证码不能为空！');
            exit(json_encode($result));
        }

        //验证验证码生成加密信息
        if ($isLogin2 == 1) {
            $user_token = $_COOKIE["user_token"] ? $_COOKIE["user_token"] : $_SESSION['user_token'];
        } else {
            $user_token = $cookiesid;
        }

        $userid = md5($user_token . ' 11111111' . $code);
        if ($_SESSION['code_userid'] != $userid) {
            $result = array('code' => -1, 'msg' => '验证码错误或已过期！', 'userid' => $userid);
        } else {
            $logrow = $DB->get_row("select * from cmy_codelog where `tel`= ? and `code`= ? order by id desc limit 1", array($tel, $code));
            if (!$logrow) {
                $result = array('code' => -1, 'msg' => '该验证码不正确！');
            } else {
                if ($logrow['status'] == 1) {
                    $result = array('code' => -1, 'msg' => '该验证码已失效！');
                } elseif ((time() - strtotime($logrow['addtime'])) > (10 * 60)) {
                    $result = array('code' => -1, 'msg' => '该验证码已过期！');
                } else {
                    unset($_SESSION['code_userid']);
                    $result = array('code' => 0, 'msg' => '已通过验证');
                    $DB->query("UPDATE `pre_codelog` set `status`='1' where id= ? limit 1", array($logrow['id']));
                }
            }
        }

        exit(json_encode($result));
        break;
    case 'setClass':
        if (!$isLogin2) {
            exit('{"code":-1,"msg":"未登录"}');
        }

        $cid       = intval($_GET['cid']);
        $active    = intval($_GET['active']);
        $classhide = explode(',', $userrow['class']);
        if ($active == 1 && in_array($cid, $classhide)) {
            $classhide = array_diff($classhide, array($cid));
        } elseif ($active == 0 && !in_array($cid, $classhide)) {
            $classhide[] = $cid;
        }
        $class = implode(',', $classhide);
        $DB->query("UPDATE `pre_site` set `class`= ? where zid= ?", array($class, $userrow['zid']));
        exit('{"code":0}');
        break;
    case 'setActiveAll':
        if (!$isLogin2 || !isset($userrow['zid'])) {
            exit('{"code":-1,"msg":"未登录"}');
        }
        $active = intval(input('active', 1));
        if ($active == 1) {
            $class == '';
        } else {
            $class == '';
            $rs = $DB->select("SELECT * FROM `pre_class`");
            if ($rs) {
                foreach ($rs as $key => $value) {
                    $class .= $value['cid'] . ",";
                }
            }
            $class = trim($class, ',');
        }
        $DB->query("UPDATE `pre_site` set `class`= ? where zid= ?", array($class, $userrow['zid']));
        exit('{"code":0}');
        break;

    case 'app_add':
        if (!$isLogin2) {
            exit('{"code":-1,"msg":"未登录"}');
        } elseif ($conf['app_open'] != 1 || empty($conf['app_key'])) {
            exit('{"code":-1,"msg":"系统未开启App生成下载！"}');
        }
        $name = input('post.name', 1);
        $url  = input('post.url', 1);
        if ($conf['app_diy'] == 1) {
            $icon       = input('post.icon', 1);
            $background = input('post.background', 1);

            $icon       = $icon > 0 ? $icon : '1';
            $background = $background > 0 ? $background : '2';
        } else {
            $icon       = '1';
            $background = '2';
        }
        if ($userrow['power'] == 2) {
            $price = sprintf('%.2f', $conf['app_price']);
        } else {
            $price = sprintf('%.2f', $conf['app_price2']);
        }
        if ($userrow['money'] >= $price) {
            if ($DB->exec("UPDATE `pre_site` set `money`=`money`- ? where `zid`= ?", [$price, $userrow['zid']]) !== false) {
                $result = $AppExtend->add($name, $icon, $background, $url);
                if ($result['code'] == 0 && $price > 0) {
                    addPointLogs($userrow['zid'], $price, '消费', '生成APP，当前余额' . ($userrow['money'] - $price) . '元', $userrow['app_task_id']);
                } else {
                    $DB->exec("UPDATE `pre_site` set `money`=`money` + ? where `zid`= ?", [$price, $userrow['zid']]);
                }
            } else {
                $result = ['code' => -1, 'msg' => '提交任务失败，' . $DB->error()];
            }
        } else {
            $result = ['code' => -1, 'msg' => '余额不足，无法提交生成任务！'];
        }
        exit(json_encode($result));
        break;
    case 'app_upload':
        if (!$isLogin2) {
            exit('{"code":-1,"msg":"未登录"}');
        } elseif ($conf['app_open'] != 1 || empty($conf['app_key'])) {
            exit('{"code":-1,"msg":"系统未开启App生成下载！"}');
        }

        if (isset($_FILES['file']) && !empty($_FILES['file']['tmp_name'])) {
            $ext = strtolower(substr($_FILES['file']['name'], strrpos($_FILES['file']['name'], '.') + 1));
            if (in_array($ext, ['png', 'gif', 'jpg', 'jpeg', 'bmp'])) {
                $filepah = __DIR__ . '/temp/' . md5($_FILES['file']['tmp_name'] . time()) . '.' . $ext;
                if (uploadFileLocal($_FILES['file']['tmp_name'], $filepah)) {
                    $type   = input('post.type');
                    $result = $AppExtend->upload($filepah, $type);
                } else {
                    $result = ['code' => -1, 'msg' => '上传文件失败，请联系网站客服'];
                }
            } else {
                $result = ['code' => -1, 'msg' => '该文件类型不受支持，请换一个！'];
            }
        } else {
            $result = ['code' => -1, 'msg' => '请选择有效的文件再操作'];
        }
        exit(json_encode($result));
        break;
    case 'app_query':
        if (!$isLogin2) {
            exit('{"code":-1,"msg":"未登录"}');
        } elseif ($conf['app_open'] != 1 || empty($conf['app_key'])) {
            exit('{"code":-1,"msg":"系统未开启App生成下载！"}');
        }
        $result = $AppExtend->query($userrow['app_weburl']);
        exit(json_encode($result));
        break;
    // 新增域名
    case 'ndomain':
        if (!$isLogin2) {
            exit('{"code":-1,"msg":"未登录"}');
        }

        if ($conf['fenzhan_editd_open'] != 1) {
            exit('{"code":-1,"msg":"未开启自助更换域名功能"}');
        }

        if ($userrow['money'] < conf('fenzhan_editd_price')) {
            exit('{"code":-1,"msg":"余额不足, 还差' . round(conf('fenzhan_editd_price') - $userrow['money'], 2) . '元"}');
        }

        $qz     = input('qz', 1);
        $domain = input('domain', 1);

        if (!$qz) {
            exit('{"code":-1,"msg":"前缀域名不能为空"}');
        }

        if (!$domain) {
            exit('{"code":-1,"msg":"新的域名不能为空"}');
        }

        $siteurl2 = $qz . '.' . $domain;

        if (strlen($qz) < 2 || strlen($qz) > 10 || !preg_match('/^[a-z0-9\-]+$/', $qz)) {
            exit('{"code":-1,"msg":"域名前缀不合格！"}');
        } elseif (!preg_match('/^[a-zA-Z0-9\_\-\.]+$/', $siteurl2)) {
            exit('{"code":-1,"msg":"域名格式不正确！"}');
        } elseif ($DB->get_row("SELECT * FROM cmy_site WHERE siteurl= ? or siteurl2= ? limit 1", [$siteurl2, $siteurl2]) || $qz == 'www' || $siteurl2 == $_SERVER['HTTP_HOST']) {
            exit('{"code":-1,"msg":"此前缀已被使用！"}');
        } elseif ($qz == 'www' || $qz == 'wap' || in_array($siteurl2, explode(',', $conf['fenzhan_remain']))) {
            exit('{"code":-1,"msg":"此前缀已被使用！"}');
        }

        $update = Db::name('site')->where([
            'zid' => $userrow['zid'],
        ])->update(['siteurl2' => $siteurl2]);

        if ($update !== false) {
            exit('{"code":0,"msg":"成功"}');
        } else {
            exit('{"code":-1,"msg":"新增失败, 数据库错误, ' . Db::error() . '"}');
        }

        break;

    default:
        exit('{"code":-4,"msg":"No Act"}');
        break;
}
