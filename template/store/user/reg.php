<?php
if (!defined('IN_CRONLITE')) {
    die();
}

@header('Content-Type: text/html; charset=UTF-8');
$addsalt = session_get();
if (empty($addsalt)) {
    $addsalt = md5(rand(1111, 9999) . x_real_ip() . time());
    session_set($addsalt, 600);
}
$x          = new \core\HieroGlyphy();
$addsalt_js = $x->hieroglyphyString($addsalt);
?>
<!doctype html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=no;"/>
    <title>注册账号 - <?php echo $conf['sitename']; ?></title>
    <meta name="keywords" content="<?php echo $conf['keywords'] ?>">
    <meta name="description" content="<?php echo $conf['description'] ?>">
    <link rel="shortcut icon" href="<?php echo $conf['default_ico_url'] ?>">
    <link href="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/store/css/foxui.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/store/css/style.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/template/store/css/iconfont.css">
    <link href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/main.css">
    <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/css/common.css">
  <!--[if lt IE 9]>
    <script src="<?php echo $cdnpublic ?>html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="<?php echo $cdnpublic ?>respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<?php echo str_replace('body', 'html', $background_css) ?>
<style>
body {
    width: 100%;
    max-width: 650px;
    margin: auto;
    background: #f3f3f3;
    line-height: 24px;
    font: 14px Helvetica Neue,Helvetica,PingFang SC,Tahoma,Arial,sans-serif;
}
.label{
    color: unset;
    line-height: 1.8;
}
.account-main{
    height: 100% !important;
}
a {
    text-decoration:none;
}
a:hover{
    text-decoration:none;
}
.fui-modal{z-index: 20;}
</style>
<body>
<div id="body">
    <div class="fui-page-group statusbar" style="max-width: 650px;left: auto;">
        <form id="form1">
            <div class="fui-modal popup-modal in">
                <div class="account-layer login" style="max-height:unset;margin:-13rem 0 0 -7.75rem;">
                        <div class="account-main">
                            <div class="account-back"><i class="icon icon-back"></i></div>
                            <div class="account-title">注　册　账　号</div>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <span class="fa fa-user"></span>
                                    </div>
                                    <input type="text" id="user" name="user" value="" class="form-control" required="required" placeholder="输入登录用户名"/>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <span class="fa fa-lock"></span>
                                    </div>
                                    <input type="text" id="pwd" name="pwd" class="form-control" required="required" placeholder="输入6位以上密码"/>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <span class="fa fa-qq"></span>
                                    </div>
                                  <input type="text" id="qq" name="qq" class="form-control" required="required" placeholder="输入QQ号，用于找回密码"/>
                                </div>
                            </div>
                            <?php if ($conf['captcha_open'] >= 1 && $conf['captcha_open_reg'] == 1) {?>
                            <input type="hidden" name="captcha_type" value="1"/>
                            <div id="captcha" style="margin: auto;">
                                <div id="captcha_text">
                                    正在加载验证码
                                </div>
                                <div id="captcha_wait">
                                    <div class="loading">
                                        <div class="loading-dot"></div>
                                        <div class="loading-dot"></div>
                                        <div class="loading-dot"></div>
                                        <div class="loading-dot"></div>
                                    </div>
                                </div>
                            </div>
                            <div id="captchaform"></div>
                            <br/>
                            <?php } else {?>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-addon"><span class="fa fa-adjust"></span></div>
                                    <input type="text" name="code" class="form-control input-lg" required="required" placeholder="输入验证码"/>
                                    <span class="input-group-addon" style="padding: 0">
                                        <img id="codeimg" src="./code.php?r=<?php echo time(); ?>" height="43" onclick="this.src='./code.php?r='+Math.random();" title="点击更换验证码">
                                    </span>
                                </div>
                            </div>
                            <?php }?>
                        </div>
                        <div class="account-btn" id="submit_reg">注册</div>
                        <div style="text-align:center">
                            <i class="fa fa-user"></i>&nbsp;已有账号？<a href="login.php">点此登录</a>
                        </div>
                        <div style="text-align: center;margin-bottom: 5px;color:#999;margin-top: 15px;">
                        <?php if ($conf['login_qq'] == 1): ?>
                        <hr style="border-top: 1px solid rgba(0,0,0,.1);">   <div style="color:#999;position: relative;top: -12px;width: 100px;background-color: white;margin: auto">            第三方登录</div>
                        <div onclick="javascript:connect('qq')" style="    width: 42px;height:42px;     border: 1px solid rgba(213, 213, 213, 1); border-radius: 21px;  margin: 15px auto ; margin-top: 10px;   background-image: url(<?php echo $cdnserver ?>assets/img/logo2.png);background-size: 100%">
                        <?php endif;?>
                        </div>
                        <div style="text-align:center;"><a href="javascript:goback();" class="">返回</a></div>
                        <br/>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="fui-navbar" style="z-index: 100000;max-width: 650px;">
        <a href="../" class="nav-item  "> <span class="icon icon-home"></span> <span class="label">首页</span> </a>
        <a href="../?mod=query" class="nav-item "> <span class="icon icon-dingdan1"></span> <span class="label">订单</span> </a>
        <a href="../?mod=cart" class="nav-item " <?php if ($conf['shoppingcart'] == 0) {?>style="display:none"<?php }?>> <span class="icon icon-cart2"></span> <span class="label">购物车</span> </a>
        <a href="../?mod=kf" class="nav-item "> <span class=" icon icon-service1"></span> <span class="label">客服</span> </a>
        <a href="./" class="nav-item active"> <span class="icon icon-person2"></span> <span class="label">会员中心</span> </a>
    </div>
</div>

<script src="<?php echo $cdnpublic ?>jquery/3.1.1/jquery.min.js"></script>
<script src="<?php echo $cdnpublic ?>layer/3.4.0/layer.js"></script>
<script src="<?php echo $cdnserver ?>assets/js/reguser.js?<?php echo $jsver ?>"></script>
<script>
function goback()
{
    document.referrer === '' ?window.location.href = '/' :window.history.go(-1);
}
<?php if (!empty($addsalt_js)): ?>
window.hashsalt=<?php echo $addsalt_js ?>;
<?php else: ?>
window.hashsalt='';
<?php endif;?>
</script>
</body>
</html>