<?php
$conf['musicurl'] = !empty($siterow['musicurl']) ? $siterow['musicurl'] : $DB->getColumn("SELECT v FROM pre_config WHERE k=:key LIMIT 1", [':key' => 'musicurl']);
?>
<html lang="zh-cn">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
  <title><?php echo $conf['sitename'] ?><?php echo $conf['title'] ?></title>
  <link rel="shortcut icon" href="/favicon.ico">
<link rel="bookmark" href="/favicon.ico">
  <meta name="keywords" content="<?php echo $conf['keywords'] ?>">
  <meta name="description" content="<?php echo $conf['description'] ?>">
  <link href="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/oneui.css">
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/css/common.css?ver=<?php echo VERSION ?>">
  <script src="<?php echo $cdnpublic ?>modernizr/2.8.3/modernizr.min.js"></script>
  <!--[if lt IE 9]>
    <script src="<?php echo $cdnpublic ?>html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="<?php echo $cdnpublic ?>respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
  <style type="text/css">
#submit_cart_shop {
    background: linear-gradient(to right,#00FFFF,#02C874);
    border-radius: 25px 0 0 25px;
}
#submit_buy {
    background: linear-gradient(to right,#84C1FF,#66B3FF);
     border-radius:
}</style>
<?php echo $background_css ?>
</head>
<body>
<?php if ($background_image) {?>
<img src="<?php echo $background_image; ?>" alt="Full Background" class="full-bg full-bg-bottom animated pulse " ondragstart="return false;" oncontextmenu="return false;">
<?php }?>
<img src="https://api.vvhan.com/api/bing" alt="Full Background" class="full-bg full-bg-bottom animated pulse " ondragstart="return false;" oncontextmenu="return false;">
<div style="padding-top:6px;">
<?php include TEMPLATE_ROOT . 'default/head.inc.php';?>
<div class="col-xs-12 col-sm-10 col-md-8 col-lg-4 center-block" style="float: none;">
<!--弹出公告-->
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
<!--弹出公告-->

<!--公告-->
<div class="modal fade" align="left" id="anounce" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">公告</h4>
      </div>
	  <div class="modal-body">
	  <?php echo $conf['anounce'] ?>
	  </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
      </div>
    </div>
  </div>
 </div>
<!--公告-->
	  <!--
  本代码由 XG 创建
  严禁反编译、逆向等任何形式的侵权行为，违者将追究法律责任
 -->

	  <!--
  本代码由 XG 创建
  严禁反编译、逆向等任何形式的侵权行为，违者将追究法律责任
 -->


<!--对接XG货源所有源码免费使用。-->
<!--XG密价所有商品一键降0.3。-->
<!--查单说明开始-->
<div class="modal fade" align="left" id="cxsm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">查询内容是什么？该输入什么？</h4>
      </div>
      	<li class="list-group-item">例如您购买的是预留的密码或者QQ号，输入下单的密码或者QQ号即可查询订单</li>
        <li class="list-group-item"><font color="red">如果您不知道下单账号是什么，可以不填写，直接点击查询，则会根据浏览器缓存查询</font></li>


      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
      </div>
    </div>
  </div>
</div>
<!--查单说明结束-->
	  <!--
  本代码由 XG 创建
  严禁反编译、逆向等任何形式的侵权行为，违者将追究法律责任
 -->
<!--顶部导航-->
         <div class="block block-link-hover3" style="box-shadow:0px 5px 10px 0 rgba(0, 0, 0, 0.26);">
        <div class="block-content block-content-full text-center bg-image" style="background-image: url('assets/beautify/img/C72F.jpeg');background-size: 100% 100%;">
            <div>
                <div>
                    <img class="img-avatar img-avatar80" src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq'] ?>&spec=100">
                    </div>
                </div>
            </div>

        <center>
            <h2>     <a href="javascript:void(alert('建议收藏到浏览器书签哦！'));"><b>
    <font color="#414324"><?php echo $conf['sitename'] ?></font></b></a></h2><font color="#414324">
   <h5><div color="wrap"><img src="">低价货源-信誉保证<img src="">
    <style>

h5 {
  text-shadow: -1px 1px 0 #FFD180;
  -webkit-animation: 1s infinite rainbowText;
          animation: 1s infinite rainbowText;
}

