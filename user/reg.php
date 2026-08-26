<?php
/**
 * 注册用户
 **/
$is_defend = true;
include "../includes/common.php";

if ($conf['template_layout_off'] == 0 && false != ($path = \core\Template::checkUserTpl('reg'))) {
    require_once $path;
    die;
}

if ($isLogin2 == 1) {
    @header('Content-Type: text/html; charset=UTF-8');
    exit("<script language='javascript'>alert('安全提醒！您已登陆！');window.location.href='./';</script>");
}

if (!$conf['user_open'] && $conf['fenzhan_buy'] == 1) {
    exit("<script language='javascript'>window.location.href='./regsite.php';</script>");
} elseif (!$conf['user_open']) {
    @header('Content-Type: text/html; charset=UTF-8');
    exit("<script language='javascript'>alert('未开放新用户注册');window.location.href='./';</script>");
}
$title = '用户注册';

// $addsalt             = md5(mt_rand(0, 999) . time());
// $_SESSION['addsalt'] = $addsalt;
$addsalt = md5(rand(1111, 9999) . x_real_ip() . time());
session_set($addsalt, 600);
$x          = new \core\HieroGlyphy();
$addsalt_js = $x->hieroglyphyString($addsalt);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title><?php echo $title ?>-<?php echo $conf['sitename'] ?></title>
  <meta name="description" content="<?php echo $conf['sitename'] ?>,用户注册">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <link crossorigin="anonymous" href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
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
      --border-radius: 10px;
      --box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
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

    .register-container {
      width: 100%;
      max-width: 480px;
      z-index: 10;
      animation: fadeInUp 0.8s ease-out;
    }

    .register-card {
      background: var(--card-bg);
      border-radius: var(--border-radius);
      box-shadow: var(--box-shadow);
      overflow: hidden;
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      transform: translateY(0);
      transition: transform 0.4s ease, box-shadow 0.4s ease;
    }

    .register-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 20px 45px rgba(0, 0, 0, 0.25);
    }

    .register-header {
      padding: 30px 30px 15px;
      text-align: center;
      background: linear-gradient(135deg, var(--primary-color) 0%, #2980b9 100%);
      position: relative;
      color: white;
    }

    .register-avatar {
      width: 90px;
      height: 90px;
      border-radius: 50%;
      border: 3px solid rgba(255, 255, 255, 0.3);
      margin: 0 auto 15px;
      background-color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .register-avatar:hover {
      transform: scale(1.05);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .register-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .register-title {
      font-size: 28px;
      font-weight: 600;
      margin-bottom: 5px;
      letter-spacing: 0.5px;
      color: white;
    }

    .register-subtitle {
      color: rgba(255, 255, 255, 0.85);
      font-size: 15px;
      margin-bottom: 5px;
    }

    .register-body {
      padding: 30px;
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
      margin: 25px 0;
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

    .btn-register {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, var(--success-color) 0%, #27ae60 100%);
      border: none;
      border-radius: var(--border-radius);
      color: white;
      font-size: 17px;
      font-weight: 600;
      cursor: pointer;
      transition: all var(--transition-speed);
      position: relative;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
      letter-spacing: 1px;
      margin: 10px 0 20px;
    }

    .btn-register:hover {
      transform: translateY(-2px);
      box-shadow: 0 7px 20px rgba(46, 204, 113, 0.4);
    }

    .btn-register:active {
      transform: translateY(1px);
      box-shadow: 0 2px 10px rgba(46, 204, 113, 0.4);
    }

    .btn-register::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.15);
      transform: translateX(-100%);
      transition: transform 0.4s ease;
    }

    .btn-register:hover::after {
      transform: translateX(100%);
    }

    .register-footer {
      display: flex;
      justify-content: space-between;
      padding-top: 20px;
      margin-top: 15px;
      border-top: 1px solid #eee;
    }

    .footer-link {
      display: inline-block;
      padding: 10px 20px;
      border-radius: 30px;
      color: #555;
      font-size: 14px;
      text-decoration: none;
      transition: all var(--transition-speed);
      background: rgba(0, 0, 0, 0.03);
      border: 1px solid #eee;
    }

    .footer-link i {
      margin-right: 8px;
    }

    .footer-link:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .footer-link.find-pwd {
      background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
      color: white;
      border-color: #2980b9;
    }

    .footer-link.find-pwd:hover {
      box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
    }

    .footer-link.login-link {
      background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
      color: white;
      border-color: #8e44ad;
    }

    .footer-link.login-link:hover {
      box-shadow: 0 4px 10px rgba(142, 68, 173, 0.3);
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
        transform: translateY(30px);
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
      .register-container {
        max-width: 100%;
      }
      
      .register-header {
        padding: 25px 20px 15px;
      }
      
      .register-body {
        padding: 20px 15px;
      }
      
      .register-footer {
        flex-direction: column;
        gap: 12px;
      }
      
      .footer-link {
        width: 100%;
        text-align: center;
      }
    }
  </style>
</head>
<body>
  <div class="register-container">
    <div class="register-card">
      <div class="register-header">
        <div class="register-avatar">
          <img src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq']; ?>&spec=100" alt="站长头像">
        </div>
        <h1 class="register-title">新用户注册</h1>
        <p class="register-subtitle"><?php echo $conf['sitename'] ?> - 开启您的代理之旅</p>
      </div>
      
      <div class="register-body">
        <div class="input-group">
          <i class="fa fa-user input-icon"></i>
          <input type="text" id="user" name="user" class="form-input" required="required" placeholder="输入5~12位的登录用户名" autocomplete="off">
        </div>
        
        <div class="input-group">
          <i class="fa fa-lock input-icon"></i>
          <input type="text" id="pwd" name="pwd" class="form-input" required="required" placeholder="输入6~16位的登录密码">
        </div>
        
        <div class="input-group">
          <i class="fa fa-qq input-icon"></i>
          <input type="text" id="qq" name="qq" class="form-input" required="required" placeholder="输入QQ号，用于找回密码">
        </div>
        
        <?php if (\core\Template::isNeedCodeCaptcha('reg')) { ?>
          <?php if ($conf['captcha_open'] > 0) { ?>
            <?php if ($conf['captcha_open'] == 1) { ?>
              <div class="captcha-container">
                <input type="hidden" name="captcha_type" value="1"/>
                <div class="captcha-box">
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
              <div class="captcha-container">
                <div class="captcha-box">
                  <img id="codeimg" src="./code.php?r=<?php echo time(); ?>" class="captcha-img" onclick="this.src='./code.php?r='+Math.random();" title="点击更换验证码">
                </div>
                <div class="input-group">
                  <i class="fa fa-check-square input-icon"></i>
                  <input type="text" name="code" class="form-input" required="required" placeholder="输入验证码">
                </div>
              </div>
            <?php } ?>
          <?php } ?>
        <?php } ?>
        
        <button class="btn-register" id="submit_reg">立即注册</button>
        
        <div class="register-footer">
          <a href="findpwd.php" class="footer-link find-pwd"><i class="fa fa-unlock"></i> 找回密码</a>
          <a href="login.php" class="footer-link login-link"><i class="fa fa-user"></i> 返回登录</a>
        </div>
      </div>
    </div>
  </div>

  <script src="<?php echo $cdnpublic ?>jquery/3.1.1/jquery.min.js"></script>
  <script src="<?php echo $cdnpublic ?>layer/3.4.0/layer.js<?php echo $jsver ?>"></script>
  <script>
    var hashsalt = <?php echo $addsalt_js ?>;
    
    // 极验验证码初始化
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
                layer.msg("极致验证依赖加载失败，请刷新再试或联系站长处理");
                return false;
            }
        });
    }
    
    $(document).ready(function() {
        // 初始化验证码
        if ($("#captcha").length > 0) {
            intGeetest();
        }
        
        // 注册按钮点击事件
        $("#submit_reg").click(function() {
            var user = $("#user").val();
            var pwd = $("#pwd").val();
            var qq = $("#qq").val();
            
            // 基本验证
            if (!user || user.length < 5 || user.length > 12) {
                layer.msg("用户名长度应为5~12位");
                return false;
            }
            
            if (!pwd || pwd.length < 6 || pwd.length > 16) {
                layer.msg("密码长度应为6~16位");
                return false;
            }
            
            if (!qq || !/^\d{5,12}$/.test(qq)) {
                layer.msg("请输入有效的QQ号");
                return false;
            }
            
            // 显示加载动画
            var ii = layer.load(2, {
                shade: [0.1, '#fff']
            });
            
            // 准备提交数据
            var postData = {
                user: user,
                pwd: pwd,
                qq: qq,
                salt: hashsalt
            };
            
            // 如果有验证码
            if ($("input[name='code']").length > 0) {
                postData.code = $("input[name='code']").val();
            }
            
            // 如果有极验验证
            if ($("input[name='geetest_challenge']").length > 0) {
                postData.geetest_challenge = $("input[name='geetest_challenge']").val();
                postData.geetest_validate = $("input[name='geetest_validate']").val();
                postData.geetest_seccode = $("input[name='geetest_seccode']").val();
            }
            
            // 提交注册请求
            $.ajax({
                type: "POST",
                url: "../ajax.php?act=reguser",
                data: postData,
                dataType: "json",
                success: function(data) {
                    layer.close(ii);
                    if (data.code == 0) {
                        layer.msg('注册成功！即将跳转到登录页面...');
                        setTimeout(function() {
                            window.location.href = 'login.php';
                        }, 1500);
                    } else {
                        layer.alert(data.msg);
                        // 刷新验证码
                        if ($("#codeimg").length > 0) {
                            $("#codeimg").click();
                        }
                    }
                },
                error: function() {
                    layer.close(ii);
                    layer.alert('服务器错误，请稍后再试');
                }
            });
        });
        
        // 输入框聚焦效果
        $('.form-input').on('focus', function() {
            $(this).parent().addClass('focused');
        }).on('blur', function() {
            $(this).parent().removeClass('focused');
        });
    });
  </script>
</body>
</html>