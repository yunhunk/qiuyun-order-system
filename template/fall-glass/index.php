<?php
if(!defined('IN_CRONLITE'))exit();
?>
<!DOCTYPE html>
<html lang="zh-cn">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no" />
		<title>
			<?php echo $hometitle?>
		</title>
		<meta name="keywords" content="<?php echo $conf['keywords']?>">
		<meta name="description" content="<?php echo $conf['description']?>">
		<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@400;500;700&display=swap" rel="stylesheet">
		<link href="<?php echo $cdnpublic?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" />
		<link href="<?php echo $cdnpublic?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />
		<!--<link href="./assets/SF_New/css/style.min.css" rel="stylesheet" />-->
		<link rel="stylesheet" href="<?php echo $cdnserver?>assets/simple/css/oneui.css">
		<link rel="stylesheet" href="<?php echo $cdnserver?>assets/css/common.css?ver=<?php echo VERSION ?>">
	
		<script src="<?php echo $cdnpublic?>modernizr/2.8.3/modernizr.min.js"></script>
		<!--[if lt IE 9]>
    <script src="<?php echo $cdnpublic?>html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="<?php echo $cdnpublic?>respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
		<?php echo $background_css?>
		<style>
		*{
            font-family: 'Noto Sans SC', sans-serif;
        }
        .sf-btn{
            border: 1px solid transparent;
		    backdrop-filter: blur(100px);
		    background-color: rgba(255, 255, 255, .6);
		    border-radius:10px;
        }
        .btn {
            border-radius:25px;
        }
		.block{
		    border-radius:10px;
		}
		.block .block-content{
		    border-radius:10px;
		}
		.form-control {
		    border-radius:25px;
		}
		.input-group-addon{
		    border-radius:25px;
		}
		.btn.btn-primary {
            color: #fff;
            background: #007aff;
            border-color: #007aff;
        }
        .btn.btn-success {
            color: #fff;
            background: #4CD964;
            border-color: #4CD964;
        }
        .btn.btn-info {
            color: #fff;
            background: #5AC8FA;
            border-color: #5AC8FA;
        }
        .btn.btn-danger {
            color: #fff;
            background: #FF3B30;
            border-color: #FF3B30;
        }
        .btn-warning {
            color: #fff;
            background-color: #f0ad4e;
            border-color: #eea236;
        }
        .badge.badge-success, .label.label-success {
            border-radius:25px;
            background: #4CD964;
        }
        .badge.badge-primary, .label.label-primary {
            border-radius:25px;
            background: #007aff;
        }
        .badge.badge-warning, .label.label-warning {
            border-radius:25px;
            background: #FF9500;
        }
        .badge.badge-danger, .label.label-danger {
            border-radius:25px;
            background: #FF3B30;
        }
        .alert{
            border-radius:10px;
        }
        .block select {
          appearance: none;
          -webkit-appearance: none;
          -moz-appearance: none;
          background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="14" height="12" viewBox="0 0 18 16"><polygon fill="black" points="1,5 9,13 17,5 "/></svg>') no-repeat;
          background-position: right 0.5em top 50%, 0 0;
          background-size: 12px 12px;
          border: 1px solid #cccccc;
        }
.nav-tabs > li > a {
    color: #fff;
} 
.nav-tabs > li.active > a, .nav-tabs > li > a:hover, .nav-tabs > li.active > a:hover, .nav-tabs > li.active > a:focus {
    color: #fff;
    cursor: default;
    background-color: rgba(255, 255, 255, .1);
    border: 1px solid transparent;
    border-bottom-color: transparent;
    text-decoration: underline;
    border-radius:10px;
    text-decoration: none;
}
.custom-ul {
  display: flex; /* 启用Flexbox布局 */
  justify-content: center; /* 水平居中 */
  padding: 0; /* 移除默认内边距 */
  list-style-type: none; /* 移除列表标记 */
  flex-wrap: wrap; /* 允许项目换行 */
}

