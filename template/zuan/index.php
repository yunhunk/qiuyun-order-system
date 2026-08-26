<?php
if (!defined('IN_CRONLITE')) {
    exit();
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
  <link rel="shortcut icon" href="img/favicon.png">
  <link href="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/plugins.css">
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/main.css">
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/oneui.css">
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/css/common.css?<?php echo VERSION ?>">

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
  <img src="<?php echo $background_image; ?>" alt="Full Background" class="full-bg full-bg-bottom animated pulse ">
</head>
<body>
<br />
<?php include TEMPLATE_ROOT . 'default/head.inc.php';?>
<div class="col-xs-12 col-sm-10 col-md-8 col-lg-4 center-block" style="float: none;">
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

    <div class="widget-content themed-background-flat text-center" style="background-image: url(<?php echo $cdnserver ?>assets/simple/img/head.png);background-size: 100% 100%;">
        <a href="javascript:void(0)">
      <img class="img-avatar img-avatar80 img-avatar-thumb" src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq'] ?>&spec=100">
        </a>
    </div>
    <!--logo-->
    <!--logo 下面按钮-->
    <img class="hide" width="100%" src="<?php echo $cdnserver ?>assets/simple/img/yunshang2.jpg">
      <div class="block-content block-content-mini block-content-full bg-gray-lighter">
        <div class="btn-group btn-group-justified">
            <div class="btn-group">
                  <a class="btn btn-default" data-toggle="modal" href="#anounce"><i class="fa fa-bullhorn"></i>&nbsp;<span style="font-weight:bold">公告</span></a>
            </div>
            <?php if ($conf['qqgroup_url']): ?>
            <div class="btn-group">
               <a class="btn btn-default" href="<?php echo $conf['qqgroup_url'] ?>"><font color="#ff0000"><i class="fa fa-paper-plane-o"></i> 加入Q群</font></a>
            </div>
            <?php endif;?>
            <div class="btn-group">
                     <a class="btn btn-default" data-toggle="modal" href="<?php echo $cdnserver ?>user/"><i class="fa fa-users fa-1x"></i>&nbsp;管理后台</a>
            </div>
        </div>
        <?php hook('header');?>
        </div>
</div>

<div class="widget">
<div class="block full2">
<!--TAB标签-->
  <div class="block-title">

        <ul class="nav nav-tabs" data-toggle="tabs">
            <li style="width: 25%;" align="center" class="active"><a href="#shop" data-toggle="tab"><span style="font-weight:bold"></i> 下单</span></a></li>
            <li style="width: 25%;" align="center"><a href="#search" data-toggle="tab" id="tab-query"><span style="font-weight:bold"><font color="#66CC99"></i> 查单</span></font></a></li>
      <li style="width: 25%;" align="center" ><a href="#Substation" data-toggle="tab"><span style="font-weight:bold"><font color="#ff0000"></i> 分站</span></font></a></li>
      <li style="width: 25%;" align="center" class="hide"><a href="#gift" data-toggle="tab"><span style="font-weight:bold"></i> 抽奖</span></a></li>
      <li style="width: 25%;" align="center"><a href="#more" data-toggle="tab"><span style="font-weight:bold"><font color="#FF9900"></i> 更多</span></font></a></li>
        </ul>
    </div>

<!--TAB标签-->
    <div class="tab-content">
<!--在线下单-->
    <div class="tab-pane active" id="shop">
      <?php if ($conf['invite_open'] == 1) {?>
        <div class="shuaibi-tip animated tada  text-center">
          <font color="#030303"><font color="#030303"><img src="<?php echo $cdnserver ?>assets/beautify/img/gg-hot.gif"> <font color="red">想要免费领赞</font></font> <a href="/?mod=invite"> <span style="color:#BF3EFF;">点我推广送**
          </span></a></font><a href="/?mod=invite"></a><center><a href="/?mod=invite"><font color="#030303">   </font></a></center>
       </div>
      <?php }?>
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
        <td><span class="glyphicon glyphicon-magnet"></span>&nbsp;快加入我们成为大家庭中的一员吧</td>
                </tr>
                <tr class="active">
                    <td>

              <a data-toggle="modal" href="#userjs" target="_blank" class="btn btn-info btn-sm" style="overflow: hidden; position: relative;"><span class="fa fa-dedent"></span>&nbsp;功能介绍</a>
            <a href="./user/regsite.php" target="_blank" class="btn btn-danger btn-sm" style="overflow: hidden; position: relative;"><span class="fa fa-user-plus"></span>&nbsp;立即搭建</a>
            <a href="./user" target="_blank" class="btn btn-success btn-sm" style="overflow: hidden; position: relative;"><span class="fa fa-user"></span>&nbsp;站长后台</a>
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
<!--更多开始-->
<div class="tab-pane" id="more">
  <div class="row">

      <center><h4><span style="font-weight:bold"><font color="#FF8000">记</font><font color="#EC6D13">得</font><font color="#D95A26">收</font><font color="#C64739">藏</font><font color="#B3344C"></font><font color="#A0215F">我</font><font color="#8D0E72">们</font><font color="#7A0085">平</font><font color="#670098">台</font><font color="#5400AB">的</font><font color="#4100BE">网</font><font color="#2E00D1">址</font><font color="#1B00E4"></font> </span></h4></center><br>
      <div class="col-sm-6">
            <a data-toggle="modal" href="<?php echo $cdnserver ?>user/regsite.php" class="widget">
                 <div class="widget-content themed-background-danger text-right clearfix" style="background: linear-gradient(to right,#9EC9FF,#5ED1D7);color:#fff;">
                    <div class="widget-icon pull-left">
                      <i class="fa fa-user-plus animated rotateIn"></i>
                    </div>
                    <h2 class="widget-heading h3">
                        <strong>开通分站</strong>
                    </h2>
                    <span>开通分站赚钱</span>
                </div>
            </a>
        </div>
    <div class="col-sm-6">
           <a class="widget" data-toggle="modal" href="<?php echo $cdnserver ?>user/login.php">
                 <div class="widget-content themed-background-danger text-right clearfix" style="background: linear-gradient(to right,#9EAEFF,#BB9EFF);color:#fff;">
                    <div class="widget-icon pull-left">
                       <i class="fa  fa-clone animated rotateIn"></i>
                    </div>
                    <h2 class="widget-heading h3">
                        <strong>站长后台</strong>
                    </h2>
                    <span>登陆站长后台</span>
                </div>
            </a>
        </div>
  </div>
  </div>
<!--更多结束-->
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
</div>

<?php
//加载插件代码
hook('bottom');
?>

<!--邀请统计-->
<?php include TEMPLATE_ROOT . 'default/invite.inc.php';?>
<!--邀请统计-->

<!--底部导航-->
<a href="javascript:void(0);" onclick="AddFavorite('<?php echo $conf['sitename'] ?>',location.href)"><div class="block panel-body text-center" style="text-align: center; font-weight:bold">
  <b style="text-shadow: LightSteelBlue 1px 0px 0px;">
  <i class="fa fa-heart text-danger"></i>
  <font color=#CB0034>本</font>
  <font color=#BE0041>站</font>
  <font color=#B1004E>网</font>
  <font color=#A4005B>址</font>
  <font color=#970068>：</font>
  <font color=#2F00D0><?php echo $_SERVER['HTTP_HOST'] ?></font>
<font color="red">
</script>
</font><br>
<font color="#CB0034">&nbsp;收&nbsp;藏&nbsp;此&nbsp;网&nbsp;站</font> <font color="#CB0034">到</font> <font color="#BE0041">书</font> <font color="#B1004E">签</font> <
<a data-toggle="modal" href="#disclaimer" class="btn btn-xs btn-default"><font color="#ff0000">如有侵权点我</font></a>
</b>
    </div>
<!--底部导航-->
</div>
<!--baidu自动推送-->
<script>
(function(){
    var bp = document.createElement('script');
    var curProtocol = window.location.protocol.split(':')[0];
    if (curProtocol === 'https') {
        bp.src = 'https://zz.bdstatic.com/linksubmit/push.js';
    }
    else {
        bp.src = 'http://push.zhanzhang.baidu.com/push.js';
    }
    var s = document.getElementsByTagName("script")[0];
    s.parentNode.insertBefore(bp, s);
})();
</script>
<!--baidu自动推送-->
<!--音乐代码-->
<div id="audio-play" style="display:none;">
  <div id="audio-btn" class="on" onclick="audio_init.changeClass(this,'media')">
    <audio loop="loop" src="" id="media" preload="preload"></audio>
  </div>
</div>
<!--音乐代码-->
<?php hook('footer_before');?>

<script src="<?php echo $cdnpublic ?>jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>
<script src="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="<?php echo $cdnpublic ?>jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="<?php echo $cdnpublic ?>layer/3.4.0/layer.js"></script>
<script src="<?php echo $cdnserver ?>assets/appui/js/app.js"></script>
<script type="text/javascript">
var isModal=<?php echo empty($conf['modal']) ? 'false' : 'true'; ?>;
var modalType=<?php echo $conf['modal_type'] > 0 ? '1' : '0'; ?>;
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