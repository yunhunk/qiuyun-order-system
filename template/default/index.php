<?php
if (!defined('IN_CRONLITE')) {
    exit();
}

//文章资讯
$type     = '0,1';
$msgcount = $DB->count("SELECT count(*) FROM pre_message WHERE type IN ($type) AND active=1");
$limit    = 10;
$rs       = $DB->query("SELECT * FROM pre_message WHERE type IN ($type) AND active=1 ORDER BY id DESC LIMIT 0,$limit");
$msgrow   = array();
while ($res = $DB->fetch($rs)) {
    $msgrow[] = $res;
}
?>

<!DOCTYPE html>

<html lang="zh-cn">

<head>

  <meta charset="utf-8"/>

  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>

  <title><?php echo $conf['sitename'] ?> - <?php echo $conf['title'] ?></title>

  <meta name="keywords" content="<?php echo $conf['keywords'] ?>">

  <meta name="description" content="<?php echo $conf['description'] ?>">

  <link href="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>

  <link href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>

  <link href="<?php echo $cdnserver ?>assets/css/nifty.min.css" rel="stylesheet">

  <link href="<?php echo $cdnserver ?>assets/css/magic-check.min.css" rel="stylesheet">

  <link href="<?php echo $cdnserver ?>assets/css/pace.min.css" rel="stylesheet">

  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/css/common.css?ver=<?php echo VERSION ?>">

  <script src="<?php echo $cdnpublic ?>jquery/1.12.4/jquery.min.js"></script>
  <?php
//加载插件代码
hook('head');
?>

  <!--[if lt IE 9]>

    <script src="<?php echo $cdnpublic ?>html5shiv/3.7.3/html5shiv.min.js"></script>

    <script src="<?php echo $cdnpublic ?>respond.js/1.4.2/respond.min.js"></script>

  <![endif]-->

<style>

body{

background:#ecedf0 url("<?php echo $background_image ?>") fixed;

<?php echo $repeat ?>}

</style>

</head>

<body>

<?php include TEMPLATE_ROOT . 'default/head.inc.php';?>
<div class="modal fade" align="left" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">

  <div class="modal-dialog">

    <div class="modal-content">

      <div class="modal-header">

        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>

        <h4 class="modal-title" id="myModalLabel"><?php echo $conf['sitename'] ?></h4>

      </div>

      <div class="modal-body">

	  <?php echo $conf['modal'] ?>

      </div>

      <div class="modal-footer">

        <button type="button" class="btn btn-default" data-dismiss="modal">知道啦</button>

      </div>

    </div>

  </div>

</div>

<br/>

<div class="col-xs-12 col-sm-10 col-md-8 col-lg-6 center-block" style="float: none;">

<div class="panel panel-default">

	<div class="panel-body" style="text-align: center;">

		<img src="<?php echo $logo ?>" style="max-width: 100%;">

	</div>

</div>

<div class="panel panel-primary">

	<div class="panel-heading"><h3 class="panel-title" ><font color="#FFFFFF"><i class=""></i><b> <script type="text/javascript">

var now=(new Date()).getHours();

if(now>0&&now<=6){

document.write("❤熬夜对身体不好哦 快睡觉！");

}else if(now>6&&now<=11){

document.write("❤早上好 心情好来下一单吧~");

}else if(now>11&&now<=14){

document.write("❤停下手中的工作 去吃饭~");

}else if(now>14&&now<=18){

document.write("❤累了一上午了 休息会吧~");

}else{

document.write("❤晚上好 下一单醒来有惊喜哟~");

}

</script></font> </b></h3></div>

	<div>

         <?php echo $conf['anounce'] ?>

	</div>

</div>

<?php
//加载插件代码
hook('header');
?>



