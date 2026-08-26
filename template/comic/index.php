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
  <link rel="shortcut icon" href="favicon.ico">
  <link href="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/plugins.css">
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/main.css">
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/oneui.css">
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/css/common.css">
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
<img src="<?php echo $background_image ?>" alt="Full Background" class="full-bg full-bg-bottom animated pulse ">
</head>
<body>
<br/>
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
    <div class="widget-content themed-background-flat text-center" style="background-image: url(assets/simple/img/color.jpg);background-size: 100% 100%;">
        <a href="javascript:void(0)">
      <img src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo !empty($siterow['qq']) ? $siterow['qq'] : $conf['kfqq'] ?>&spec=100" alt="Avatar" width="80" alt="avatar" style="height: auto filter: alpha(Opacity=80);-moz-opacity: 0.80;opacity: 0.80;" class="img-circle img-thumbnail img-thumbnail-avatar-1x animated zoomInDown">
        </a>
    </div>
<!--logo-->
<!--logo下面按钮-->
  <img width="100%" src="<?php echo $cdnserver ?>assets/simple/img/yunshang2.jpg">
  <div class="widget-content themed-background-muted text-center ">
    <div class="btn-group themed-background-muted ">
      <a href="#anounce" data-toggle="modal" class="btn btn-effect-ripple btn-default collapsed "><b><font color="#ff0000"><i class="fa fa-wifi fa-fw"></i> <span style="font-weight:bold">平台公告</span></font></b></a>
      <a href="/?mod=invite" class="btn btn-effect-ripple btn-default"><i class="fa fa-gift"></i> <span style="font-weight:bold">推广领赞</span></a>
      <?php if (!empty($conf['zfb_code'])) {?>
      <a class="btn btn-default" data-toggle="modal" href="#ewm"><i class="fa fa-gift fa-fw"></i><font color="#5F9EA0">秒领红包</span></font></a>
      <?php }?>
    </div>
    <div id="mustsee" class="panel-collapse collapse" aria-expanded="false" style="height: 0px;">
          <div id="mustsee" class="panel-collapse collapse in" aria-expanded="true" style="">

          </div>
    </div>
  </div>

     <a target="_blank" href="<?php echo $cdnserver ?>user/login.php"><img src="<?php echo $cdnserver ?>assets/simple/img/tjreg.png" style="width: 100%;"></a></aside>
    </div>
    <?php
//加载插件代码
hook('header');
?>
<style>
.elevator_item .hd-time-limited {
    display: block;
    position: fixed;
    right: 0;
    bottom: 445px;
    width: 40px;
    height: 140px;
    background-color: skyblue;
}

.elevator_item {
    position: fixed;
    right: 0;
    bottom: 95px;
    z-index: 11;
}
.elevator_item .feedback {
    width: 36px;
    height: 41px;
    font-size: 12px;
    padding: 5px 6px;
    display: block;
    border-radius: 5px;
    text-align: center;
    margin-top: 10px;
    box-shadow: 0 1px 2px rgba(0,0,0,.35);
    cursor: pointer;
}
.graHover {
    position: relative;
    overflow: hidden;
}
</style>

<div class="elevator_item" id="elevator_item" style="display:none;">

<a target="_self" class="feedback graHover" style="background-color: #FF3399;color:#fff;" href="/skm.jpg" rel="nofollow">免费红包</a>

<a target="_self" class="feedback graHover" style="background-color: #AF3A9F;color:#fff;" href="/?cid=319&tid=5281" rel="nofollow">QQ靓号</a>

<a target="_self" class="feedback graHover" id="sign_daily" style="background-color: #ffd900;color:#383838;" rel="nofollow" href="/?cid=52&tid=560">QQ**</a>

<a target="_self" class="feedback graHover" style="background-color: #AF3A9F;color:#fff;" href="/?cid=20&tid=1100" rel="nofollow">全民K歌</a>

