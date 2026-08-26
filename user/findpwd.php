<?php
/**
 * 找回密码
 **/
$is_defend = true;
include "../includes/common.php";
if (isset($_GET['act']) && $_GET['act'] == 'qrlogin') {
    if (isset($_SESSION['findpwd_qq']) && $qq = $_SESSION['findpwd_qq']) {
        $qq  = strip_tags(addslashes($qq));
        $row = $DB->get_row("SELECT * FROM pre_site WHERE qq= ? limit 1", array($qq));
        unset($_SESSION['findpwd_qq']);

        if ($row['user']) {
            $session = md5($row['user'] . $row['pwd'] . $password_hash);
            $token   = authcode("{$row['zid']}\t{$session}", 'ENCODE', SYS_KEY);
            setcookie("user_token", $token, time() + 604800, '/');
            fzlog_result($row['zid'], '找回密码', '用户名：' . $row['user'] . '；登录IP：' . $clientip, '找回密码登录成功', 1);
            $DB->query("update pre_site set lasttime='$date' where zid='{$row['zid']}'");
            @header('Content-Type: application/json; charset=UTF-8');
            exit('{"code":1,"msg":"登录成功，请在用户资料设置里重置密码","url":"/user/index.php"}');
        } else {
            @header('Content-Type: application/json; charset=UTF-8');
            exit('{"code":-1,"msg":"当前QQ不存在，请确认你已开通过分站"}');
        }
    } else {
        @header('Content-Type: application/json; charset=UTF-8');
        exit('{"code":-2,"msg":"验证失败，请重新扫码"}');
    }
} elseif ($_GET['act'] == 'sendcode') {
    $tel = (int) strip_tags(addslashes($_POST['tel']));
    $row = $DB->get_row("SELECT * FROM pre_site WHERE tel= ? order by zid desc limit 1", array($tel));
    if (!preg_match('/^[1-9]{1}[0-9]{10}$/', $tel, $arr)) {
        $result = array('code' => '-1', 'msg' => '手机号不正确！请填写国内1开头的11位手机号');
    } elseif ($row == false || $row['zid'] < 2) {
        $result = array('code' => '-1', 'msg' => '该手机号未绑定本平台任何账号');
    } else {
        $result = sendcode($tel, 5, null, $row['zid']);
    }
    exit(json_encode($result));
} elseif ($_GET['act'] == 'find_tel') {
    $tel  = input('post.tel', 1);
    $code = input('post.code', 1);
    $row  = $DB->get_row("SELECT * FROM pre_site WHERE tel= ? order by zid desc limit 1", [$tel]);
    if (empty($code)) {
        $result = array('code' => -1, 'msg' => '验证码不能为空！');
    } elseif (!preg_match('/^[1-9]{1}[0-9]{10}$/', $tel, $arr)) {
        $result = array('code' => -1, 'msg' => '手机号不正确！请填写国内1开头的11位手机号');
    } elseif ($row == false || $row['zid'] < 2) {
        $result = array('code' => '-1', 'msg' => '该手机号未绑定本平台任何账号');
    } else {
        $userid = getUserId($code);
        if ($_SESSION['code_userid'] != $userid) {
            $result = array('code' => -1, 'msg' => '验证码错误或已过期！', 'userid' => $userid);
        } else {
            $logrow = $DB->get_row("select * from pre_codelog where `tel`= ? and `code`= ? order by id desc limit 1", array($tel, $code));
            if (!$logrow) {
                $result = array('code' => -1, 'msg' => '该验证码不正确！');
            } else {
                if ($logrow['status'] == 1) {
                    $result = array('code' => -1, 'msg' => '该验证码已失效！');
                } elseif ((time() - strtotime($logrow['addtime'])) > (10 * 60)) {
                    $result = array('code' => -1, 'msg' => '该验证码已过期！');
                } else {
                    unset($_SESSION['code_userid']);
                    $session = md5($row['user'] . $row['pwd'] . $password_hash);
                    $token   = authcode($row['zid'] . "\t" . $session, 'ENCODE', SYS_KEY);
                    setcookie("user_token", $token, time() + 604800, '/');
                    $DB->query("update `pre_site` set `loginIp`= ? where zid= ? limit 1", array($clientip, $row['zid']));
                    fzlog_result($row['zid'], '分站登录', '用户名：' . $user . '；登录IP：' . $clientip, '分站通过手机号找回密码登录成功', 1);
                    $result = array('code' => 0, 'msg' => '已通过验证');
                    $DB->query("update `pre_codelog` set `status`='1' where id= ? limit 1", array($logrow['id']));
                }
            }
        }
    }
    exit(json_encode($result));
} elseif (isset($_GET['act']) && $_GET['act'] == 'qrcode') {
    $result = array('code' => '0', 'msg' => 'succ');
    exit(json_encode($result));
} elseif ($isLogin2 == 1) {
    @header('Content-Type: text/html; charset=UTF-8');
    exit("<script language='javascript'>alert('安全提醒！您已登陆！');window.location.href='./';</script>");
}

