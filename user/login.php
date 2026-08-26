<?php
/**
 * 登录
 **/
$is_defend = true;
include "../includes/common.php";
$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
if ($act == 'login') {
    $user = input('post.user', 1);
    $pwd  = input('post.pwd', 1);
    $row  = $DB->get_row("SELECT * FROM pre_site WHERE user= ? limit 1", array($user));
    if ($row && $user === $row['user'] && checkPwd($pwd, $row['pwd'], $row['salt'])) {
        if ($row['status'] == 0 && $row['user'] == $user) {
            @header('Content-Type: text/html; charset=UTF-8');
            $result = array('code' => -1, "msg" => "当前分站已关闭，无法登陆！<br>关闭原因：" . ($row['closure'] != '' ? $row['closure'] : '站点运行异常临时封禁处理') . "<br>如有疑问，请联系站长QQ" . $conf['zzqq'] . "处理", "row" => $row);
            setcookie("user_token", "", time() - 604800, '/');
            exit(json_encode($result));
        } elseif ($conf['cloud_open'] == 1 && $conf['fenzhan_tel'] == 1) {
            if (checkMessage('yidilogin') && $row['tel'] != "" && $row['loginIp'] != "" && $row['loginIp'] != $clientip) {
                $telcode = daddslashes($_POST['telcode']);
                $userid  = md5($cookiesid . ' 11111111' . $telcode);
                if (empty($telcode)) {
                    $result = array('code' => -2, 'msg' => '验证码不能为空！');
                } elseif ($_SESSION['code_userid'] != $userid) {
                    $result = array('code' => -2, 'msg' => '异地登录，请先发送验证码！');
                } else {
                    if ($_SESSION['code_userid'] != $userid) {
                        $result = array('code' => -1, 'msg' => '验证码错误或已过期！', 'data' => null);
                    } else {
                        $logrow = $DB->get_row("select * from pre_codelog where `tel`= ? and `code`= ? order by id desc limit 1", array($row['tel'], $telcode));
                        if (!$logrow) {
                            $result = array('code' => -1, 'msg' => '该验证码不正确！');
                        } else {
                            if ($logrow['status'] == 1) {
                                $result = array('code' => -1, 'msg' => '该验证码已失效，请重新发送！');
                            } elseif ((time() - strtotime($logrow['addtime'])) > (10 * 60)) {
                                $result = array('code' => -1, 'msg' => '该验证码已过期！');
                            } else {
                                $DB->query("update `pre_codelog` set `status`='1' where id= ? limit 1", array($logrow['id']));
                            }
                            unset($_SESSION['code_userid']);
                        }

                    }
                }

                if (isset($result) && is_array($result)) {
                    exit(json_encode($result));
                }
            }
        }

        if (\core\Template::isNeedCodeCaptcha('login')) {
            if ($conf['captcha_open'] == 1 && $conf['captcha_id'] && $conf['captcha_key']) {
                captchaCheck('login');
            } else {
                if ($conf['captcha_open'] == 2) {
                    $code = isset($_POST['code']) ? input('post.code', 1) : input('get.code', 1);
                    if (!$code || !captcha_check($code)) {
                        exit('{"code":3,"msg":"验证码错误"}');
                    }
                }
            }
        }

        // if ($row['power']>0 && $_SERVER['HTTP_HOST']!=$row['siteurl'] && $_SERVER['HTTP_HOST']!=$row['siteurl2']) {
        //     $result = array('code' => 1,"msg" => "网址不匹配","siteurl" => $row['siteurl']);
        //     exit(json_encode($result));
        // }
        $session = md5($user . getEncodePwd($pwd, $row['salt'], $row['pwd']) . $password_hash);
        $token   = authcode($row['zid'] . "\t" . $session, 'ENCODE', SYS_KEY);
        setcookie("user_token", $token, time() + 604800, '/');
        $DB->query("update `pre_site` set `loginIp`= ? where zid= ? limit 1", array($clientip, $row['zid']));
        fzlog_result($row['zid'], '分站登录', '用户名：' . $user . '；登录IP：' . $clientip, '分站登录成功', 1);
        @header('Content-Type: text/html; charset=UTF-8');
        exit('{"code":0,"msg":"succ"}');
    } else {
        @header('Content-Type: text/html; charset=UTF-8');
        fzlog_result($siterow['zid'] ? $siterow['zid'] : 1, '分站登录', '用户名：' . $user . '；登录IP：' . $clientip, '用户名或密码不正确！', 0);
        exit('{"code":-1,"msg":"用户名或密码不正确！"}');
    }

} elseif ($act == 'sendCode') {
    $user = strip_tags(daddslashes($_POST['user']));
    if (empty($user)) {
        $result = array('code' => -1, 'msg' => '请先填写登录账号再操作！');
        exit(json_encode($result));
    }
    $row = $DB->get_row("SELECT * FROM pre_site WHERE user= ? limit 1", array($user));
    if ($row) {
        $tel    = daddslashes($row['tel']);
        $result = sendCode($tel, 4);
    } else {
        $result = array('code' => -1, 'msg' => '该账号不存在！');
    }
    exit(json_encode($result));
} elseif (isset($_GET['logout'])) {
    setcookie("user_token", "", time() - 604800, '/');
    exit("<script language='javascript'>window.location.href='./login.php?goto=" . $goto . "';</script>");
} elseif ($isLogin2 == 1) {
    exit("<script language='javascript'>window.location.href='./';</script>");
}