<a target="_self" class="feedback graHover" style="background-color: #1e6be3;color:#fff;" href="/?cid=236&tid=3712" rel="nofollow">无限流量</a>

<a target="_self" class="feedback graHover" style="background-color: #fa3c63;color:#fff;" href="/?cid=42&tid=1127" rel="nofollow">爆音业务</a>

<a target="_self" class="feedback graHover" style="background-color: #ffa500;color:#fff;" href="/?cid=69&tid=1432" rel="nofollow">QQ代挂</a>

<a target="_self" class="feedback graHover" style="background-color: #3cbdfa;color:#fff;" href="/?cid=163&tid=2927" rel="nofollow">新浪微博</a>

<a target="_self" class="feedback graHover" style="background-color: #06C17E;color:#fff;" href="<?php echo $cdnserver ?>user/regsite.php" rel="nofollow">代理赚钱</a>
</div>
<div class="block full2">
<!--免费   -->
<!--TAB标签-->
  <div class="block-title">

        <ul class="nav nav-tabs" data-toggle="tabs">
            <li style="width: 25%;" align="center" class="active"><a href="#shop" data-toggle="tab"><span style="font-weight:bold"><i class="fa fa-shopping-bag fa-fw"></i> 下单</span></a></li>
            <li style="width: 25%;" align="center"><a href="#search" data-toggle="tab" id="tab-query"><span style="font-weight:bold"><i class="fa fa-search"></i> 查询</span></a></li>
      <li style="width: 25%;" align="center" ><a href="#Substation" data-toggle="tab"><span style="font-weight:bold"><font color="#ff0000"><i class="fa fa-location-arrow fa-spin"></i> 赚钱</span></font></a></li>
      <li style="width: 25%;" align="center" class="hide"><a href="#gift" data-toggle="tab"><span style="font-weight:bold"><i class="fa fa-gift fa-fw"></i> 抽奖</span></a></li>
      <li style="width: 25%;" align="center" class="hide"><a href="#cardbuy" data-toggle="tab"><span style="font-weight:bold"><i class="glyphicon glyphicon-th"></i> 卡密</span></a></li>
      <li style="width: 25%;" align="center"><a href="#more" data-toggle="tab"><span style="font-weight:bold"><i class="fa fa-folder-open"></i> 更多</span></a></li>
        </ul>
    </div>
<!--TAB标签-->
    <div class="tab-content">
<!--在线下单-->
    <div class="tab-pane active" id="shop">
        <center>
<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true"><i class="fa fa-times-circle"></i></span></button>
<a href="/?cid=9&tid=4475" class="hide" style="color: red;"><img border="0" width="20" style="margin-bottom: 0.35em;" src="<?php echo $cdnserver ?>assets/xiaoyao/hhh2.gif">&nbsp;QQ**最低0.3/万，速度日唰千万</a>
<?php if ($conf['invite_open'] == 1) {?>
<div class="shuaibi-tip animated tada  text-center"><img src="<?php echo $cdnserver ?>assets/xiaoyao/hhh.gif"> <font color="red">想要免费**</font></font> <a href="/?mod=invite"> <span style="color:#BF3EFF;">点我推广送**
</font></a></b></div>
<?php }?>
<?php if ($conf['fenzhan_buy'] == 1) {?>
<div class="shuaibi-tip animated tada  text-center"><img src="<?php echo $cdnserver ?>assets/xiaoyao/hhh2.gif"> <font color="red">想要免费下单</font></font> <a href="<?php echo $cdnserver ?>user/regsite.php"><span style="color:green;"> 点我开通分站赚钱
</font></a></b></div>
<?php }?>