@-webkit-keyframes rainbowText {
  0% {
    text-shadow: -0.1rem 0.1rem #FFFF8D, -0.2rem 0.2rem #CCFF90, -0.3rem 0.3rem #A7FFEB, -0.4rem 0.4rem #82B1FF, -0.5rem 0.5rem #B388FF, -0.6rem 0.6rem #EA80FC, -0.7rem 0.7rem #FF80AB, -0.8rem 0.8rem #FFD180;
  }
  12.5% {
    text-shadow: -0.1rem 0.1rem #FFD180, -0.2rem 0.2rem #FFFF8D, -0.3rem 0.3rem #CCFF90, -0.4rem 0.4rem #A7FFEB, -0.5rem 0.5rem #82B1FF, -0.6rem 0.6rem #B388FF, -0.7rem 0.7rem #EA80FC, -0.8rem 0.8rem #FF80AB;
  }
  25% {
    text-shadow: -0.1rem 0.1rem #FF80AB, -0.2rem 0.2rem #FFD180, -0.3rem 0.3rem #FFFF8D, -0.4rem 0.4rem #CCFF90, -0.5rem 0.5rem #A7FFEB, -0.6rem 0.6rem #82B1FF, -0.7rem 0.7rem #B388FF, -0.8rem 0.8rem #EA80FC;
  }
  37.5% {
    text-shadow: -0.1rem 0.1rem #EA80FC, -0.2rem 0.2rem #FF80AB, -0.3rem 0.3rem #FFD180, -0.4rem 0.4rem #FFFF8D, -0.5rem 0.5rem #CCFF90, -0.6rem 0.6rem #A7FFEB, -0.7rem 0.7rem #82B1FF, -0.8rem 0.8rem #B388FF;
  }
  50% {
    text-shadow: -0.1rem 0.1rem #B388FF, -0.2rem 0.2rem #EA80FC, -0.3rem 0.3rem #FF80AB, -0.4rem 0.4rem #FFD180, -0.5rem 0.5rem #FFFF8D, -0.6rem 0.6rem #CCFF90, -0.7rem 0.7rem #A7FFEB, -0.8rem 0.8rem #82B1FF;
  }
  62.5% {
    text-shadow: -0.1rem 0.1rem #82B1FF, -0.2rem 0.2rem #B388FF, -0.3rem 0.3rem #EA80FC, -0.4rem 0.4rem #FF80AB, -0.5rem 0.5rem #FFD180, -0.6rem 0.6rem #FFFF8D, -0.7rem 0.7rem #CCFF90, -0.8rem 0.8rem #A7FFEB;
  }
  75% {
    text-shadow: -0.1rem 0.1rem #A7FFEB, -0.2rem 0.2rem #82B1FF, -0.3rem 0.3rem #B388FF, -0.4rem 0.4rem #EA80FC, -0.5rem 0.5rem #FF80AB, -0.6rem 0.6rem #FFD180, -0.7rem 0.7rem #FFFF8D, -0.8rem 0.8rem #CCFF90;
  }
  87.5% {
    text-shadow: -0.1rem 0.1rem #CCFF90, -0.2rem 0.2rem #A7FFEB, -0.3rem 0.3rem #82B1FF, -0.4rem 0.4rem #B388FF, -0.5rem 0.5rem #EA80FC, -0.6rem 0.6rem #FF80AB, -0.7rem 0.7rem #FFD180, -0.8rem 0.8rem #FFFF8D;
  }
  100% {
    text-shadow: -0.1rem 0.1rem #FFFF8D, -0.2rem 0.2rem #CCFF90, -0.3rem 0.3rem #A7FFEB, -0.4rem 0.4rem #82B1FF, -0.5rem 0.5rem #B388FF, -0.6rem 0.6rem #EA80FC, -0.7rem 0.7rem #FF80AB, -0.8rem 0.8rem #FFD180;
  }
}

</style>
</div>
</h5>
</font></center><font color="#414324">
        <div class="flip-box-1-3">
        <div class="block-content block-content-mini block-content-full">
            <div class="btn-group btn-group-justified">
				<div class="btn-group">
<a class="btn btn-default" data-toggle="modal" href="./sup"><img src="https://z3.ax1x.com/2021/06/19/RCRVzT.png">&nbsp;<font color="#B008B"><span style="font-weight:bold">供货上架</span></font></a>
					</div>
					 	<a href="#anounce" target="_blank" data-toggle="modal" class="btn btn-default"><img src="https://z3.ax1x.com/2021/06/19/RCRtyD.gif">&nbsp;<span style="font-weight:bold"><b><font color="#DC143C">平台公告</font></b></span></a>
						                <div class="btn-group">
                 <a class="btn btn-default" data-toggle="modal" href="./user/login.php"><img src="https://z3.ax1x.com/2021/06/19/RCRNOe.gif">&nbsp;<b><font color="#0000CD">注册登录</font></b></a></div>
				</div>
             <center>
          <!--         <div <span style="font-weight:bold"><span style="color: #90EE90;"> <img border="0" width="22" src="assets/beautify/img/iconfont-pinglun.png"></i></span> <a class="my-text"> <span style="color: #006400; font-family: YourFontName">售后问题『 7×24 』在线人工客服</a><a class="btn btn-xs btn-danger " style="border:1px solid #b3cde3; background: " href="/chat.html">点击联系客服</a>-->
         	<!--</div>-->
         	<div class="btn-group">
                <a class="btn btn-default" href="<?php echo !empty($siterow['kfwx']) ? $siterow['kfwx'] : $DB->getColumn("SELECT v FROM pre_config WHERE k=:key LIMIT 1", [':key' => 'kfwx']) ?>" target="_blank">&nbsp;&nbsp;&nbsp;<i class="fa fa-comment text-dark">
                </i>
 &nbsp;&nbsp;24小时在线售后客服&nbsp;&nbsp;&nbsp;</a>
			 </div>

                 <center>

	 </center></div>

    </div>
      </font></div><font color="#414324">

    	<style>
    #nr{
    	font-size:20px;
    	margin: 0;
        background: -webkit-linear-gradient(left,
            #ffffff,
            #ff0000 6.26%,
            #ff7d00 12.5%,
            #ffff00 18.75%,
            #00ff00 26%,
            #00ffff 31.26%,
            #0000ff 37.5%,
            #ff00ff 43.75%,
            #ffff00 50%,
            #ff0000 56.26%,
            #ff7d00 62.5%,
            #ffff00 68.75%,
            #00ff00 75%,
            #00ffff 81.26%,
            #0000ff 87.5%,
            #ff00ff 93.75%,
            #ffff00 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-size: 200% 100%;
        animation: masked-animation 2s infinite linear;
    }
    @keyframes masked-animation {
        0% {
            background-position: 0 0;
        }
        100% {
            background-position: -100%, 0;
        }
    }



