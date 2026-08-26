<?php
if (!defined('IN_CRONLITE')) {
    exit();
}
?>
<?php include '/include_send.php'; ?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="zh-cn">
<head>
  <meta charset="utf-8"/>
  <meta name="renderer"  content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
  <title><?php echo $conf['sitename'] ?> - <?php echo $conf['title'] ?></title>
  <meta name="keywords" content="<?php echo $conf['keywords'] ?>">
  <meta name="description" content="<?php echo $conf['description'] ?>">
  <link rel="shortcut icon" href="<?php echo $cdnserver ?>assets/img/favicon.png">
  <link href="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/main.css">
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/oneui.css">
  <script src="<?php echo $cdnpublic ?>modernizr/2.8.3/modernizr.min.js"></script>
  <script src="<?php echo $cdnpublic ?>jquery/1.12.4/jquery.min.js"></script>
  <?php
//加载插件代码
hook('head');
?>
  <!--[if lt IE 9]>
    <script src="<?php echo $cdnpublic ?>html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="<?php echo $cdnpublic ?>respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
<img src="<?php echo $background_image; ?>" alt="Full Background" class="full-bg full-bg-bottom animated pulse">

</head>
<body>
<br />
<?php include TEMPLATE_ROOT . 'default/head.inc.php';?>
<div class="col-xs-12 col-sm-10 col-md-8 col-lg-5 center-block" style="float: none;">
<!--弹出公告-->
<div class="modal fade" align="left" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header-tabs">
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
<div class="widget">
<!--logo-->
    <div class="widget-content themed-background-flat text-center" style="background-image: url(<?php echo $cdnserver ?>assets/xiaoyao/head4.png);background-size: 100% 100%;">
        <a href="javascript:void(0)">
      <img src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['qq']; ?>&spec=100" alt="Avatar" width="80" alt="avatar" style="height: auto filter: alpha(Opacity=80);-moz-opacity: 0.80;opacity: 0.80;" class="img-circle img-thumbnail img-thumbnail-avatar-1x animated zoomInDown">
        </a>
    </div>
  <img width="100%" src="<?php echo $cdnserver ?>assets/img/yewu.jpg">
<!--logo-->
<!--logo下面按钮-->
  <div class="widget-content themed-background-muted text-center ">
    <div class="btn-group themed-background-muted ">
      <a href="#anounce" data-toggle="modal" class="btn btn-effect-ripple btn-default collapsed "><b><font color="#ff0000"><i class="fa fa-wifi fa-fw"></i> <span style="font-weight:bold">公告</span></font></b></a>
      <?php if ($conf['cmkj_open'] == 1) {?>
      <a href="./kjshop.php" class="btn btn-effect-ripple btn-default"><i class="fa fa-gift"></i> <span style="font-weight:bold">砍价</span></a>
      <?php }?>
      <?php if ($isLogin2 == 1) {?>
      <a href="<?php echo $cdnserver ?>user/" class="btn btn-effect-ripple btn-default"><i class="glyphicon glyphicon-user"></i> <span style="font-weight:bold">后台</span></a>
      <?php } else {?>
      <a href="<?php echo $cdnserver ?>user/login.php" class="btn btn-effect-ripple btn-default"><i class="glyphicon glyphicon-user"></i> <span style="font-weight:bold">登录</span></a>
      <?php }?>
      <?php if ($conf['cmkj_open'] != 1) {?>
      <a href="<?php echo $cdnserver ?>user/reg.php" class="btn btn-effect-ripple btn-default"><i class="glyphicon glyphicon-plus"></i> <span style="font-weight:bold">注册</span></a>
      <?php }?>
    </div>

    <div id="mustsee" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
          <div id="mustsee" class="panel-collapse collapse in" aria-expanded="true" style="">

          </div>
    </div>
  </div>
  <?php