.custom-ul li {
  margin: 5px; /* 为了美观添加一些外边距 */
  flex: 1; /* 让所有li元素平均分配空间 */
  min-width: 20px; /* 最小宽度，根据需要调整 */
  text-align: center; /* 文字居中 */
}

		</style>
	</head>
	<body>
		<?php if($background_image){?>
		<img src="<?php echo $background_image;?>" alt="Full Background" class="full-bg full-bg-bottom animated pulse " ondragstart="return false;" oncontextmenu="return false;">
		<?php }?>
		<div style="padding-top:6px;">
			<div class="col-xs-12 col-sm-10 col-md-8 col-lg-4 center-block" style="float: none;">
				<!--弹出公告-->
				<div class="modal fade" align="left" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">
									<span aria-hidden="true">&times;</span>
									<span class="sr-only">Close</span>
								</button>
								<h4 class="modal-title" id="myModalLabel">
									<?php echo $conf['sitename']?>
								</h4>
							</div>
							<div class="modal-body">
								<?php echo $conf['modal']?>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">知道啦</button>
							</div>
						</div>
					</div>
				</div>
				<!--弹出公告-->
				<!--公告-->
				<div class="modal fade" align="left" id="anounce" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">
									<span aria-hidden="true">×</span>
									<span class="sr-only">Close</span>
								</button>
								<h4 class="modal-title" id="myModalLabel">公告</h4>
							</div>
							<div class="modal-body">
								<?php echo $conf['anounce']?>
							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
							</div>
						</div>
					</div>
				</div>
				<!--公告-->

				<!--顶部导航-->
				
					<div class="block block-link-hover3" style="background-color: transparent;backdrop-filter: blur(30px);background-color: rgba(0, 0, 0, .1);border-radius:10px;">
						<div class="block-content block-content-full text-center">
							<div>
								<div>
									<img class="img-avatar img-avatar80 img-avatar-thumb" src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq'] ?>&spec=100" style="z-index:2">
								</div>
							</div>
						</div>
						<div class="block-content block-content-mini block-content-full">
							<div class="btn-group btn-group-justified">
								<div class="btn-group">
									<a class="btn btn-default sf-btn" data-toggle="modal" href="#anounce">
										<i class="fa fa-bullhorn"></i>&nbsp;<span style="font-weight:bold">公告</span>
									</a>
								</div>
								<?php if($conf['appurl']){?>
								<a href="<?php echo $conf['appurl']; ?>" target="_blank" class="btn btn-effect-ripple btn-default sf-btn">
									<i class="fa fa-android"></i>
									<span style="font-weight:bold">客户端</span>
								</a>
								<?php }else{?>
								<a href="#customerservice" target="_blank" data-toggle="modal" class="btn btn-default sf-btn">
									<i class="fa fa-qq"></i>&nbsp;<span style="font-weight:bold">客服</span>
								</a>
								<?php }?>
								<?php if($islogin2==1){?>
								<div class="btn-group">
									<a class="btn btn-default sf-btn" data-toggle="modal" href="user/">
										<i class="fa fa-users fa-1x"></i>&nbsp;管理后台
									</a>
								</div>
								<?php }else{?>
								<div class="btn-group">
									<a class="btn btn-default sf-btn" data-toggle="modal" href="user/login.php">
										<i class="fa fa-users fa-1x"></i>&nbsp;登录
									</a>
								</div>
								<?php }?>
							</div>
						</div>
					</div>
				<!--顶部导航-->