</style>

<!--<div style="background-color:#333;border-radius: 25px;box-shadow: 0px 0px 5px #f200ff;padding:5px;margin-top: 10px;margin-bottom:0px;">
    <marquee>
    	<b id="nr">站长祝各位幸福安康，快乐美满，好事成双，生意兴隆，有项目上架联系客服上架，有问题第一时间找客服处理，友友们请第一时间收藏本网址-售后做不到最好,但是一定竭尽全力去解决您遇到的问题-开通分站,密价提卡，诚信邀代理!</b>
    </marquee>
    </div>。-->

<!--本代码由 XG 创建
  严禁反编译、逆向等任何形式的侵权行为，违者将追究法律责任
 -->
<!--顶部导航-->
</font><div class="tab-content"><font color="#414324">

</font><div class="block" style="margin-top:15px;font-size:15px;padding:1px;border-radius:15px;background-color: white;"><font color="#414324">

        <ul class="nav nav-tabs btn btn-block animated zoomInLeft btn-rounded" data-toggle="tabs">
            <li style="width: 25%;" align="center" class="active"><a href="#shop" data-toggle="tab"><span style="font-weight:bold"><i class="fa fa-shopping-cart"></i> <font color="#0000FF">下单</font></span></a></li>
            <li style="width: 25%;" align="center"><a href="#search" data-toggle="tab" id="tab-query"><span style="font-weight:bold"><i class="fa fa-search"></i> <font color="#8B008B">查单</font></span></a></li>
			<li style="width: 25%;" align="center"><a href="#Substation"><font color="#FF4000"><i class="fa fa-location-arrow fa-spin"></i> <b>分站</b></font></a></li>

			<li style="width: 25%;" align="center"><a href="#more" data-toggle="tab"><span style="font-weight:bold"><i class="fa fa-folder-open"></i> <font color="#FF8C00">更多</font></span></a></li>
        </ul>






    </marquee>

    </marquee>
    <div class="block-content tab-content">


<!--TAB标签-->
<!--在线下单-->

<div class="tab-content">

    <div class="tab-pane active" id="shop">

    <div  style=" z-index:9999; text-decoration:none; font-weight:bold; position: fixed; z-index: 999; Left: -6px; bottom: 230px; display: inline-block; width: 20px; border-top-left-radius: 10px; border-top-Left-radius: 5px; border-bottom-Left-radius: 5px; border-bottom-left-radius: 0px; color: white; font-size: 17px; line-height: 17px; box-shadow: rgb(100 149 237) 0px 0px 5px; word-wrap: break-word; padding: 8px 12px; border: 2px solid white; background: rgb(100 149 237);"><a href="/toollogs.php" target="_blank" style="position: relative;left: -7px;top: 2px; color:#ffffff;">上架通知</a ></div>

    <div style=" z-index:9999; text-decoration:none; font-weight:bold; position: fixed; z-index: 999; Left: -6px; bottom: 128px; display: inline-block; width: 20px; border-top-left-radius: 10px; border-top-Left-radius: 5px; border-bottom-Left-radius: 5px; border-bottom-left-radius: 10px; color: white; font-size: 17px; line-height: 17px; box-shadow: rgb(205 92 92) 0px 0px 5px; word-wrap: break-word; padding: 8px 13px; border: 2px solid white; background: rgb(205 92 92);">
    <a href="https://chuantu.fxszx.top/" style="position: relative;left: -7px;top: 2px; color:#ffffff;" target="_blank">页端转图</a>
    </div>

    <center><span style="font-size:11px;"><strong><span><span style="color:#E53333;">下单流程</span>: &nbsp;</span><span><span style="color:#0000EE;">选择分类</span>≯ &nbsp;</span><span style="color:#009900;">选择商品<span style="color:#E53333;">≯</span></span><span> &nbsp;</span><span style="color:#EE33EE;">填写信息</span><strong>≯ &nbsp;<span style="color:#006600;">下单成功</span></strong><strong><span>&nbsp; ≯ &nbsp;</span><span style="color:#64451D;">查询订单</span></strong></strong></span>
       <p></p>
      </center>

<!--<center> <a href="./sup" target="_blank" class="btn btn-sm btn-success btn-xs">点击入驻供货</a>      <a href="./user/regsite.php" target="_blank" class="btn btn-sm btn-warning btn-xs">点击开通分站</a> </center>。-->

<?php include TEMPLATE_ROOT . 'default/shop.inc.php';?>
<marquee>
    	<b id="nr">诚信经营,价格最低,货源最全,卡密问题质保可退换,放心下单即可!!!</b>
    </marquee>
	</div>

<!--在线下单-->

<!--查询订单-->

						<div class="tab-pane fade fade-up" id="search">