<div class="tab-content">

	<div id="demo-tabs-box-1" class="tab-pane fade active in">

		<div class="panel panel-primary">

			<div class="panel-heading">

				<h3 class="panel-title"><font color="#fff"><i class="fa fa-shopping-cart"></i>&nbsp;&nbsp;<b>自助下单</b></font><span class="pull-right"><a data-toggle="tab" href="#demo-tabs-box-2" aria-expanded="true" class="btn btn-warning btn-rounded"><i class="fa fa-warning"></i> 注意</a></span></h3>

			</div>

	<ul class="nav nav-tabs" data-toggle="tabs">

		<li class="active"><a href="#onlinebuy" data-toggle="tab"><i class="fa fa-shopping-cart"></i> 下单</a></li>

		<li <?php if ($conf['iskami'] == 0) {?>style="display:none;"<?php }?>><a href="#cardbuy" data-toggle="tab"><i class="glyphicon glyphicon-th"></i> 卡密</a></li>

		<li><a href="#query" data-toggle="tab" id="tab-query"><i class="fa fa-search"></i> 查单</a></li>

		<li <?php if ($conf['gift_open'] == 0) {?>class="hide"<?php }?>><a href="#gift" data-toggle="tab"><i class="fa fa-gift"></i> 抽奖</a></li>

		<li <?php if (empty($conf['daiguaurl'])) {?>class="hide"<?php }?>><a href="./?mod=daigua"><i class="fa fa-rocket"></i> 代挂</a></li>

		<li <?php if (empty($conf['chatframe'])) {?>class="hide"<?php }?>><a href="#chat" data-toggle="tab"><i class="fa fa-comments"></i> 聊天</a></li>

		<li <?php if ($conf['fenzhan_buy'] == 0) {?>class="hide"<?php }?>><a href="./user/regsite.php" style="color:red">开通分站</a></li>

		<?php if ($isLogin2 == 1) {?>

		<li <?php if ($conf['fenzhan_buy'] == 0) {?>class="hide"<?php }?>><a href="./user/">用户中心</a></li>

		<?php } else {?>

		<li <?php if ($conf['fenzhan_buy'] == 0) {?>class="hide"<?php }?>><a href="./user/login.php">后台登录</a></li>

		<?php }?>

	</ul>

	<div class="modal-body">

		<div class="tab-content">
		<div class="tab-pane fade in active" id="onlinebuy">

         <?php include TEMPLATE_ROOT . 'default/shop.inc.php';?>

		</div>

		<div class="tab-pane fade" id="query">

		<?php include TEMPLATE_ROOT . 'default/query.inc.php';?>

		</div>

		<div class="tab-pane fade" id="cardbuy">

			<?php if (!empty($conf['kaurl'])) {?>

			<div class="form-group">

				<a href="<?php echo $conf['kaurl'] ?>" class="btn btn-default btn-block" target="_blank"/>点击进入购买卡密</a>

			</div>

			<?php }?>

			<div class="form-group">

				<div class="input-group"><div class="input-group-addon">输入卡密</div>

				<input type="text" name="km" id="km" value="" class="form-control" onkeydown="if(event.keyCode==13){submit_checkkm.click()}" required/>

			</div></div>

			<input type="submit" id="submit_checkkm" class="btn btn-primary btn-block" value="检查卡密">

			<div id="km_show_frame" style="display:none;">

			<div class="form-group">

				<div class="input-group"><div class="input-group-addon">商品名称</div>

				<input type="text" name="name" id="km_name" value="" class="form-control" disabled/>

			</div></div>

			<div id="km_inputsname"></div>

			<div id="km_alert_frame" class="alert alert-warning" style="display:none;font-weight: bold;"></div>

			<input type="submit" id="submit_card" class="btn btn-primary btn-block" value="立即购买">

			<div id="result1" class="form-group text-center" style="display:none;">

			</div>

			</div>

		</div>

		<div class="tab-pane fade in" id="lqq">

			<div class="form-group">

				<div class="input-group"><div class="input-group-addon">请输入QQ</div>

				<input type="text" name="qq" id="qq4" value="" class="form-control" required/>

			</div></div>

			<input type="submit" id="submit_lqq" class="btn btn-primary btn-block" value="立即提交">

			<div id="result3" class="form-group text-center" style="display:none;"></div>

		</div>

		<div class="tab-pane fade in" id="gift">

			<div class="panel-body text-center">

			<div id="roll">点击下方按钮开始抽奖</div>

			<hr>

			<p>

			<a class="btn btn-info" id="start" style="display:block;">开始抽奖</a>

			<a class="btn btn-danger" id="stop" style="display:none;">停止</a>

			</p>

			<div id="result"></div><br/>

			<div class="giftlist" style="display:none;"><strong>最近中奖记录</strong><ul id="pst_1"></ul></div>

			</div>

		</div>

		<div class="tab-pane fade in" id="chat">

			<?php echo $conf['chatframe'] ?>

		</div>

		</div>

	</div>

</div>

</div>

	<div id="demo-tabs-box-2" class="tab-pane fade">

		<div class="panel panel-primary">

			<div class="panel-heading">

				<h3 class="panel-title"><font color="#fff"><i

						class="fa fa-warning"></i>&nbsp;&nbsp;<b>注意事项</b></font><span class="pull-right"><a

						data-toggle="tab" href="#demo-tabs-box-1" aria-expanded="false"

						class="btn btn-warning btn-rounded"><i class="fa fa-shopping-cart"></i> 下单</a>

				</span></h3>

			</div>

			<div class="panel-body">

				<!--注意事项-->
				<div id="demo-acc-faq" class="panel-group accordion">
					<div class="panel panel-trans pad-top"><a href="#demo-acc-faq1" class="text-semibold text-lg text-main" data-toggle="collapse" data-parent="#demo-acc-faq">为什么下单很久了还没有发货？</a><div id="demo-acc-faq1" class="mar-ver collapse in">部分虚拟商品不能马上完成发货需要时间，如果需要收货地址的，请等待客服发货处理！等待时间较长可联系客服咨询</div></div>
				</div>
				<div id="demo-acc-faq" class="panel-group accordion">
					<div class="panel panel-trans pad-top"><a href="#demo-acc-faq1" class="text-semibold text-lg text-main" data-toggle="collapse" data-parent="#demo-acc-faq">客服联系不上怎么办？</a><div id="demo-acc-faq1" class="mar-ver collapse in">有时候客户私聊太多、消息过多可能屏蔽了或者加不上，建议在本站找找售后群或者换客服联系方式试试，也可以在查单订单处提交工单等待回复处理</div></div>
				</div>
			</div>
		</div>
	</div>

