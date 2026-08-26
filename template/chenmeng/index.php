
<?php
if (!defined('IN_CRONLITE')) {
    exit();
}

$agent  = $_SERVER['HTTP_USER_AGENT'];
$iphone = stripos($agent, 'iphone') !== false ? true : false;
//文章资讯
$type     = '0,1';
$msgcount = $DB->count("SELECT count(*) FROM pre_message WHERE type IN ($type) AND active=1");
$limit    = 10;
$rs       = $DB->query("SELECT * FROM pre_message WHERE type IN ($type) AND active=1 ORDER BY id DESC LIMIT 0,$limit");
$msgrow   = array();
while ($res = $DB->fetch($rs)) {
    $msgrow[] = $res;
}

$class_rand = mt_rand(1, 99999);

?>
<!DOCTYPE html>
<meta name="baidu-site-verification" content="2ZEODK2gW6" />
<html lang="zh-cn">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
  <title><?php echo $conf['sitename'] ?>-<?php echo $conf['title'] ?></title>
  <meta name="keywords" content="layer/3.4.0/<?php echo $conf['keywords'] ?>">
  <meta name="description" content="layer/3.4.0/<?php echo $conf['description'] ?>">
  <link rel="shortcut icon" href="favicon.ico">
  <link href="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/plugins.css">
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/oneui.css">
  <script src="<?php echo $cdnpublic ?>jquery/1.12.4/jquery.min.js"></script>
  <script src="<?php echo $cdnpublic ?>modernizr/2.8.3/modernizr.min.js"></script>
  <?php
//加载插件代码
hook('head');
?>
  <!--[if lt IE 9]>
    <script src="//lib.baomitu.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="//lib.baomitu.com/respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
<style>
<?php echo $conf['qjcss'] ?>