<table class="table table-striped table-borderless table-vcenter remove-margin-bottom">
         <tbody>
            <tr class="shuaibi-tip animation-bigEntrance">
                <td class="text-center" style="width: 90px;">
                    <img src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq'] ?>&spec=100" alt="avatar" class="img-circle img-thumbnail img-thumbnail-avatar">
                </td>
                <td>
                    <h4><i class="fa fa-child text-danger"></i>运营站长<h4>
					<h6><i class="fa fa-fw fa-qq text-primary"></i> <?php echo $conf['kfqq'] ?><br>
					<i class="fa fa-fw fa-history text-danger"></i>使用问题请联系站长<h6>

                </td>
                <td class="text-right" style="width: 20%;">
                   <a styel="letter-spacing: 3px;"   <a href="#customerservice" target="_blank" data-toggle="modal" class="btn btn-sm btn-info">联系</a>
                </td>
            </tr>
         </tbody>
        </table>



<div class="col-xs-12 well well-sm animation-pullUp">



<span style="background-color:#FFE500;color:#E53333;"><strong>卡密信息平台只会保留三天，请自行保存好卡密</strong></span><br>
<span class="label btn-danger">待处理</span>显示待处理说明库存不足或发卡延迟，请等待站长处理<br>





<font color="#0000FF">-------------最简单的查单方式--------------</font><br>
<font color="#DC143C">什么浏览器购买的，直接用什么浏览器打开，什么也别填写，直接点立即查询。在手机QQ打开购买的，用手机QQ打开网址点立即查询！</font><br>		</div>
							<div class="form-group">
								<div class="input-group">
									<div class="input-group-btn">
										<select class="form-control" id="searchtype" style="padding: 6px 4px;width:90px"><option value="0">下单账号</option><option value="1">17位单号</option></select>
									</div>
									<input type="text" name="qq" id="qq3" value="" class="form-control" placeholder="请输入要查询的内容（留空则显示最新订单）" required="">
									<span class="input-group-btn"><a href="#cxsm" data-toggle="modal" class="btn btn-warning">说明</a></span>
								</div>
							</div>

								<input type="submit" id="submit_query" class="btn btn-primary btn-block"
							value="立即查询">



									<!--<input type="submit" id="submit_query" class="btn btn-default btn-block btn-rounded" style="background-image: url(https://pan.suyanw.cn/view.php/6240ef859a11d3a31a7b3ccb0358dc02.jpg);font-weight:bold" value="立即查询">。-->

								<font color="red">
										<i class="">
										</i>
									</font>
									<font color="red">
				查单号:请输入您购买时候填写的QQ号，如果填写的时候忘记QQ号请点击立即查询即可！

									</font>
							<br>
							<div id="result2" class="form-group" style="display:none;">
								<center>
									<small>
										<font color="#ff0000">
											手机用户可以左右滑动
										</font>
									</small>
								</center>
								<div class="table-responsive">
									<table class="table table-vcenter table-condensed table-striped">
										<thead>
											<tr>
												<th class="hidden-xs">
													下单账号
												</th>
												<th>
													商品名称
												</th>
												<th>
													数量
												</th>
												<th class="hidden-xs">
													购买时间
												</th>
												<th>
													状态
												</th>
												<th>
													操作
												</th>
											</tr>
										</thead>
										<tbody id="list">
										</tbody>
									</table>
								</div>
							</div>
						</div>
	  <!--
  本代码由 XG 创建
  严禁反编译、逆向等任何形式的侵权行为，违者将追究法律责任
 -->
<!--查询订单-->
<!--开通分战-->
    <div class="tab-pane" id="Substation">
	<table class="table table-borderless animated bounceIn" style="text-align: center;">
    <tbody>
      <tr class="active">
        <td>
          <h4>
            <span style="font-weight:bold">
              <font color="#FF8000">搭</font>
              <font color="#EC6D13">建</font>
              <font color="#D95A26">属</font>
              <font color="#C64739"></font>
              <font color="#A0215F">自</font>
              <font color="#8D0E72">己</font>
              <font color="#5400AB">的</font>
              <font color="#4100BE">货</font>
              <font color="#2E00D1">源</font>
              <font color="#1B00E4">站</font></span>
          </h4>
        </td>
      </tr>
      <tr class="active">
        <td>开通分站零成本高收益</td></tr>
      <tr class="active">
        <td>
          <strong>
            网站轻轻松松推广日挣上千￥不是梦</strong></td>
      </tr>
            <tr class="active">
        <td><span class="glyphicon glyphicon-magnet"></span>&nbsp;快加入我们成为大家庭中的一员吧<hr> <a href="#userjs" data-toggle="modal" class="btn btn-effect-ripple  btn-info btn-sm" style="float:left;overflow: hidden; position: relative;">
            <span class="glyphicon glyphicon-eye-open"></span>&nbsp;分站介绍</a>
          <a href="./user/regsite.php" target="_blank" class="btn btn-effect-ripple  btn-success btn-sm" style="float:right;overflow: hidden; position: relative;">
            <span class="glyphicon glyphicon-share-alt"></span>&nbsp;开通分站</a></td></tr>
      <tr>
    </tbody>
  </table>
	</div>
<!--开通分战-->
	  <!--
  本代码由 XG 创建
  严禁反编译、逆向等任何形式的侵权行为，违者将追究法律责任
 -->