//加载插件代码
hook('header');
?>
<!--复制广告词分享开始-->
      <div class="modal fade col-xs-12 " align="left" id="fxhy" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <br>
        <br>
        <br>
        <div class="modal-dialog panel panel-primary  animation-fadeInQuick2">
          <div class="modal-content ">
            <div class="modal-header">
              <button type="button" class="close " data-dismiss="modal">
                <span aria-hidden="true">
                  <i class="fa  fa-times-circle"></i>
                </span>
                <span class="sr-only">Close</span></button>将网站分享给好友</div>
            <li class="list-group-item">
              <div class="input-group">
                <div class="input-group-addon">广告语</div>
                <textarea id="fxggc" class="form-control" rows="5" cols="30" readonly="" unselectable="on">哇，好消息！ 给你们分享一个很好用的网站。各种物美价廉的小商品、影视业务应有尽有，下单方便快捷！
点击访问：<?php echo $_SERVER['HTTP_HOST'] ?></textarea></div>
            </li>
            <li class="list-group-item">
              <button  data-clipboard-target="#fxggc" class="btn btn-sm btn-block btn-success fenx">点击一键复制广告语</button></li>
            <li class="list-group-item">将网站分享给你的好友，有机会获取神秘礼包哟！</li>
          </div>
        </div>
      </div>
<script src="<?php echo $cdnpublic ?>clipboard.js/1.7.1/clipboard.min.js"></script>
<script>
var clipboard = new ClipboardJS('.fenx');
clipboard.on('success', function(e) {
  layer.msg("复制成功,快去分享给朋友一起来领免费**吧！", {icon: 1});
});
clipboard.on('error', function(e) {
   layer.msg("复制失败，请长按链接后手动复制", {icon: 2});
});
</script>
<!--复制广告词分享结束-->

</div>

<div class="block full2">
<!--TAB标签-->
  <div class="block-title">

        <ul class="nav nav-tabs" data-toggle="tabs">
            <li style="width: 20%;" align="center" class="active"><a href="#shop" data-toggle="tab"><span style="font-weight:bold"><i class="fa fa-shopping-bag fa-fw hidden-xs"></i> 下单</span></a></li>
            <li style="width: 20%;" align="center"><a href="#search" data-toggle="tab" id="tab-query"><span style="font-weight:bold"><i class="fa fa-search hidden-xs"></i> 查询</span></a></li>
            <li style="width: 20%;" align="center" <?php if ($conf['fenzhan_buy'] == 0) {?>class="hide"<?php }?>><a href="#Substation" data-toggle="tab"><span style="font-weight:bold"><font color="#ff0000"><i class="fa fa-coffee fa-fw hidden-xs"></i> 赚钱</span></font></a></li>
            <?php if ($conf['index_article'] == 1) {?>
              <li style="width: 20%;" align="center"><a href="#news" data-toggle="tab"><span style="font-weight:bold"><i class="fa fa-gift fa-bars  hidden-xs"></i> 通知</span></a></li>
            <?php } elseif ($conf['gift_open'] == 1) {?>
            <li style="width: 20%;" align="center"><a href="#gift" data-toggle="tab"><span style="font-weight:bold"><i class="fa fa-gift fa-fw"></i> 抽奖</span></a></li>
            <?php }?>
            <li style="width: 20%;" align="center" <?php if ($conf['iskami'] == 0 || $conf['fenzhan_buy'] == 1 || $conf['gift_open'] == 1) {?>class="hide"<?php }?>><a href="#cardbuy" data-toggle="tab"><span style="font-weight:bold"><i class="glyphicon glyphicon-th hidden-xs"></i> 卡密</span></a></li>
            <li style="width: 20%;" align="center"><a href="#more" data-toggle="tab"><span style="font-weight:bold"><i class="fa fa-folder-open hidden-xs"></i> 更多</span></a></li>
        </ul>
    </div>
<!--TAB标签-->
    <div class="tab-content">
