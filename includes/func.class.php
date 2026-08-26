<?php
if (!defined('IN_CRONLITE')) {
    exit("-2");
}

if (!defined("authcode")) {
    exit("-1");
}

function setPrice($price1, $tool)
{
    global $DB, $date, $conf;
    if (($conf['pricejk_edit'] == 0 && $price1 >= $tool['cost2']) || $conf['pricejk_edit'] == 1) {
        if ($tool['prid'] > 0) {
            $prow   = $DB->get_row("SELECT * from cmy_price where id='" . $tool['prid'] . "' limit 1");
            $p_kind = $prow['kind'];
            $p_0    = $prow['p_0'];
            $p_1    = $prow['p_1'];
            $p_2    = $prow['p_2'];
        } elseif ($conf['tool_price_open'] == 1) {
            $p_kind = $conf['tool_price_kind'];
            $p_0    = $conf['tool_price_0'];
            $p_1    = $conf['tool_price_1'];
            $p_2    = $conf['tool_price_2'];
        }

        if ($p_kind > 0) {
            $price = $p_kind == 2 ? round($price1 + $price1 * $p_0 / 100, 2) : round($p_0 + $price1, 2);
            $cost  = $p_kind == 2 ? round($price1 + $price1 * $p_1 / 100, 2) : round($p_1 + $price1, 2);
            $cost2 = $p_kind == 2 ? round($price1 + $price1 * $p_2 / 100, 2) : round($p_2 + $price1, 2);
            if ($price > $price1 && $price > 0) {
                $sqlData = [':price1' => $price1, ':price' => $price, ':cost' => $cost, ':cost2' => $cost2, ':uptime' => time(), ':tid' => $tool['tid']];
                $DB->query("UPDATE cmy_tools set `price1`=:price1,`price`=:price,`cost`=:cost,`cost2`=:cost2,`uptime`=:uptime where tid=:tid", $sqlData);
            }
        }
    }
    $DB->query("UPDATE cmy_tools set `price1`= ? where tid= ?", [$price1, $tool['tid']]);
}