<!--抽奖-->
    <div class="tab-pane" id="gift">
		<div class="panel-body text-center">
		<div id="roll">点击下方按钮开始</div>
		<hr>
		<p>
		<a class="btn btn-info" id="start" style="display:block;">开始</a>
		<a class="btn btn-danger" id="stop" style="display:none;">停止</a>
		</p>
		<div id="result"></div><br/>
		<div class="giftlist" style="display:none;"><strong>最近记录</strong><ul id="pst_1"></ul></div>
		</div>
	</div>
<!--抽奖-->
<!--更多-->
						<div class="tab-pane fade fade-right" id="more">
							<div class="col-xs-6 col-sm-4 col-lg-4">
								<a class="block block-link-hover2 text-center" href="./user/" target="_blank">
									<div class="block-content block-content-full bg-city">
										<i class="fa fa-certificate fa-3x text-white">
										</i>
										<div class="font-w600 text-white-op push-15-t">
											站长后台
										</div>
									</div>
								</a>
							</div>

							<div class="col-xs-6 col-sm-4 col-lg-4">
								<a class="block block-link-hover2 text-center" href="/chat.html">
									<div class="block-content block-content-full bg-success">
										<i class="fa fa-comments fa-3x text-white">
										</i>
										<div class="font-w600 text-white-op push-15-t">
											售后客服
										</div>
									</div>
								</a>

							</div>
	</div>
	</div>
<!--更多-->
<!--版本介绍-->
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
                            <td>专属卡密平台</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
                        <tr class="">
                            <td>三种在线支付接口</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
						<tr class="success">
                            <td>专属网站域名</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
						<tr class="">
                            <td>賺取用户提成</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>

						<tr class="">
                            <td>设置商品价格</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
                        	<tr class="info">
                            <td>賺取下级分战提成</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-danger"><i class="fa fa-close"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
						<tr class="warning">
                            <td>设置下级分战商品价格</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-danger"><i class="fa fa-close"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
						<tr class="">
                            <td>搭建下级分战</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-danger"><i class="fa fa-close"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
						<tr class="danger">
                            <td>赠送专属精致APP</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-danger"><i class="fa fa-close"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
                    </tbody>
                </table>
            </div>
				<center style="color: #b2b2b2;"><small><em>* 自己的能力决定着你的收入！</em></small></center>
        </div>
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
		</div>
    </div>
  </div>
</div>
<!--版本介绍-->
    </div>
</div>
	  <!--
  本代码由 XG 创建
  严禁反编译、逆向等任何形式的侵权行为，违者将追究法律责任
 -->
<!--关我们弹窗-->
<div class="modal fade" align="left" id="customerservice" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
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
					订单显示（已完成）就证明已经提交到服务器内！并不是订单已刷完。<br>
					可以立即提交工单，客服会优先给您处理！<br>
					订单长时间显示（待处理）请联系客服！
					</div>
				</div>
			</div>
			<div class="panel panel-default" style="margin-bottom: 6px;">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" class="collapsed">QQ会员钻类/代刷业务什么时候到账？</a>
					</h4>
				</div>
				<div id="collapseTwo" class="panel-collapse collapse" style="height: 0px;">
					<div class="panel-body">
					下单后的48小时内到账（QQ会员或代刷业务全部都是一样48小时内到账）！<br>
					如果超过48小时，请联系客服退款或补单，提供QQ号码！或提交工单
					</div>
				</div>
			</div>
			<div class="panel panel-default" style="margin-bottom: 6px;">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" data-parent="#accordion" href="#collapseThree" class="collapsed">购买之后不会使用？</a>
					</h4>
				</div>
				<div id="collapseThree" class="panel-collapse collapse" style="height: 0px;">
					<div class="panel-body">下载地址内有教程请自己仔细多看说明，如果没有教程请自己多研究<br>
					平台不提供任何姿势和教学，卡密问题请联系客服！
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
					<div class="panel-body" style="margin-bottom: 6px;">先点击（查单）按钮输入你填写的信息去查单，如果查询不到，请联系客服处理<br>请提供（付䕀详细记录截图）（下单商品名称）（下单账号）!<br>
                         直接把以上三个信息发给客服，然后等待客服回复处理！
					</div>
				</div>
			</div>
			<ul class="list-group" style="margin-bottom: 0px;">
			<li class="list-group-item">
			   <div class="media">
					<span class="pull-left thumb-sm"><img src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq'] ?>&spec=100" alt="..." class="img-circle img-thumbnail img-avatar"></span>
			   <div class="pull-right push-15-t">
					<a href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo $conf['kfqq'] ?>&site=qq&menu=yes" target="_blank"  class="btn btn-sm btn-info">联系</a>
			   </div>
			   <div class="pull-left push-10-t">
					<div class="font-w600 push-5">运营站长</div>
					<div class="text-muted"><b>QQ：<?php echo $conf['kfqq'] ?></b>
					</div>
			   </div>
			   </div>
			</li>
			<li class="list-group-item">
			想要快速回答你的问题就请把问题描述讲清楚!<br>
			下单账号+业务名称+问题，直奔主题，按顺序回复!<br>
			有问题直接留言，请勿抖动语音否则直接无视。<br>
			</li>
			</ul>
		</div>
    </div>
  </div>