if ($conf['template_layout_off'] == 0 && false != ($path = \core\Template::checkUserTpl('findpwd'))) {
    include_once $path;
    die;
}

$title = '找回密码';
include './head2.php';
?>
<style type="text/css">
.cmds-bg{
    position: fixed;
    width: 100%;
    height: 100%;
    padding: 0;
    margin: 0;
    top: 0;
}
</style>
<img style="" src="<?php echo $background_image; ?>" alt="Full Background" class="cmds-bg" ondragstart="return false;" oncontextmenu="return false;">
<div class="col-xs-12 col-sm-10 col-md-8 col-lg-4 center-block" style="float: none;padding-top: 7%;">
    <div class="block panel panel-body">
        <div class="block-title">
            <div class="block-options pull-right">
            <a href="../" class="btn btn-effect-ripple btn-default toggle-bordered enable-tooltip">返回首页</a>
            </div>
            <h2><i class="fa fa-unlock"></i>&nbsp;&nbsp;<b>找回密码</b></h2>
        </div>
            <ul class="nav nav-tabs nav-tabs-alt">
            <li class="active" style="width:50%"><a onclick="qrload()" href="#qrcode" data-toggle="tab" aria-expanded="true"><center>扫码找回</center></a></li>
            <li style="width:50%" class=""><a onclick="cleartime()" href="#mbtel" data-toggle="tab" aria-expanded="false"><center>手机找回</center></a></li>
            </ul>
               <div id="myTabContent" class="tab-content" style=" margin-top: 2px;">
                   <div class="tab-pane fade active in dasd" id="qrcode">
                        <div class="form-group" style="text-align: center;">
                            <div class="list-group-item list-group-item-info" style="font-weight: bold;" id="login">
                                <span id="loginmsg">请使用QQ手机版扫描二维码</span><span id="loginload" style="padding-left: 10px;color: #790909;">.</span>
                            </div>
                            <div id="qrimg">
                            </div>
                            <div class="list-group-item" id="mobile" style="display:none;"><button type="button" id="mlogin" onclick="mloginurl()" class="btn btn-warning btn-block">跳转QQ快捷登录</button><br/><button type="button" onclick="loadScript()" class="btn btn-success btn-block">我已完成登录</button></div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="mbtel">
                        <div class="alert alert-info">
                            1.如果你在本站有多个注册账号且不清楚绑定的哪个，请联系网站客服找回！<br>
                            2.通过本方式找回的账号为你在本平台最新注册的分站账户！历史注册的将无法通过本方式找回
                        </div>
                        <div class="form-group">
                            <div class="input-group"><div class="input-group-addon">绑定手机号</div>
                            <input type="text" name="tel" id="tel" class="form-control"/>
                        </div></div>
                        <div class="form-group">
                            <div class="input-group"><div class="input-group-addon">填写验证码</div>
                            <input type="text" name="code" id="code" class="form-control"/>
                            <span onclick="sendcode()" class="input-group-addon">发送</span>
                        </div></div>
                        <div class="form-group" style="text-align: center;">
                                <br/><button type="button" onclick="mobileLoad()" class="btn btn-success btn-block">确认登录</button>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                    <a href="login.php" class="btn btn-primary btn-rounded"><i class="fa fa-user"></i>&nbsp;返回登录</a>
                    <a href="reg.php" class="btn btn-danger btn-rounded" style="float:right;"><i class="fa fa-user-plus"></i>&nbsp;注册用户</a>
                    </div>
                </div>
        </div>
      </div>
    </div>
  </div>
<script src="<?php echo $cdnpublic ?>jquery/1.12.4/jquery.min.js"></script>
<script src="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="../assets/js/qrlogin.js?ver=<?php echo VERSION ?>"></script>
</body>
</html>