<!--在线下单-->
    <div class="tab-pane active" id="shop">
      <div class="alert alert-warning alert-dismissable text-center" style="background-color: #fdf3e5;" onclick="$('#fxhy').modal('show')">
      <a title="点击分享本站" style="color: red;"><img border="0" width="20" style="margin: 2px auto;" src="<?php echo $cdnserver ?>assets/xiaoyao/hot.gif" style="border-radius:50%">&nbsp;点这里将网站分享给好友,一起领取福利礼包！</a>
      </div>
       <center><span style="font-size:10px;"><strong><span><span style="color:#E53333;">下单流程</span>: &nbsp;</span><span><span style="color:#0000EE;">选择分类</span>≯ &nbsp;</span><span style="color:#009900;">选择商品<span style="color:#E53333;">≯</span></span><span> &nbsp;</span><span style="color:#EE33EE;">填写信息</span><strong>≯ &nbsp;<span style="color:#006600;">下单成功</span></strong><strong><span>&nbsp; ≯ &nbsp;</span><span style="color:#64451D;">查询订单</span></strong></strong></span>
       <p></p>
      </center>
    <?php include TEMPLATE_ROOT . 'default/shop.inc.php';?>
    </div>
<!--在线下单-->
<!--查询订单-->
    <div class=" tab-pane fade-up" id="search">
       <?php include TEMPLATE_ROOT . 'default/query.inc.php';?>
    </div>
<!--查询订单-->
<!--开通分站-->
    <div class="tab-pane" id="Substation">
    <table class="table table-borderless table-pricing">
            <tbody>
                <tr class="active">
                    <td>
                        <h4><i class="fa fa-cny fa-fw"></i><strong><?php echo $conf['fenzhan_price'] ?>元</strong> / <i class="fa fa-cny fa-fw"></i><strong><?php echo $conf['fenzhan_price2'] ?>元</strong><br><small>普及版 / 专业版两种分站供你选择</small></h4>
                    </td>
                </tr>
                <tr>
                    <td>无聊时可以赚点零花钱</td>
                </tr>
                <tr>
                    <td>还可以锻炼自己销售口才</td>
                </tr>
        <tr>
                    <td>宝妈、学生等网络兼职首选</td>
                </tr>
                <tr>
                    <td>分站满<?php echo $conf['tixian_min']; ?>元即可申请提现</td>
                </tr>
                <tr>
                    <td><strong>轻轻松松推广日赚100+不是梦</td>
                </tr>
                <tr class="active">
                    <td>
            <a href="#userjs" data-toggle="modal" class="btn btn-effect-ripple  btn-info"><i class="fa fa-th-list"></i><span class="btn-ripple animate" style="height: 100px; width: 100px; top: -34.4px; left: 2.58749px;"></span> 功能介绍</a>
                        <a href="<?php echo $cdnserver ?>user/regsite.php" target="_blank" class="btn btn-effect-ripple  btn-danger"><i class="fa fa-arrow-right"></i> 马上开通</a>
                    </td>
                </tr>
                <tr>
                    <td class="text-muted">
                        <small><em>* 欢迎加入网赚大家庭！</em></small>
                    </td>
                </tr>
            </tbody>
        </table>
    </table>
  </div>