</div>
</style>


	  <!--
  本代码由 XG 创建
  严禁反编译、逆向等任何形式的侵权行为，违者将追究法律责任
 -->
<div class="block animated bounceInDown btn-rounded" style="border:1px solid #FFF0F5;margin-top:15px;font-size:15px;padding:15px;border-radius:15px;background-color: white;"><div class="panel-heading"><h3 class="panel-title" types=""><font color="#000000"><span class="glyphicon glyphicon-stats"></span>&nbsp;&nbsp;<b>今日订单详细</b><img src="https://z3.ax1x.com/2021/06/19/RCRtyD.gif"/></i></a></span></h3></div>
<div class="btn-group btn-group-justified">
		<a target="_blank" class="btn btn-effect-ripple btn-default collapsed" style="overflow: hidden; position: relative;"><b><font color="modal-title">购买用户</font></b></a>
		<a target="_blank" class="btn btn-effect-ripple btn-default collapsed" style="overflow: hidden; position: relative;"><b><font color="modal-title">下单日期</font></b></a>
		<a target="_blank" class="btn btn-effect-ripple btn-default collapsed" style="overflow: hidden; position: relative;"><b><font color="modal-title">物品名称</font></b></a>
		</div>
		<marquee class="zmd" behavior="scroll" direction="UP" onmouseover="this.stop()" onmouseout="this.start()" scrollamount="5" style="height:16em">
			<table class="table table-hover table-striped" style="text-align:center">
				<thead>
				    <h4 class="modal-title" id="myModalLabel">
                    <?php
$c = 80;
for ($a = 0; $a < $c; $a++) {
    $sim = rand(1, 5); #随机数
    $a1  = ''; #超级会员
    $a2  = ''; #视频会员
    $a3  = ''; #豪华黄钻
    $a4  = ''; #豪华绿钻
    $a5  = ''; #名片赞
    $e   = 'a' . $sim;
    if ($sim == '1') {
        $name = '超级会员';
    } else if ($sim == '2') {
        $name = '视频会员';
    } else if ($sim == '3') {
        $name = '豪华黄钻';
    } else if ($sim == '4') {
        $name = '腾讯视频会员月卡';
    } else if ($sim == '5') {
        $name = '爱奇艺年卡黄金会员';
    } else if ($sim == '6') {
        $name = '腾讯视频会员周卡';
    } else if ($sim == '7') {
        $name = '爱奇艺月卡黄金会员';
    } else if ($sim == '8') {
        $name = '优酷会员天卡卡密';
    } else if ($sim == '9') {
        $name = '哔哩哔哩月卡';
    }

    $date = date('Y-m-d'); #今日
    $time = date("Y-m-d", strtotime("-1 day"));
    if ($a > 50) {
        $date = $time;
    } else {
        if (date('H') == 0 || date('H') == 1 || date('H') == 2) {
            if ($a > 9) {
                $date = $time;
            }
        }
    }
    echo '<tr></tr><tr><td>本站用户' . rand(10, 999) . '***' . rand(100, 999) . '</td><td>于' . $date . '日下单成功</td><td><font color="0000">' . $name . '</font></td></tr>';
}
?>
                    </thead>
                </table>
            </marquee>
        </div>
<?php if ($conf['articlenum'] > 0) {
    $limit  = intval($conf['articlenum']);
    $rs     = $DB->query("SELECT id,title FROM pre_article WHERE active=1 ORDER BY top DESC,id DESC LIMIT {$limit}");
    $msgrow = array();
    while ($res = $rs->fetch()) {
        $msgrow[] = $res;
    }
    $class_arr = ['danger', 'warning', 'primary', 'success', 'info'];
    $i         = 0;
    ?>

	<!--文章列表-->
				<div class="block block-themed" style="border-radius: 20px;box-shadow:0 5px 10px 0 rgba(0, 0, 0, 0.09);">
					<div class="block-header bg-amethyst" style="border-top-left-radius: 20px; border-top-right-radius: 20px;background-color: #b3cde3;border-color: #b3cde3; padding: 10px 10px;">
						<h3 class="block-title"><i class="fa fa-newspaper-o"></i> 文章列表</h3>
					</div>
					<?php foreach ($msgrow as $row) {
        echo '<a target="_blank" class="list-group-item" href="' . article_url($row['id']) . '"><span class="btn btn-' . $class_arr[($i++) % 5] . ' btn-xs">' . $i . '</span>&nbsp;' . $row['title'] . '</a>';
    }?>
					<a href="<?php echo article_url() ?>" title="查看全部文章" class="btn-default btn btn-block" style="border-bottom-left-radius: 20px; border-bottom-right-radius: 20px;font-weight: 100;/* border-radius: 20px; */-webkit-transition: all 0.15s ease-out;transition: all 0.15s ease-out;" target="_blank">查看全部文章</a>
				</div>
				<!--文章列表-->
<?php
}?>
<div class="panel panel-primary">
<div class="panel-heading"><h3 class="panel-title"><font color="#000000"><i class="fa fa-bar-chart-o"></i>&nbsp;&nbsp;<b>近30天数据统计</b></font></h3></div>
<table class="table table-bordered">
<tbody>
<tr>
<td align="center"><font size="2"><b><font color=#0000FF>896<span id="count_yxts"></span>关键词</font><b/><br><font color="#65b1c9"><img src="https://z3.ax1x.com/2021/06/19/RC44DU.jpg"/></i></font><br>百度收录</font></td>
<td align="center"><font size="2"><b><font color="#DC143C">999+<span id="cou1nt_yxts"></span>软妹币</font><b/><br><font color="#65b1c9"><img src="https://z3.ax1x.com/2021/06/19/RC595d.jpg"/></i></font><br>销售金额</font></td>
<td align="center"><font size="2"><b><font color=#8B4513>999+<span id="co1unt_yxts"></span>次好评</font><b/><br><font color="#65b1c9"><img src="https://z3.ax1x.com/2021/06/19/RC45bF.jpg"/></i></font><br>用户好评</font>

