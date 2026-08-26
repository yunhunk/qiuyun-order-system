<?php
if (!defined('IN_CRONLITE')) {
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8"/>
  <meta name="renderer"  content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
  <title><?php echo $conf['sitename'] ?> - <?php echo $conf['title'] ?></title>
  <meta name="keywords" content="<?php echo $conf['keywords'] ?>">
  <meta name="description" content="<?php echo $conf['description'] ?>">
  <link href="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/oneui.css">
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/css/common.css?ver=<?php echo VERSION ?>">
  <script src="<?php echo $cdnpublic ?>modernizr/2.8.3/modernizr.min.js"></script>
  <?php
//加载插件代码
hook('head');
?>
  <!--[if lt IE 9]>
    <script src="<?php echo $cdnpublic ?>html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="<?php echo $cdnpublic ?>respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
<?php
include_once 'head.php';

include_once TEMPLATE_ROOT . 'default/head.inc.php';
?>
<?php if ($conf['template_purpleYear_bgui'] == 1 && !empty($background_image)) {?>
<img src="<?php echo $background_image; ?>" alt="Full Background" class="full-bg full-bg-bottom animated pulse">
<?php }?>
<body>
<div style="padding-top:6px;">
<div class="col-xs-12 col-sm-8 col-md-6 col-lg-4 center-block" style="float: none;">
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
<?php include 'gg.php';?>
<!--公告-->
<!--查单说明开始-->
<div class="modal fade" align="left" id="cxsm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">查询内容是什么？该输入什么？</h4>
      </div>
        <li class="list-group-item"><font color="red">请在右侧的输入框内输入您下单时，在第一个输入框内填写的信息</font></li>
        <li class="list-group-item">例如您购买的是QQ赞类商品，输入下单的QQ账号即可查询订单</li>
        <li class="list-group-item">例如您购买的是邮箱类商品，需要输入您的邮箱号，输入QQ号是查询不到的</li>
        <li class="list-group-item">例如您购买的是短视频类商品，输入视频链接即可查询，不要带其他中文字符</li>
        <li class="list-group-item"><font color="red">如果您不知道下单账号是什么，可以不填写，直接点击查询，则会根据浏览器缓存查询</font></li>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
      </div>
    </div>
  </div>
</div>
<!--查单说明结束-->

<!--顶部导航-->
    <?php include 'daohang.php';?>
     <?php
//加载插件代码
hook('header');
?>
<!--顶部导航-->
<div class="block animated bounceInDown btn-rounded" style="font-size:15px;background-color: white;box-shadow:0 5px 10px 0 rgba(0, 0, 0, 0.25);">
        <ul class="nav nav-tabs nav-tabs-alt animated zoomInLeft" data-toggle="tabs">
            <li style="width: 25%;" align="center" class="active"><a href="#shop" data-toggle="tab"><span style="font-weight:bold"><i class="fa fa-shopping-bag fa-fw"></i> 下单</span></a></li>
            <li style="width: 25%;" align="center"><a href="#search" data-toggle="tab" id="tab-query"><span style="font-weight:bold"><i class="fa fa-search"></i> 查询</span></a></li>
      <li style="width: 25%;" align="center" <?php if ($conf['fenzhan_buy'] == 0) {?>class="hide"<?php }?>><a href="#Substation" data-toggle="tab"><span style="font-weight:bold"><font color="#ff0000"><i class="fa fa-coffee fa-fw"></i> 分站</span></font></a></li>
      <li style="width: 25%;" align="center" <?php if ($conf['gift_open'] == 0 || $conf['fenzhan_buy'] == 1) {?>class="hide"<?php }?>><a href="#gift" data-toggle="tab"><span style="font-weight:bold"><i class="fa fa-gift fa-fw"></i> 抽奖</span></a></li>
      <li style="width: 25%;" align="center"><a href="#more" data-toggle="tab"><span style="font-weight:bold"><i class="fa fa-folder-open"></i> 更多</span></a></li>
        </ul>
<!--TAB标签-->
    <div class="block-content tab-content">
<!--在线下单-->
    <div class="tab-pane active" id="shop">
<?php include_once TEMPLATE_ROOT . 'default/shop.inc.php';?>
    </div>
<!--在线下单-->
<!--查询订单-->
    <?php include 'chaxun.php';?>
<!--查询订单-->
<!--开通分站-->
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
              <font color="#C64739">于</font>
              <font color="#A0215F">自</font>
              <font color="#8D0E72">己</font>
              <font color="#5400AB">的</font>
              <font color="#4100BE">代</font>
              <font color="#2E00D1">刷</font>
              <font color="#1B00E4">网</font></span>
          </h4>
        </td>
      </tr>
      <tr class="active">
        <td>学生/上班族/创业/休闲赚钱必备工具</td></tr>
      <tr class="active">
        <td>
          <strong>
            网站轻轻松松推广日赚上千元不是梦</strong></td>
      </tr>
            <tr class="active">
        <td><span class="glyphicon glyphicon-magnet"></span>&nbsp;快加入我们成为大家庭中的一员吧<hr>
            <a href="#userjs" data-toggle="modal" class="btn btn-effect-ripple  btn-info btn-sm" style="float:left;overflow: hidden; position: relative;">
            <span class="glyphicon glyphicon-eye-open"></span>&nbsp;网站详情介绍</a>
          <a href="./user/regsite.php" target="_blank" class="btn btn-effect-ripple  btn-success btn-sm" style="float:right;overflow: hidden; position: relative;">
            <span class="glyphicon glyphicon-share-alt"></span>&nbsp;免费开通网站</a></td></tr>
      <tr>
    </tbody>
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
<!--更多按钮开始-->
<div class="tab-pane fade" id="more"><table class="table table-bordered animated bounceIn"><tbody>

<tr height="50">
<td><a href="/?mod=daigua" target="_blank" data-toggle="modal" aria-expanded="false"class="tooltip-toggle btn btn-primary btn-sm btn-block" style="background: linear-gradient(to right,#14b7ff,#14b7ff);">
<i class="fa fa-credit-card"></i>&nbsp;等级代挂</a></td><td>
<a href="#gift" data-toggle="tab" aria-expanded="false" class="btn btn-warning btn-sm btn-block" style="background: linear-gradient(to right,#14b7ff,#14b7ff);"><i class="fa fa-gift"></i>&nbsp;活动抽奖</a></td></tr>
<tr height="50"><td>

<a href="/?mod=invite" target="_blank" data-toggle="modal"class="tooltip-toggle btn btn-primary btn-sm btn-block" style="background: linear-gradient(to right,#FF7F00,#FFB90F);">
<i class="fa fa-sun-o"></i>&nbsp;每日领赞</a></td>
<td><a href ="/<?php echo $conf['appurl']; ?>" data-toggle="modal" class="tooltip-toggle btn btn-primary btn-sm btn-block" style="background: linear-gradient(to right,#FF7F00,#FFB90F);">
<i class="fa fa-address-card"></i>&nbsp;APP下载</a></td></tr>


<tr height="50"><td>
<a href="./user/reg.php" class="tooltip-toggle btn btn-primary btn-sm btn-block" style="background: linear-gradient(to right,#FF0000,#FF0000);" data-original-title="" title=""><span style="font-weight:bold">
<i class="fa fa-sitemap"></i>&nbsp;开通分站</span></a></td>
<td><a href="./user/login.php" class="tooltip-toggle btn btn-primary btn-sm btn-block" style="background: linear-gradient(to right,#FF0000,#FF0000);" data-original-title="" title=""><span style="font-weight:bold">
<i class="fa fa-user fa-fw"></i>&nbsp;代理登录</span></a></td></tr>

<tr height="50"><td>
<a href="#customerservice" data-toggle="modal" aria-expanded="false" class="btn btn-warning btn-sm btn-block" style="background: linear-gradient(to right,#B452CD,#9400D3);">
<i class="fa fa-qq"></i>&nbsp;在线客服</a></td>
<td><a href="#aboutym"data-toggle="modal" class="tooltip-toggle btn btn-primary btn-sm btn-block" style="background: linear-gradient(to right,#B452CD,#9400D3);">
<i class="fa fa-user-circle"></i>&nbsp;关于我们</a></td></tr>

</tbody></table></div>
<!--更多按钮结束-->
    </div>
</div>

<!--关于我们弹窗-->
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
          如果长时间没到账请联系客服处理！<br>
          订单长时间显示（待处理）请联系客服！
          </div>
        </div>
      </div>
      <div class="panel panel-default" style="margin-bottom: 6px;">
        <div class="panel-heading">
          <h4 class="panel-title">
            <a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" class="collapsed">QQ会员/钻类等什么时候到账？</a>
          </h4>
        </div>
        <div id="collapseTwo" class="panel-collapse collapse" style="height: 0px;">
          <div class="panel-body">
          下单后的48小时内到账（会员或钻全部都是一样48小时内到账）！<br>
          如果超过48小时，请联系客服退款或补单，提供QQ号码！
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
          <div class="panel-body">没有收到请检查自己邮箱的垃圾箱！也可以去查单区：输入自己下单时填写的邮箱进行查单。<br>
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
          <div class="panel-body" style="margin-bottom: 6px;">联系客服处理，请提供（付款详细记录截图）（下单商品名称）（下单账号）<br>直接把三个信息发给客服，然后等待客服回复处理（请不要发抖动窗口或者QQ电话）！
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
          <div class="font-w600 push-5">订单售后客服</div>
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
<!--关于我们弹窗-->
<!--文章列表-->
<?php
if ($conf['template_purpleYear_blog'] == 1) {
    include_once 'blog.php';
}
?>
<!--文章列表-->
<?php include 'footer.php';?>
  <!--底部导航-->

<?php if (!isset($conf['template_purpleYear_hover']) || $conf['template_purpleYear_hover'] != 0) {?>
<canvas class="fireworks" style="position:fixed;left:0;top:0;z-index:99999999;pointer-events:none;"></canvas>
<script type="text/javascript" src="<?php echo $cdnserver ?>assets/template/purpleYear/js/hover.js"></script>
<?php }?>
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
   <script>
    function wx(title,mod){
    var area = [$(window).width() > 750 ? '750px' : '95%', $(window).height() > 600 ? '600px' : '95%'];
    layer.open({
       type: 2,
       title: title,
       shadeClose: true,
       closeBtn:2,
       shade: false,
       scrollbar: false,
       area: area,
       content: '//<?php echo $_SERVER['HTTP_HOST']; ?>?mod=wx'
    });
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

<?php hook('footer_before');?>
<script src="<?php echo $cdnpublic ?>jquery/1.12.4/jquery.min.js"></script>
<script src="<?php echo $cdnpublic ?>jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>
<script src="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="<?php echo $cdnpublic ?>jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="<?php echo $cdnpublic ?>layer/3.4.0/layer.js"></script>
<script src="<?php echo $cdnserver ?>assets/appui/js/app.js"></script>
<script type="text/javascript">
var isModal=<?php echo empty($conf['modal']) ? 'false' : 'true'; ?>;
var _modalType=<?php echo $conf['modal_type'] > 0 ? '1' : '0'; ?>;
var homepage=true;
var hashsalt=<?php echo $addsalt_js ?>;
$(function() {
  $("img.lazy").lazyload({effect: "fadeIn"});
});
</script>
<script src="<?php echo $cdnserver ?>assets/js/main.js?<?php echo VERSION ?>"></script>
<?php hook('footer_after');?>
</body>
</html>