img.logo{width:14px;height:14px;margin:0 5px 0 3px;}
.onclick{cursor: pointer;touch-action: manipulation;}
.giftlist{overflow:hidden;width:90%;margin:0 auto}
.giftlist ul{height:270px;overflow:hidden;padding:0}
.giftlist li{width:100%;line-height:35px;padding:0 10px;overflow:hidden;box-sizing:border-box;-webkit-box-sizing:border-box;-moz-box-sizing:border-box}
.giftlist li strong{margin:0 5px 0 0;font-weight:400;color:#1977d8}
.modal-backdrop{z-index:1;}
</style>
  <img  style="background: linear-gradient(to bottom, #49BDAD,#f2b9ca);"  class="full-bg full-bg-bottom">
</head>
<body>
<br/>
<?php include TEMPLATE_ROOT . 'default/head.inc.php';?>
 <div class=" col-xs-12 col-sm-12 col-md-10 col-lg-6 center-block" id="contentBox" style="float: none;margin: 0 auto;padding: 0;z-index: 99;display: block;">
<!--平台公告和推荐-->
<div class=" modal fade" align="left" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class=" modal-dialog">
        <div class=" modal-content" >
          <div class=" modal-header" style="background:linear-gradient(120deg, #31B404 0%, #D7DF01 100%);margin:0;padding:5px 0;">
            <button type="button" class=" close" data-dismiss="modal" onclick=""><span aria-hidden="true">&times;</span><span class=" sr-only">关闭</span>
            </button>
            <center><h4 class=" modal-title" style="color:#fff"><?php echo date('m月-d日'); ?>商品/售后公告</h4></center>
          </div>
          <div class=" modal-body" style="">
            <ul class=" nav nav-tabs nav-tabs-alt text-center">
                <li style="width: 50%;" class=" active"><a href="#article" style="color: blue" data-toggle="tab">平台公告</a></li>
                <li style="width: 50%;" ><a href="#shopArticle" style="color: red" data-toggle="tab">商品通知<img src="<?php echo $cdnserver ?>assets/xiaoyao/hot.gif"/></a></li>
            </ul>
            <div id="myTabContent" class=" tab-content">
                <div class=" tab-pane fade in active" id="article">
                    <table class=" table table-hover table-bordered">
                    <tbody id="msglist">
                    <?php
$i = 0;
if ($conf['message_page'] < 1) {
    $conf['message_page'] = '10';
}

foreach ($msgrow as $row) {
    if ($row['cid'] == 1 && $i < $conf['message_page']) {
        echo '<tr class=" widget animation-fadeInQuick onclick" onclick="window.location.href=\'http://' . $_SERVER['HTTP_HOST'] . '/article/' . $row['id'] . '.html\'" style="font-size:12px;padding: 2px 3px;"><td style="font-size:12px;"><a href="http://' . $_SERVER['HTTP_HOST'] . '/article/' . $row['id'] . '.html"><b class=" pull-left">' . $row['title'] . '</b><small class=" pull-right"><span class=" text-muted">' . $row['addtime'] . '</span></small></a></td></tr>';
        $i++;
    }
}
if ($i == 0) {
    echo '<tr><td class=" text-center"><font color="grey">平台公告文章空空如也</font></td></tr>';
}
?>
                     </tbody>
                     </table>
                     <a href="/article/index.html" class=" btn btn-primary btn-block">查看全部平台公告</a>
                </div>
                <div class=" tab-pane fade in" id="shopArticle">
                    <table class=" table table-hover table-bordered">
                    <tbody id="msglist">
                    <?php
$i = 0;
foreach ($msgrow as $row) {
    if ($row['cid'] == 2 && $i < $conf['message_page']) {
        echo '<tr class=" widget animation-fadeInQuick onclick" onclick="window.location.href=\'http://' . $_SERVER['HTTP_HOST'] . '/article/' . $row['id'] . '.html\'" style="font-size:12px;padding: 2px 3px;"><td style="font-size:12px"><a href="http://' . $_SERVER['HTTP_HOST'] . '/article/' . $row['id'] . '.html"><b class=" pull-left">' . $row['title'] . '</b><small class=" pull-right"><span class=" text-muted">' . $row['addtime'] . '</span></small></a></td></tr>';
        $i++;
    }
}
if ($i == 0) {
    echo '<tr><td class=" text-center"><font color="grey">商品通知文章空空如也</font></td></tr>';
}
?>
                     </tbody>
                     </table>
                      <a href="/article/index.html" class=" btn btn-primary btn-block">查看全部商品通知</a>
                </div>

             </div>
          </div>
       <div class=" modal-footer text-center">
            <center><button type="button" class=" btn btn-white" data-dismiss="modal" onclick="">朕知道了</button></center>
          </div>
          </div>
      </div>
</div>
<!--平台公告和推荐-->
<div class=" widget">
<!--logo-->
  <!--顶部导航-->
        <div class=" block block-link-hover3" href="javascript:void(0)">
          <div class=" block-content block-content-full text-center bg-image" style="background-image: url('assets/xiaoyao/head4.png');background-size: 100% 100%;">
            <div>
              <div>
                <a href="/">
                  <img class=" img-avatar img-avatar80 img-avatar-thumb animated zoomInDown qqdsw" src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $siterow['qq'] ? $siterow['qq'] : $conf['kfqq'] ?>&amp;spec=100" onerror="this.src='img-avatar img-avatar80 img-avatar-thumb animated zoomInDown qqdsw" ></a>
              </div>
            </div>
          </div>
           <img width="100%" src="<?php echo $cdnserver ?>assets/img/yewu.jpg">
          <div class=" block-content block-content-mini block-content-full bg-gray-lighter">
            <div class=" text-center text-muted">
              <div class=" btn-group btn-group-justified">
                <?php if ($conf['template_chenmeng_btn'] == 1) {?>
                <div class=" btn-group">
                  <?php if ($conf['template_chenmeng_btn_type'] == 1): ?>
                    <a class=" btn btn-default" onclick="layer.alert('<?php echo $conf['template_chenmeng_btn_content'] ?>')"><i class=" fa fa-users"></i> <span style="font-weight:bold"><?php echo $conf['template_chenmeng_btn_title'] ?></span></a>
                  <?php else: ?>
                    <a href="<?php echo $conf['template_chenmeng_btn_content'] ?>" target="_blank" class="btn btn-default"><i class=" fa fa-users"></i> <span style="font-weight:bold"><?php echo $conf['template_chenmeng_btn_title'] ?></span></a>
                  <?php endif;?>
                </div>
                <?php }?>
                <div class=" btn-group">
                    <a class=" btn btn-default"  href="./?mod=invite"><i class=" fa fa-share-alt"></i> <span style="font-weight:bold"> 邀人有礼</span></a>
                 </div>
                <?php if (!$iphone) {?>
                <div class=" btn-group">
                      <a class=" btn btn-default" id="appurl" target="_blank" href="<?php echo $siterow['appurl'] ? $siterow['appurl'] : $conf['appurl'] ?>" ><font id="appfile" color="#ff0000"><i class=" fa fa-mobile"></i>下载APP</font></a>
                </div>
                <?php }?>
              </div>
            </div>
          </div>
          <?php echo $conf['zz_top'] ?>
        </div>

</div>
<?php
//加载插件代码
hook('header');
?>

        <div class=" block full2">
  <!--TAB标签开始-->
      <ul class=" nav nav-tabs nav-tabs-alt" >
            <li style="width: 20%;" align="center" class=" active"><a data-toggle="tab" href="#shop"><i class=" fa fa-shopping-cart fa-lg" style="color: green"></i><br style="margin:0;padding:0"><b>下单</b></a></li>
            <li style="width: 20%;" align="center" class=" "><a data-toggle="tab" href="#search"> <i class=" fa fa-search fa-lg" style="color: blue"></i><br style="margin:0;padding:0"><b>查单</b></a></li>
            <li style="width: 20%;" align="center" class=" "><a data-toggle="tab" href="#Substation"><font color="#FF4000"><i class=" fa fa-jpy fa-lg" style=""></i><br style="margin:0;padding:0"><b>赚钱</b></font></a></li>
            <li style="width: 20%;" align="center" class=" "><a data-toggle="tab" href="#news"><font color="blue"><i class=" fa fa-volume-up fa-lg" style="color: red"></i><br style="margin:0;padding:0"><b>通知</b></font></a></li>

            <li style="width: 20%;" align="center" class=" "><a data-toggle="tab" href="#more"><i class=" fa fa-hourglass-half fa-lg" style=""></i><br style="margin:0;padding:0"><b>福利</b></a></li>

        </ul>
<!--TAB标签结束-->
          <div class=" block-content tab-content">
<!--在线下单-->
     <div class=" tab-pane active" id="shop">
        <?php include TEMPLATE_ROOT . 'default/shop.inc.php';?>
    </div>
<!--在线下单-->
<!--查询订单-->
    <div class=" tab-pane fade-up" id="search">
       <?php include TEMPLATE_ROOT . 'default/query.inc.php';?>
    </div>
<!--查询订单-->
<!--新闻资讯-->
  <div class=" tab-pane" id="news">
      <?php include TEMPLATE_ROOT . 'default/news.inc.php';?>
  </div>
<!--新闻资讯-->
<!--更多-->
    <div class=" tab-pane fade fade-right" id="more">
        <br>
        <div class=" col-xs-6 col-sm-4 col-lg-4">
                <a class=" block block-link-hover2 text-center" href="<?php echo $cdnserver ?>user">
                    <div class=" block-content block-content-full bg-city">
                       <i class=" fa fa-lock fa-3x text-white"></i>
                        <div class=" font-w600 text-white-op push-15-t">分站后台</div>
                    </div>
                </a>
            </div>
      <div class=" col-xs-6 col-sm-4 col-lg-4">
                <a class=" block block-link-hover2 text-center" data-toggle="modal" href="<?php echo $cdnserver ?>user/reg.php">
                    <div class=" block-content block-content-full bg-primary-dark">
                      <i class=" fa  fa-files-o  fa-3x text-white"></i>
                        <div class=" font-w600 text-white-op push-15-t">搭建分站</div>
                    </div>
                </a>
            </div>
        <div class=" col-xs-6 col-sm-4 col-lg-4">
                <a target="_blank" class=" block block-link-hover2 text-center" href="#gift" data-toggle="tab" rel="nofollow">
                    <div class=" block-content block-content-full bg-success">
                       <i class=" fa fa-credit-card fa-3x text-white"></i>
                        <div class=" font-w600 text-white-op push-15-t">抽奖</div>
                    </div>
                </a>
            </div>
      <div class=" col-xs-6 col-sm-4 col-lg-4">
                <a class=" block block-link-hover2 text-center" data-toggle="modal" href="#lqq">
                    <div class=" block-content block-content-full bg-primary">
                      <i class=" fa fa-circle-o fa-3x text-white"></i>
                        <div class=" font-w600 text-white-op push-15-t">免费拉圈</div>
                    </div>
                </a>
            </div>
           <div class=" col-xs-6 col-sm-4 col-lg-4">
                <a href="?mod=invite" target="_blank" class=" block block-link-hover2 text-center">
                    <div class=" block-content block-content-full bg-amethyst">
                      <i class=" fa fa-rocket fa-3x text-white"></i>
                        <div class=" font-w600 text-white-op push-15-t">免费领赞</div>
                    </div>
                </a>
          </div>
       </div>
<!--更多-->
<!--开通分站-->
    <div class=" tab-pane" id="Substation">
                 <div class=" block block-link-hover2 text-center">
                    <div class=" block-content block-content-full bg-success">
                        <div class=" h4 font-w700 text-white push-10"><i
                                    class=" fa fa-cny fa-fw"></i><strong><?php echo $conf['ktfz_price'] ? $conf['ktfz_price'] : $conf['fenzhan_price'] ?>元</strong> /
                            <i
                                    class=" fa fa-cny fa-fw"></i><strong><?php echo $conf['ktfz_price2'] ? $conf['ktfz_price2'] : $conf['fenzhan_price2'] ?>元</strong>
                        </div>
                        <div class=" h5 font-w300 text-white-op">普及版 / 专业版两种分站供你选择</div>
                    </div>
                    <div class=" block-content">
                        <table class=" table table-borderless table-condensed">
                            <tbody>
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
                            </tbody>
                        </table>
                    </div>
                    <div class=" block-content block-content-mini block-content-full bg-gray-lighter">
                     <a href="#userjs" data-toggle="modal" class=" btn btn-success btn-sm hidden-xs hidden-sm">版本介绍</a>
                    <button onclick="window.open('./user/reg.php')" class=" btn btn-danger btn-sm">开通分站</button>
                    <button onclick="window.open('./user/index.php')" class=" btn btn-primary btn-sm">分站后台</button>

                    </div>
                </div>
  </div>
<!--开通分站-->
<!--抽奖-->
    <div class=" tab-pane" id="gift">
   <li class=" list-group-item" style="background: linear-gradient;"><center><b>（完成全部任务 - 高机率中大奖）</b></center></li>
      <li class=" list-group-item" style="background: linear-gradient;"><center>一：拥有一个分站不限制版本 （<a href="<?php echo $cdnserver ?>user/reg.php" target="_blank">点击开通</a>）</center></li>
      <li class=" list-group-item" style="background: linear-gradient;"><center>二：将本网站分享给一个朋友 （<a href="?mod=invite" target="_blank">点击分享</a>） </center></li>
      <br>
    <div class=" widget-content themed-background-flat text-right clearfix animation-pullup">
        <a id="start" style="display:block;"><img src="<?php echo $cdnserver ?>assets/xiaoyao/choujiang.gif" alt="avatar" class=" img-circle img-thumbnail img-thumbnail-avatar pull-left">
        </a>
        <a id="stop" style="display:none;"><img src="<?php echo $cdnserver ?>assets/xiaoyao/choujiang2.gif" alt="avatar" class=" img-circle img-thumbnail img-thumbnail-avatar pull-left">
        </a>
        <p></p>
        <h4 id="roll" class=" widget-heading h4"><font color="#00BFFF"><i class=" fa fa-hand-o-left" aria-hidden="true"></i> 猛击按钮进行抽奖</font></h4>
    <h4 id="roll" class=" widget-heading h4"><font color="#ff0000"><i class=" fa fa-heartbeat" aria-hidden="true"></i> 再次猛击获取奖品</font></h4>
         </div>
     <hr>
     <font color="#FF7F00">
     <li class=" list-group-item bord-top"><b>抽奖规则：</b>每人每天限抽1次，欢迎您每天来抽奖！
         <br><b>奖品内容：</b>本站的N个商品，持续添加劲爆更新中！
         <br><font color="#008000">抽奖心得：赶快邀请你的朋友来吧，听说推广网站有几率中大奖哦！</font>
    <button id="copy-btn" class=" btn btn-success btn-xs" data-clipboard-text="我在这里参与抽奖，你也快来吧！地址：http://<?php echo $wzlj ?>/?<?php echo rand(10, 999) ?>（请复制网址到浏览器内打开）">点我复制推广链接</button>
<br></font></li><br>
  </div>
<!--卡密下单-->
    <div class=" tab-pane" id="cardbuy">
    <div class=" form-group">
      <div class=" input-group"><div class=" input-group-addon">输入卡密</div>
      <input type="text" name="km" id="km" value="" class=" form-control" onkeydown="if(event.keyCode==13){submit_checkkm.click()}" required/>
    </div></div>
    <input type="submit" id="submit_checkkm" class=" btn btn-primary btn-block" value="检查卡密">
    <div id="km_show_frame" style="display:none;">
    <div class=" form-group">
      <div class=" input-group"><div class=" input-group-addon">商品名称</div>
      <input type="text" name="name" id="km_name" value="" class=" form-control" disabled/>
    </div></div>
    <div class=" form-group">
      <div class=" input-group"><div class=" input-group-addon" id="km_inputname">下单ＱＱ</div>
      <input type="text" name="inputvalue" id="km_inputvalue" value="" class=" form-control" required/>
    </div></div>
    <div id="km_inputsname"></div>
    <div id="km_alert_frame" class=" alert alert-success animation-pullUp" style="display:none;font-weight: bold;"></div>
    <input type="submit" id="submit_card" class=" btn btn-primary btn-block" value="立即购买">
    <div id="result1" class=" form-group text-center" style="display:none;">
    </div>
    </div>
    <br />
  </div>
<!--卡密下单-->
<!--聊天-->
    <div class=" tab-pane" id="chat">
  </div>
<!--聊天-->
    </div>
</div>

<!--奖励统计-->
    <?php if ($conf['invite_open'] == 1) {?>
     <div class=" panel panel-primary">
        <?php include TEMPLATE_ROOT . 'default/invite.inc.php';?>
    </div>
  <?php }?>
<!--奖励统计-->

<?php
//加载插件代码
hook('bottom');
?>

<!--底部统计代码-->
   <div class="panel panel-primary">
    <?php echo $conf['bottom'] ?>
  </div>
<!--底部统计代码-->

<!--网站统计-->
     <div class=" panel panel-primary">
            <div class=" panel-heading">
              <h3 class=" panel-title">
                <font color="#000000">
                  <i class=" fa fa-bar-chart-o"></i>&nbsp;
                  <b>数据统计</b>
                </font>
              </h3>
            </div>
            <div class=" panel-body text-center">

            <div class=" col-xs-3 col-sm-3 col-md-3">
                    <h6 class=" widget-heading"><small>订单总数</small><br><a href="javascript:void(0)" class=" themed-color-flat"><span id="count_orders">***</span>条</a></h6>
            </div>
            <div class=" col-xs-3 col-sm-3 col-md-3">
               <h6 class=" widget-heading"><small>加盟分站</small><br><a href="javascript:void(0)" class=" themed-color-flat"><span id="count_site">388</span>个</a></h6>
            </div>
            <div class=" col-xs-3 col-sm-3 col-md-3">
               <h6 class=" widget-heading"><small>商品数量</small><br><a href="javascript:void(0)" class=" themed-color-flat"><span id="count_tool">123</span>个</a></h6>
            </div>
            <div class=" col-xs-3 col-sm-3 col-md-3">
              <h6 class=" widget-heading"><small>运营天数</small><br><a href="javascript:void(0)" class=" themed-color-flat"><span id="count_yxts">365</span>天</a></h6>
            </div>
            </div>

          </div>
<!--网站统计-->

<!--底部导航-->
<div class=" panel panel-default">
 <center>  <div class=" panel-body"><b>
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
<font color="#890037">（</font><font color="#7E0042">欢</font><font color="#73004D">迎</font><font color="#680058">收</font><font color="#5D0063"></font><font color="#52006E">藏</font><font color="#470079">）</b></font></b></a><br><span style="font-weight:bold"><font color="#C00000">C</font><font color="#B5000B">o</font><font color="#AA0016">p</font><font color="#9F0021">y</font><font color="#94002C">R</font><font color="#890037">i</font><font color="#7E0042">g</font><font color="#73004D">h</font><font color="#680058">t</font><font color="#5D0063"></font> <i class=" fa fa-heart text-danger"></i> <font color="#52006E">2</font><font color="#470079">0</font><font color="#3C0084">1</font><font color="#31008F">9</font><font color="#26009A"> | </font></span>
<br>
<P>
    <script type="text/javascript">var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1263049246'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s13.cnzz.com/z_stat.php%3Fid%3D1263049246%26online%3D2' type='text/javascript'%3E%3C/script%3E"));</script>
    </P>
       @ <a href="http://<?php $_SERVER['HTTP_HOST']?>"><?php echo $conf['sitename'] ?></a> 诚信为本，信誉至上
       <br>
      <span style="font-size: 12px;font-size:1.0rem; "> 本站关键词：<a href="http://<?php echo $_SERVER['HTTP_HOST'] ?>">qq云商城</a>|<a href="http://<?php echo $_SERVER['HTTP_HOST'] ?>">刷**网址</a>|<a href="http://<?php echo $_SERVER['HTTP_HOST'] ?>">免费刷**</a>|<a href="http://<?php echo $_SERVER['HTTP_HOST'] ?>">QQ**网站</a>|<a href="http://<?php echo $_SERVER['HTTP_HOST'] ?>">**软件</a>|<a href="http://<?php echo $_SERVER['HTTP_HOST'] ?>">云商城</a></span>

<!--底部导航-->


       <!-- 全局底部代码 -->
<?php echo $conf['index_html_bottom'] ?>
    <!-- 全局底部代码 -->


</div>

<!--右侧悬浮分类按钮-->
<?php
if ($conf['right_btn_open'] == 1) {
    echo $conf['right_btn_code'];
}
?>
<!--右侧悬浮分类按钮-->
<?php hook('footer_before');?>
<script src="<?php echo $cdnserver ?>assets/js/removead.js?ver=<?php echo VERSION ?>"></script>
<script src="<?php echo $cdnpublic ?>jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>
<script src="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="<?php echo $cdnpublic ?>jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="<?php echo $cdnpublic ?>clipboard.js/1.7.1/clipboard.min.js"></script>
<script src="<?php echo $cdnpublic ?>layer/3.4.0/layer.js"></script>
<script src="<?php echo $cdnpublic ?>distpicker/2.0.3/distpicker.min.js"></script>
<script src="<?php echo $cdnpublic ?>layer/3.4.0/layer.js"></script>
<script src="<?php echo $cdnserver ?>assets/appui/js/plugins.js"></script>
<script src="<?php echo $cdnserver ?>assets/appui/js/app.js"></script>
<script type="text/javascript">
var isModal=true;
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
<script src="<?php echo $cdnserver ?>assets/js/main.js<?php echo VERSION; ?>"></script>
<?php
//加载插件代码
hook('footer_after');
?>
</body>
</html>