<ul class="nav nav-tabs btn btn-block animated zoomInLeft custom-ul" data-toggle="tabs" style="background-color: transparent;backdrop-filter: blur(30px);background-color: rgba(0, 0, 0, .1);overflow: hidden;border-bottom: 1px solid transparent;border-radius:10px;margin-bottom:10px;">
						<li class="active">
							<a href="#shop" data-toggle="tab">
								<span style="font-weight:bold">
									<i class="fa fa-shopping-bag fa-fw"></i> 下单
								</span>
							</a>
						</li>
						<li >
							<a href="#search" data-toggle="tab" id="tab-query">
								<span style="font-weight:bold">
									<i class="fa fa-search"></i> 查询
								</span>
							</a>
						</li>
						<?php if($conf['fenzhan_buy']==1){?>
						<li>
							<a href="#Substation" data-toggle="tab">
								<span style="font-weight:bold">
									<i class="fa fa-coffee fa-fw"></i> 分站
								</span>
							</a>
						</li>
						<?php }?>
						<?php if($conf['gift_open']==1&&$conf['fenzhan_buy']==0){?>
						<li>
							<a href="#gift" data-toggle="tab">
								<span style="font-weight:bold">
									<i class="fa fa-gift fa-fw"></i> 抽奖
								</span>
							</a>
						</li>
						<?php }?>
						<li>
							<a href="#more" data-toggle="tab">
								<span style="font-weight:bold">
									<i class="fa fa-folder-open"></i> 更多
								</span>
							</a>
						</li>
					</ul>
				<div class="block" style="box-shadow:0px 5px 10px 0 rgba(0, 0, 0, 0.25);border-radius:10px">

					
					<!--TAB标签-->
					<div class="block-content tab-content">
						<!--在线下单-->
						<div class="tab-pane active" id="shop">
							<?php include TEMPLATE_ROOT.'default/shop.inc.php'; ?>
						</div>
						<!--在线下单-->
						<!--查询订单-->
						<div class="tab-pane" id="search">
							<ul class="list-group animated bounceIn">
								<li class="list-group-item" style="border-radius:10px;">
									<div class="media">
										<span class="pull-left thumb-sm">
											<img src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq'] ?>&spec=100" class="img-circle img-thumbnail img-avatar">
										</span>
										<div class="pull-right push-15-t">
											<a href="#customerservice" target="_blank" data-toggle="modal" class="btn btn-sm btn-info">联系客服</a>
										</div>
										<div class="pull-left push-10-t">
											<div class="font-w600 push-5">订单售后QQ客服</div>
											<div class="text-muted">
												<script>var online = new Array();</script>
												<script src="https://webpresence.qq.com/getonline?Type=1&<?php echo $conf['kfqq'] ?>:"></script>
												<script>if (online[0] == 0) document.write('<i class="fa fa-clock-o" aria-hidden="true"></i>&nbsp;'+ "8:00 - 23:00");else document.write('<i class="fa fa-circle text-success"></i>&nbsp;'+"8:00 - 23:00");</script>
											</div>
										</div>
									</div>
								</li>
							</ul>
							<div class="col-xs-12 well well-sm animation-pullUp" <?php if(empty($conf['gg_search'])){?>style="display:none;"
								<?php }else{?> style="border-radius:10px;"<?php }?>>
								<?php echo $conf['gg_search']?>
							</div>
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-btn">
										<select class="form-control custom-select" id="searchtype" style="padding: 6px 4px;width:90px;border-radius:25px 0px 0px 25px;">
											<option value="0">下单账号</option>
											<option value="1">订单号</option>
										</select>
									</div>
									<input type="text" name="qq" id="qq3" value="<?php echo $qq?>" class="form-control" placeholder="请输入要查询的内容（留空则显示最新订单）" onkeydown="if(event.keyCode==13){submit_query.click()}" required />
									<span class="input-group-btn">
										<a tabindex="0" class="btn btn-default" role="button" data-container="body" data-toggle="popover" data-trigger="focus" data-placement="top" title="查询内容是什么？" data-content="请输入您下单时，在第一个输入框内填写的信息。如果您不知道下单账号是什么，可以不填写，直接点击查询，则会根据浏览器缓存查询！">
											<i class="glyphicon glyphicon-exclamation-sign"></i>
										</a>
									</span>
								</div>
							</div>
							<input type="submit" id="submit_query" class="btn btn-sm btn-success btn-block btn-sm" style="text-shadow: black 1px 1px 1px;" value="查询订单">
							<div id="result2" class="form-group" style="display:none;">
								<center>
									<small>
										<font color="#ff0000">手机用户可以左右滑动</font>
									</small>
								</center>
								<div class="table-responsive">
									<table class="table table-vcenter table-condensed table-striped">
										<thead>
											<tr>
												<th>下单账号</th>
												<th>商品名称</th>
												<th>数量</th>
												<th class="hidden-xs">购买时间</th>
												<th>状态</th>
												<th>操作</th>
											</tr>
										</thead>
										<tbody id="list">
										</tbody>
									</table>
								</div>
							</div>
							<br />
						</div>
						<!--查询订单-->
						<!--开通分站-->
						<?php if($conf['fenzhan_buy']==1){?>
						<div class="tab-pane" id="Substation">
							<table class="table table-borderless animated bounceIn" style="text-align: center;">
								<tbody>
									<tr class="active" style="border-radius:10px">
										<td>
											<h4>
												<span style="font-weight:bold">
													<font color="#FF8000">搭</font>
													<font color="#EC6D13">建</font>
													<font color="#D95A26">属</font>
													<font color="#C64739">于</font>
													<font color="#A0215F">自</font>
													<font color="#8D0E72">己</font>
													<font color="#5400AB">的</font>
													<font color="#4100BE">商</font>
													<font color="#2E00D1">城</font>
												</span>
											</h4>
										</td>
									</tr>
									<tr class="active">
										<td>学生/上班族/创业/休闲赚钱必备工具</td>
									</tr>
									<tr class="active">
										<td>
											<strong>
												网站轻轻松松推广日赚上千元不是梦</strong>
										</td>
									</tr>
									<tr class="active">
										<td>
											<span class="glyphicon glyphicon-magnet"></span>&nbsp;快加入我们成为大家庭中的一员吧
											<hr>
											<a href="#userjs" data-toggle="modal" class="btn btn-effect-ripple  btn-info btn-sm" style="float:left;overflow: hidden; position: relative;">
												<span class="glyphicon glyphicon-eye-open"></span>&nbsp;网站详情介绍
											</a>
											<a href="./user/regsite.php" target="_blank" class="btn btn-effect-ripple  btn-success btn-sm" style="float:right;overflow: hidden; position: relative;">
												<span class="glyphicon glyphicon-share-alt"></span>&nbsp;免费开通网站
											</a>
										</td>
									</tr>
									<tr>
								</tbody>
							</table>
						</div>
						<?php }?>
						<!--开通分站-->
						<!--抽奖-->
						<?php if($conf['gift_open']==1){?>
						<div class="tab-pane" id="gift">
							<div class="panel-body text-center">
								<div id="roll">点击下方按钮开始抽奖</div>
								<hr>
								<p>
									<a class="btn btn-info" id="start" style="display:block;">开始抽奖</a>
									<a class="btn btn-danger" id="stop" style="display:none;">停止</a>
								</p>
								<div id="result"></div>
								<br />
								<div class="giftlist" style="display:none;">
									<strong>最近中奖记录</strong>
									<ul id="pst_1"></ul>
								</div>
							</div>
						</div>
						<?php }?>
						<!--抽奖-->
						<!--更多-->
						<div class="tab-pane fade fade-right" id="more">
							<?php if($conf['gift_open']==1){?>
							<div class="col-xs-6 col-sm-4 col-lg-4">
								<a class="block block-link-hover2 text-center" href="#gift" data-toggle="tab">
									<div class="block-content block-content-full bg-info">
										<i class="fa fa-gift fa-3x text-white"></i>
										<div class="font-w600 text-white-op push-15-t">抽奖</div>
									</div>
								</a>
							</div>
							<?php }?>

							<?php if(!empty($conf['appurl'])){?>
							<div class="col-xs-6 col-sm-4 col-lg-4">
								<a class="block block-link-hover2 text-center" href="<?php echo $conf['appurl']; ?>" target="_blank">
									<div class="block-content block-content-full bg-success">
										<i class="fa fa-cloud-download fa-3x text-white"></i>
										<div class="font-w600 text-white-op push-15-t">APP下载</div>
									</div>
								</a>
							</div>
							<?php }?>

							<?php if(!empty($conf['daiguaurl'])){?>
							<div class="col-xs-6 col-sm-4 col-lg-4">
								<a class="block block-link-hover2 text-center" href="./?mod=daigua">
									<div class="block-content block-content-full bg-primary">
										<i class="fa fa-rocket fa-3x text-white"></i>
										<div class="font-w600 text-white-op push-15-t">等级代挂</div>
									</div>
								</a>
							</div>
							<?php }?>
							<?php if(!empty($conf['invite_tid'])){?>
							<div class="col-xs-6 col-sm-4 col-lg-4">
								<a class="block block-link-hover2 text-center" href="./?mod=invite" target="_blank">
									<div class="block-content block-content-full bg-warning">
										<i class="fa fa-paper-plane-o fa-3x text-white"></i>
										<div class="font-w600 text-white-op push-15-t">免费领赞</div>
									</div>
								</a>
							</div>
							<?php }?>
							<?php if(!empty($conf['cutshop_open'])){?>
							<div class="col-xs-6 col-sm-4 col-lg-4">
								<a class="block block-link-hover2 text-center" href="./?mod=cutshop" target="_blank">
									<div class="block-content block-content-full bg-danger">
										<i class="fa fa-cutlery fa-3x text-white"></i>
										<div class="font-w600 text-white-op push-15-t">砍价商城</div>
									</div>
								</a>
							</div>
							<?php }?>
							<?php if(!empty($conf['groupshop_open'])){?>
							<div class="col-xs-6 col-sm-4 col-lg-4">
								<a class="block block-link-hover2 text-center" href="./?mod=groupshop" target="_blank">
									<div class="block-content block-content-full bg-success">
										<i class="fa fa-tags fa-3x text-white"></i>
										<div class="font-w600 text-white-op push-15-t">团购商城</div>
									</div>
								</a>
							</div>
							<?php }?>
							<?php if(!empty($conf['seckill_open'])){?>
							<div class="col-xs-6 col-sm-4 col-lg-4">
								<a class="block block-link-hover2 text-center" href="./?mod=seckill" target="_blank">
									<div class="block-content block-content-full bg-primary">
										<i class="fa fa-puzzle-piece fa-3x text-white"></i>
										<div class="font-w600 text-white-op push-15-t">秒杀专场</div>
									</div>
								</a>
							</div>
							<?php }?>
							<?php if(!empty($conf['package_open'])){?>
							<div class="col-xs-6 col-sm-4 col-lg-4">
								<a class="block block-link-hover2 text-center" href="./?mod=package" target="_blank">
									<div class="block-content block-content-full bg-info">
										<i class="fa fa-archive fa-3x text-white"></i>
										<div class="font-w600 text-white-op push-15-t">团购优惠</div>
									</div>
								</a>
							</div>
							<?php }?>
							<?php if(!empty($conf['coupon_open'])){?>
							<div class="col-xs-6 col-sm-4 col-lg-4">
								<a class="block block-link-hover2 text-center" href="./?mod=coupon" target="_blank">
									<div class="block-content block-content-full bg-warning">
										<i class="fa fa-credit-card fa-3x text-white"></i>
										<div class="font-w600 text-white-op push-15-t">领取优惠券</div>
									</div>
								</a>
							</div>
							<?php }?>

							<div class="col-xs-6 col-sm-4 col-lg-4">
								<a class="block block-link-hover2 text-center" href="./user/" target="_blank">
									<div class="block-content block-content-full bg-city">
										<i class="fa fa-certificate fa-3x text-white"></i>
										<div class="font-w600 text-white-op push-15-t">用户中心</div>
									</div>
								</a>
							</div>
						</div>
						<!--更多-->
						<!--版本介绍-->
						<?php if($conf['fenzhan_buy']==1){?>
						<div class="modal fade" align="left" id="userjs" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<h4 class="modal-title" id="myModalLabel">版本介绍</h4>
									</div>
									<div class="block">
										<div class="table-responsive">
											<table class="table table-borderless table-vcenter">
												<thead>
													<tr>
														<th style="width: 100px;">功能</th>
														<th class="text-center" style="width: 20px;">普及版/专业版</th>
													</tr>
												</thead>
												<tbody>
													<tr class="active">
														<td>专属商城平台</td>
														<td class="text-center">
															<span class="btn btn-effect-ripple btn-xs btn-success">
																<i class="fa fa-check"></i>
															</span>
															<span class="btn btn-effect-ripple btn-xs btn-success">
																<i class="fa fa-check"></i>
															</span>
														</td>
													</tr>
													<tr class="">
														<td>三种在线支付接口</td>
														<td class="text-center">
															<span class="btn btn-effect-ripple btn-xs btn-success">
																<i class="fa fa-check"></i>
															</span>
															<span class="btn btn-effect-ripple btn-xs btn-success">
																<i class="fa fa-check"></i>
															</span>
														</td>
													</tr>
													<tr class="success">
														<td>专属网站域名</td>
														<td class="text-center">
															<span class="btn btn-effect-ripple btn-xs btn-success">
																<i class="fa fa-check"></i>
															</span>
															<span class="btn btn-effect-ripple btn-xs btn-success">
																<i class="fa fa-check"></i>
															</span>
														</td>
													</tr>
													<tr class="">
														<td>赚取用户提成</td>
														<td class="text-center">
															<span class="btn btn-effect-ripple btn-xs btn-success">
																<i class="fa fa-check"></i>
															</span>
															<span class="btn btn-effect-ripple btn-xs btn-success">
																<i class="fa fa-check"></i>
															</span>
														</td>
													</tr>
													<tr class="info">
														<td>赚取下级分站提成</td>
														<td class="text-center">
															<span class="btn btn-effect-ripple btn-xs btn-danger">
																<i class="fa fa-close"></i>
															</span>
															<span class="btn btn-effect-ripple btn-xs btn-success">
																<i class="fa fa-check"></i>
															</span>
														</td>
													</tr>
													<tr class="">
														<td>设置商品价格</td>
														<td class="text-center">
															<span class="btn btn-effect-ripple btn-xs btn-success">
																<i class="fa fa-check"></i>
															</span>
															<span class="btn btn-effect-ripple btn-xs btn-success">
																<i class="fa fa-check"></i>
															</span>
														</td>
													</tr>
													<tr class="warning">
														<td>设置下级分站商品价格</td>
														<td class="text-center">
															<span class="btn btn-effect-ripple btn-xs btn-danger">
																<i class="fa fa-close"></i>
															</span>
															<span class="btn btn-effect-ripple btn-xs btn-success">
																<i class="fa fa-check"></i>
															</span>
														</td>
													</tr>
													<tr class="">
														<td>搭建下级分站</td>
														<td class="text-center">
															<span class="btn btn-effect-ripple btn-xs btn-danger">
																<i class="fa fa-close"></i>
															</span>
															<span class="btn btn-effect-ripple btn-xs btn-success">
																<i class="fa fa-check"></i>
															</span>
														</td>
													</tr>
													<tr class="danger">
														<td>赠送专属精致APP</td>
														<td class="text-center">
															<span class="btn btn-effect-ripple btn-xs btn-danger">
																<i class="fa fa-close"></i>
															</span>
															<span class="btn btn-effect-ripple btn-xs btn-success">
																<i class="fa fa-check"></i>
															</span>
														</td>
													</tr>
												</tbody>
											</table>
										</div>
										<center style="color: #b2b2b2;">
											<small>
												<em>* 自己的能力决定着你的收入！</em>
											</small>
										</center>
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
									</div>
								</div>
							</div>
						</div>
						<?php }?>
						<!--版本介绍-->
					</div>
				</div>

				<!--关于我们弹窗-->
				<div class="modal fade" align="left" id="customerservice" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">
									<span aria-hidden="true">&times;</span>
									<span class="sr-only">Close</span>
								</button>
								<h4 class="modal-title" id="myModalLabel">客服与帮助</h4>
							</div>
							<div class="modal-body" id="accordion">
								<div class="panel panel-default" style="margin-bottom: 6px;">
									<div class="panel-heading">
										<h4 class="panel-title">
											<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">为什么订单显示已完成了却一直没到账？</a>
										</h4>
									</div>
									<div id="collapseOne" class="panel-collapse in" style="height: auto;">
										<div class="panel-body">
											订单显示（已完成）就证明已经提交到服务器内！
											<br>
											如果长时间没到账请联系客服处理！
											<br>
											订单长时间显示（待处理）请联系客服！
										</div>
									</div>
								</div>
								<div class="panel panel-default" style="margin-bottom: 6px;">
									<div class="panel-heading">
										<h4 class="panel-title">
											<a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" class="collapsed">商品什么时候到账？</a>
										</h4>
									</div>
									<div id="collapseTwo" class="panel-collapse collapse" style="height: 0px;">
										<div class="panel-body">
											请参考商品简介里面，有关于到账时间的说明。
										</div>
									</div>
								</div>
								<div class="panel panel-default" style="margin-bottom: 6px;">
									<div class="panel-heading">
										<h4 class="panel-title">
											<a data-toggle="collapse" data-parent="#accordion" href="#collapseThree" class="collapsed">卡密/CDK没有发送我的邮箱？</a>
										</h4>
									</div>
									<div id="collapseThree" class="panel-collapse collapse" style="height: 0px;">
										<div class="panel-body">没有收到请检查自己邮箱的垃圾箱！也可以去查单区：输入自己下单时填写的邮箱进行查单。
											<br>
											查询到订单后点击（详细）就可以看到自己购买的卡密/cdk！
										</div>
									</div>
								</div>
								<div class="panel panel-default" style="margin-bottom: 6px;">
									<div class="panel-heading">
										<h4 class="panel-title">
											<a data-toggle="collapse" data-parent="#accordion" href="#collapseFourth" class="collapsed">已付款了没有查询到我订单？</a>
										</h4>
									</div>
									<div id="collapseFourth" class="panel-collapse collapse" style="height: 0px;">
										<div class="panel-body" style="margin-bottom: 6px;">联系客服处理，请提供（付款详细记录截图）（下单商品名称）（下单账号）
											<br>直接把三个信息发给客服，然后等待客服回复处理（请不要发抖动窗口或者QQ电话）！
										</div>
									</div>
								</div>
								<ul class="list-group" style="margin-bottom: 0px;">
									<li class="list-group-item">
										<div class="media">
											<span class="pull-left thumb-sm">
												<img src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq'] ?>&spec=100" alt="..." class="img-circle img-thumbnail img-avatar">
											</span>
											<div class="pull-right push-15-t">
												<a href="<?php echo $conf['kflj'] ?>" target="_blank" class="btn btn-sm btn-info">联系</a>
											</div>
											<div class="pull-left push-10-t">
												<div class="font-w600 push-5">订单售后客服</div>
												<div class="text-muted">
													<b>QQ：
														<?php echo $conf['kfqq'] ?>
													</b>
												</div>
											</div>
										</div>
									</li>
									<li class="list-group-item">
										想要快速回答你的问题就请把问题描述讲清楚!
										<br>
										下单账号+业务名称+问题，直奔主题，按顺序回复!
										<br>
										有问题直接留言，请勿抖动语音否则直接无视。
										<br>
									</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<!--关于我们弹窗-->

				<?php if($conf['articlenum']>0){
$limit = intval($conf['articlenum']);
$rs=$DB->query("SELECT id,title FROM pre_article WHERE active=1 ORDER BY top DESC,id DESC LIMIT {$limit}");
$msgrow=array();
while($res = $rs->fetch()){
	$msgrow[]=$res;
}
$class_arr = ['danger','warning','primary','success','info'];
$i=0;
?>
				<!--文章列表-->
				<div class="block block-themed" style="background-color:rgb(255,255,255,0)">
					<div class="block-header bg-amethyst" style="background-color: transparent;backdrop-filter: blur(30px);background-color: rgba(0, 0, 0, .1);border-radius:10px 10px 0px 0px;margin-bottom:0px;margin-top:10px">
						<h3 class="block-title">
							<i class="fa fa-newspaper-o"></i> 文章列表
						</h3>
					</div>
					<?php foreach($msgrow as $row){
	echo '<a target="_blank" class="list-group-item" href="'.article_url($row['id']).'" style="background-color: transparent;backdrop-filter: blur(30px);background-color: rgba(0, 0, 0, .1);border: 1px solid transparent;"><span class="btn btn-'.$class_arr[($i++)%5].' btn-xs" style="background-color: transparent;backdrop-filter: blur(30px);background-color: rgba(0, 0, 0, .1);width: 20px; height: 20px; border-radius: 50%; padding: 0px 0px 5px 0px; text-align: center;border: 1px solid transparent;color:#fff;font-weight: 400;"">'.$i.'</span>&nbsp;'.$row['title'].'</a>';
	}?>
					<a href="<?php echo article_url()?>" title="查看全部文章" class="btn-default btn btn-block" target="_blank" style="background-color: transparent;backdrop-filter: blur(30px);background-color: rgba(0, 0, 0, .1);border-radius:0px 0px 10px 10px;border: 1px solid transparent;color:#fff;font-weight: 400;">查看全部文章</a>
				</div>
				<!--文章列表-->
				<?php }?>

				<div class="block block-content block-content-mini block-content-full"  style="background-color: transparent;backdrop-filter: blur(30px);background-color: rgba(0, 0, 0, .1);border-radius:10px;margin-bottom:0px;margin-top:10px">
					<!--网站日志-->
					<?php if(!$conf['hide_tongji']){?>
					<div class="row text-center">
						<div class="col-xs-4">
							<h5 class="widget-heading">
								<small style="color:#fff;font-weight: 400;">订单总数</small>
								<br>
								<a href="javascript:void(0)" class="themed-color-flat" style="color: rgba(255,255,255,.3);font-weight: 400;">
									<span id="count_orders"></span> 条
								</a>
							</h5>
						</div>
						<div class="col-xs-4">
							<h5 class="widget-heading">
								<small style="color:#fff;font-weight: 400;">今日订单</small>
								<br>
								<a href="javascript:void(0)" class="themed-color-flat" style="color: rgba(255,255,255,.3);font-weight: 400;">
									<span id="count_orders2"></span> 条
								</a>
							</h5>
						</div>
						<div class="col-xs-4">
							<h5 class="widget-heading">
								<small style="color:#fff;font-weight: 400;">运营天数</small>
								<br>
								<a href="javascript:void(0)" class="themed-color-flat" style="color: rgba(255,255,255,.3);font-weight: 400;">
									<span id="count_yxts"></span> 天
								</a>
							</h5>
						</div>
					</div>
					<?php }?>
					<!--网站日志-->
					<!--底部导航-->
					<div class="block-content text-center border-t"  style="border: 3px solid rgba(0, 0, 0, 0);>
						<a href="javascript:void(0);" onclick="AddFavorite('<?php echo $conf['sitename']?>',location.href)">
							<b style="text-shadow: LightSteelBlue 1px 0px 0px;color: rgba(255,255,255,.3);font-weight: 400;">
								本
								站
								网
								址
								：
									<?php echo $_SERVER['HTTP_HOST'];?>
								
								
								&nbsp;
								建
								议
								收
								藏
							</b>
						</a>
						<br />
						<?php echo $conf['footer']?>
					</div>
					<!--底部导航-->
				</div>
			</div>

			<!-- 收藏代码开始-->
			<script>
				function AddFavorite(title, url) {
				  try {
				      window.external.addFavorite(url, title);
				  }
				catch (e) {
				     try {
				       window.sidebar.addPanel(title, url, "");
				    }
				     catch (e) {
				         alert("手机用户：点击底部 “≡” 添加书签/收藏网址!\n\n电脑用户：请您按 Ctrl+D 手动收藏本网址! ");
				     }
				  }
				}
			</script>
			<!-- 收藏代码结束-->

			<!--音乐代码-->
			<div id="audio-play" <?php if(empty($conf['musicurl'])){?>style="display:none;"
				<?php }?>>
				<div id="audio-btn" class="on" onclick="audio_init.changeClass(this,'media')">
					<audio loop="loop" src="<?php echo $conf['musicurl']?>" id="media" preload="preload"></audio>
				</div>
			</div>
			<!--音乐代码-->

			<script src="<?php echo $cdnpublic?>jquery/1.12.4/jquery.min.js"></script>
			<script src="<?php echo $cdnpublic?>jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>
			<script src="<?php echo $cdnpublic?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
			<script src="<?php echo $cdnpublic?>jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
			<script src="<?php echo $cdnpublic ?>clipboard.js/1.7.1/clipboard.min.js"></script>
			<script src="<?php echo $cdnpublic?>layer/2.3/layer.js"></script>
			<script src="<?php echo $cdnserver?>assets/appui/js/app.js"></script>
			<script type="text/javascript">
				var isModal=<?php echo empty($conf['modal'])?'false':'true';?>;
				var homepage=true;
				var hashsalt=<?php echo $addsalt_js?>;
				$(function() {
					$("img.lazy").lazyload({effect: "fadeIn"});
				});
			</script>
			<script src="assets/js/main.js?ver=<?php echo VERSION ?>"></script>
			<?php if($conf['classblock']==1 || $conf['classblock']==2 && checkmobile()==false)include TEMPLATE_ROOT.'default/classblock.inc.php'; ?>
	</body>
</html>