<?php if ($conf['user_open'] == 1) {?>
<div class="shuaibi-tip" style="">
<div class="text-danger"><b></font></b><i class="fa fa-volume-up"></i>&nbsp; 注册后更优惠，签到领余额免费下单
<span class="label label-danger">
<a href="<?php echo $cdnserver ?>user/reg.php" target="_blank" data-toggle="modal">
<font color="#FFFFFF">点我注册</font></a></span></div></div>
<?php }?>
<p><span style="font-size:10px;"><strong><span><span style="color:#E53333;">下单步骤</span>≯ &nbsp;</span><span style="color:#009900;">选择分类<span style="color:#E53333;">≯</span></span><span> &nbsp;</span><span style="color:#EE33EE;">选择商品</span><strong>≯ &nbsp;<span style="color:#006600;">支付金额</span></strong><strong><span>&nbsp;≯ &nbsp;</span><span style="color:#64451D;">购买成功</span></strong></strong></span></p>
</center>
       <?php include TEMPLATE_ROOT . 'default/shop.inc.php';?>
    </div>
<!--在线下单-->
<!--查询订单-->
    <div class="tab-pane" id="search">
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
                    <td>分站满10元即可申请提现</td>
                </tr>
                <tr>
                    <td><strong>轻轻松松推广日赚100+不是梦</td>
                </tr>
                <tr class="active">
                    <td>
            <a href="#userjs" data-toggle="modal" class="btn btn-effect-ripple  btn-info"><i class="fa fa-th-list"></i><span class="btn-ripple animate" style="height: 100px; width: 100px; top: -34.4px; left: 2.58749px;"></span> 功能介绍</a>
                        <a href="<?php echo $cdnserver ?>user/regsite.php" target="_blank" class="btn btn-effect-ripple  btn-danger"><i class="fa fa-arrow-right"></i> 马上开通</a>
                      <a href="<?php echo $cdnserver ?>user/" target="_blank" class="btn btn-effect-ripple btn-success" style="overflow: hidden; position: relative;"><i class="fa fa-arrow-right"></i><span class="btn-ripple animate" style="height: 100px; width: 100px; top: -34.4px; left: 2.58749px;"></span> 分站登陆</a>
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
    <div class="modal fade" id="gift" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
  <div class="modal-dialog">
    <div class="modal-content">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">关闭</span>
      </button>
          <h4 class="modal-title">在线抽奖</h4>
    </div>
    <div class="tab-pane fade in" id="gift">
          <li class="list-group-item" style="background: linear-gradient;height:60px;"><center><div class="col-xs-12 well well-sm animated tada"><i class="fa fa-heart text-danger"></i> <b>支付宝领取红包下单享折扣：<a  href="#ewm" target="_blank" data-toggle="modal" aria-expanded="true">点击领取</a></b><br></div></center></li>
               <li class="list-group-item" style="background: linear-gradient;">
              <center><div class="alsp">完成以下任务之一能永久提升中奖机率！</div>
                <b>（全部完成 - 高几率抽大奖！）</b></center></li>
           <style>.alsp{ font-family:"楷体";}</style>
         <li class="list-group-item" style="background: linear-gradient;">一、拥有一个分站，不限版本（<a target="_blank" href="<?php echo $cdnserver ?>user/reg.php">去完成</a>）</li>
     <li class="list-group-item" style="background: linear-gradient;">  二、分享本站获取百万福利！（<a target="_blank" href="./?mod=invite">去完成</a>）<br></li>
      <div class="panel-body text-center">
      <div id="roll">点击下方按钮开始抽奖</div>
      <hr>
      <p>
      <a class="btn btn-info" id="start" style="display:block;">开始抽奖</a>
      <a class="btn btn-danger" id="stop" style="display:none;">停止</a>
      </p>
      <div id="result"></div>
      </div>
      <li class="list-group-item bord-top"><font color="#FF7F00">抽奖规则：每人每天限抽1次，期待您每天满载而归！<br> 奖品内容：本站的N个商品，钻类、名片、空间类等各种劲爆商品。</font></li>
    </div>
    <div class="tab-pane fade in" id="chat">
          </div>
          <div class="modal-footer">
      <button type="button" class="btn btn-default" data-dismiss="modal">关闭窗口</button>
    </div>
    </div>
  </div>