</tbody>
</table>

<div class="block block-content block-content-mini block-content-full" style="box-shadow:0px 5px 10px 0 rgba(0, 0, 0, 0.26);">
	<!--网站日志-->
	<!--<div class="row text-center" >-->
	<!--	<div class="col-xs-4">-->
	<!--		<h5 class="widget-heading"><small>订单总数</small><br><a href="javascript:void(0)" class="themed-color-flat"><span id="count_orders"></span>条</a></h5>-->
	<!--	</div>-->
	<!--	<div class="col-xs-4">-->
	<!--		 <h5 class="widget-heading"><small>今日订单</small><br><a href="javascript:void(0)" class="themed-color-flat"><span id="count_orders2"></span>条</a></h5>-->
	<!--	</div>-->
	<!--	<div class="col-xs-4">-->
	<!--		<h5 class="widget-heading"><small>运营天数</small><br><a href="javascript:void(0)" class="themed-color-flat"><span id="count_yxts"></span>天</a></h5>-->
	<!--	</div>-->
	<!--</div>-->
	<!--底部导航-->
				<div class="block-content text-center border-t">
		<a href="javascript:void(0);" onclick="AddFavorite('QQ代刷网',location.href)">
  <b style="text-shadow: LightSteelBlue 1px 0px 0px;">
  <i class="fa fa-edge text-danger animation-pulse"></i>
  <font color=#CB0034>本</font>
  <font color=#BE0041>站</font>
  <font color=#B1004E>网</font>
  <font color=#A4005B>址</font>
  <font color=#970068>：<?php echo $_SERVER['HTTP_HOST']; ?></font>
  <font color=#2F00D0></font>
  <font color=#CB0034>&nbsp;</font>
  <font color=#CB0034>建</font>
  <font color=#BE0041>议</font>
  <font color=#B1004E>收</font>
  <font color=#A4005B>藏</font>
  </b>
</a><br><br>
<?php echo $conf['footer'] ?>
<script>LA.init({id: "JTj6MWryNtZKd9e5",ck: "JTj6MWryNtZKd9e5"})</script>
<span style="font-size:14px;font-weight:700;color:#E53333;background-color:#FFE500;font-family:&quot;"><span style="color:#FF9900;background-color:#FFFFFF;font-size:12px;"><strong>项目/上架/对接/批卡/请联系在线客服</strong></span></span>
			</div><br>
                                            <center>
                                                <img src="https://pan.suyanw.cn/view.php/0604f9f4cf2b895fe4da0b163add338e.png" height="26px"></img>

                                                <img src="https://pan.suyanw.cn/view.php/d1e978792c2b796a04514a277fa72b5c.jpg" height="26px"></img>

                                                <img src="https://pan.suyanw.cn/view.php/0c28f568861d37e9e58f2a22bba2506a.jpg" height="26px"></img>

                                                <img src="https://pan.suyanw.cn/view.php/dc1f6a276f1f6a05bd7afd504ce182b7.jpg" height="26px"></img>
                                            </center>
	<!--底部导航-->
</div>
</div>
</font></div><font color="#000000">






    <!--<div  style="z-index:9999; text-decoration:none; font-weight:bold; position: fixed; z-index: 999; Left: -6px; bottom: 120px; display: inline-block; width: 20px; border-top-left-radius: 10px; border-top-Left-radius: 5px; border-bottom-Left-radius: 5px; border-bottom-left-radius: 10px; color: white; font-size: 17px; line-height: 17px; box-shadow: rgb(102, 102, 102) 0px 0px 5px; word-wrap: break-word; padding: 8px 12px; border: 2px solid white; background: rgb(242, 12, 12);"><a href="https://zt.sykt2022.top/" target="_blank" style="position: relative;left: -7px;top: 2px; color:#ffffff;">每日效果图</a ></div>-->

<!--每日推荐-->
<div class="custom-richtext" >





            <!--  <p  style=" z-index:9999; text-decoration:none; font-weight:bold; position: fixed; z-index: 999; Left: -6px; bottom: 218px; display: inline-block; width: 20px; border-top-left-radius: 10px; border-top-Left-radius: 5px; border-bottom-Left-radius: 5px; border-bottom-left-radius: 10px; color: white; font-size: 17px; line-height: 17px; box-shadow: rgb(72,209,204) 0px 0px 5px; word-wrap: break-word; padding: 8px 12px; border: 2px solid white; background: rgb(72,209,204);"><a href="#xinshoubangzhu" target="_blank" data-toggle="modal" style="position: relative;left: -7px;top: 2px; color:#fff;" target="_blank">每日推荐</a></p></div>