if ($conf['template_layout_off'] == 0 && false != ($path = \core\Template::checkUserTpl('login'))) {
    include_once $path;
    die;
}

$weburl = str_ireplace('user/', '', $weburl);
if (isset($_GET['back'])) {
    $goto = preg_match('/http/', $_GET['back']) ? $_GET['back'] : $weburl . '?' . ltrim($_GET['back'], '?');
} elseif (isset($_GET['goto'])) {
    $goto = preg_match('/http/', $_GET['goto']) ? $_GET['goto'] : $weburl . '?' . ltrim($_GET['goto'], '?');
} else {
    $goto = isset($_SERVER["HTTP_REFERER"]) && $_SERVER["HTTP_REFERER"] ? $_SERVER["HTTP_REFERER"] : '';
}

$title = '用户登录';
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title><?php echo $title ?>-<?php echo $conf['sitename'] ?></title>
  <meta name="description" content="<?php echo $conf['sitename'] ?>,用户登录">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <link crossorigin="anonymous" href="<?php echo $cdnpublic ?>animate.css/3.7.2/animate.min.css" rel="stylesheet">
  <link href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
  <style>
    :root {
      --primary-color: #3498db;
      --secondary-color: #2c3e50;
      --accent-color: #e74c3c;
      --light-color: #f8f9fa;
      --dark-color: #1a1a2e;
      --success-color: #2ecc71;
      --error-color: #e74c3c;
      --transition-speed: 0.3s;
      --border-radius: 8px;
      --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
      --card-bg: rgba(255, 255, 255, 0.92);
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Segoe UI', 'PingFang SC', 'Microsoft YaHei', sans-serif;
      background: linear-gradient(135deg, var(--dark-color) 0%, #16213e 100%);
      color: #333;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
      position: relative;
      overflow-x: hidden;
    }

    body::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiPjxkZWZzPjxwYXR0ZXJuIGlkPSJwYXR0ZXJuIiB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHBhdHRlcm5Vbml0cz0idXNlclNwYWNlT25Vc2UiIHBhdHRlcm5UcmFuc2Zvcm09InJvdGF0ZSg0NSkiPjxyZWN0IHdpZHRoPSIxIiBoZWlnaHQ9IjQwIiBmaWxsPSJyZ2JhKDI1NSwyNTUsMjU1LDAuMDUpIi8+PC9wYXR0ZXJuPjwvZGVmcz48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSJ1cmwoI3BhdHRlcm4pIi8+PC9zdmc+');
      z-index: -1;
    }

    .login-container {
      width: 100%;
      max-width: 420px;
      z-index: 10;
      animation: fadeInUp 0.6s ease-out;
    }

    .login-card {
      background: var(--card-bg);
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      overflow: hidden;
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      transform: translateY(0);
      transition: transform 0.4s ease, box-shadow 0.4s ease;
    }

    .login-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
    }

    .login-header {
      padding: 30px 30px 20px;
      text-align: center;
      position: relative;
    }

    .login-avatar {
      width: 90px;
      height: 90px;
      border-radius: 50%;
      border: 3px solid white;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
      margin: 0 auto 15px;
      background-color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .login-avatar:hover {
      transform: scale(1.05);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .login-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .login-title {
      font-size: 24px;
      font-weight: 600;
      color: var(--secondary-color);
      margin-bottom: 5px;
      letter-spacing: 0.5px;
    }

    .login-subtitle {
      color: #777;
      font-size: 14px;
      margin-bottom: 20px;
    }

    .login-body {
      padding: 0 30px 30px;
    }

    .input-group {
      position: relative;
      margin-bottom: 25px;
    }

    .input-icon {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #aaa;
      transition: color var(--transition-speed);
      z-index: 2;
    }

    .form-input {
      width: 100%;
      padding: 14px 20px 14px 45px;
      border: 1px solid #e1e5eb;
      border-radius: var(--border-radius);
      background: rgba(255, 255, 255, 0.7);
      font-size: 16px;
      transition: all var(--transition-speed);
      box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
      color: #333;
    }

    .form-input:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
      outline: none;
      background: white;
    }

    .form-input:focus + .input-icon {
      color: var(--primary-color);
    }

    .captcha-container {
      margin: 20px 0;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .captcha-box {
      width: 100%;
      min-height: 80px;
      background: #f5f7fa;
      border-radius: var(--border-radius);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 15px;
      padding: 15px;
    }

    .captcha-img {
      max-width: 100%;
      height: auto;
      border-radius: 4px;
      cursor: pointer;
      transition: opacity 0.3s;
    }

    .captcha-img:hover {
      opacity: 0.9;
    }

    .btn-login {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, var(--primary-color) 0%, #2980b9 100%);
      border: none;
      border-radius: var(--border-radius);
      color: white;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all var(--transition-speed);
      position: relative;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
      letter-spacing: 1px;
    }

    .btn-login:hover {
      transform: translateY(-2px);
      box-shadow: 0 7px 20px rgba(52, 152, 219, 0.4);
    }

    .btn-login:active {
      transform: translateY(1px);
      box-shadow: 0 2px 10px rgba(52, 152, 219, 0.4);
    }

    .btn-login::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.1);
      transform: translateX(-100%);
      transition: transform 0.4s ease;
    }

    .btn-login:hover::after {
      transform: translateX(100%);
    }

    .login-footer {
      display: flex;
      justify-content: space-between;
      padding-top: 20px;
      margin-top: 15px;
      border-top: 1px solid #eee;
    }

    .footer-link {
      display: inline-block;
      padding: 8px 16px;
      border-radius: 30px;
      color: #555;
      font-size: 14px;
      text-decoration: none;
      transition: all var(--transition-speed);
      background: rgba(0, 0, 0, 0.03);
      border: 1px solid #eee;
    }

    .footer-link i {
      margin-right: 5px;
    }

    .footer-link:hover {
      background: var(--primary-color);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
      border-color: var(--primary-color);
    }

    .footer-link.find-pwd:hover {
      background: var(--success-color);
      border-color: var(--success-color);
      box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3);
    }

    .footer-link.register:hover {
      background: var(--accent-color);
      border-color: var(--accent-color);
      box-shadow: 0 4px 10px rgba(231, 76, 60, 0.3);
    }

    .sms-group {
      display: flex;
      gap: 10px;
      margin-bottom: 25px;
    }

    .sms-group .form-input {
      flex: 1;
    }

    .btn-send {
      min-width: 120px;
      padding: 0 15px;
      background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
      border: none;
      border-radius: var(--border-radius);
      color: white;
      font-size: 14px;
      cursor: pointer;
      transition: all var(--transition-speed);
      box-shadow: 0 4px 10px rgba(142, 68, 173, 0.3);
      white-space: nowrap;
    }

    .btn-send:hover {
      transform: translateY(-2px);
      box-shadow: 0 7px 15px rgba(142, 68, 173, 0.4);
    }

    .btn-send:disabled {
      background: #bdc3c7;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    .loading-dots {
      display: flex;
      justify-content: center;
      padding: 20px 0;
    }

    .dot {
      width: 10px;
      height: 10px;
      background: var(--primary-color);
      border-radius: 50%;
      margin: 0 5px;
      animation: dotPulse 1.5s infinite ease-in-out;
    }

    .dot:nth-child(2) {
      animation-delay: 0.2s;
    }

    .dot:nth-child(3) {
      animation-delay: 0.4s;
    }

    .dot:nth-child(4) {
      animation-delay: 0.6s;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    @keyframes dotPulse {
      0%, 60%, 100% {
        transform: translateY(0);
      }
      30% {
        transform: translateY(-10px);
      }
    }

    /* 响应式设计 */
    @media (max-width: 576px) {
      .login-container {
        max-width: 100%;
      }
      
      .login-header {
        padding: 25px 20px 15px;
      }
      
      .login-body {
        padding: 0 20px 25px;
      }
      
      .login-footer {
        flex-direction: column;
        gap: 10px;
      }
      
      .footer-link {
        width: 100%;
        text-align: center;
      }
      
      .sms-group {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="login-avatar">
          <img src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq']; ?>&spec=100" alt="站长头像">
        </div>
        <h1 class="login-title">用户登录</h1>
        <p class="login-subtitle"><?php echo $conf['sitename'] ?> - 安全可靠的系统</p>
      </div>
      
      <div class="login-body">
        <div class="input-group">
          <i class="fa fa-user input-icon"></i>
          <input type="text" name="user" class="form-input" placeholder="请输入您的用户名" autocomplete="off">
        </div>
        
        <div class="input-group">
          <i class="fa fa-lock input-icon"></i>
          <input type="password" name="pwd" class="form-input" placeholder="请输入您的密码">
        </div>
        
        <?php if (\core\Template::isNeedCodeCaptcha('login')) { ?>
          <?php if ($conf['captcha_open'] > 0) { ?>
            <?php if ($conf['captcha_open'] == 1) { ?>
              <div class="captcha-container">
                <div class="captcha-box">
                  <input type="hidden" name="captcha_type" value="1"/>
                  <div id="captcha">
                    <div id="captcha_text">正在加载验证码</div>
                    <div id="captcha_wait" style="display: none;">
                      <div class="loading-dots">
                        <div class="dot"></div>
                        <div class="dot"></div>
                        <div class="dot"></div>
                        <div class="dot"></div>
                      </div>
                    </div>
                  </div>
                </div>
                <div id="captchaform"></div>
              </div>
            <?php } else { ?>
              <div class="captcha-container" id="codeform">
                <div class="captcha-box">
                  <img id="codeimg" src="./code.php?r=<?php echo time(); ?>" class="captcha-img" onclick="this.src='./code.php?r='+Math.random();" title="点击更换验证码">
                </div>
                <div class="input-group">
                  <i class="fa fa-check-square input-icon"></i>
                  <input type="text" name="code" class="form-input" placeholder="请输入验证码">
                </div>
              </div>
            <?php } ?>
          <?php } ?>
        <?php } ?>
        
        <div class="sms-group" id="display_code" style="display: none;">
          <input type="text" id="telcode" class="form-input" placeholder="请输入短信验证码">
          <button id="sendCode" class="btn-send">发送验证码</button>
        </div>
        
        <button class="btn-login" onclick="login()">登 录</button>
        
        <div class="login-footer">
          <a href="findpwd.php" class="footer-link find-pwd"><i class="fa fa-unlock"></i> 找回密码</a>
          <a href="reg.php" class="footer-link register"><i class="fa fa-user-plus"></i> 代理注册</a>
        </div>
      </div>
    </div>
  </div>

  <!-- scripts -->
  <script src="<?php echo $cdnpublic ?>jquery/3.1.1/jquery.min.js"></script>
  <script src="<?php echo $cdnpublic ?>layer/3.4.0/layer.js<?php echo $jsver ?>"></script>
  <script type="text/javascript">
    var goto = '<?php echo $goto ? $goto : './index.php'; ?>';
    var handlerEmbed = function(captchaObj) {
        captchaObj.appendTo('#captcha');
        captchaObj.onReady(function() {
            $('#captcha_text').hide();
            $("#captcha_wait").hide();
        }).onSuccess(function() {
            var result = captchaObj.getValidate();
            if (!result) {
                return alert('请完成验证');
            }
            $("#captchaform").html('<input type="hidden" name="geetest_challenge" value="' + result.geetest_challenge + '" /><input type="hidden" name="geetest_validate" value="' + result.geetest_validate + '" /><input type="hidden" name="geetest_seccode" value="' + result.geetest_seccode + '" />');
        });
    };

    function intGeetest() {
        $.getScript("//static.geetest.com/static/tools/gt.js", function(res, status){
            if (status=='success') {
                console.log('geetest is loading ok！');
                $.ajax({
                    url: "../ajax.php?act=captcha&t=" + (new Date()).getTime(),
                    type: "get",
                    dataType: "json",
                    success: function(data) {
                        $('#captcha_wait').show();
                        initGeetest({
                            gt: data.gt,
                            challenge: data.challenge,
                            new_captcha: data.new_captcha,
                            product: "popup",
                            width: "100%",
                            offline: !data.success
                        }, handlerEmbed);
                    }
                });
            }
            else{
                console.log('getScript.res', res)
                layer.msg("极致验证依赖加载失败，请刷新再试或联系站长处理");
                return false;
            }
        });
    }

    function login() {
        var user = $("input[name=user]").val();
        var pwd = $("input[name=pwd]").val();
        var telcode = $("#telcode").val();
        if (user == '' || pwd == '') {
            layer.alert("请确保登录账号密码不能为空");
            return false;
        }
        var adddata = {};
        if ($("#captcha").length > 0) {
            var geetest_challenge = $("input[name='geetest_challenge']").val();
            var geetest_validate = $("input[name='geetest_validate']").val();
            var geetest_seccode = $("input[name='geetest_seccode']").val();
            if (geetest_challenge == undefined) {
                intGeetest();
                return layer.alert('请先完成滑动验证！');
            }
            adddata = {
                geetest_challenge: geetest_challenge,
                geetest_validate: geetest_validate,
                geetest_seccode: geetest_seccode
            };
        }
        else{
            if ($("#vcodeform").length > 0) $("#vcodeform").show();
            if ($("#codeform").length > 0) $("#codeform").show();
            if ($("input[name='code']").length > 0) {
                adddata = {
                    code: $("input[name='code']").val()
                };
                if (!adddata.code) {
                    layer.alert('验证码不能为空！');
                    return false;
                }
            }
        }
        var ii = layer.load(2, {
            shade: [0.1, '#fff']
        });
        $.ajax({
            type: "POST",
            url: "?act=login",
            dataType: "json",
            data: Object.assign({
                user: user,
                pwd: pwd,
                telcode: telcode
            }, adddata),
            success: function(data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.msg('登录成功！正在进入后台..');
                    setTimeout(function() {
                        window.location.href = goto;
                    }, 800);
                } else if (data.code == 1) {
                    window.location.href = 'http://' + data.siteurl + '/user/login.php';
                } else if (data.code == -2) {
                    layer.alert(data.msg);
                    $("#display_code").show();
                }else if (data.code == 3) {
                    layer.msg(data.msg);
                    if ($("img#codeimg").length > 0) {
                        $("img#codeimg").click();
                    }
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function(e, r, d) {
                layer.close(ii);
                layer.alert('服务器错误，请稍后再试试，' + d + '！<br>多次出现请联系客服<?php echo $conf['
                    zzqq '] ?>处理');
            }
        });
    }
    
    $("#sendCode").click(function() {
        var user = $("input[name=user]").val();
        var btn = $(this);
        var originalText = btn.text();
        var count = 60;
        
        if (user == '') {
            layer.alert("请先填写登录账号再操作！");
            return false;
        }
        
        btn.prop('disabled', true).text('发送中...');
        
        var ii = layer.load(2, {
            shade: [0.1, "#fff"]
        });
        
        $.ajax({
            type: "POST",
            url: "?act=sendCode&type=4",
            data: 'user=' + user,
            dataType: "json",
            success: function(data) {
                layer.close(ii);
                if (data.code == 0) {
                    layer.msg(data.msg);
                    
                    // 倒计时效果
                    var timer = setInterval(function() {
                        if (count <= 0) {
                            clearInterval(timer);
                            btn.prop('disabled', false).text(originalText);
                        } else {
                            btn.text(count + '秒后重试');
                            count--;
                        }
                    }, 1000);
                } else {
                    layer.alert(data.msg);
                    btn.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                layer.close(ii);
                layer.alert('发送请求失败，请稍后再试');
                btn.prop('disabled', false).text(originalText);
            }
        });
    });
    
    window.onload = function() {
        if ($("#captcha").length > 0) {
            intGeetest();
        }
        
        // 输入框聚焦效果增强
        $('.form-input').on('focus', function() {
            $(this).parent().addClass('focused');
        }).on('blur', function() {
            $(this).parent().removeClass('focused');
        });
    };
  </script>
</body>
</html>