<!--开通分站-->
<!--抽奖-->
    <div class="tab-pane" id="gift">
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
<!--抽奖-->
<!--更多-->
    <div class="tab-pane" id="more">
  <div class="row">
    <div class="col-sm-6<?php if ($conf['gift_open'] == 0) {?> hide<?php }?>">
            <a href="#gift" data-toggle="tab" class="widget">
                <div class="widget-content themed-background-info text-right clearfix" style="color: #fff;">
                    <div class="widget-icon pull-left">
                        <i class="fa fa-gift"></i>
                    </div>
                    <h2 class="widget-heading h3">
                        <strong>抽奖</strong>
                    </h2>
                    <span>在线抽奖领取免费商品</span>
                </div>
            </a>
    </div>
    <div class="col-sm-6<?php if (empty($conf['appurl']) || $is_fenzhan) {?> hide<?php }?>">
            <a href="<?php echo $conf['appurl']; ?>" target="_blank" class="widget">
                <div class="widget-content themed-background-info text-right clearfix" style="color: #fff;">
                    <div class="widget-icon pull-left">
                        <i class="fa fa-cloud-download"></i>
                    </div>
                    <h2 class="widget-heading h3">
                        <strong>APP下载</strong>
                    </h2>
                    <span>下载APP，下单更方便</span>
                </div>
            </a>

    </div>
    <div class="col-sm-6<?php if ($conf['invite_open'] == 0) {?> hide<?php }?>">
            <a  href="./?mod=invite" target="_blank" class="widget">
                <div class="widget-content themed-background-warning text-right clearfix" style="color: #fff;">
                    <div class="widget-icon pull-left">
                        <i class="fa fa-paper-plane-o"></i>
                    </div>
                    <h2 class="widget-heading h3">
                        <strong>免费领赞</strong>
                    </h2>
                    <span>推广本站免费领取**</span>
                </div>
            </a>
        </div>
    <div class="col-sm-6<?php if (empty($conf['dgurl'])) {?> hide<?php }?>">
            <a href="./?mod=daigua" class="widget">
                <div class="widget-content themed-background-success text-right clearfix" style="color: #fff;">
                    <div class="widget-icon pull-left">
                        <i class="fa fa-rocket"></i>
                    </div>
                    <h2 class="widget-heading h3">
                        <strong>QQ等级代挂</strong>
                    </h2>
                    <span>管理自己的QQ代挂</span>
                </div>
            </a>
        </div>
    <div class="col-sm-6<?php if (empty($conf['chatframe'])) {?> hide<?php }?>">
            <a href="#chat" data-toggle="tab" class="widget">
                <div class="widget-content themed-background-danger text-right clearfix" style="color: #fff;">
                    <div class="widget-icon pull-left">
                        <i class="fa fa-comments"></i>
                    </div>
                    <h2 class="widget-heading h3">
                        <strong>在线聊天</strong>
                    </h2>
                    <span>你我更亲近</span>
                </div>
            </a>
        </div>
    <div class="col-sm-6<?php if ($conf['iskami'] == 0) {?> hide<?php }?>">
            <a href="#cardbuy" data-toggle="tab" class="widget">
                <div class="widget-content themed-background-warning text-right clearfix" style="color: #fff;">
                    <div class="widget-icon pull-left">
                        <i class="fa fa-credit-card"></i>
                    </div>
                    <h2 class="widget-heading h3">
                        <strong>卡密下单</strong>
                    </h2>
                    <span>卡密下单方便快捷</span>
                </div>
            </a>
        </div>
    <div class="col-sm-6">
            <a  href="<?php echo $cdnserver ?>user/" target="_blank" class="widget">
                <div class="widget-content themed-background-info text-right clearfix" style="color: #fff;">
                    <div class="widget-icon pull-left">
                        <i class="fa fa-certificate"></i>
                    </div>
                    <h2 class="widget-heading h3">
                        <strong>分站后台</strong>
                    </h2>
                    <span>登录分站后台</span>
                </div>
            </a>
        </div>
  </div>
  </div>
<!--更多-->
<!--新闻资讯-->
<div class="tab-pane fade in" id="news">
  <?php include TEMPLATE_ROOT . 'default/news.inc.php';?>
</div>
<!--新闻资讯-->
<!--卡密下单-->
    <div class="tab-pane" id="cardbuy">
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
    <div id="km_alert_frame" class="alert alert-success animation-pullUp" style="display:none;font-weight: bold;"></div>
    <input type="submit" id="submit_card" class="btn btn-primary btn-block" value="立即购买">
    <div id="result1" class="form-group text-center" style="display:none;">
    </div>
    </div>
    <br />
  </div>