<div class="modal fade col-xs-12" align="left" id="xinshoubangzhu" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >  <br />  <br />
  <div class="modal-dialog panel panel-primary  animation-fadeInQuick2">
    <div class="modal-content">
<div class="panel-body">
  <h4 style="color:red;font-size:17px;text-align:center;">每日推荐</h4></br>
<center>

<h6 style="color:red">和平直装</h6></center>超人,幻神,二郎神,鸡冠,魔法,九色鹿,法拉利,茄子,猪猪侠</br>





 <hr>

<center><h6 style="color:red">和平root</h6></center>内部手搓,Sketch内核,xo,XL内部,内部无极,仙鹤内核,Broken,Mimi,凯撒,太极内核,路人，小蜜蜂驱动，影子追踪，雷神，正版火花，火花增强版</br>




<hr>




<center><h6 style="color:#009900;">王者直装</h6></center>坚果,企鹅,武则天,XGdj,光头强直装,小土豆增强版,朱雀</br>



<hr>


<center><h6 style="color:#009900;">王者root</h6></center>CC内核,xo,zoo定制,nikou内核尊贵pro版,黑镜 Pro,猎手,koyomi定制内核,坚果,nikou,六花,green内核,白泽</br>

<hr>
<center><h6 style="color:#00D5F">地铁直装</h6></center>花瓶直装,拿破仑,所罗门,八爪鱼,NASA直装,吞魂兽</br>




<hr>




<center><h6 style="color:#00D5F">地铁root</h6></center>XO,小泡芙,苍穹内核,巡查员内核,Sketch,星河,圣罗兰,呱呱,柚子,鸡腿中文版,正版火花</br>




<hr>




<center><h6 style="color:#60D978">暗区突围</h6></center>老兵,浩克,晚风直装,代码K,哪吒,百步穿杨,落叶内核,Microsoft</br>




<hr>




<center><h6 style="color:#CC33E5">cfm</h6></center>奥斯卡,焚天直装,光环助手,全屏子追,芒果改枪术,波吉,巅峰子追,红富士,金枪鱼,迈凯伦,Cc,六花,AOE内核,辉煌内核,小飞内核</br>




<hr>

<center><h6 style="color:red">防封端口</h6></center>蕴意和平,青云,光环全防,XO私端,JY游戏加速器,窃瑶私端,green</br>








</div>

<h8 style="color:red">诚邀供货商“提现无手续费,结算不墨迹”</h8><a href="./sup" target="_blank" class="btn btn-sm btn-success btn-xs">点我入驻供货</a>


         <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">我知道了</button>
      </div>
    </div>
  </div>
 </div> 。-->





        	<!--域名访问次数统计-->
	<style>
        img.hidden {
            visibility: hidden;
        }
    </style>
	<img class="hidden" src="https://api.shserve.cn/api/fwltj?name=<?php echo $_SERVER['HTTP_HOST'] ?>&theme=rule34">
	<!--域名访问次数统计-->





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
<div id="audio-play" <?php if (empty($conf['musicurl'])) {?>style="display:none;"<?php }?>>
  <div id="audio-btn" class="on" onclick="audio_init.changeClass(this,'media')">
    <audio loop="loop" src="<?php echo $conf['musicurl'] ?>" id="media" preload="preload"></audio>
  </div>
</div>
<!--音乐代码-->
<!--
  严禁反编译、逆向等任何形式的侵权行为，违者将追究法律责任
 -->
<script src="<?php echo $cdnpublic ?>jquery/1.12.4/jquery.min.js"></script>
<script src="<?php echo $cdnpublic ?>jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>
<script src="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="<?php echo $cdnpublic ?>jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="<?php echo $cdnpublic ?>layer/3.4.0/layer.js"></script>
<script src="<?php echo $cdnserver ?>assets/appui/js/app.js"></script>
	<script type="text/javascript">
		var isModal = <?php echo empty($conf['modal']) ? 'false' : 'true'; ?> ;
		var homepage = true;
		var hashsalt = <?php echo $addsalt_js ?> ;
		$(function() {
   		 	$("img.lazy").lazyload({
        		effect: "fadeIn"
    		});
		});
		var ss = 0,
		    mm = 0,
		    hh = 0;

		function TimeGo() {
		    ss++;
		    if (ss >= 60) {
		        mm += 1;
		        ss = 0
		    }
		    if (mm >= 60) {
		        hh += 1;
		        mm = 0
		    }
		    ss_str = (ss < 10 ? "0" + ss : ss);
		    mm_str = (mm < 10 ? "0" + mm : mm);
		    tMsg = "" + hh + "小时" + mm_str + "分" + ss_str + "秒";
		    document.getElementById("stime").innerHTML = tMsg;
		    setTimeout("TimeGo()", 1000)
		}
		TimeGo();
</script>
<script src="assets/js/main.js?ver=<?php echo VERSION ?>"></script>
<?php if ($conf['classblock'] == 1 || $conf['classblock'] == 2 && checkmobile() == false) {
    include TEMPLATE_ROOT . 'default/classblock.inc.php';
}
?>
</body>
</html>
</html>

<script type="text/javascript">
/* 鼠标特效 */


</script>


</font></div></aside></div></body>
