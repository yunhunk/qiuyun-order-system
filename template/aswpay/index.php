<?php
if (!defined('IN_CRONLITE')) {
    exit();
}

if ($conf['message_static'] == 1) {
    $_indexUrl = '/article/index.html';
} else {
    $_indexUrl = '/article/index.php';
}
?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8">
    <meta name="renderer"  content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title><?php echo $conf['sitename'] ?> - <?php echo $conf['title'] ?></title>
    <meta name="keywords" content="<?php echo $conf['keywords'] ?>">
    <meta name="description" content="<?php echo $conf['description'] ?>">
    <link href="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/oneui.css">
    <script src="<?php echo $cdnpublic ?>jquery/1.12.4/jquery.min.js"></script>
     <?php
//加载插件代码
hook('head');
?>
    <!--[if lt IE 9]>
      <script src="<?php echo $cdnpublic ?>html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="<?php echo $cdnpublic ?>respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>.xiaobozai-tip{background:#fafafa repeating-linear-gradient(-45deg,#fff,#fff 1.125rem,transparent 1.125rem,transparent 2.25rem);box-shadow:0 2px 5px rgba(0,0,0,0.15);margin:20px 0px;padding:5px;border-radius:5px;margin-top:3px;font-size:14px;color:#555555}</style>
    <style>.tongzhi-tip{background:#fafafa repeating-linear-gradient(-45deg,#fff,#fff 1.125rem,transparent 1.125rem,transparent 2.25rem);box-shadow:0 2px 5px rgba(0,0,0,0.15);margin:20px 0px;padding:5px;border-radius:5px;margin-top:3px;}</style>
    <style>
    img.logo{width:14px;height:14px;margin:0 5px 0 3px;}
    .onclick{cursor: pointer;touch-action: manipulation;}
    .giftlist{overflow:hidden;width:90%;margin:0 auto}
    .giftlist ul{height:270px;overflow:hidden;padding:0}
    .giftlist li{width:100%;line-height:35px;padding:0 10px;overflow:hidden;box-sizing:border-box;-webkit-box-sizing:border-box;-moz-box-sizing:border-box}
    .giftlist li strong{margin:0 5px 0 0;font-weight:400;color:#1977d8}

</style>
<body>
<?php include TEMPLATE_ROOT . 'default/head.inc.php';?>
<!--音乐代码-->
<div id="audio-play" <?php if (empty($conf['musicurl'])) {?>style="display:none;"<?php }?>>
  <div id="audio-btn" class="on" onclick="audio_init.changeClass(this,'media')">
    <audio loop="loop" src="<?php echo $conf['musicurl'] ?>" id="media" preload="preload"></audio>
  </div>
</div>
<!--音乐代码-->
      <img style="background: linear-gradient(to bottom,#43CBFF,#9708CC);color:#000;" class="full-bg full-bg-bottom"/>
<!--弹出公告-->
      <div class="modal fade" align="left" id="mustsee" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
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
      <!--弹出公告-->
      <!--公告-->
<div class="modal fade" align="left" id="mustsee" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
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
      <div class="col-xs-12 col-sm-10 col-md-8 col-lg-4 center-block" style="float: none;">
        <br>
        <!--顶部导航-->
        <div class="block block-link-hover3" href="javascript:void(0)">
          <div class="block-content block-content-full text-center bg-image" style="background-image: url('/assets/template/public/img/head2.png');background-size: 100% 100%;">
            <div>
              <div>
                <a href="/">
                  <img class="img-avatar img-avatar80 img-avatar-thumb animated zoomInDown qqdsw" src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq'] ?>&spec=100" onerror="this.src='img-avatar img-avatar80 img-avatar-thumb animated zoomInDown qqdsw" ></a>
              </div>
          </div>
          </div>
            <!--logo-->
            <img width="100%" src="<?php echo $cdnserver ?>assets/template/public/img/yewu.jpg">
            <!--logo下面按钮-->
              <div class="block-content block-content-mini block-content-full bg-gray-lighter">
              <div class="text-center text-muted">
            <div class="btn-group btn-group-justified">
            <div class="btn-group">
            <a class="btn btn-default" href="<?php echo $_indexUrl ?>"><i class="fa fa-bell" style="color: #7367F0;"></i>&nbsp;<span style="font-weight:bold;color: #e8198b;">公告</span></a>
            </div>
            <!--客服qq  $conf['appurl']-->
            <?php if ($conf['appurl']) {?>
              <div class="btn-group">
              <a href="<?php echo $conf['appurl'] ?>" target="_blank" class="btn btn-effect-ripple btn-default" style="overflow: hidden; position: relative;"><i class="fa fa-android" style="color: #F55555;"></i> <span style="font-weight:bold;color: #0ba360;">客户端</span></a>
              </div>
            <?php }?>
              <div class="btn-group">
                <a class="btn btn-default" data-toggle="modal" href="./user/login.php"><i class="fa fa-users" style="color: #B3315F;"></i>&nbsp;<span style="font-weight:bold;color: #ee609c;"><?php if (!$isLogin2) {?>登录<?php } else {?>后台<?php }?></span></a>
              </div>
                <!--logo下面按钮-->
                </div>
              </div>
          </div>
        </div>
        <!--最新通知-->
        <?php
//加载插件代码
hook('header');
?>

        <!--复制广告词分享开始-->
             <!--复制广告词分享结束-->
           <div class="block">
           <ul class="nav nav-tabs" data-toggle="tabs" style="background-image: linear-gradient(60deg, #64b3f4 0%, #c2e59c 100%);">
              <li class="active qqdsw" style="width: 25%;" align="center">
            <a href="#shop" data-toggle="tab"><b><font color="#FD6E6A"><i class="fa fa-shopping-cart"></i>&nbsp;下单</font></b></a>
            </li>
           <li style="width: 25%;" align="center">
             <li style="width: 25%;" align="center" class="">
              <a href="#search" data-toggle="tab" id="tab-query"><b><font color="#0D25B9"><i class="fa fa-search"></i>&nbsp;查单</font></b></a>
            </li>
            <li style="width: 25%;" align="center">
             <li style="width: 25%;" align="center" class="">
                <a href="#ktfz"><b><font color="#8C1BAB"><i class="fa fa-user-plus"></i>&nbsp;加盟</font></b></a>

            </li>
             <li style="width: 25%;" align="center">
             <li style="width: 25%;" align="center" class="">
              <a href="#more" data-toggle="tab" class="qqdsw"><b><font color="#F6416C"><i class="fa fa-list"></i>&nbsp;更多</font></a></a>
            </li>
          </ul>
          <!--TAB-->
          <div class="block-content tab-content">
            <!--在线下单-->
           <div class="tab-pane fade fade-up in active" id="shop">
<center>
<!-- <img src="<?php echo $cdnserver ?>assets/img/other/logo0.png" alt="超级会员"><img src="<?php echo $cdnserver ?>assets/img/other/logo1.png" alt="QQ会员">&nbsp;<img src="<?php echo $cdnserver ?>assets/img/other/logo_1.png" alt="黄钻贵族豪华版">&nbsp;<img src="<?php echo $cdnserver ?>assets/img/other/logo_2.png" alt="绿钻豪华版">&nbsp;<img src="<?php echo $cdnserver ?>assets/img/other/logo_3.png" alt="腾讯视频VIP（QQ账号）">&nbsp;<img src="<?php echo $cdnserver ?>assets/img/other/logo_4.png" alt="红钻贵族">&nbsp;<img src="<?php echo $cdnserver ?>assets/img/other/logo_5.png" alt="蓝钻贵族">&nbsp;<img src="<?php echo $cdnserver ?>assets/img/other/logo_6.png" alt="微云超级会员">&nbsp;
 -->
<div align="center" style="color:red;font-size: 10px;" class="list-group-item reed"><span style="font-size:12px;"><strong><span><span style="color:#E53333;">下单步骤</span>≯ &nbsp;</span><span style="color:#009900;">选择分类<span style="color:#E53333;">≯</span></span><span> &nbsp;</span><span style="color:#EE33EE;">选择商品</span><strong>≯ &nbsp;<span style="color:#006600;">支付金额</span></strong><strong><span>&nbsp;≯ &nbsp;</span><span style="color:#64451D;">购买成功</span></strong></strong></span></div>
<br>
</center>

    <?php include TEMPLATE_ROOT . 'default/shop.inc.php';?>
    </div>
    <!--在线下单-->
    <!--查询订单-->
    <div class="tab-pane fade fade-up" id="search">
    <?php include TEMPLATE_ROOT . 'default/query.inc.php';?>
    </div>
    <!--查询订单-->
            <!--开通分站-->
     <div class="tab-pane fade fade-up" id="ktfz">
<a href="<?php echo $cdnserver ?>user/regsite.php"><img src="<?php echo $cdnserver ?>assets/template/public/img/daili.gif" width="100%"></a><br><br>
<div class="animation-pulse">
<center>
<h4><span style="font-weight:bold"><font color="#FF8000">加</font><font color="#EC6D13">入</font><font color="#D95A26">我</font><font color="#C64739">们</font><font color="#B3344C"> </font><font color="#A0215F">赚</font><font color="#8D0E72">钱</font><font color="#7A0085">就</font><font color="#670098">是</font><font color="#5400AB">如</font><font color="#4100BE">此</font><font color="#2E00D1">简</font><font color="#1B00E4">单</font> </span></h4>
</center>
</div>
<h4>
<br>
</h4>
<table class="table table-borderless animated bounceIn" style="text-align: center;">
<tbody>
<tr class="active">
<td>学生/上班族/创业/休闲赚钱必备工具</td>
</tr>
<tr class="active">
<td><strong>轻松松推广网站/日赚上百元不是梦</strong></td>
</tr>
<tr class="active">
<td><strong><span class="glyphicon glyphicon-magnet"></span> 快加入我们成为大家庭中的一员吧</strong></td>
</tr>
<tr class="active">
<td>
<a href="#userjs" data-toggle="modal" class="btn btn-effect-ripple  btn-info" style="overflow: hidden; position: relative;"><i class="fa fa-th-list"></i><span class="btn-ripple animate" style="height: 100px; width: 100px; top: -34.4px; left: 2.58749px;"></span> 功能介绍</a>
<a href="<?php echo $cdnserver ?>user/reg.php" class="btn btn-effect-ripple  btn-danger" style="overflow: hidden; position: relative;"><i class="fa fa-arrow-right"></i> 马上开通</a>
<a href="<?php echo $cdnserver ?>user/" class="btn btn-effect-ripple btn-success" style="overflow: hidden; position: relative;"><i class="fa fa-arrow-right"></i><span class="btn-ripple animate" style="height: 100px; width: 100px; top: -34.4px; left: 2.58749px;"></span> 登录网站</a>
</td>
</tr>
</tbody>
</table>
</div>
            <!--开通分站-->
            <!--日志通知-->
            <div class="tab-pane fade fade-up" id="rztz">
              <div class="widget-content themed-background-muted text-dark text-center animation-pullUp">
                <span class="label label-warning">
                  <i class="fa fa-info-circle"></i>&nbsp;最新业务价格通知请看下方信息噢~</span>&nbsp;
                <span class="label label-info">可上下滑动</span></div>
              <br>
              <div style="height:188px; overflow:scroll; overflow-x:hidden;">
                <div class="tongzhi-tip"><img border="0" width="26" src="<?php echo $cdnserver ?>assets/img/mgt.gif">&nbsp;<b>2018-11-30</b>：**直播权限开通恢复接单。</div><div class="tongzhi-tip"><img border="0" width="26" src="<?php echo $cdnserver ?>assets/img/mgt.gif">&nbsp;<b>2018-11-28</b>：新上架**秒刷粉丝。</div><div class="tongzhi-tip"><img border="0" width="26" src="<?php echo $cdnserver ?>assets/img/mgt.gif">&nbsp;<b>2018-11-28</b>：最右粉丝/点赞/分享，恢复接单。</div><div class="tongzhi-tip"><img border="0" width="26" src="<?php echo $cdnserver ?>assets/img/dx.png">&nbsp;<b>2018-11-28</b>：小红书部分业务维护清单。</div><div class="tongzhi-tip"><img border="0" width="26" src="<?php echo $cdnserver ?>assets/img/mgt.gif">&nbsp;<b>2018-11-27</b>：**60秒长视频权限恢复。</div><div class="tongzhi-tip"><img border="0" width="26" src="<?php echo $cdnserver ?>assets/img/mgt.gif">&nbsp;<b>2018-11-24</b>：新上架QQ情侣空间送鲜花。</div><div class="tongzhi-tip"><img border="0" width="26" src="<?php echo $cdnserver ?>assets/img/mgt.gif">&nbsp;<b>2018-11-23</b>：**秒刷点赞恢复，日刷10w。</div><div class="tongzhi-tip"><img border="0" width="26" src="<?php echo $cdnserver ?>assets/img/mgt.gif">&nbsp;<b>2018-11-22</b>：理论永久会员降价，欢迎下单。</div><div class="tongzhi-tip"><img border="0" width="26" src="<?php echo $cdnserver ?>assets/img/mgt.gif">&nbsp;<b>2018-11-22</b>：新上架QQ空间留言代删除。</div><div class="tongzhi-tip"><img border="0" width="26" src="<?php echo $cdnserver ?>assets/img/mgt.gif">&nbsp;<b>2018-11-22</b>：新上架QQ空间说说代删除。</div><div class="tongzhi-tip"><img border="0" width="26" src="<?php echo $cdnserver ?>assets/img/mgt.gif">&nbsp;<b>2018-11-22</b>：新上架QQ情侣空间恩爱值。</div><div class="tongzhi-tip"><img border="0" width="26" src="<?php echo $cdnserver ?>assets/img/mgt.gif">&nbsp;<b>2018-11-22</b>：官方好莱坞会员降价，15元/月。</div><div class="tongzhi-tip"><img border="0" width="26" src="<?php echo $cdnserver ?>assets/img/dx.png">&nbsp;<b>2018-11-22</b>：**快刷粉丝维护清单。</div><div class="tongzhi-tip"><img border="0" width="26" src="<?php echo $cdnserver ?>assets/img/mgt.gif">&nbsp;<b>2018-11-22</b>：QQ秒赞网会员卡密已大量补卡。</div><div class="tongzhi-tip"><img border="0" width="26" src="<?php echo $cdnserver ?>assets/img/mgt.gif">&nbsp;<b>2018-11-22</b>：球球大作战新增包夜观战。</div><div class="tongzhi-tip"><img border="0" width="26" src="<?php echo $cdnserver ?>assets/img/mgt.gif">&nbsp;<b>2018-11-22</b>：新上架网课代看，大学生福利。</div><div class="tongzhi-tip"><img border="0" width="26" src="<?php echo $cdnserver ?>assets/img/mgt.gif">&nbsp;<b>2018-11-21</b>：网站内所有商品全部降价！</div><table class="table table-borderless table-vcenter">
                  <tbody>

                  </tbody>
                </table>
              </div>
            </div>
            <!--日志通知-->
            <!--卡密下单-->
            <div class="tab-pane fade fade-up" id="cardbuy">
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
          <div class="form-group">
            <div class="input-group"><div class="input-group-addon" id="km_inputname">下单ＱＱ</div>
            <input type="text" name="inputvalue" id="km_inputvalue" value="" class="form-control" required/>
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
            <!--新闻资讯-->
      <div class="tab-pane fade in" id="news">
        <?php include TEMPLATE_ROOT . 'default/news.inc.php';?>
      </div>
      <!--新闻资讯-->
            <!--更多-->
            <div class="tab-pane fade fade-right" id="more">
                <div class="col-xs-6 col-sm-4 col-lg-4<?php if (empty($conf['appurl'])) {?> hide<?php }?>">
                    <a class="block block-link-hover2 text-center" href="kjshop.php" target="_blank">
                        <div class="block-content block-content-full bg-success">
                            <i class="fa fa-cut fa-3x text-white"></i>
                            <div class="font-w600 text-white-op push-15-t">我要砍价</div>
                        </div>
                    </a>
                </div>

            <div class="col-xs-6 col-sm-4 col-lg-4<?php $col = $DB->count("SELECT count(*) FROM `shua_message` WHERE `type` < 2 ");if ($col == 0) {?> hide<?php }?>">
                    <a class="block block-link-hover2 text-center" href="?mod=articlelist">
                        <div class="block-content block-content-full bg-success">
                            <i class="fa fa-book fa-3x text-white"></i>
                            <div class="font-w600 text-white-op push-15-t">文章列表</div>
                        </div>
                    </a>
               </div>
                <div class="col-xs-6 col-sm-4 col-lg-4<?php if (empty($conf['lqqapi'])) {?> hide<?php }?>">
                    <a class="block block-link-hover2 text-center" data-toggle="modal" href="#tuangou">
                        <div class="block-content block-content-full bg-primary">
                            <i class="fa fa-circle-o fa-3x text-white"></i>
                            <div class="font-w600 text-white-op push-15-t">免费拉圈</div>
                        </div>
                    </a>
                </div>
        <div class="col-xs-6 col-sm-4 col-lg-4<?php if (empty($conf['invite_tid'])) {?> hide<?php }?>">
          <a class="block block-link-hover2 text-center" href="./?mod=invite" target="_blank">
            <div class="block-content block-content-full bg-warning">
              <i class="fa fa-paper-plane-o fa-3x text-white"></i>
              <div class="font-w600 text-white-op push-15-t">免费领赞</div>
              </div>
          </a>
        </div>

                <div class="col-xs-6 col-sm-4 col-lg-4<?php if ($conf['iskami'] == 0 || $conf['fenzhan_buy'] == 0 || $conf['gift_open'] == 0) {?> hide<?php }?>">
                    <a class="block block-link-hover2 text-center" href="#cardbuy" data-toggle="tab">
                        <div class="block-content block-content-full bg-amethyst">
                            <i class="fa fa-credit-card fa-3x text-white"></i>
                            <div class="font-w600 text-white-op push-15-t">卡密下单</div>
                        </div>
                    </a>
                </div>
        <div class="col-xs-6 col-sm-4 col-lg-4<?php if (empty($conf['chatframe'])) {?> hide<?php }?>">
          <a class="block block-link-hover2 text-center" href="#chat" data-toggle="tab">
            <div class="block-content block-content-full bg-success">
               <i class="fa fa-comments fa-3x text-white"></i>
              <div class="font-w600 text-white-op push-15-t">在线聊天</div>
            </div>
          </a>
        </div>
                <div class="col-xs-6 col-sm-4 col-lg-4">
                    <a class="block block-link-hover2 text-center" href="./user/" target="_blank">
                        <div class="block-content block-content-full bg-city">
                            <i class="fa fa-certificate fa-3x text-white"></i>
                            <div class="font-w600 text-white-op push-15-t">分站后台</div>
                        </div>
                    </a>
                </div>
            </div>
            <!--更多-->
            <!--拉圈圈-->
             <div class="modal fade" id="lqq" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-dialog-popin">
                    <div class="modal-content">
                        <div class="block block-themed block-transparent remove-margin-b">
                            <div class="block-header bg-primary-dark">
                                <ul class="block-options">
                                    <li>
                                        <button data-dismiss="modal" type="button"><i class="si si-close"></i></button>
                                    </li>
                                </ul>
                                <h4 class="block-title">免费拉圈圈99+</h4>
                            </div>
                            <div class="modal-body">
                                <div id="alert_frame" class="alert alert-info">
                                    免费拉取圈圈标签赞 99+ ，不是100%成功哦！
                                </div>
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-addon">请输入QQ</div>
                                        <input type="text" name="qq" id="qq4" value="" class="form-control" required/>
                                    </div>
                                </div>
                                <input type="submit" id="submit_lqq" class="btn btn-primary btn-block" value="立即提交">
                                <div id="result3" class="form-group text-center" style="display:none;"></div>
                                <br/>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-sm btn-default" type="button" data-dismiss="modal">关闭</button>
                        </div>
                    </div>
                </div>
            </div>
            <!--拉圈圈-->
            <!--聊天-->
            <div class="tab-pane fade fade-right" id="chat">
                <?php echo $conf['chatframe'] ?>
            </div>
            <!--聊天-->
           </div>
          </div>
<?php if ($conf['bottom']) {?>
<div class="panel panel-primary">
<?php echo $conf['bottom']; ?>
</div>
<?php }?>

<?php
//加载插件代码
hook('bottom');
?>

<?php
if ($conf['hide_tongji'] != 1):
    $fenzhan = $DB->count("SELECT count(*) from shua_site");
    ?>
          <div class="panel panel-primary">
                <div class="panel-heading">
                  <h3 class="panel-title">
                    <font color="#000000">
                      <i class="fa fa-bar-chart-o"></i>&nbsp;
                      <b>数据统计</b>
                    </font>
                  </h3>
                </div>
                <table class="table table-bordered">
                  <tbody>
                    <tr>
                              <td align="center">
                        <font size="2">
                          <span id="count_orders_all">99999</span>条
                          <br>
                          <font color="#65b1c9">
                            <i class="fa fa-cart-plus fa-2x"></i></font>
                          <br>订单总数</font></td>
                                        <td align="center">
                        <font size="2">
                          <span id="orders">999</span>条
                          <br>
                          <font color="#65b1c9">
                            <i class="fa fa-check-square fa-2x"></i></font>
                          <br>今日订单</font></td>
                      <td align="center">
                        <font size="2">
                          <span id="yxts">365</span>天
                          <br>
                          <font color="#65b1c9">
                            <i class="fa fa-shield fa-2x"></i></font>
                          <br>安全运营</font></td>

                    </tr>
                  </tbody>
                </table>
              </div>
<?php endif;?>
  <div class="panel panel-default">
 <center>  <div class="panel-body"><b>
 <center>
 <span style="color:#FF0000">网址收藏到浏览器书签 方便下次购物查单</span>
<br><img border="0" width="95" src="<?php echo $cdnserver ?>assets/xiaoyao/anquan1.png"><img border="0" width="95" src="<?php echo $cdnserver ?>assets/xiaoyao/anquan2.png"><img border="0" width="95" src="<?php echo $cdnserver ?>assets/xiaoyao/anquan3.png">
 </center>
<a href="javascript:void(0);" onclick="AddFavorite('QQ云商城',location.href)"><font color="#C00000">本</font><font color="#B5000B">站</font><font color="#AA0016">地</font><font color="#9F0021">址</font><font color="#94002C">：</font>
<font color="red">
<script language="javascript">
host = window.location.host;
document.write(""+host)
</script>
</font>
<font color="#890037">（</font><font color="#7E0042">欢</font><font color="#73004D">迎</font><font color="#680058">收</font><font color="#5D0063"></font><font color="#52006E">藏</font><font color="#470079">）</b></font></b></a><br><span style="font-weight:bold"><font color="#C00000">C</font><font color="#B5000B">o</font><font color="#AA0016">p</font><font color="#9F0021">y</font><font color="#94002C">R</font><font color="#890037">i</font><font color="#7E0042">g</font><font color="#73004D">h</font><font color="#680058">t</font><font color="#5D0063"></font> <i class="fa fa-heart text-danger"></i> <font color="#52006E">2</font><font color="#470079">0</font><font color="#3C0084">1</font><font color="#31008F">9</font><font color="#26009A"> | </font><a href="/"><?php echo $conf['sitename'] ?></a></span>
<br>
  <!--底部统计代码-->
  <div class="form-group">
    <?
if ($is_fenzhan !== true) {
    echo $conf['zz_index_html_bottom'];
}
?>
  </div>
  <!--底部统计代码-->

<!--底部导航-->

</div>

<!--右侧悬浮分类按钮-->
<?php
if ($conf['right_btn_open'] == 1) {
    echo $conf['right_btn_code'];
}
?>
<!--右侧悬浮分类按钮-->
<?php hook('footer_before');?>

<script src="<?php echo $cdnpublic ?>jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>
<script src="<?php echo $cdnserver ?>assets/js/removead.js?ver=<?php echo $jsver ?>"></script>
<script src="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="<?php echo $cdnpublic ?>jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="<?php echo $cdnpublic ?>layer/3.4.0/layer.js"></script>
<script src="<?php echo $cdnpublic ?>distpicker/2.0.3/distpicker.min.js"></script>
<script src="<?php echo $cdnpublic ?>clipboard.js/1.7.1/clipboard.min.js"></script>
<script src="<?php echo $cdnserver ?>assets/appui/js/plugins.js"></script>
<script src="<?php echo $cdnserver ?>assets/appui/js/app.js"></script>
<script type="text/javascript">
var isModal=<?php echo empty($conf['modal']) ? 'false' : 'true'; ?>;
var _modalType=<?php echo $conf['modal_type'] > 0 ? '1' : '0'; ?>;
var homepage=true;
var hashsalt=<?php echo $addsalt_js ?>;
var clipboard = new Clipboard('#copy-btn');
clipboard.on('success', function(e) {
layer.msg('复制成功，快去发给你的朋友吧！');
});
clipboard.on('error', function(e) {
layer.msg('复制失败，请长按链接后手动复制');
});

</script>
<script src="<?php echo $cdnserver ?>assets/js/main.js?<?php echo $jsver; ?>"></script>
<?php
//加载插件代码
hook('footer_after');
?>
</body>
</html>