<!--卡密下单-->
<!--聊天-->
    <div class="tab-pane" id="chat">
    <?php echo $conf['chatframe'] ?>
    </div>
<!--聊天-->
    </div>
  <!--插件代码-->
  <!--插件代码-->
</div>

  <?php
//加载插件底部代码
hook('bottom');
?>

  <div class="block">
  <!--网站日志-->
  <div class="row text-center" <?php if ($conf['hide_tongji'] == 1) {?>style="display:none;"<?php }?>>
    <div class="col-xs-4">
      <h5 class="widget-heading"><small>订单总数</small><br><a href="javascript:void(0)" class="themed-color-flat"><span id="count_orders"></span>条</a></h5>
    </div>
    <div class="col-xs-4">
       <h5 class="widget-heading"><small>今日订单</small><br><a href="javascript:void(0)" class="themed-color-flat"><span id="count_orders2"></span>条</a></h5>
    </div>
    <div class="col-xs-4">
      <h5 class="widget-heading"><small>运营天数</small><br><a href="javascript:void(0)" class="themed-color-flat"><span id="count_yxts"></span>天</a></h5>
    </div>
  </div>
  <!--网站日志-->
  <!--底部导航-->
  <div class="block-content text-center border-t">
    <p><span style="font-weight:bold"><?php echo $conf['sitename'] ?> <i class="fa fa-heart text-danger"></i> 2019~<?php echo date("Y") ?> | </span><a class="" href="#kefu" style="font-weight:bold" data-toggle="modal">客服与帮助</span></a> | <a data-toggle="modal" href="#disclaimer" class="btn btn-xs btn-default"><font color="#ff0000">如有侵权点我</font></a>
    </p>
     <?php echo $conf['index_html_bottom'] ?>
  </div>
  <!--底部导航-->
</div>

</div>
<!--音乐代码-->
<div id="audio-play" <?php if (empty($conf['musicurl'])) {?>style="display:none;"<?php }?>>
  <div id="audio-btn" class="on" onclick="audio_init.changeClass(this,'media')">
    <audio loop="loop" src="<?php echo $conf['musicurl'] ?>" id="media" preload="preload"></audio>
  </div>
</div>
<!--音乐代码-->
<?php hook('footer_before');?>
<script src="<?php echo $cdnserver ?>assets/js/removead.js?<?php echo $jsver ?>"></script>
<script src="<?php echo $cdnpublic ?>jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>
<script src="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="<?php echo $cdnpublic ?>jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="<?php echo $cdnpublic ?>layer/3.4.0/layer.js"></script>
<script src="<?php echo $cdnpublic ?>distpicker/2.0.3/distpicker.min.js"></script>
<script src="<?php echo $cdnserver ?>assets/appui/js/app.js"></script>
<script type="text/javascript">
var isModal=<?php echo empty($conf['modal']) ? 'false' : 'true'; ?>;
var _modalType=<?php echo $conf['modal_type'] > 0 ? '1' : '0'; ?>;
var homepage=true;
var hashsalt=<?php echo $addsalt_js ?>;
$.ajax({
    type : "GET",
    url : "/ajax.php?act=create_url",
    dataType : 'json',
    async: true,
    success : function(data) {
        var msg = '哇，好消息！ 给你们分享一个很厉害的网站 可以免费刷**，**，**、**、全民K歌粉丝鲜花、QQ刷砖等等业务。还有超实惠小狮子全息手链，流量卡等等！！几毛钱就可以满足你当网红的梦！\n点击访问☞' + data.url;
        if(data.code == 0){
            $("#fxggc").val(msg);
        }else{
            $("#fxggc").val(msg);
        }
    }
});

</script>
<script src="<?php echo $cdnserver ?>assets/js/main.js?<?php echo VERSION ?>"></script>
<?php hook('footer_after');?>
</body>
</html>
