<?php

include_once './includes/common.php';
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no, width=device-width">
  <meta name="renderer" content="webkit"/>
  <title>扫码登录</title>
  <link href=".<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
  <!--[if lt IE 9]>
    <script src="<?php echo $cdnpublic ?>html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="<?php echo $cdnpublic ?>respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
</head>
<body>
<div class="container">
<div class="col-xs-12 col-sm-10 col-md-8 col-lg-6 center-block" style="float: none;">
<div class="panel panel-primary">
	<div class="panel-heading" style="text-align: center;"><h3 class="panel-title">
        扫码登录以获取QQ空间说说
	</div>
	<div class="panel-body" style="text-align: center;">
		<div class="list-group">
			<div class="list-group-item">
                <img src="<?php echo $cdnserver ?>assets/img/imlogo_b.png"></div>
                <div class="list-group-item list-group-item-info" style="font-weight: bold;" id="login">
                    <span id="loginmsg">使用QQ手机版扫描二维码以登录QQ空间</span><span id="loginload" style="padding-left: 10px;color: #790909;">.</span>
                </div>
                <div class="list-group-item" id="qrimg">
                </div>
			<div class="list-group-item" id="mobile" style="display:none;">
                <button type="button" id="mlogin" onclick="mloginurl()" class="btn btn-warning btn-block">跳转QQ快捷登录</button><br/><button type="button" onclick="qrlogin()" class="btn btn-success btn-block">我已完成登录</button>
            </div><br/><br/>
            <div class="form-check hide">
                <input type="checkbox" id="agreement2" class="form-check-input">
                <label class="form-check-label"> 我已阅读并同意《<a href="./readme.html">扫码登录协议</a> 》</label>
            </div><br/>
		</div>
	</div>
</div>
</div>
</div>
<script src="<?php echo $cdnpublic ?>jquery/3.1.1/jquery.min.js"></script>
<script src="<?php echo $cdnpublic ?>layer/3.4.0/layer.js"></script>
<script src="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="<?php echo $cdnserver ?>assets/js/qzone/qrlogin.js?<?php echo time(); ?>"></script>
</body>
</html>