function pricejk_yile_one($tool)
{
    global $DB;
    $shequ = $DB->get_row("SELECT * from cmy_shequ where id= ? limit 1", [$tool['shequ']]);
    if ($shequ && $shequ['type'] == 1) {
        $url  = 'http://' . $shequ['url'] . '.api.94sq.cn/api/goods/info';
        $data = array(
            'api_token' => $shequ['username'],
            'gid'       => $tool['goods_id'],
        );
        $key  = strSafeEnCode($shequ['password'], "DECODE", '18e8b42137e93e7879bc770b302b73b1');
        $post = http_build_query($data) . '&sign=' . yile_getSign($data, $key);
        //$result=get_curl($url,$post,0,0,0,0,0,1);
        $result = shequ_get_curl($url, $post, 0, 0, 0, 0, 0, 1);
        $json   = json_decode($result, true);
        if (is_array($json) && $json['status'] == 0) {
            $price1 = $json['data']['price'] * $tool['value'];
            if ($price1 != $tool['price1'] && $price1 > 0) {
                setPrice($price1, $tool);
                return 1;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    } else {
        return 0;
    }
}

function getKashangCards($arr)
{
    $cards = "";
    if (is_array($arr)) {
        foreach ($arr as $row) {
            if (!empty($row['no']) && !empty($row['password'])) {
                $cards .= $row['no'] . "----" . $row['password'] . "<br>" . PHP_EOL;
            }

            if (!empty($row['password'])) {
                $cards .= $row['password'] . "<br>" . PHP_EOL;
            } else {
                $cards .= $row['no'] . "<br>" . PHP_EOL;
            }
        }
    }

    $cards = trim($cards, "<br>" . PHP_EOL);
    return $cards;
}

function getDwzApiList($api = 3)
{

    $file = ROOT . 'includes/core/' . md5($api) . '.json';
    $data = file_exists($file) ? file_get_contents($file) : '';
    if ($data && is_array($arr = json_decode($data, true)) && $arr['time'] + 120 > time()) {
        return $arr['result'];
    } else {
        $url  = '';
        $data = '';
        switch ($api) {
            case '3':
                $url = 'http://cm.xn--zfrn171gxti3o7b.com/api/apiList.php';
                break;

            default:
                $url = '';
                break;
        }
        if ($url) {
            $data = get_curl($url);
            if (strstr($data, 'code')) {
                $result = ['result' => json_decode($data, true), 'time' => time()];
                @file_put_contents($file, json_encode($result, JSON_UNESCAPED_UNICODE));
            }
        }
    }
    return is_array($arr = json_decode($data, true)) ? $arr : ['code' => -1, 'msg' => '打开网站失败，' . $data];
}

function getUrlDwz($url, $force = false)
{
    global $conf;

    if ($conf['dwz_api_urls'] != "") {
        $url_arr = explode(",", $conf['dwz_api_urls']);
        $in      = 0;
        foreach ($url_arr as $value) {
            if (stripos($url, $value) !== false) {
                $in++;
            }
        }

        if ($in == 0) {
            return $url;
        }
    }

    if ($conf['dwz_api'] == (0 - 1)) {
        $apiUrl = $conf['dwz_api_url'];
    } else {
        if ($conf['dwz_api'] == 3) {
            $type    = $conf['dwz_type'] ? $conf['dwz_type'] : 'mi';
            $pattern = $conf['dwz_pattern'] > 0 ? $conf['dwz_pattern'] : 2;
            $apiUrl  = "http://cm.xn--zfrn171gxti3o7b.com/api/url.php?type=" . $type . "&pattern=" . $pattern . "&format=json";
            if (empty($conf['dwz_token'])) {
                return ['code' => -1, 'msg' => '系统未填好短链接生成的token！'];
            }
            $apiUrl .= '&token=' . $conf['dwz_token'] . '&url=';
        } else {
            return ['code' => -1, 'msg' => '系统未配置好短链接生成！'];
        }
    }

    $data = get_curl($apiUrl . $url);
    $json = json_decode($data, true);
    if (is_array($json)) {
        if (isset($json['ae_url']) && $json['ae_url'] != "") {
            $dwzUrl = $json['ae_url'];
        } elseif (isset($json['longurl']) && $json['longurl'] != "") {
            $dwzUrl = $json['longurl'];
        } elseif (isset($json['url']) && $json['url'] != "") {
            $dwzUrl = $json['url'];
        } elseif (preg_match('/(http|https):\/\/[\w\-\.]+\/[\w\-\.=&]+/', $data, $match)) {
            $dwzUrl = $match[0];
        } else {
            $dwzUrl = $url;
        }
    } elseif (preg_match('/(http|https):\/\/[\w\-\.]+\/[\w\-\.=&]+/', $data, $match)) {
        $dwzUrl = $match[0];
    } else {
        $dwzUrl = $url;
    }

    if ($dwzUrl != $url && $dwzUrl) {
        return ['code' => 0, 'msg' => 'msg', 'url' => $dwzUrl];
    } elseif (isset($json['msg']) || isset($json['message']) || isset($json['info'])) {
        if (isset($json['msg'])) {
            $msg = $json['msg'];
        } elseif (isset($json['message'])) {
            $msg = $json['message'];
        } else {
            $msg = isset($json['info']) ? $json['info'] : $json['data'];
        }
        return ['code' => -1, 'msg' => '生成失败，' . $msg, 'url' => $url, 'result' => $data];
    } else {
        return ['code' => -1, 'msg' => '生成失败，该接口状态异常或不支持！', 'url' => $url, 'result' => $data];
    }
}

function checkVip($qq, $typename)
{

    return '该功能已取消';
}

function sendTixianDaifu($money, $zid, $paytype, $pay_account, $pay_name)
{
    global $DB, $date, $date, $conf;
    $money   = round($money, 2);
    $zid     = intval($zid);
    $paytype = intval($paytype);
    if ($money <= 0) {
        return array('code' => -1, 'msg' => '代付金额不正确！');
    }

    if ($zid <= 1) {
        return array('code' => -1, 'msg' => '代付站点ID不能小于2！');
    }

    if ($paytype <= 0 || $paytype > 3) {
        return array('code' => -1, 'msg' => '收款方式不正确！');
    }

    if (empty($conf['daifu_api_url'])) {
        return array('code' => -1, 'msg' => '未配置提现接口地址！');
    }

    $realname = $conf['fenzhan_tixian_realname'] == 1 ? "FORCE_CHECK" : 'NO_CHECK';
    $param    = [
        'api_id'        => $conf['daifu_api_id'],
        'money'         => $money,
        'payee_type'    => $paytype,
        'payee_account' => $pay_account,
        'payee_name'    => urlencode($pay_name),
        'pay_pass'      => md5($conf['daifu_api_pwd']),
        'realname'      => $realname,
        'timestamp'     => time(),
        'note'          => urlencode('给分站' . $zid . '的提现，时间' . $date),
    ];

    $param['sign'] = getDaifuSign($param, $conf['daifu_api_key']);

    //exit($conf['daifu_api_key']);

    $url = $conf['daifu_api_url'];

    $data = get_curl($url, http_build_query($param));

    $json = json_decode($data, true);
    if (is_array($json) && $data != "") {
        if ($json['code'] == 1 || $json['code'] == true) {
            return array('code' => 0, 'msg' => "汇款成功，共" . $money . "元。" . ($json['data'] ? "汇款单号：" . $json['data'] : null), 'orderid' => $json['data'], 'data' => $data);
        } else {
            if ($json['msg'] == '支付密码错误') {
                return array('code' => -1, 'msg' => "汇款失败，" . $json['msg'], 'pay_pass' => $conf['daifu_api_pwd'], 'params' => $param);
            }
            return array('code' => -1, 'msg' => "汇款失败，" . $json['msg']);
        }
    } else {
        return array('code' => -1, 'msg' => "汇款失败，返回：" . $data);
    }
}

/**
 * 发送验证码
 *
 * @param string $mobile 手机号
 * @param string $type   类型
 * @param string $bz     备注
 * @return void
 */
function sendCode($mobile = '', $type = '1', $bz = '')
{
    global $conf, $DB, $date, $isLogin, $userrow, $clientip;
    if (intval($conf['cloud_open']) !== 1) {
        return array(
            "code" => -1,
            "msg"  => "短信验证功能未开启或未配置好！",
        );
    }

    if (!$mobile) {
        return [
            "code" => -1,
            "msg"  => "手机号不能为空，请检查！",
        ];
    } elseif (!preg_match('/^[1]{1}[0-9]{10}$/', $mobile)) {
        return [
            "code" => -1,
            "msg"  => "手机号格式不正确，请检查！",
        ];
    }

    $thtime = date("Y-m-d") . ' 00:00:00';
    $rows   = $DB->count("SELECT count(*) cmy_codelog where `addtime`>'" . $thtime . "' or (`tel`='" . $mobile . "' and `ip`='" . $clientip . "')");
    if ($rows >= $conf['cloud_num'] && $isLogin !== 1) {
        exit('{"code":-1,"msg":"当天内次数已超过上限，请明天再来~！"}');
    }

    $row = $DB->get_row("SELECT * from `pre_codelog` where `tel`='" . $mobile . "' and `addtime`>'" . $thtime . "' order by id desc limit 1");
    if ($conf['cloud_max'] > 0 && $isLogin !== 1) {
        $lastTime = strtotime($row['addtime']);
        $nextTime = time() - $lastTime;
        if ($conf['cloud_max'] == 1 && $nextTime < 60) {
            exit('{"code":-1,"msg":"您已经获取过验证码，1分钟内请勿重复获取~！"}');
        } elseif ($conf['cloud_max'] == 2 && $nextTime < 300) {
            exit('{"code":-1,"msg":"您已经获取过验证码，5分钟内请勿重复获取~！"}');
        } elseif ($conf['cloud_max'] == 3 && $nextTime < 1800) {
            exit('{"code":-1,"msg":"您已经获取过验证码，30分钟内请勿重复获取~！"}');
        } elseif ($conf['cloud_max'] == 4 && $nextTime < 7200) {
            exit('{"code":-1,"msg":"您已经获取过验证码，2小时内请勿重复获取~！"}');
        } elseif ($conf['cloud_max'] == 5 && $nextTime < 21600) {
            exit('{"code":-1,"msg":"您已经获取过验证码，6小时内请勿重复获取~！"}');
        } elseif ($conf['cloud_max'] == 6 && $nextTime < 43200) {
            exit('{"code":-1,"msg":"您已经获取过验证码，12小时内请勿重复获取~！"}');
        }
    }

    if ($isLogin == 1) {
        $zid = 1;
    } else if ($userrow && $userrow['zid']) {
        $zid = $userrow['zid'];
    }

    $result['code'] = -1;
    $result['msg']  = '发送失败，请联系平台客服处理';
    if ($conf['cloud_api'] == 2) {
        $data = sendCode_ucpaas($mobile, $type, $bz, $zid);
    } else {
        $data = sendCode_monyun($mobile, $type, $bz, $zid);
    }

    if (is_array($data) && $data['msg']) {
        return $data;
    } else {
        return $result;
    }
}

function sms_send_msg($mobile, $content = '')
{
    global $conf;

    $result['code'] = -1;
    $result['msg']  = '发送失败，请联系平台客服处理';

    if ($conf['cloud_api'] == 1) {
        $data = sendCode_monyun($mobile, 0, '', 1, $content);
    }

    if (is_array($data) && $data['msg']) {
        return $data;
    } else {
        return $result;
    }
}

function sendCode_monyun($mobile, $type, $bz = null, $zid = 1, $content = null)
{
    global $conf, $DB, $clientip, $date, $isLogin, $isLogin2, $cookiesid;
    if (!file_exists(ROOT . 'includes/core/SmsSendConn.php')) {
        return array(
            "code" => -1,
            "msg"  => "缺少短信支持库文件，无法发送！请联系客服" . $conf['zzqq'] . "处理",
        );
    }

    $cloud_length = intval($conf['cloud_length'] >= 6 ? $conf['cloud_length'] : 6);
    $code         = randomNumer($cloud_length);

    if ($_SESSION['code_userid']) {
        unset($_SESSION['code_userid']);
    }

    if ($isLogin2) {
        $user_token = $_COOKIE["user_token"] ? $_COOKIE["user_token"] : $_SESSION['user_token'];
        $userid     = md5($user_token . ' 11111111' . $code);
    } elseif ($isLogin) {
        $admin_token = $_COOKIE["admin_token"] ? $_COOKIE["admin_token"] : $_SESSION['admin_token'];
        $userid      = md5($admin_token . ' 11111111' . $code);
    } else {
        $userid = md5($cookiesid . ' 11111111' . $code);
    }

    $_SESSION['code_userid'] = $userid;

    try {
        //南方短信节点url地址
        $url = 'http://api01.monyun.cn:7901/sms/v2/std/';
        //北方短信节点url地址
        //$url = 'http://api02.monyun.cn:7901/sms/v2/std/';
        $smsSendConn = new \core\SmsSendConn($url);
    } catch (\Exception $e) {
        return ['code' => -1, 'msg' => '错误，' . $e->getMessage()];
    }

    $data = array();
    //设置账号(必填)
    $data['userid'] = $conf['cloud_user'];
    //设置密码（必填.填写明文密码,如:1234567890）
    $data['pwd'] = $conf['cloud_pwd'];
    // 设置手机号码 此处只能设置一个手机号码(必填)
    $data['mobile'] = $mobile;
    //设置发送短信内容(必填)
    if ($content) {
        $data['content'] = $content;
    } else {
        $data['content'] = getSendModel($type, $code);
    }
    // 业务类型(可选)
    $data['svrtype'] = '';
    // 设置扩展号(可选)
    $data['exno'] = '';
    //用户自定义流水编号(可选)
    $data['custid'] = '';
    // 自定义扩展数据(可选)
    $data['exdata'] = '';

    $json = $smsSendConn->singleSend($data);

    @addWebLog('发送短信', 'mobile：' . $mobile . '；data：' . $content . '；result' . json_encode($json), 'Sms', '1');
    if ($type == '1') {
        $name = '绑定手机';
    } elseif ($type == '2') {
        $name = '换绑手机';
    } elseif ($type == '3') {
        $name = '修改密码';
    } elseif ($type == '4') {
        $name = '异地验证';
    } elseif ($type == '5') {
        $name = '找回密码';
    } else {
        $name = '信息通知';
    }

    if ($json['result'] === 0) {
        if (empty($bz)) {
            $bz = $data['content'] . '！发送结果：' . json_encode($json);
        }

        $sql = "INSERT into `pre_codelog` (`tel`,`userid`,`name`,`code`,`addtime`,`ip`,`bz`,`status`) values ('" . $mobile . "','" . $userid . "','" . $name . "','" . $code . "','" . $date . "','" . $clientip . "','" . $bz . "','0')";
        if ($DB->query($sql)) {
            $result = array(
                "code" => 0,
                "msg"  => "验证码已发送至您的手机,请注意查收!",
            );
        } else {
            $result = array(
                "code" => -1,
                "msg"  => "发送失败,原因：" . $DB->error(),
            );
        }
    } else {
        if ($json['result'] == (-100001)) {
            $result = array('code' => -1, "msg" => '发送失败，通信密码错误，请联系平台客服处理！', "data" => $json);
        } elseif ($json['result'] == (-100999)) {
            $result = array('code' => -1, "msg" => '验证平台内部出现错误，请等待恢复！', "data" => $json);
        } elseif ($json['result'] == (-310099)) {
            $result = array('code' => -1, "msg" => '发送请求服务超时，请联系平台客服处理！', "data" => $json);
        } else {
            $result = array(
                "code" => -1,
                "msg"  => "发送失败！原因：" . $json['result'] . "!",
            );
        }

        $result['conf'] = array('user' => $conf['cloud_user'], 'pwd' => $conf['cloud_pwd']);

        $bz = ($isLogin === 1 ? '主站长' : '分站长') . $data['content'] . '！发送结果：' . json_encode($json);
        $DB->query("INSERT into `pre_codelog` (`tel`,`userid`,`name`,`code`,`addtime`,`ip`,`bz`,`status`) values ('" . $mobile . "','" . $zid . "','" . $name . "','" . $code . "','" . $date . "','" . $clientip . "','" . $bz . "','0')");
    }

    return $result;
}

function sendCode_ucpaas($mobile, $type, $bz = null, $zid = 1, $content = null)
{
    global $conf, $siterow, $DB, $clientip, $date, $isLogin, $isLogin2, $cookiesid;
    if (!file_exists(ROOT . 'includes/core/Ucpaas.php')) {
        return ["code" => -1, "msg" => "缺少短信支持库文件，无法发送！请联系客服" . $conf['zzqq'] . "处理"];
    } elseif (empty($conf['cloud_account_sid']) || empty($conf['cloud_auth_token'])) {
        return ["code" => -1, "msg" => "配置错误，短信ID和密钥都不能为空！"];
    } elseif (empty($conf['cloud_app_id'])) {
        return ["code" => -1, "msg" => "配置错误，应用ID不能为空！"];
    } elseif (stripos($conf['cloud_auth_token'], '*****') !== false) {
        return ["code" => -1, "msg" => "配置错误，密钥格式不正确，不能包含*****"];
    } elseif (empty($conf['cloud_template_id'])) {
        return ["code" => -1, "msg" => "配置错误，短信模板ID不能为空"];
    }

    $options['accountsid'] = $conf['cloud_account_sid'];
    //填写在开发者控制台首页上的Auth Token
    $options['token'] = $conf['cloud_auth_token'];

    try {
        $ucpass = new \core\Ucpaas($options);
    } catch (Exception $e) {
        return ['code' => -1, 'msg' => '错误，' . $e->getMessage()];
    }

    $cloud_length = intval($conf['cloud_length'] >= 4 ? $conf['cloud_length'] : 4);
    $content      = randomNumer($cloud_length);

    if ($_SESSION['code_userid']) {
        unset($_SESSION['code_userid']);
    }

    if ($isLogin2) {
        $user_token = $_COOKIE["user_token"] ? $_COOKIE["user_token"] : $_SESSION['user_token'];
        $userid     = md5($user_token . ' 11111111' . $content);
    } elseif ($isLogin) {
        $admin_token = $_COOKIE["admin_token"] ? $_COOKIE["admin_token"] : $_SESSION['admin_token'];
        $userid      = md5($admin_token . ' 11111111' . $content);
    } else {
        $userid = md5($cookiesid . ' 11111111' . $content);
    }

    $_SESSION['code_userid'] = $userid;

    $appid      = $conf['cloud_app_id']; //应用的ID，可在开发者控制台内的短信产品下查看
    $templateid = $conf['cloud_template_id']; //可在后台短信产品→选择接入的应用→短信模板-模板ID，查看该模板ID
    if (empty($templateid) || intval($templateid) < 1 || !is_numeric($templateid)) {
        return array(
            "code" => -1,
            "msg"  => "发送失败，短信模板ID不正确或为空",
        );
    }

    if ($type == '1') {
        $name = '绑定手机';
    } elseif ($type == '2') {
        $name = '换绑手机';
    } elseif ($type == '3') {
        $name = '修改密码';
    } elseif ($type == '4') {
        $name = '异地验证';
    } elseif ($type == '5') {
        $name = '找回密码';
    }

    $param  = $content . ",10"; //多个参数使用英文逗号隔开（如：param=“a,b,c”），如为参数则留空
    $mobile = $mobile;
    $uid    = $siterow['zid'] > 0 ? $siterow['zid'] . rand(1, 9999999) : '1' . rand(1, 9999999);
    $data   = $ucpass->SendSms($appid, $templateid, $param, $mobile, $uid);
    $json   = json_decode($data, true);

    if (is_array($json) && $json['code']) {
        if ($json['code'] == '000000') {

            if (empty($bz)) {
                $bz = '来自：' . ($isLogin === 1 ? '主站' : '分站长') . '；Param：' . $param . '；结果：' . $data;
            }

            $sql = "INSERT INTO `pre_codelog` (`tel`,`userid`,`name`,`code`,`addtime`,`ip`,`bz`,`status`) values ('" . $mobile . "','" . $zid . "','" . $name . "','" . $content . "','" . $date . "','" . $clientip . "','" . $bz . "','0')";
            if ($DB->query($sql)) {
                $result = array(
                    "code" => 0,
                    "msg"  => "验证码已发送至您的手机,请注意查收!",
                );
            } else {
                $result = array(
                    "code" => -1,
                    "msg"  => "发送失败,原因：" . $DB->error(),
                );
            }
            return $result;
        } else {
            if ($json['code'] == 100001) {
                $result = array('code' => -1, "msg" => '短信额度不足，请联系平台客服处理');
            } elseif ($json['code'] == 102102) {
                $result = array('code' => -1, "msg" => '系统配置的短信AppID不存在！');
            } elseif ($json['code'] == 100005) {
                $result = array('code' => -1, "msg" => '发送请求的IP不在白名单内');
            } elseif ($json['code'] == 100008) {
                $result = array('code' => -1, "msg" => '请输入标准的国内手机号码');
            } elseif ($json['code'] == 100009) {
                $result = array('code' => -1, "msg" => '该手机号不支持发送，请更换一个');
            } elseif ($json['code'] == 105150) {
                $result = array('code' => -1, "msg" => '发送频率过快');
            } elseif ($json['code'] == 105161 || $json['code'] == 105162) {
                $result = array('code' => -1, "msg" => '服务器时间不正确，联系平台客服更新后再操作');
            } elseif ($json['code'] == 105112 || $json['code'] == 105117 || $json['code'] == 105115 || $json['code'] == 105113) {
                $result = array('code' => -1, "msg" => '系统未配置好验证码模板，请联系客服处理');
            } elseif ($json['code'] == 105168) {
                $result = array('code' => -1, "msg" => '短信系统配置错误，参数sid或token错误');
            } elseif ($json['code'] == 105140) {
                $result = array('code' => -1, "msg" => '短信系统账号未认证，联系平台客服更新后再操作');
            } else {
                $result = array(
                    "code" => -1,
                    "msg"  => "发送失败，请联系客服处理，状态码：" . $json['code'],
                );
            }

            $bz = ($isLogin === 1 ? '主站长' : '分站长') . $data['content'] . '！发送结果：' . $data;
            $DB->query("insert into `pre_codelog` (`tel`,`userid`,`name`,`code`,`addtime`,`ip`,`bz`,`status`) values ('" . $mobile . "','" . $userid . "','" . $name . "','" . $content . "','" . $date . "','" . $clientip . "','" . $bz . "','0')");
            return $result;
        }
    } else {
        $result = array(
            "code"   => -1,
            "msg"    => "发送失败，请联系客服处理 [未知的异常]",
            "result" => $data,
        );

        $bz = ($isLogin === 1 ? '主站长' : '分站长') . $data['content'] . '！发送结果：' . $data;
        $DB->query("insert into `pre_codelog` (`tel`,`userid`,`name`,`code`,`addtime`,`ip`,`bz`,`status`) values ('" . $mobile . "','" . $userid . "','" . $name . "','" . $content . "','" . $date . "','" . $clientip . "','" . $bz . "','0')");
        return $result;
    }

}

if (!function_exists("curl_get5")) {
    function curl_get5($url)
    {
        $ch           = curl_init($url);
        $httpheader[] = "Accept: */*";
        $httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
        $httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
        $httpheader[] = "Connection: close";
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_ENCODING, "gzip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Linux; U; Android 4.4.1; zh-cn; R815T Build/JOP40D) AppleWebKit/533.1 (KHTML, like Gecko)Version/4.0 MQQBrowser/4.5 Mobile Safari/533.1");
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }
}

function checkVal($inputArr, $tool)
{
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

    $ret         = array();
    $ret['code'] = 0;
    if (isExistStr($tool['name'], "k歌") && isExistStr($inputvalue, "s=")) {
        if (preg_match('/(http|https):\/\/[\w\.\/\-\$\&\!\?\(\)#%+:;]+/i', $inputvalue, $ma1)) {
            $inputvalue = $ma1[0];
        }
        $inputvalue = explode("s=", $inputvalue)[1];
        if (isExistStr($inputvalue, "&")) {
            $inputvalue = explode("&", $inputvalue)[0];
        }
    } elseif (isExistStr($tool['input'], "红薯") || isExistStr($tool['input'], "红书")) {
        if (preg_match('/(http|https):\/\/[\w\.\/\-\$\&\!\?\(\)#%+:;]+/i', $inputvalue, $ma1)) {
            $inputvalue = $ma1[0];
            $res        = getxiaohongshu($inputvalue);
            if ($res['code'] == 0) {
                $inputvalue = $res['videoid'];
            }
        }
    }

    if (empty($inputvalue) || $inputvalue == "") {
        $inputvalue = $inputArr[0];
    }

    $inputVal[0] = $inputvalue;
    if ($inputvalue2) {
        $inputVal[1] = $inputvalue2;
    }

    if ($inputvalue3) {
        $inputVal[2] = $inputvalue3;
    }

    if ($inputvalue4) {
        $inputVal[3] = $inputvalue4;
    }

    if ($inputvalue5) {
        $inputVal[4] = $inputvalue5;
    }

    $ret['inputArr'] = $inputVal;
    return $ret;
}

function isExistStr($source, $target)
{
    return stripos($source, $target) !== false;
}

function getsongid($songurl)
{
    if ($songurl == '') {
        return array('code' => -1, 'songid' => "", 'msg' => '歌曲链接不能为空！');
    }

    if (stripos($songurl, ".qq.com") === false) {
        return array('code' => -1, 'songid' => "", 'msg' => '请输入正确的歌曲的分享链接！');
    }

    $songid = explode("s=", $songurl)[1];
    if (stripos($songid, "&") !== false) {
        $songid = explode("&", $songid[1])[0];
    }
    if ($songid) {
        $result = array('code' => 0, 'songid' => $songid, 'msg' => '请输入正确的歌曲的分享链接！');
    } else {
        $result = array('code' => -1, 'songid' => "", 'msg' => '请输入正确的歌曲的分享链接！');
    }
    return $result;
}

function getshuoshuo($uin, $page)
{

    global $authcode, $conf;
    $json = null;
    $row  = getQqCookie();
    if (isset($row['cookies']) && stripos($row['cookies'], 'skey') !== false) {
        $json = getsslist($row, $uin, $page);
    }

    if (is_null($json) || $json['code'] != 0) {
        if (isset($row['id'])) {
            setQqCookieError($row['id']);
        }
        if ($conf['site_qzone_api'] == 'diy' && $conf['site_qzone_api_url']) {
            $urls = str_replace(['{{uin}}', '{{page}}'], [$uin, $page], getQqApiUrl());
        } else {
            $urls = getQqApiUrl() . "ajax.php?act=getshuoshuo&uin=" . $uin . "&page=" . $page . "&authcode=" . $authcode;
        }
        $data = get_curl($urls);
        $json = json_decode($data, true);
    }

    if (is_array($json)) {
        return $json;
    } else {
        return array('code' => -1, 'msg' => "获取结果解析失败，请稍后重试[1]！", 'uin' => $uin, 'data' => $json);
    }
}

function getQqCookie()
{
    global $DB;
    $uptime = time() - (3600 * 24);
    $row    = $DB->get_row("SELECT * FROM `pre_cookies` WHERE `uin`<>'' AND `error`<3 AND `uptime`>{$uptime}  AND (`num` IS NULL OR `num`<60) order BY rand()");
    if ($row) {
        $DB->query("UPDATE `pre_cookies` SET `num`=`num`+1 where `id`='{$row['id']}'");
        return $row;
    }
    return [];
}

function setQqCookieError($id)
{
    global $DB;
    return $DB->query("UPDATE `pre_cookies` SET `error`=`error`+1 where `id`='{$id}'");
}

function getsslist($cInfo, $uin, $page)
{
    $cookie  = strpos($cInfo['cookies'], 'p_skey') !== false ? $cInfo['cookies'] : base64_decode($cInfo['cookies']);
    $g_tk1   = $cInfo['g_tk1'];
    $num     = 20;
    $pos     = ($page - 1) * $num;
    $url     = "https://user.qzone.qq.com/proxy/domain/taotao.qq.com/cgi-bin/emotion_cgi_msglist_v6?uin=" . $uin . "&inCharset=utf-8&outCharset=utf-8&hostUin=" . $uin . "&notice=0&sort=0&pos=" . $pos . "&num=" . $num . "&cgi_host=https%3A%2F%2Fuser.qzone.qq.com%2Fproxy%2Fdomain%2Ftaotao.qq.com%2Fcgi-bin%2Femotion_cgi_msglist_v6&code_version=1&format=jsonp&need_private_comment=1&g_tk=" . $g_tk1 . "&g_tk=" . $g_tk1;
    $GetData = get_curl($url, 0, 0, $cookie);

    $GetData   = getSubstr($GetData, '_Callback(', ');');
    $GetQQInfo = json_decode($GetData, true);
    if (is_array($GetQQInfo) && $GetQQInfo['code'] == 0) {
        $data = [];
        foreach ($GetQQInfo["msglist"] as $value) {
            $data[] = array(
                'content'    => $value['content'],
                'createTime' => $value['createTime'],
                'name'       => $value['name'],
                'tid'        => $value['tid'],
            );
        }
        return ['code' => 0, 'msg' => 'succ', 'data' => $data];
    } elseif (is_array($GetQQInfo)) {
        return ['code' => 0, 'msg' => $GetQQInfo["message"], 'data' => []];
    } else {
        return ['code' => 0, 'msg' => '获取获取结果解析失败，请稍后再试[0]！', 'data' => []];
    }
}

function getrizhi($uin, $page)
{
    $result = get_curl(getQqApiUrl() . "ajax.php?act=getrizhi&uin=" . $uin . "&page=" . $page);
    $data   = json_decode($result, true);
    if (is_array($data) || is_object($data)) {
        return $data;
    } else {
        return array('code' => -1, 'msg' => "获取失败，请稍后重试！", 'data' => $result);
    }
}

function getkuaishou($url)
{
    $result = array();
    if (strstr($url, "photoId=") !== false && strstr($url, "userId=") !== false) {
        $videoid = explode("photoId=", $url)[1];
        if (strstr($videoid, "&") !== false) {
            $videoid = explode("&", $videoid)[0];
        }

        $authorid = explode("userId=", $url)[1];
        if (strstr($authorid, "&") !== false) {
            $authorid = explode("&", $authorid)[0];
        }

        if (preg_match("/([A-Za-z0-9\_\-]+)/", $authorid) && preg_match("/([A-Za-z0-9\_\-]+)/", $videoid)) {
            $result = array('code' => 0, 'authorid' => $authorid, 'videoid' => $videoid, 'msg' => 'succ');
        } else {
            $result = array('code' => -1, 'authorid' => $authorid, 'videoid' => $videoid, 'msg' => '自动获取失败，请输入正确且完整的作品链接', 'warn' => '1', 'data' => $url);
        }
        return $result;
    } elseif (strripos($url, "u/") !== false) {
        $url = explode("u/", $url)[1];
        if (strstr($url, "?") !== false) {
            $url = explode("?", $url)[0];
        }
        $videoid  = explode("/", $url)[1];
        $authorid = explode("/", $url)[0];
        $result   = array('code' => 0, 'authorid' => $authorid, 'videoid' => $videoid, 'msg' => 'succ', 'url' => $url);
    } else {
        $json = getData($url);
        if ($json['code'] == 0) {
            $data = $json['data'];

            if (stripos($data, '/s/') !== false && stripos($data, 'Location') !== false) {
                if (preg_match('/location:(.*?)\n/iu', $data, $ma1)) {
                    $url = trim($ma1[1]);
                } elseif (preg_match('/location:(.*?)/iu', $data, $ma2)) {
                    $url = trim($ma2[1]);
                }

                if (function_exists("curl_init") && function_exists("get_curl")) {
                    $data = get_curl($url, 0, $url, 0, 1);
                } else {
                    $data = file_get_contents($url);
                }
            }

            if (strstr($data, "photoId=") !== false && strstr($data, "userId=") !== false) {
                $videoid = explode("photoId=", $data)[1];
                if (strstr($videoid, "&") !== false) {
                    $videoid = explode("&", $videoid)[0];
                }

                $authorid = explode("userId=", $data)[1];
                if (strstr($authorid, "&") !== false) {
                    $authorid = explode("&", $authorid)[0];
                }

                if (preg_match("/([A-Za-z0-9\_\-]+)/", $authorid) && preg_match("/([A-Za-z0-9\_\-]+)/", $videoid)) {
                    $result = array('code' => 0, 'authorid' => $authorid, 'videoid' => $videoid, 'msg' => 'succ');
                } else {
                    $result = array('code' => -1, 'authorid' => $authorid, 'videoid' => $videoid, 'msg' => '自动获取失败，请输入正确且完整的作品链接', 'warn' => '1', 'data' => $data);
                }
            } elseif (strstr($data, "u/") !== false) {
                $userid = explode("u/", $data)[1];
                if (strstr($userid, "?") !== false) {
                    $userid = explode("?", $userid)[0];
                }

                $videoid  = explode("/", $userid)[1];
                $authorid = explode("/", $userid)[0];

                if (preg_match("/([A-Za-z0-9\_\-]+)/", $authorid) && preg_match("/([A-Za-z0-9\_\-]+)/", $videoid)) {
                    $result = array('code' => 0, 'authorid' => $authorid, 'videoid' => $videoid, 'msg' => 'succ');
                } else {
                    $result = array('code' => -1, 'authorid' => $authorid, 'videoid' => $videoid, 'msg' => '自动获取失败，请输入正确且完整的作品链接', 'warn' => '1', 'data' => $data);
                }
            } else {
                $result = array('code' => -1, 'videoid' => $url, 'msg' => '自动获取失败，请输入正确且完整的作品链接', 'warn' => '2', 'data' => $data);
            }
        } else {
            $result = array('code' => -1, 'videoid' => $url, 'msg' => '获取失败，请填写正确的作品分享链接', 'arr' => $json['arr']);

        }
        return $result;
    }
    return $result;
}

function getbilibili($url)
{
    $result = array();
    if (strstr($url, "video/") !== false) {
        $videoid = explode("video/", $url)[1];
        if (strstr($videoid, "?p") !== false) {
            $videoid = explode("?p", $videoid)[0];
        } elseif (strstr($videoid, "/") !== false) {
            $videoid = explode("/", $videoid)[0];
        }
        $result = array('code' => 0, 'videoid' => $videoid, 'msg' => 'succ', 'url' => $url);
        return $result;
    } else {
        $json = getData($url);
        if ($json['code'] == 0) {
            $data = $json['data'];
            if (strstr($data, "video/") !== false) {
                $videoid = explode("video/", $data)[1];
                if (strstr($videoid, "?p") !== false) {
                    $videoid = explode("?p", $videoid)[0];
                } elseif (strstr($videoid, "/") !== false) {
                    $videoid = explode("/", $videoid)[0];
                }
                $result = array('code' => 0, 'videoid' => $videoid, 'msg' => 'succ');
            } else {
                $result = array('code' => -1, 'videoid' => $url, 'msg' => '获取失败，链接不正确或已更新规则', 'data' => $data);
            }
        } else {
            $result = array('code' => -1, 'videoid' => $url, 'msg' => '获取失败，请填写正确的作品分享链接', 'arr' => $json['arr']);

        }

        return $result;
    }
}

function getDouyinUserId($url)
{
    $id     = '';
    $result = array();
    if (strstr($url, "user/") !== false) {
        $authorid = explode("user/", $url)[1];
        if (strstr($authorid, "?") !== false) {
            $authorid = explode("?", $authorid)[0];
        }
        $result = array('code' => 0, 'authorid' => $authorid, 'msg' => 'succ', 'url' => $url);
        return $result;
    } else {
        $json = getData($url);
        if ($json['code'] == 0) {
            $data = $json['data'];
            if (stripos($data, '/s/') !== false && stripos($data, 'location') !== false) {
                if (preg_match('/location:(.*?)\n/iu', $data, $ma1)) {
                    $temp_url = trim($ma1[1]);
                } elseif (preg_match('/location:(.*?)/iu', $data, $ma2)) {
                    $temp_url = trim($ma2[1]);
                }

                if (function_exists("curl_init") && function_exists("get_curl")) {
                    $data = get_curl($temp_url, 0, $url, 0, 1);
                } else {
                    $data = file_get_contents($temp_url);
                }
            }

            if (strstr($data, "user/") !== false) {
                $authorid = explode("user/", $data)[1];
                if (strstr($authorid, "?") !== false) {
                    $authorid = explode("?", $authorid)[0];
                }
                $result = array('code' => 0, 'authorid' => $authorid, 'msg' => 'succ');
            } else {
                $result = array('code' => -1, 'authorid' => $url, 'msg' => '获取失败，请填写正确的个人主页链接', 'data' => $data);
            }
        } else {
            $result = array('code' => -1, 'authorid' => $url, 'msg' => '获取失败，请填写正确的个人主页链接', 'arr' => $json['arr']);

        }
        return $result;
    }
}

function getdouyin($url)
{
    $id     = '';
    $result = array();
    if (strstr($url, "video/") !== false) {
        $videoid = explode("video/", $url)[1];
        if (strstr($videoid, "/") !== false) {
            $videoid = explode("/", $videoid)[0];
        }
        $result = array('code' => 0, 'videoid' => $videoid, 'msg' => 'succ', 'url' => $url);
        return $result;
    } else {
        $json = getData($url);
        if ($json['code'] == 0) {
            $data = $json['data'];
            if (strstr($data, "video/") !== false) {
                $videoid = explode("video/", $data)[1];
                if (strstr($videoid, "/") !== false) {
                    $videoid = explode("/", $videoid)[0];
                }
                $result = array('code' => 0, 'videoid' => $videoid, 'msg' => 'succ');
            } else {
                $result = array('code' => -1, 'videoid' => $url, 'msg' => '获取失败，请填写正确的作品分享链接', 'data' => $data);
            }
        } else {
            $result = array('code' => -1, 'videoid' => $url, 'msg' => '获取失败，请填写正确的作品分享链接', 'arr' => $json['arr']);

        }
        return $result;
    }
}

function getpipixia($url)
{
    $id     = '';
    $result = array();
    if (strstr($url, "item/") !== false) {
        $videoid = explode("item/", $url)[1];
        if (strstr($videoid, "?") !== false) {
            $videoid = explode("?", $videoid)[0];
        }
        $result = array('code' => 0, 'videoid' => $videoid, 'msg' => 'succ', 'url' => $url);
        return $result;
    } else {
        $json = getData($url);
        if ($json['code'] == 0) {
            $data = $json['data'];
            if (strstr($data, "item/") !== false) {
                try {
                    $videoid = explode("item/", $url)[1];
                    if (strstr($videoid, "?") !== false) {
                        $videoid = explode("?", $videoid)[0];
                    }
                    $result = array('code' => 0, 'videoid' => $videoid, 'msg' => 'succ');
                } catch (Exception $e) {
                    $result = array('code' => -1, 'videoid' => $url, 'msg' => '获取失败，请填写正确的作品分享链接', 'data' => $data);
                }
            } else {
                $result = array('code' => -1, 'videoid' => $url, 'msg' => '获取失败，请填写正确的作品分享链接', 'data' => $data);
            }
        } else {
            $result = array('code' => -1, 'videoid' => $url, 'msg' => '获取失败，请填写正确的作品分享链接', 'arr' => $json['arr']);

        }

        return $result;
    }
}

function gethuoshan($url)
{
    $id     = '';
    $result = array();
    if (strstr($url, "item_id=") !== false) {
        $videoid = explode("item_id=", $url)[1];
        if (strstr($videoid, "&") !== false) {
            $videoid = explode("&", $videoid)[0];
        }
        $result = array('code' => 0, 'videoid' => $videoid, 'msg' => 'succ', 'url' => $url);
        return $result;
    } else {
        $json = getData($url);
        if ($json['code'] == 0) {
            $data = $json['data'];
            if (strstr($data, "item_id=") !== false) {
                $videoid = explode("item_id=", $data)[1];
                if (strstr($videoid, "&") !== false) {
                    $videoid = explode("&", $videoid)[0];
                }
                $result = array('code' => 0, 'videoid' => $videoid, 'msg' => 'succ', 'url' => $url);
            } else {
                $result = array('code' => -1, 'videoid' => $url, 'msg' => '获取失败，请填写正确的作品分享链接', 'data' => $data);
            }
        } else {
            $result = array('code' => -1, 'videoid' => $url, 'msg' => '获取失败，请填写正确的作品分享链接', 'arr' => $json['arr']);

        }
        return $result;
    }
}

function getshareurl($url)
{
    $preg = '/(http|https):\/\/[\w\.\/\-\$\!\?\(\)_&=#%+:;]+/';
    if (preg_match($preg, $url, $match)) {
        $result = ['code' => 0, 'shareurl' => $match[0], 'msg' => 'succ'];
    } else {
        $urlInfo = getData($url);
        if ($urlInfo['code'] == 0) {
            if (preg_match('/location:(.*?)\n/iu', $urlInfo['data'], $ma1)) {
                $temp_url = trim($ma1[1]);
            } elseif (preg_match('/location:(.*?)/iu', $urlInfo['data'], $ma2)) {
                $temp_url = trim($ma2[1]);
            } else {
                $temp_url = $urlInfo['data'];
            }
            $result = ['code' => 0, 'shareurl' => $temp_url, 'msg' => 'succ'];
        } else {
            $result = ['code' => -1, 'msg' => '转换到链接失败，内容不包含正确格式的链接！', 'data' => ['url' => $url]];
        }
    }
    return $result;
}

function getshareid($url)
{
    $result = getshareidauto($url);
    if ($result['code'] == 0) {
        return $result;
    } else {
        $urlInfo = getData($url);
        if ($urlInfo['code'] == 0) {
            if (preg_match('/location:(.*?)\n/iu', $urlInfo['data'], $ma1)) {
                $temp_url = trim($ma1[1]);
            } elseif (preg_match('/location:(.*?)/iu', $urlInfo['data'], $ma2)) {
                $temp_url = trim($ma2[1]);
            } else {
                $temp_url = $urlInfo['data'];
            }
            $result = getshareidauto($temp_url);
        } else {
            $result = array('code' => -1, 'msg' => '该链接不支持自动获取ID或链接不正确，请填写正确的后再试！', 'data' => ['url' => $url]);
        }
        return $result;
    }
}

function getzpid($url)
{
    $result = getzpidauto($url);
    if ($result['code'] == 0) {
        return $result;
    } else {
        $urlInfo = getData($url);
        if ($urlInfo['code'] == 0) {
            if (preg_match('/location:(.*?)\n/iu', $urlInfo['data'], $ma1)) {
                $temp_url = trim($ma1[1]);
            } elseif (preg_match('/location:(.*?)/iu', $urlInfo['data'], $ma2)) {
                $temp_url = trim($ma2[1]);
            } else {
                $temp_url = $urlInfo['data'];
            }
            return getzpidauto($temp_url);
        } else {
            return ['code' => -1,
                'url'          => $url,
                'msg'          => '自动获取ID失败，请填写正确的后再试！',
            ];
        }
    }
}

function getzpidauto($temp_url = '')
{
    if (preg_match('/douyin/', $temp_url)) {
        return getdouyin($temp_url);
    } elseif (preg_match('/kuaishou/', $temp_url)) {
        return getkuaishou($temp_url);
    } elseif (preg_match('/xiaohongshu\.com|xiaohongshu\.cn/', $temp_url)) {
        return getxiaohongshu($temp_url);
    } elseif (preg_match('/weishi\.qq/', $temp_url)) {
        if (stripos($temp_url, 'feed/') !== false) {
            $videoid = explode('/', explode('feed/', $temp_url)[1])[0];
        } else if (stripos($temp_url, 'personal/') !== false) {
            $videoid = explode('/', explode('personal/', $temp_url)[1])[0];
        } else {
            $videoid = explode('&', explode('id=', $temp_url)[1])[0];
        }
    } elseif (preg_match('/kg3\.qq|kg[\d]+\.qq/', $temp_url)) {
        return getsongid($temp_url);
    } elseif (preg_match('/weibo/', $temp_url)) {
        if (preg_match('/\/([\d]{14,})/', $temp_url, $match)) {
            $videoid = $match[1];
        } elseif (stripos($temp_url, 'status/') !== false) {
            $videoid = explode('status/', $temp_url)[1];
            if (stripos($videoid, '?') !== false) {
                $videoid = explode('?', $videoid)[0];
            }
        }
    } elseif (preg_match('/changba/', $temp_url)) {
        if (stripos($temp_url, 's/') !== false) {
            $videoid = explode('s/', $temp_url)[1];
            if (stripos($videoid, '?') !== false) {
                $videoid = explode('?', $videoid)[0];
            }
        }
    } elseif (preg_match('/huoshan\.com|huoshan\.cn/', $temp_url)) {
        return gethuoshan($temp_url);
    } elseif (preg_match('/meipai\.com|meipai\.cn/', $temp_url)) {
        if (stripos($temp_url, 'meipai/') !== false) {
            $videoid = explode('meipai/', $temp_url)[1];
            if (stripos($videoid, '?') !== false) {
                $videoid = explode('?', $videoid)[0];
            }
        }
    } elseif (preg_match('/miaopai/', $temp_url)) {
        if (stripos($temp_url, 'media/') !== false) {
            $videoid = explode('media/', $temp_url)[1];
            if (stripos($videoid, '.') !== false) {
                $videoid = explode('.', $videoid)[0];
            }
        }
    } elseif (preg_match('/bilibili\./', $temp_url)) {
        return getbilibili($temp_url);
    } elseif (preg_match('/izuiyou\.com|izuiyou\.cn/', $temp_url)) {
        $videoid = '';
        if (stripos($temp_url, 'detail/') !== false) {
            $videoid = explode('detail/', $temp_url)[1];
            if (stripos($videoid, '?') !== false) {
                $videoid = explode('?', $videoid)[0];
            }
        }
    } elseif (preg_match('/quanmin/', $temp_url)) {
        $videoid = '';
        if (stripos($temp_url, 'vid/') !== false) {
            $videoid = explode('vid/', $temp_url)[1];
            if (stripos($videoid, '&') !== false) {
                $videoid = explode('&', $videoid)[0];
            }
        }
    }

    if (!empty($videoid)) {
        return [
            'code'    => 0,
            'msg'     => 'succ',
            'videoid' => $videoid,
            'url'     => $temp_url,
        ];
    } else {
        return [
            'code'    => -1,
            'msg'     => '该链接不存在自动获取ID，请确认后再试！',
            'videoid' => '',
            'url'     => $temp_url,
        ];
    }
}

function getshareidauto($url)
{
    $result['code']     = -1;
    $result['shareurl'] = $url;
    $authorid           = null;
    if (preg_match('/kuaishou|chenzhongtech\.com|chenzhongtech\.cn/', $url)) {
        if (stripos($url, 'userId=') !== false) {
            $authorid = explode('userId=', $url)[1];
            if (stripos($authorid, '&') !== false) {
                $authorid = explode('&', $authorid)[0];
            }
            $result['code']     = 0;
            $result['authorid'] = $authorid;
        }
    } elseif (preg_match('/weishi/', $url)) {
        if (stripos($url, 'personal/') !== false) {
            $authorid = explode('personal/', $url)[1];
            if (stripos($url, '/') !== false) {
                $authorid = explode('/', $authorid)[0];
            }
        } elseif (stripos($url, 'id=') !== false) {
            $authorid = explode('id=', $url)[1];
            if (stripos($url, '&') !== false) {
                $authorid = explode('&', $authorid)[0];
            }
        }
    } elseif (preg_match('/xiaohongshu/', $url)) {
        if (stripos($url, 'profile/') !== false) {
            $authorid = explode('profile/', $url)[1];
            if (stripos($url, '?') !== false) {
                $authorid = explode('?', $authorid)[0];
            }
        } elseif (stripos($url, 'appuid=') !== false) {
            $authorid = explode('appuid=', $url)[1];
            if (stripos($url, '&') !== false) {
                $authorid = explode('&', $authorid)[0];
            }
        }
    } elseif (preg_match('/bilibili/', $url)) {
        if (stripos($url, 'bilibili/') !== false) {
            $authorid = explode('profile/', $url)[1];
            if (stripos($url, '?') !== false) {
                $authorid = explode('?', $authorid)[0];
            }
        }
    } elseif (preg_match('/weibo/', $url)) {
        if (stripos($url, 'u/') !== false) {
            $authorid = explode('u/', $url)[1];
            if (stripos($url, '?') !== false) {
                $authorid = explode('?', $authorid)[0];
            }
        }
    } elseif (preg_match('/taobao\./', $url) && preg_match('/contentId=(\d+)&/', $url, $match)) {
        //TBgg
        $result['code']     = 0;
        $result['authorid'] = $match[1];
    } elseif (preg_match('/taobao\./', $url) && preg_match('/id=(\d+)&/', $url, $match)) {
        //TBgg
        $result['code']     = 0;
        $result['authorid'] = $match[1];
    } elseif (preg_match('/kg3\./', $url) && preg_match('/s=(\w+)/', $url, $match)) {
        //qmkg
        $result['code']     = 0;
        $result['authorid'] = $match[1];
    } elseif (preg_match('/douyin\./', $url) && preg_match('/video\/(\w+)/', $url, $match)) {
        //dy
        $result['code']     = 0;
        $result['authorid'] = $match[1];
        $result['videoid']  = $match[1];
    }

    if ($result['code'] == 0) {
        $result['msg'] = 'succ';
    } elseif ($authorid) {
        $result['code']     = 0;
        $result['msg']      = 'succ';
        $result['authorid'] = $authorid;
    } else {
        $result['authorid'] = '';
        $result['msg']      = '该链接不支持或链接不正确，请填写正确的后再试！';
    }
    return $result;
}

function getData($url)
{
    //$preg = '/(http|https):\/\/([\w\-\_\.]+)\.([A-Za-z]{2,5})\/([\w\-\_\/]+)/';
    $preg = '/(http|https):\/\/[\w\.\/\-\$\!\?\(\)_&=#%+:;]+/';
    if (preg_match($preg, $url, $arr)) {
        $url = isset($arr[0]) && $arr[0] != "" ? $arr[0] : $url;
        $url = trim($url);
        if (function_exists("curl_init")) {
            $data = get_curl($url, 0, $url, 0, 1);
        } else {
            if (stripos($url, 'https') !== false) {
                $opts = array(
                    'https' => array(
                        'method'  => "GET",
                        'timeout' => 10, //单位秒
                    ),
                );
            } else {
                $opts = array(
                    'http' => array(
                        'method'  => "GET",
                        'timeout' => 10, //单位秒
                    ),
                );
            }
            $data = file_get_contents($url, false, stream_context_create($opts));
        }

        return ['code' => 0, 'data' => $data];
    }
    return ['code' => -1, 'arr' => $arr, 'url' => $url];
}

function getxiaohongshu($url)
{
    $id     = '';
    $result = array();
    if (strstr($url, "item/") !== false) {
        preg_match('/item\/([A-Za-z0-9]+)/', $url, $arr);
        $videoid = $arr[1];
        $result  = array('code' => 0, 'videoid' => $videoid, 'msg' => 'succ', 'url' => $url);
        return $result;
    } else {
        $json = getData($url);
        if ($json['code'] == 0) {
            $data = $json['data'];
            if (strstr($data, "item/") !== false) {
                preg_match('/item\/([A-Za-z0-9]+)/', $data, $arr);
                $videoid = $arr[1];
                $result  = array('code' => 0, 'videoid' => $videoid, 'msg' => 'succ', 'url' => $url);
            } else {
                $result = array('code' => -1, 'videoid' => $url, 'msg' => '获取失败，请填写正确的作品分享链接哦！', 'arr' => [], 'data' => $data);
            }
        } else {
            $result = array('code' => -1, 'videoid' => $url, 'msg' => '获取失败，请填写正确的作品分享链接', 'arr' => $json['arr']);

        }
        return $result;
    }
}

function getInviteUrl($sid, $qq)
{
    global $DB, $date, $conf, $siterow, $clientip, $is_fenzhan;
    $result = array();
    $row    = $DB->get_row("SELECT * from cmy_inviteorders where qq= ? and sid= ? and status=0 order by rid desc limit 1", array($qq, $sid));
    if ($row) {
        $srow  = $DB->get_row("SELECT * from cmy_invitetools where sid= ? limit 1", array($row['sid']));
        $urls  = explode('|', $conf['urls_list']);
        $tgurl = getTgUrl($urls, $row['t']);

        if ($conf['invite_text']) {
            $text = $conf['invite_text'];
            $text = str_replace('[URL]', $tgurl, $text);
            $text = str_replace(array("\r\n", "\r", "\n"), "", $text);
            $text = str_replace(array("<br>", "<p>", "[换行]"), "\n", $text);
            $text = strip_tags($text);
        } else {
            $text = "哇，好消息！ 给你们分享一个很厉害的网站，每天可以免费领取名片贊、空间说说贊、**，全民K歌、永久QQ钻等等业务\n领取地址：" . $tgurl;
        }

        $result = array('code' => 1, 'msg' => "任务查询成功", 'url' => $tgurl, 'ip' => $clientip, 'name' => $srow['name'], 'text' => $text, 'console' => '定制/购买联系星河');
        return $result;
    } else {
        if (stripos($conf['ips_list'], '|') !== false) {
            $ips     = explode('|', $conf['ips_list']);
            $ips_arr = array();
            foreach ($ips as $value) {
                if (stripos($value, '*') !== false) {
                    $ip = substr($value, 0, stripos($value, '*'));
                } else {
                    $ip = $value;
                }

                if (stripos($clientip, $ip) !== false) {
                    $result = array('code' => -1, 'msg' => "该ip已被拉黑，无法领取推广任务");
                    return $result;
                }
            }
        } else {
            if ($conf['ips_list'] != "") {
                if (stripos($conf['ips_list'], '*') !== false) {
                    $ip = substr($conf['ips_list'], 0, stripos($conf['ips_list'], '*'));
                } else {
                    $ip = $conf['ips_list'];
                }
                if (stripos($clientip, $ip) !== false) {
                    $result = array('code' => -1, 'msg' => "该ip已被拉黑，无法领取推广任务");
                    return $result;
                }
            }
        }

        $srow   = $DB->get_row("SELECT * from cmy_invitetools where sid= ? limit 1", array($sid));
        $input  = trim(daddslashes($_POST['input']));
        $input2 = trim(daddslashes($_POST['input2']));
        $input3 = trim(daddslashes($_POST['input3']));
        $input4 = trim(daddslashes($_POST['input4']));
        $input5 = trim(daddslashes($_POST['input5']));
        $thtime = date('Y-m-d') . ' 00:00:00';
        $count  = $DB->count("SELECT count(*) from cmy_inviteorders where qq= ? and sid= ? and addtime>= ?", array($qq, $sid, $thtime));
        $count2 = $DB->count("SELECT count(*) from cmy_inviteorders where input= ? and sid= ? and addtime>= ?", array($input, $sid, $thtime));
        if ($count >= intval($srow['maxnum'])) {
            exit(json_encode(array('code' => -1, 'msg' => "你今天领取该任务已达上限，明天再来吧！")));
        } elseif ($count2 >= intval($srow['maxnum'])) {
            exit(json_encode(array('code' => -1, 'msg' => "你今天领取该任务已达上限，明天再来吧！")));
        } else {
            $t    = random(8);
            $urls = explode('|', $conf['urls_list']);

            $tgurl = getTgUrl($urls, $t);

            if ($is_fenzhan == true) {
                $sitezid = $siterow['zid'];
            } else {
                $sitezid = 1;
            }

            $sql = "insert into cmy_inviteorders (`sid`,`zid`,`qq`,`t`,`url`,`input`,`input2`,`input3`,`input4`,`input5`,`countNum`,`nowNum`,`ip`,`status`,`active`,`addtime`) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,'0', ?,'0','0', ?)";
            if ($DB->query($sql, array($sid, $sitezid, $qq, $t, $tgurl, $input, $input2, $input3, $input4, $input5, $srow['endnum'], $clientip, $date))) {
                if ($conf['invite_text']) {
                    $text = $conf['invite_text'];
                    $text = str_replace('[URL]', $tgurl, $text);
                    $text = str_replace(array("\r\n", "\r", "\n"), "", $text);
                    $text = str_replace(array("<br>", "[换行]", "<p>"), "\n", $text);
                    $text = strip_tags($text);
                } else {
                    $text = "哇，好消息！ 给你们分享一个很厉害的网站，每天可以免费领取名片贊、空间说说贊、**，全民K歌、永久QQ钻等等业务\n领取地址：" . $tgurl;
                }

                $result = array('code' => 0, 'msg' => "任务领取成功", 'url' => $tgurl, 'text' => $text, 'name' => $srow['name'], 'ip' => $clientip, 'console' => '定制/购买联系星河');
            } else {
                $result = array('code' => -1, 'msg' => "任务领取失败！" . $DB->error(), 'url' => $tgurl);
            }

        }
    }

    return $result;
}

function addBlockip($ip, $qq = '', $bz = 'ip作弊行为拉黑')
{
    global $DB, $cookiesid, $date;
    $sqlData = [':qq' => $qq, ':bz' => $bz, ':ip' => $ip, ':userid' => $cookiesid, ':addtime' => $date];
    return $DB->insert("INSERT INTO `pre_blockip` (`qq`,`bz`,`ip`,`userid`,`status`,`addtime`) VALUES (:qq,:bz,:ip,:userid,'1',:addtime)", $sqlData);
}

function getFindNum($ipList, $ip)
{
    $num = 0;
    foreach ($ipList as $key => $value) {
        if (preg_match("/^{$ip}/", $value)) {
            $num++;
        }
    }
    return $num;
}

function isClientAgent($row, $ipList)
{
    $num = count($ipList);
    foreach ($ipList as $key => $value) {
        preg_match("/^[\d]]{1,3}\.[\d]]{1,3}\.[\d]]{1,3}/", $value, $match);
        $ip_q = $match[0];
        $fnum = getFindNum($ipList, $ip_q);
        if ($fnum > floor($num / 4)) {
            return ['code' => -1, 'num' => $num, 'fnum' => $fnum, 'ip' => $ip_q];
        }
        preg_match("/^[\d]{1,3}\.[\d]{1,3}/", $value, $match2);
        $ip_q = $match2[0];
        $fnum = getFindNum($ipList, $ip_q);
        if ($fnum > floor($num / 2)) {
            return ['code' => -1, 'num' => $num, 'fnum' => $fnum, 'ip' => $ip_q];
        }

    }
    // if (!empty($_SERVER['HTTP_VIA'])) {
    //     return ['code' => 0, 'ip' => $_SERVER['HTTP_VIA']];
    // } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    //     return ['code' => 0, 'ip' => $_SERVER['HTTP_X_FORWARDED_FOR']];
    // }
    return ['code' => 0];
}

function checkCheat($row)
{
    global $DB;
    //$thtime = date('Y-m-d') . ' 00:00:00';
    //$rs = $DB->query("SELECT ip,addtime FROM cmy_invitelogs where `rid`= ? and addtime>= ?", [$row['rid'], $thtime]);
    $rs   = $DB->query("SELECT ip,addtime FROM cmy_invitelogs where `rid`= ?", [$row['rid']]);
    $data = [];
    if ($rs) {
        $inum = 2;
        if ($row['countNum'] > 6) {
            $inum = ceil($row['countNum'] / 3);
        }
        $data   = $DB->fetchAll($rs);
        $ipList = getIpList($data);
        $arr    = isClientAgent($row, $ipList);
        if ($arr['code'] != 0) {
            $bz = '连续多次使用同一ip【' . $arr['ip'] . '】开头访问，疑似代理ip！推广IP总数：' . $arr['num'] . '；相同开头数：' . $arr['fnum'];
            addBlockip($row['ip'], $row['qq'], $bz);
            return true;
        }
        foreach ($ipList as $tip) {
            if (empty($tip)) {addBlockip($tip, $row['qq'], 'ip地址为空');return true;}
            if (preg_match('/^10\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $tip)) {addBlockip($tip, $row['qq'], '使用本地ip或保留地址');return true;}
            if (preg_match('/^192\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $tip)) {addBlockip($tip, $row['qq'], '使用本地ip或保留地址');return true;}
            if (preg_match('/^127\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/', $tip)) {addBlockip($tip, $row['qq'], '使用本地ip或保留地址');return true;}
            $num = getRepeatIp($ipList, $tip);
            if ($num > $inum) {addBlockip($tip, $row['qq'], '疑似使用代理ip重复作弊');return true;}
        }

        $count     = count($data);
        $count2    = $DB->query("SELECT ip,addtime FROM cmy_invitelogs where `qq`= ? and rid= ?", [$row['qq'], $row['rid']]);
        $row1      = $DB->get_row("SELECT * from cmy_invitelogs where `rid`= ? ORDER BY id asc limit 1", [$row['rid']]);
        $row2      = $DB->get_row("SELECT * from cmy_invitelogs where `rid`= ? ORDER BY id desc limit 1", [$row['rid']]);
        $startTime = $row1['addtime'];
        $endTime   = $row2['addtime'];
        $onetime   = floor((strtotime($endTime) - strtotime($startTime)) / $count);
        if ($count >= 5 && $onetime < 30 && $count2 >= 5) {
            addBlockip($row['ip'], $row['qq'], '平均推广访问间隔太短');
            return true;
        } elseif ($count >= 10 && $onetime < 60 && $count2 >= 10) {
            addBlockip($row['ip'], $row['qq'], '平均推广访问间隔太短');
            return true;
        } else if ($count >= 15 && $onetime < 90 && $count2 >= 15) {
            addBlockip($row['ip'], $row['qq'], '平均推广访问间隔太短');
            return true;
        } else if ($count >= 20 && $onetime < 120 && $count2 >= 20) {
            addBlockip($row['ip'], $row['qq'], '平均推广访问间隔太短');
            return true;
        }
    }
    return false;
}

function getIpList($data)
{
    $ret = [];
    foreach ($data as $res) {
        if (empty($res['ip'])) {
            $ret[] = '127.0.0.1';
        } else {
            $ret[] = $res['ip'];
        }
    }
    return $ret;
}

function getRepeatIp($ipList, $ip)
{
    if (!is_array($ipList)) {
        return 0;
    }

    $arr   = explode('.', $ip);
    $ipStr = $arr[0] . '.' . $arr[1] . '.' . $arr[2];
    $num   = 0;
    foreach ($ipList as $cip) {
        if (stripos($cip, $ipStr) === 0) {
            $num++;
        }
        //ip地址开头相同，就+1
    }
    return $num;
}

function addInvite($t)
{

    global $DB, $conf, $siterow, $date, $cookiesid, $clientip;
    $row = $DB->get_row("SELECT * from cmy_inviteorders where t= ? limit 1", array($t));
    if ($row) {
        $sqlData = [$clientip, $cookiesid, $row['qq']];
        $brow    = $DB->get_row("SELECT * from cmy_blockip where ip= ? or userid= ? or `qq`= ? limit 1", $sqlData);
        if ($brow) {
            $result = array('code' => -1, 'msg' => "因违反本活动公平性，该任务已被取消资格");
            return $result;
        } elseif (checkCheat($row)) {
            $result = array('code' => -1, 'msg' => "检测到你可能存在作弊行为，请及时规范！", 't' => $t);
            return $result;
        } else {
            $thtime = date('Y-m-d') . ' 00:00:00';
            $logs   = $DB->get_row("SELECT * from cmy_invitelogs where ip= ? and addtime>= ? limit 1", array($clientip, $thtime));
            if ($logs) {
                $result = array('code' => -2, 'msg' => "您今天已经来访过啦！", 't' => $t, 'console' => '定制/购买联系星河');
                return $result;
            } else {
                if ($row['status'] == 1) {
                    if (empty($row['orderid']) && $row['active'] == 0) {
                        addlnviteOrder($row);
                    }
                    $result = array('code' => -1, 'msg' => "该分享任务已完成，奖励已发放！", 't' => $t, 'console' => '定制/购买联系星河');
                    return $result;
                } elseif ($row['active'] == 0) {
                    if ($conf['captcha_open'] == 1 && $conf['captcha_id'] && $conf['captcha_key']) {

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
                                    $result = array('code' => -1, 'msg' => "验证失败，请重新验证");
                                    return $result;
                                }
                            } else {
                                //服务器宕机,走failback模式
                                if ($GtSdk->fail_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'])) {
                                    //echo '{"status":"success"}';
                                } else {
                                    $result = array('code' => -1, 'msg' => "验证失败，请重新验证");
                                    return $result;
                                }
                            }
                        } else {
                            $result = array('code' => 2, 'msg' => "请先完成验证");
                            return $result;
                        }
                    }

                    $bz = "恭喜" . $row['qq'] . "成功邀请一位来自" . $clientip . "的用户访问本站，邀人数增加1人";
                    if ($row['nowNum'] + 1 >= $row['countNum'] && $row['countNum'] > 0) {
//分享任务完成，发放奖励
                        $srow = $DB->get_row("SELECT * from cmy_invitetools where sid= ? limit 1", array($row['sid']));
                        if ($srow && $srow['status'] == 1) {
                            $DB->query("UPDATE cmy_inviteorders set `nowNum`=`nowNum`+1,`status`=1 where `t`= ?", array($t));
                            addlnviteLogs($row['rid'], $row['sid'], $row['qq'], $bz);
                            addlnviteOrder($row);
                            $result = array('code' => 0, 'msg' => "恭喜分享任务完成，奖励已发放！", 't' => $t, 'invite_jump' => ($conf['invite_jump'] > 0 ? 1 : 0), 'console' => '定制/购买联系星河');
                            return $result;
                        } else {
                            addlnviteLogs('0', '0', '0', "该任务奖励商品已下架或被删除，帮助好友失败！" . $DB->error());
                            $result = array('code' => -1, 'msg' => "该任务奖励商品已下架或被删除，帮助好友失败！" . $DB->error(), 'invite_jump' => ($conf['invite_jump'] > 0 ? 1 : 0), 't' => $t);
                            return $result;
                        }
                    } else {
                        $ins  = addlnviteLogs($row['rid'], $row['sid'], $row['qq'], $bz);
                        $ins2 = $DB->query("UPDATE cmy_inviteorders set nowNum=nowNum+1 where rid= ?", array($row['rid']));
                        if ($ins && $ins2) {
                            if ($conf['invite_jump'] == 1) {
                                $result = array('code' => 0, 'msg' => "新增分享成功", 'invite_jump' => 1, 't' => $t, 'console' => '定制/购买联系星河');
                            } else {
                                $result = array('code' => 0, 'msg' => "新增分享成功", 'invite_jump' => 0, 't' => $t, 'console' => '定制/购买联系星河');
                            }
                        } else {
                            $result = array('code' => -1, 'msg' => "新增分享记录失败！" . $DB->error(), 't' => $t, 'console' => '定制/购买联系星河');
                        }
                        return $result;
                    }
                } else {
                    $result = array('code' => -1, 'msg' => "可能存在作弊，已被拉黑！", 't' => $t);
                    return $result;
                }
            }
        }

    } else {
        $result = array('code' => -1, 'msg' => "该分享任务不存在", 't' => $t);
    }

    return $result;
}

function addlnviteOrder($row, $srow = array())
{
    global $DB, $date, $cookiesid;
    if (!$srow['tid']) {
        $srow = $DB->get_row("SELECT * from cmy_invitetools where sid= ? limit 1", array($row['sid']));
    }

    $trade_no = date("YmdHis") . rand(111, 999);
    $bz       = "来自推广任务ID（" . $row['rid'] . "）的推广奖励订单";
    $sql      = "INSERT into `pre_orders` (`tid`,`type`,`zid`,`input`,`input2`,`input3`,`input4`,`input5`,`bz`,`value`,`addtime`,`status`,`payorder`,`userid`,`djzt`,`money`) values ( ?,'share_free', ?, ?, ?, ?, ?, ?, ?,'1', ?,'0', ?, ?,'2','0')";
    if ($orderid = $DB->insert($sql, array($srow['tid'], $row['zid'], $row['input'], $row['input2'], $row['input3'], $row['input4'], $row['input5'], $bz, $date, 'share_' . $trade_no, $cookiesid))) {
        $DB->query("UPDATE cmy_inviteorders set `orderid`= ? where `rid`= ?", array($orderid, $row['rid']));
        if ($srow['active'] == 1) {
            require_once SYSTEM_ROOT . 'ajax.class.php';
            do_orders_all($orderid);
            return $orderid;
        } else {
            return "恭喜分享任务完成，奖励发放失败！" . $DB->error();
        }
    } else {
        addlnviteLogs('0', '0', '0', '错误记录：生成推f广订单失败，' . $DB->error());
        return "恭喜分享任务完成，生成推广订单失败！" . $DB->error();
    }
}

function addlnviteLogs($rid, $sid, $qq, $bz)
{
    global $DB, $clientip, $date;
    return $DB->insert("insert into `pre_invitelogs` (`rid`,`sid`,`qq`,`bz`,`ip`,`addtime`) VALUES ( ?, ?, ?, ?, ?, ?)", array($rid, $sid, $qq, $bz, $clientip, $date));
}
