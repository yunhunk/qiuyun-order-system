<?php

use core\Db;
use core\Ems;
use core\Sms;

include "../includes/common.php";

$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;

@header('Content-Type: application/json; charset=UTF-8');

switch ($act) {
    case 'checkuser':
        $user = daddslashes($_GET['user']);
        $srow = $DB->get_row("SELECT * FROM cmy_site WHERE user= ? limit 1", array($user));
        if ($srow) {
            exit('1');
        } else {
            exit('0');
        }
        break;
    case 'getUserInfo':

        if (conf('master_open') != 1) {
            exit('{"code":-1,"msg":"当前供货商系统已关闭, 无法操作"}');
        }

        if (!$isLogin3) {
            exit(json_encode([
                'code' => -1,
                'msg'  => '未登录, 请先登录',
            ]));
        }

        $masterrow['nickname'] = '';
        $masterrow['name']     = $masterrow['user'];
        $masterrow['avatar']   = '//q4.qlogo.cn/headimg_dl?dst_uin=' . $masterrow['qq'] . '&spec=100';

        exit(json_encode([
            'code' => 0,
            'msg'  => '成功',
            'data' => [
                'income'       => $masterrow['income'],
                'qq'           => $masterrow['qq'],
                'zid'          => $masterrow['zid'],
                'avatar'       => $masterrow['avatar'],
                'nickname'     => $masterrow['user'],
                'name'         => $masterrow['user'],
                'master_open'  => $masterrow['master_open'],
                'master_price' => $masterrow['master_price'],
                'pay_type'     => $masterrow['pay_type'],
                'pay_account'  => $masterrow['pay_account'],
                'pay_name'     => $masterrow['pay_name'],
                'skimg'        => $masterrow['skimg'],
                'email'        => $masterrow['email'],
                'tel'          => $masterrow['tel'],
            ],
        ]));
        break;
    case 'getConfig':
        $keys = array_keys($conf);
        foreach ($keys as $key => $value) {
            if (strpos($value, 'adm_') !== false) {
                unset($conf[$value]);
            }

            if (strpos($value, 'admin_') !== false) {
                unset($conf[$value]);
            }

            if (preg_match('/^index_/', $value) == 1) {
                unset($conf[$value]);
            }

            if (preg_match('/^zz_/', $value) == 1) {
                unset($conf[$value]);
            }

            if (preg_match('/^fenzhan_/', $value) == 1) {
                unset($conf[$value]);
            }

            if (preg_match('/^captcha_/', $value) == 1) {
                unset($conf[$value]);
            }

            if (preg_match('/^cloud_/', $value) == 1) {
                unset($conf[$value]);
            }
            if (preg_match('/^workorder_/', $value) == 1) {
                unset($conf[$value]);
            }
            if (preg_match('/^pricejk_/', $value) == 1) {
                unset($conf[$value]);
            }

            if (preg_match('/^(pricejk_|qiandao_|refund_|user_|file_|epay_|template_|syskey)/', $value) == 1) {
                unset($conf[$value]);
            }

            if (preg_match('/logo/', $value) == 1) {
                $conf[$value] = cdnurl($conf[$value], true);
            }

        }
        exit(json_encode([
            'code' => 0,
            'msg'  => '成功',
            'data' => $conf,
        ]));
        break;
    case 'login':
        if (conf('master_open') != 1) {
            exit('{"code":-1,"msg":"当前供货商系统已关闭, 无法登录"}');
        }

        $master_login_type = conf('master_login_type');

        $mobile = input('post.mobile', 1);
        $type   = input('post.type', 1);

        if ($type == 'mobile' && !in_array(intval($master_login_type), [0, 2])) {
            exit('{"code":-1,"msg":"当前供货商系统未开启手机登录方式"}');
        }

        if ($type == 'mobile') {
            if (!Sms::checkIsRun()) {
                exit('{"code":-1,"msg":"当前不支持手机号登录"}');
            }

            if (!$mobile) {
                exit('{"code":-1,"msg":"手机号不能为空"}');
            }

            $code = input('post.code', 1);
            if (!$code) {
                exit('{"code":-1,"msg":"短信验证码不能为空"}');
            }

            $row = $DB->get_row("SELECT * FROM `pre_master` WHERE `tel`= ? limit 1", array($mobile));
            if (!$row) {
                exit('{"code":-1,"msg":"未绑定供货商账户"}');
            }

            if ($row['status'] != 1) {
                exit('{"code":-1,"msg":"该供货商已封禁"}');
            }

            $sms   = new Sms();
            $check = $sms->check($mobile, $code, 'login');
            if ($check !== true) {
                json($check, 407, [
                    'mobile' => $mobile,
                    'code'   => $code,
                ]);
            }

            $user            = $row['user'];
            $pwd             = $row['pwd'];
            $row['nickname'] = '';
            $row['name']     = $row['user'];
            $row['avatar']   = '//q4.qlogo.cn/headimg_dl?dst_uin=' . $row['qq'] . '&spec=100';

            $session = md5($user . getEncodePwd($row['pwd'], $row['salt'], $row['pwd']) . $password_hash);
            $token   = authcode($row['zid'] . "\t" . $session, 'ENCODE', SYS_KEY);
            setcookie("master_token", $token, time() + 604800, '/');
            $DB->query("UPDATE `pre_master` set `loginIp`= ? where zid= ? limit 1", array($clientip, $row['zid']));
            fzlog_result($row['zid'], '供货商登录', '用户名：' . $user . ';  登录IP：' . $clientip, '供货商登录成功', 1);
        } else {
            $user = input('post.username', 1);
            $pwd  = isset($_POST['password']) ? input('post.password', 1) : input('post.pwd', 1);
            $row  = $DB->get_row("SELECT * FROM `pre_master` WHERE user= ? limit 1", array($user));
            if ($row && $user === $row['user'] && checkPwd($pwd, $row['pwd'], $row['salt'])) {

                if ($row['master_open'] != 1) {
                    exit('{"code":-1,"msg":"用户名或密码错误[0]"}');
                }

                if ($row['status'] == 0 && $row['user'] == $user) {
                    @header('Content-Type: text/html; charset=UTF-8');
                    $result = array('code' => -1, "msg" => "当前账户已封禁，无法登陆！<br>关闭原因：" . ($row['closure'] != '' ? $row['closure'] : '账户异常临时封禁处理') . "<br>如有疑问, 请联系站长QQ" . $conf['zzqq'] . "处理");
                    setcookie("master_token", "", time() - 604800, '/');
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

                $row['nickname'] = '';
                $row['name']     = $row['user'];
                $row['avatar']   = '//q4.qlogo.cn/headimg_dl?dst_uin=' . $row['qq'] . '&spec=100';

                $session = md5($user . getEncodePwd($pwd, $row['salt'], $row['pwd']) . $password_hash);
                $token   = authcode($row['zid'] . "\t" . $session, 'ENCODE', SYS_KEY);
                setcookie("master_token", $token, time() + 604800, '/');
                $DB->query("UPDATE `pre_master` set `loginIp`= ? where zid= ? limit 1", array($clientip, $row['zid']));
                fzlog_result($row['zid'], '供货商登录', '用户名：' . $user . ';  登录IP：' . $clientip, '供货商登录成功', 1);
            } else {
                fzlog_result($siterow['zid'] ? $siterow['zid'] : 1, '供货商登录', '用户名：' . $user . ';  登录IP：' . $clientip, '用户名或密码不正确！', 0);
                exit(json_encode([
                    'code' => -1,
                    "msg"  => "用户名或密码不正确！",
                    'data' => [
                        'pwd'  => $pwd,
                        'user' => $user,
                        'post' => $_POST,
                        'get'  => $_GET,
                    ],
                ]));
            }
        }

        if ($token) {
            exit(json_encode([
                'code' => 0,
                'msg'  => '登录成功',
                'data' => [
                    'token'      => $token,
                    'expiretime' => time() + 604800,
                    'username'   => $user,
                    'userInfo'   => [
                        'income'       => $row['income'],
                        'qq'           => $row['qq'],
                        'zid'          => $row['zid'],
                        'avatar'       => $row['avatar'],
                        'nickname'     => $row['user'],
                        'name'         => $row['user'],
                        'master_open'  => $row['master_open'],
                        'master_price' => $row['master_price'],
                        'pay_type'     => $row['pay_type'],
                        'pay_account'  => $row['pay_account'],
                        'pay_name'     => $row['pay_name'],
                        'skimg'        => $row['skimg'],
                        'email'        => $row['email'],
                        'tel'          => $row['tel'],
                    ],
                ],
            ]));
        }

        break;
    case 'reguser':

        // if ($isLogin3 == 1) {
        //     exit('{"code":-1,"msg":"您已登陆！"}');
        // }

        if ($conf['user_open'] == 0) {
            exit('{"code":-1,"msg":"当前站点未开启用户注册功能！"}');
        }

        $user     = input('post.user', 1);
        $pwd      = input('post.pwd', 1);
        $qq       = input('post.qq', 1);
        $tel      = input('post.mobile', 1);
        $email    = input('post.email', 1);
        $hashsalt = input('post.hashsalt', 1);
        $code     = isset($_POST['code']) ? input('post.code', 1) : null;
        $codes    = isset($_POST['code2']) ? input('post.code2', 1) : null;
        $codee    = isset($_POST['code1']) ? input('post.code1', 1) : null;

        if ($conf['verify_open'] == 1 && (empty($hashsalt) || $hashsalt != session_get())) {
            exit('{"code":-1,"msg":"验证失败，请刷新页面重试"}');
        }

        if (!preg_match('/^[a-zA-Z0-9]+$/', $user)) {
            exit('{"code":-1,"msg":"用户名只能为英文或数字！"}');
        } elseif (!preg_match('/^[a-zA-Z0-9\.\_\-\@]{6,16}$/', $pwd)) {
            exit('{"code":-1,"msg":"密码只能为6~16位的英文数字下划线小数点！"}');
        } elseif ($DB->get_row("SELECT * FROM cmy_master WHERE user= ? limit 1", array($user))) {
            exit('{"code":-1,"msg":"用户名已存在！"}');
        } elseif (strlen($pwd) < 6) {
            exit('{"code":-1,"msg":"密码不能低于6位"}');
        } elseif (!preg_match('/^([1-9]){1}([0-9]){4,11}$/', $qq)) {
            exit('{"code":-1,"msg":"QQ格式不正确！"}');
        } elseif (!$tel) {
            exit('{"code":-1,"msg":"手机号不能为空"}');
        } elseif (!validateData($tel, 'mobile')) {
            exit('{"code":-1,"msg":"手机号格式不正确"}');
        } elseif (!$email) {
            exit('{"code":-1,"msg":"邮箱不能为空"}');
        } elseif (!validateData($email, 'email')) {
            exit('{"code":-1,"msg":"邮箱格式不正确"}');
        }

        // if (\core\Template::isNeedCodeCaptcha('reg')) {
        //     if ($conf['captcha_open'] == 1 && $conf['captcha_id'] && $conf['captcha_key']) {
        //         captchaCheck('reg');
        //     } else {
        //         if ($conf['captcha_open'] == 2) {
        //             $code = isset($_POST['code']) ? input('post.code', 1) : input('get.code', 1);
        //             if (!$code || !captcha_check($code, 'user')) {
        //                 exit('{"code":3,"msg":"验证码错误"}');
        //             }
        //         }
        //     }
        // }

        $event = 'register';
        if (conf('sms_open') == 1 && conf('sms_check_register') == 1 && validateData($tel, 'mobile')) {
            if (!$codes) {
                json('验证码不能为空', 407);
            }

            $sms   = new Sms();
            $check = $sms->check($tel, $codes ?: null, $event);
            if ($check !== true) {
                json($check, 407, [
                    'code' => $codes,
                ]);
            }
        }

        if (conf('ems_check_register') == 1 && validateData($email, 'email')) {
            if (!$codee) {
                json('验证码不能为空', 406);
            }

            $ems   = new Ems();
            $check = $ems->check($email, $codee ?: null, $event);
            if ($check !== true) {
                json($check, 406, [
                    'code' => $codee,
                ]);
            }
        }

        $zid = Db::name('master')->insert([
            'master_open' => 1,
            'power'       => 0,
            'user'        => $user,
            'pwd'         => $pwd,
            'tel'         => $tel,
            'email'       => $email,
            'qq'          => $qq,
            'addtime'     => $date,
            'createtime'  => time(),
            'lasttime'    => $date,
            'status'      => 1,
            'income'      => 0,
        ]);
        if ($zid) {
            $masterrow = Db::name('master')->find(['zid' => $zid]);
            session_set('', 0);
            $session = md5($user . getEncodePwd($pwd, '', $pwd) . $password_hash);
            $token   = authcode("{$zid}\t{$session}", 'ENCODE', SYS_KEY);
            setcookie("master_token", $token, time() + 604800, '/');
            fzlog_result($row['zid'], '注册并自动登录', '供货商用户名：' . $user . ';  登录IP：' . $clientip, '供货商注册登录成功', 1);
            $masterrow['nickname'] = '';
            $masterrow['name']     = $masterrow['user'];
            $masterrow['avatar']   = '//q4.qlogo.cn/headimg_dl?dst_uin=' . $masterrow['qq'] . '&spec=100';

            exit(json_encode([
                'code' => 0,
                'msg'  => '注册供货商成功',
                'data' => [
                    'token'      => $token,
                    'expiretime' => time() + 604800,
                    'username'   => $user,
                    'userInfo'   => [
                        'income'       => $masterrow['income'],
                        'qq'           => $masterrow['qq'],
                        'zid'          => $masterrow['zid'],
                        'avatar'       => $masterrow['avatar'],
                        'nickname'     => $masterrow['user'],
                        'name'         => $masterrow['user'],
                        'master_open'  => $masterrow['master_open'],
                        'master_price' => $masterrow['master_price'],
                        'pay_type'     => $masterrow['pay_type'],
                        'pay_account'  => $masterrow['pay_account'],
                        'pay_name'     => $masterrow['pay_name'],
                        'skimg'        => $masterrow['skimg'],
                        'email'        => $masterrow['email'],
                        'tel'          => $masterrow['tel'],
                    ],
                ],
                'zid'  => $zid,
            ], 256));
        } else {
            exit(json_encode([
                'code' => -1,
                'msg'  => '注册供货商失败, ' . $DB->error(),
            ], 256));
        }
        break;
    case 'create_url':
        if (!$isLogin3) {
            exit('{"code":-1,"msg":"未登录"}');
        }

        $force = trim(daddslashes($_GET['force']));
        if (!$masterrow['siteurl']) {
            exit('{"code":-1,"msg":"当前分站还未绑定域名"}');
        }

        $url    = 'http://' . $masterrow['siteurl'] . '/?' . rand(1, 999);
        $result = getUrlDwz($url);
        exit(json_encode($result, JSON_UNESCAPED_UNICODE));
        break;
    case 'qdcount':
        if (!$isLogin3) {
            exit('{"code":-1,"msg":"未登录"}');
        }

        $day         = date("Y-m-d");
        $lastday     = date("Y-m-d", strtotime("-1 day"));
        $count1      = $DB->count("SELECT count(*) FROM cmy_qiandao WHERE `date`= ?", array($day));
        $count2      = $DB->count("SELECT count(*) FROM cmy_qiandao WHERE `date`= ?", array($lastday));
        $count3      = $DB->count("SELECT count(*) FROM cmy_qiandao");
        $rewardcount = $DB->count("SELECT sum(reward) FROM cmy_qiandao WHERE zid='{$masterrow['zid']}'");
        $result      = array("count1" => $count1, "count2" => $count2, "count3" => $count3, "rewardcount" => round($rewardcount, 2));
        exit(json_encode($result));
        break;
    case 'msg':
        if (!$isLogin3) {
            exit('{"code":-1,"msg":"未登录"}');
        }

        if ($masterrow['power'] == 2) {
            $type = '0,2,4';
        } elseif ($masterrow['power'] == 1) {
            $type = '0,2,3';
        } else {
            $type = '0,1';
        }
        $msgread = trim($masterrow['msgread'], ',');
        if (empty($msgread)) {
            $msgread = '0';
        }

        $count        = $DB->count("SELECT count(*) FROM cmy_message WHERE id NOT IN ({$msgread}) and cid!=2 and type IN ({$type})");
        $count2       = $DB->count("SELECT count(*) FROM cmy_workorder WHERE zid= ? AND status=2", [$masterrow['zid']]);
        $thtime       = date("Y-m-d") . ' 00:00:00';
        $income_today = $DB->count("SELECT sum(point) FROM cmy_points WHERE zid= ? AND action='提成' AND addtime> ?", [$masterrow['zid'], $thtime]);
        exit('{"code":0,"count":' . $count . ',"count2":' . $count2 . ',"income_today":"' . round($income_today, 2) . '"}');
        break;
    case 'msginfo':
        if (!$isLogin3) {
            exit('{"code":-1,"msg":"未登录"}');
        }

        if ($masterrow['power'] == 2) {
            $type = array(0, 2, 4);
        } elseif ($masterrow['power'] == 1) {
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

        if (!in_array($id, explode(',', $masterrow['msgread']))) {
            $msgread_n = $masterrow['msgread'] . $id . ',';
            $DB->query("UPDATE cmy_message SET count=count+1 WHERE id= ?", [$id]);
            $DB->query("UPDATE cmy_site SET msgread= ? WHERE zid= ?", [$msgread_n, $masterrow['zid']]);
        }
        $result = array("code" => 0, "msg" => "succ", "title" => $row['title'], "type" => $row['type'], "content" => $row['content'], "date" => $row['addtime']);
        exit(json_encode($result));
        break;
    case 'recharge':
        if (!$isLogin3) {
            exit('{"code":-1,"msg":"未登录"}');
        }

        $value = floatval(input('value'));
        $type  = strtolower(input('type'));

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

        $sql = "INSERT into `pre_pay` (`trade_no`,`zid`,`type`,`tid`,`input`,`name`,`money`,`ip`,`addtime`,`siteurl`,`status`) values ( ?, ?, ?,'-4', ?,'在线充值余额', ?, ?, ?, ?,'0')";
        if ($DB->query($sql, array($trade_no, $masterrow['zid'], $type, $masterrow['zid'], $value, $clientip, $date, $_SERVER['HTTP_HOST']))) {
            exit('{"code":0,"msg":"提交订单成功！","trade_no":"' . $trade_no . '","money":"' . $value . '","name":"在线充值余额"}');
        } else {
            json('提交订单失败！' . $DB->error());
        }
        break;
    //获取短信验证码
    case 'sms_send':
        if (input('mobile')) {
            $tel = input('mobile');
        } else {
            $tel = input('tel');
        }

        $event = input('event');
        if ($tel == "") {
            $result = array("code" => -1, "msg" => "手机号不能为空！");
            exit(json_encode($result));
        } else {
            try {
                $result = send_code($tel, null, $event);
                if ($result === true) {
                    $result = array('code' => 0, 'msg' => '发送成功');
                } else {
                    $result = array('code' => -1, 'msg' => '发送失败, ' . $result);
                }
            } catch (\Throwable $th) {
                $result = array('code' => -1, 'msg' => '发送失败, ' . $th->getMessage());
            }
        }
        exit(json_encode($result));
        break;
    //验证短信验证码
    case 'sms_check':

        if (input('mobile')) {
            $tel = input('mobile');
        } else {
            $tel = input('tel');
        }

        $code  = input('code');
        $event = input('event');
        if (!$event) {
            $event = 'default';
        }

        if ($tel == "") {
            $result = array("code" => -1, "msg" => "手机号不能为空！");
        } elseif ($code == "") {
            $result = array("code" => -1, "msg" => "验证码不能为空！");
        } else {
            try {
                $sms    = new \core\Sms();
                $result = $sms->check($tel, $code, $event);
                if ($result === true) {
                    $result = array('code' => 0, 'msg' => '验证成功');
                } else {
                    $result = array('code' => -1, 'msg' => '验证失败, ' . $result);
                }
            } catch (\Throwable $th) {
                $result = array('code' => -1, 'msg' => $th->getMessage());
            }
        }
        exit(json_encode($result));
        break;
    //获取短信验证码
    case 'ems_send':

        $hashsalt = input('hashsalt');

        if ($conf['verify_open'] == 1 && (empty($hashsalt) || $hashsalt != session_get())) {
            exit('{"code":-1,"msg":"设备身份验证失败，请刷新页面重试"}');
        }

        $email = input('email');
        $event = input('event');
        if ($email == "") {
            $result = array("code" => -1, "msg" => "邮箱不能为空！");
        } else {
            $code = null;
            try {
                $sms    = new \core\Ems();
                $result = $sms->send($email, $code, $event, $isLogin3 ? $masterrow['zid'] : 0);
                if ($result === true) {
                    $result = array('code' => 0, 'msg' => '发送成功');
                } else {
                    $result = array('code' => -1, 'msg' => '发送失败, ' . $result);
                }
            } catch (\Throwable $th) {
                $result = array('code' => -1, 'msg' => $th->getMessage());
            }
        }
        exit(json_encode($result));
        break;
    //验证短信验证码
    case 'sms_check':
        $email = input('email');
        $event = input('event');
        $code  = input('code');
        if (!$event) {
            $event = 'default';
        }

        if ($email == "") {
            $result = array("code" => -1, "msg" => "邮箱不能为空！");
        } elseif ($code == "") {
            $result = array("code" => -1, "msg" => "验证码不能为空！");
        } else {
            try {
                $sms    = new \core\Ems();
                $result = $sms->check($email, $code, $event);
                if ($result === true) {
                    $result = array('code' => 0, 'msg' => '验证成功');
                } else {
                    $result = array('code' => -1, 'msg' => '验证失败, ' . $result);
                }
            } catch (\Throwable $th) {
                $result = array('code' => -1, 'msg' => $th->getMessage());
            }
        }
        exit(json_encode($result));
        break;
    //文件上传
    case 'upload':

        if (!isset($_FILES['file'])) {
            json_error('上传文件不能为空或KET值不对');
        }

        $extension = explode('.', $_FILES['file']['name']);
        if (($length = count($extension)) > 1) {
            $ext = strtolower($extension[$length - 1]);
        }

        $uploaded_size    = $_FILES['file']['size'];
        $uploaded_tmp     = $_FILES['file']['tmp_name'];
        $uploaded_type    = $_FILES['file']['type'];
        $uploaded_max     = 2.5;
        $uploaded_maxsize = 1024 * 1024 * $uploaded_max;

        if ($uploaded_size > $uploaded_maxsize) {
            json("图片文件不能超过" . $uploaded_max . "MB，可以将截图发给微信文件助手,然后再保存图片重新上传！");
        } elseif ($ext == 'png' || $ext == 'gif' || $ext == 'jpg' || $ext == 'bmp' || $ext == 'webp' || $ext == 'jpeg') {
            $logoPath = 'file_' . $masterrow['zid'] . '_' . substr(MD5(time() . rand(11, 9999)), 0, 16) . '.png';
            $data     = uploadFile_fenzhan('file', $logoPath, 'supimg');
            if ($data['code'] == 0) {
                json_success('成功', [
                    'src' => cdnurl($data['path'], true),
                ]);
            } else {
                json_error('上传文件失败，' . $data['msg']);
            }
        } else {
            json_error('文件格式不支持，请更换一个试试');
        }
        break;
    default:
        exit('{"code":-4,"msg":"No Act"}');
        break;
}