</div>
<!--抽奖-->
<!--更多-->
<div class="tab-pane" id="more">
    <div class="row">
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
    <div class="col-sm-6 <?php if ($conf['gift_open'] == 0) {?> hide<?php }?>">
            <a href="#gift" data-toggle="modal" class="widget">
                <div class="widget-content themed-background-danger text-right clearfix" style="background: linear-gradient(to right,#f093fb,#f5576c);color:#fff;">
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
    <div class="col-sm-6 <?php if (empty($conf['appurl']) || $conf['gift_open'] == 1 || $is_fenzhan) {?> hide<?php }?>">
            <a href="" target="_blank" class="widget">
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
    <div class="col-sm-6">
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
    <div class="col-sm-6 <?php if (empty($conf['mzurl'])) {?> hide<?php }?>">
            <a href="<?php echo $conf['mzurl']; ?>" target="_blank" class="widget">
                <div class="widget-content themed-background-success text-right clearfix" style="background: linear-gradient(to right,#ff9a9e,#fecfef);color:#fff;">
                    <div class="widget-icon pull-left">
                        <i class="fa fa-thumbs-up"></i>
                    </div>
                    <h2 class="widget-heading h3">
                        <strong>QQ秒赞</strong>
                    </h2>
                    <span>专业的QQ秒赞平台</span>
                </div>
            </a>
        </div>

      <div class="col-sm-6 <?php if (empty($conf['zfbcode'])) {?> hide<?php }?>">
            <a class="widget">
                <div class="widget-content themed-background-danger text-right clearfix" style="background: linear-gradient(to right,#43e97b,#38f9d7);color:#fff;">
                    <div class="widget-icon pull-left">
                        <i class="fa fa-rocket"></i>
                    </div>
                    <h2 class="widget-heading h3">
                        <strong>免费红包</strong>
                    </h2>
                    <span>支付宝首页搜索“<?php echo $conf['zfbcode']; ?>”  最高可以领取99元，赶快试试运气把？</span>
                </div>
            </a>
        </div>
    <div class="col-sm-6 <?php if (empty($conf['chatframe'])) {?> hide<?php }?>">
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
    <div class="col-sm-6 <?php if (empty($conf['dgurl'])) {?> hide<?php }?>">
            <a href="./?mod=daigua" target="_blank" class="widget">
                <div class="widget-content themed-background-info text-right clearfix" style="background: linear-gradient(to right,#43e97b,#38f9d7);color:#fff;">
                    <div class="widget-icon pull-left">
                        <i class="fa fa-qq"></i>
                    </div>
                    <h2 class="widget-heading h3">
                        <strong>QQ代挂</strong>
                    </h2>
                    <span>管理QQ代挂功能</span>
                </div>
            </a>
        </div>
       </div>
       </div>
<!--更多-->
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
      </div>
<!--聊天-->
    </div>
</div>

<!--二维码-->
    <div class="modal fade" align="left" id="ewm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">红包二维码</h4>
      </div>
      <div class="modal-body">
            <center>
             <p>手机支付宝首页搜索{<b style="color:red;"><?php echo $conf['zfb_code'] ?></b>}  <a id="copyurl" data-clipboard-text="<?php echo $conf['zfb_code'] ?>" class="btn btn-info btn-sm"> 复制红包码</a>  </p>
              <hr /> ①复制-打开支付宝搜索-粘贴-领取0-99元
              <hr />
              <?php if ($conf['zfb_codeurl']) {?>
                <img src="<?php echo $conf['zfb_codeurl'] ?>" width="50%" height="50%" />
              <hr /> ②长按保存-打开支付宝扫码-选择红包码
              <hr />
              <?php }?>
             </center>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
      </div>
    </div>
  </div>
</div>
<!--二维码-->

 <!--关于我们弹窗-->
<div class="panel">
  <?php
//加载插件代码
hook('bottom');
?>
</div>