</div>

<div class="panel panel-primary">

<?php include TEMPLATE_ROOT . 'default/news.inc.php';?>

</div>

<div class="row" <?php if ($conf['hide_tongji'] == 1) {?>style="display:none;"<?php }?>>

	<div class="col-lg-6">

	<div class="panel panel-success panel-colorful">

			<div class="pad-all media">

				<div class="media-left">

					<i class="demo-pli-coin icon-3x icon-fw"></i>

				</div>

				<div class="media-body">

					<p class="h3 text-light mar-no media-heading"><span id="count_money"></span>元</p>

					<span>累计交易金额</span>

				</div>

			</div>

			<div class="progress progress-xs progress-success mar-no">

				<div class="progress-bar progress-bar-light" style="width: 100%"></div>

			</div>

			<div class="pad-all text-sm">

				今天交易金额 <span class="text-semibold" id="count_money1"></span> 元

			</div>

		</div>

	</div>

	<div class="col-lg-6">

	<div class="panel panel-info panel-colorful">

			<div class="pad-all media">

				<div class="media-left">

					<i class="demo-pli-add-cart icon-3x icon-fw"></i>

				</div>

				<div class="media-body">

					<p class="h3 text-light mar-no media-heading"><span id="count_orders"></span>条</p>

					<span>累计订单总数</span>

				</div>

			</div>

			<div class="progress progress-xs progress-dark-base mar-no">

				<div class="progress-bar progress-bar-light" style="width: 100%"></div>

			</div>

			<div class="pad-all text-sm bg-trans-dark">

				今天订单总数 <span class="text-semibold" id="count_orders2"></span> 条

			</div>

		</div>

	</div>

</div>



<div class="panel panel-primary" <?php if ($conf['bottom'] == '') {?>style="display:none;"<?php }?>>

<div class="panel-heading"><h3 class="panel-title"><font color="#fff"><i class="fa fa-skyatlas"></i>&nbsp;&nbsp;<b>站点助手</b></font></h3></div>

<?php echo $conf['bottom'] ?>

</div>

<?php
//加载插件代码
hook('bottom');
?>

</div>

<p style="text-align:center"><span style="font-weight:bold">CopyRight <i class="fa fa-heart text-danger"></i> 2019 <a href="/"><?php echo $conf['sitename'] ?></a></span></p>

<!-- 全局底部代码 -->
<?php echo $conf['index_html_bottom'] ?>
<!-- 全局底部代码 end-->

</div>



<!--音乐代码-->

<div id="audio-play" <?php if (empty($conf['musicurl'])) {?>style="display:none;"<?php }?>>

  <div id="audio-btn" class="on" onclick="audio_init.changeClass(this,'media')">

    <audio loop="loop" src="<?php echo $conf['musicurl'] ?>" id="media" preload="preload"></audio>

  </div>

</div>

<!--音乐代码-->

<?php hook('footer_before');?>

<script src="<?php echo $cdnserver ?>assets/js/removead.js?ver=<?php echo $jsver ?>"></script>

<script src="<?php echo $cdnpublic ?>jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>

<script src="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>

<script src="<?php echo $cdnpublic ?>jquery-cookie/1.4.1/jquery.cookie.min.js"></script>

<script src="<?php echo $cdnpublic ?>layer/3.4.0/layer.js"></script>

<script src="<?php echo $cdnpublic ?>distpicker/2.0.3/distpicker.min.js"></script>

<script src="<?php echo $cdnserver ?>assets/js/pace.min.js"></script>

<script type="text/javascript">

var isModal=<?php echo empty($conf['modal']) ? 'false' : 'true'; ?>;
var _modalType=<?php echo $conf['modal_type'] > 0 ? '1' : '0'; ?>;

var homepage=true;

var hashsalt=<?php echo $addsalt_js ?>;

$(function() {

	$("img.lazy").lazyload({effect: "fadeIn"});

});


</script>

<script src="<?php echo $cdnserver ?>assets/js/main.js?ver=<?php echo VERSION ?>"></script>
<?php hook('footer_after');?>
</body>
</html>