<div class="panel panel-default">
  <center>  <div class="panel-body"><span style="font-weight:bold">CopyRight <i class="fa fa-heart text-danger"></i> 2020 |
</font><a class="" href="#disclaimer" data-toggle="modal"><span style="font-weight:bold">免责声明</span></a> |</font>
  </span><a class="" href="#about" data-toggle="modal"><span style="font-weight:bold">客服与帮助</span></a>


  <a href="javascript:void(0);" onclick="AddFavorite('QQ云商城',location.href)"><div class="block panel-body text-center" style="text-align: center; font-weight:bold">
  <b style="text-shadow: LightSteelBlue 1px 0px 0px;">
  <i class="fa fa-heart text-danger"></i>
  <font color="#CB0034">本</font>
  <font color="#BE0041">站</font>
  <font color="#B1004E">网</font>
  <font color="#A4005B">址</font>
  <font color="#970068">：</font>
  <font color="#2F00D0"><?=$siteurl?></font>
  <font color="#CB0034">&nbsp;</font>
  <font color="#CB0034">欢</font>
  <font color="#BE0041">迎</font>
  <font color="#B1004E">收</font>
  <font color="#A4005B">藏</font>
  </b>
</div>
</a>

     </p><center><tr> <font color="red">（把本站网址添加到浏览器书签方便再买）<tr> </font>

<!--底部导航-->

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

<?php if ($conf['music_url']) {?>
<!--音乐代码-->
<div id="audio-play" >
  <div id="audio-btn" class="on" onclick="audio_init.changeClass(this,'media')">
    <audio loop="loop" src="<?php echo $conf['music_url'] ?>" id="media" preload="preload"></audio>
  </div>
</div>
<!--音乐代码-->
<?php }?>

<?php hook('footer_before');?>
<script src="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="<?php echo $cdnpublic ?>jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>
<script src="<?php echo $cdnpublic ?>clipboard.js/1.7.1/clipboard.min.js"></script>
<script src="<?php echo $cdnpublic ?>jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="<?php echo $cdnpublic ?>layer/3.4.0/layer.js"></script>
<script src="<?php echo $cdnpublic ?>distpicker/2.0.3/distpicker.min.js"></script>
<script src="<?php echo $cdnserver ?>assets/appui/js/plugins.js"></script>
<script src="<?php echo $cdnserver ?>assets/appui/js/app.js"></script>
<script type="text/javascript">
var isModal=<?php echo empty($conf['modal']) ? 'false' : 'true'; ?>;
var _modalType=<?php echo $conf['modal_type'] > 0 ? '1' : '0'; ?>;
var homepage=true;
var hashsalt=<?php echo $addsalt_js ?>;
</script>
<script type="text/javascript">
    var helloTitile = document.title;
    var titleTime;
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
          document.title = 'φ(>ω<*) 这有好东西哦~ ' + helloTitile;
          clearTimeout(titleTime);
        } else {
          document.title = '( • ̀ω•́ )✧被你发现了~ ' + helloTitile;
          titleTime = setTimeout(function() {
            document.title = helloTitile;
        },
          2000);
        }
   });
</script>
<script>
var clipboard = new Clipboard('#copyurl');
new Clipboard('#copyurl', {
    container: document.getElementById('copyurl')
});
clipboard.on('success', function (e) {
  layer.msg('复制成功,快发给好友砍价吧！');
  e.clearSelection();
});
clipboard.on('error', function (e) {
  layer.msg('复制失败，请长按链接后手动复制');
});

</script>
<!-- 360自动推送 -->
<script>
(function(){
var src = "https://jspassport.ssl.qhimg.com/11.0.1.js?d182b3f28525f2db83acfaaf6e696dba";
document.write('<script src="' + src + '" id="sozz"><\/script>');
})();
</script>
<script src="<?php echo $cdnserver ?>assets/js/main.js?ver=<?php echo VERSION ?>"></script>
<?php hook('footer_after');?>
</body>
</html>