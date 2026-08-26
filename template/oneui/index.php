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
if ($conf['message_static'] == 1) {
    $_indexUrl = $cdnserver . 'article/index.html';
} else {
    $_indexUrl = $cdnserver . 'article/index.php';
}

?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8"/>
    <meta name="renderer"  content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="renderer"  content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title><?php echo $conf['sitename'] ?> - <?php echo $conf['title'] ?></title>
    <meta name="keywords" content="<?php echo $conf['keywords'] ?>">
    <meta name="description" content="<?php echo $conf['description'] ?>">
    <link href="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/oneui.css">
    <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/css/common.css?ver=<?php echo VERSION ?>">
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
</head>
<body>
<img src="<?php echo $background_image; ?>" alt="Full Background" class="full-bg full-bg-bottom animated pulse " ondragstart="return false;" oncontextmenu="return false;">
<?php include TEMPLATE_ROOT . 'default/head.inc.php';?>
<!--弹出公告-->
<div class="modal fade" align="left" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
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
<div class="modal fade" align="left" id="mustsee" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">公告</h4>
      </div>
      <div class="modal-body" style="padding:6px;font-size:13px;text-align: left;">
       <table class="table table-hover table-bordered">
            <tbody id="msglist">
            <?php
$i = 0;
foreach ($msgrow as $row) {
    if ($conf['message_static'] == 1) {
        $_url = $weburl . 'article/' . $row['id'] . '.html';
    } else {
        $_url = $weburl . 'article/?id=' . $row['id'] . '.html';
    }
    echo '<tr class="widget animation-fadeInQuick onclick" onclick="window.location.href=\'' . $_url . '\'"><td><a href="' . $_url . '" ><b class="pull-left">' . $row['title'] . '</b><br/><small class="pull-right"><span class="text-muted">' . $row['addtime'] . '</span></small></a></td></tr>';
    $i++;
}
if ($msgcount == 0) {
    echo '<tr><td class="text-center"><font color="grey">消息列表空空如也</font></td></tr>';
}
?>
             </tbody>
          </table>
           <a href="<?php echo $_indexUrl ?>" class="btn btn-primary btn-block">进入文章首页</a>
    </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
      </div>
    </div>
  </div>
 </div>
<!--公告-->
<div class="col-xs-12 col-sm-10 col-md-8 col-lg-5 center-block" style="float: none;">
    <br/>
    <!--顶部导航-->
    <div class="block block-link-hover3" href="javascript:void(0)">
        <div class="block-content block-content-full text-center bg-image"
             style="background-image: url('<?php echo $cdnserver ?>assets/simple/img/head2.png');background-size: 100% 100%;">
            <div>
                <div>
                    <img class="img-avatar img-avatar80 img-avatar-thumb animated zoomInDown"
                         src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq'] ?>&spec=100">
                </div>
            </div>
        </div>
        <div class="block-content block-content-mini block-content-full bg-gray-lighter">
            <div class="text-center text-muted">
                <div class="btn-group">
                    <div class="btn-group">
                        <a class="btn btn-default" data-toggle="modal" href="#mustsee"><font color="#ff0000"><i class="fa fa-wifi"></i>
                            平台公告</a></font>
                    </div>
                     <?php if ($conf['appurl']) {?>
                      <div class="btn-group">
                        <a  target="_blank" id="appurl" style="background: linear-gradient(to right,pink ,#FFFFFF,#FFFFFF,#FFFFFF);" class="btn btn-default" target="_blank" href="<?php echo $siterow['appurl'] ? $siterow['appurl'] : $conf['appurl'] ?>" ><i class="fa fa-mobile fa-lg"></i> APP端</a>
                    </div>
                    <?php }?>
                    <?php if ($isLogin2 == 1) {?>
                    <div class="btn-group">
                        <a href="./user/" class="btn btn-effect-ripple btn-default"><i class="glyphicon glyphicon-user"></i> <span style="font-weight:bold">管理后台</span></a>
                    </div>
                    <?php } else {?>
                    <div class="btn-group">
                        <a href="./user/login.php" class="btn btn-effect-ripple btn-default"><i class="glyphicon glyphicon-user"></i> <span style="font-weight:bold">登录</span></a>
                    </div>
                    <div class="btn-group">
                        <a href="./user/reg.php" class="btn btn-effect-ripple btn-default"><i class="glyphicon glyphicon-plus"></i> <span style="font-weight:bold">注册</span></a>
                    </div>
                    <?php }?>
                </div>
            </div>
        </div>
        <div class="row panel-body" style="text-align: left;">
           <table class="table table-hover table-bordered">
            <tbody id="msglist">
            <?php
$i = 0;
foreach ($msgrow as $row) {
    if ($i > 2) {
        continue;
    }

    if ($conf['message_static'] == 1) {
        $_url = '' . $weburl . 'article/' . $row['id'] . '.html';
    } else {
        $_url = '' . $weburl . 'article/?id=' . $row['id'] . '.html';
    }
    echo '<tr class="widget animation-fadeInQuick onclick" onclick="window.location.href=\'' . $_url . '\'"><td><a href="' . $_url . '" ><b class="pull-left">' . $row['title'] . '</b><br/><small class="pull-right"><span class="text-muted">' . $row['addtime'] . '</span></small></a></td></tr>';
    $i++;
}
if ($msgcount == 0) {
    echo '<tr><td class="text-center"><font color="grey">消息列表空空如也</font></td></tr>';
}
?>
             </tbody>
          </table>
           <a href="<?php echo $_indexUrl ?>" class="btn btn-primary btn-block">进入文章首页</a>
        </div>
    </div>

    <?php
//加载插件代码
hook('header');
?>

    <!--顶部导航-->
    <div class="block">
        <ul class="nav nav-tabs" data-toggle="tabs">
            <li class="active" style="width: 20%;" align="center">
                <a href="#shop" data-toggle="tab"><i class="fa fa-shopping-bag fa-fw"></i> 下单</a>
            </li>
            <li style="width: 20%;" align="center">
                <a href="#search" data-toggle="tab" id="tab-query"><i class="fa fa-search"></i> 查单</a>
            </li>
            <li style="width: 20%;" align="center" <?php if ($conf['fenzhan_buy'] == 0) {?>class="hide"<?php }?>>
                <a href="#ktfz" data-toggle="tab"><i class="fa fa-coffee fa-fw"></i> 赚钱</a>
            </li>
            <li style="width: 20%;" align="center" <?php if ($conf['gift_open'] == 0) {?>class="hide"<?php }?>>
                <a href="#gift" data-toggle="tab"><i class="fa fa-gift fa-fw"></i> 抽奖</a>
            </li>
            <li style="width: 20%;" align="center" <?php if ($conf['iskami'] == 0 || $conf['fenzhan_buy'] == 1 && $conf['gift_open'] == 1) {?>class="hide"<?php }?>>
                <a href="#cardbuy" data-toggle="tab"><i class="glyphicon glyphicon-th"></i> 卡密</a>
            </li>
            <li style="width: 20%;" align="center">
                <a href="#more" data-toggle="tab"><i class="fa fa-folder-open"></i> 更多</a>
            </li>
        </ul>
        <!--TAB-->
        <div class="block-content tab-content">
            <!--在线下单-->
            <div class="tab-pane fade fade-up in active" id="shop">
            <?php include TEMPLATE_ROOT . 'default/shop.inc.php';?>
            </div>
            <!--在线下单-->
            <!--查询订单-->
              <div class=" tab-pane fade-up" id="search">
                 <?php include TEMPLATE_ROOT . 'default/query.inc.php';?>
              </div>
            <!--查询订单-->
            <!--开通分站-->
            <div class="tab-pane fade fade-up" id="ktfz">
                <div class="block block-link-hover2 text-center">
                    <div class="block-content block-content-full bg-success">
                        <div class="h4 font-w700 text-white push-10"><i
                                    class="fa fa-cny fa-fw"></i><strong><?php echo $conf['fenzhan_price'] ?>元</strong> /
                            <i
                                    class="fa fa-cny fa-fw"></i><strong><?php echo $conf['fenzhan_price2'] ?>元</strong>
                        </div>
                        <div class="h5 font-w300 text-white-op">普及版 / 专业版两种分站供你选择</div>
                    </div>
                    <div class="block-content">
                        <table class="table table-borderless table-condensed">
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
                                <td>分站满<?php echo $conf['tixian_min']; ?>元即可申请提现</td>
                            </tr>
                            <tr>
                                <td><strong>轻轻松松推广日赚100+不是梦</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="block-content block-content-mini block-content-full bg-gray-lighter">
                     <a href="#userjs" data-toggle="modal" class="btn btn-success">版本介绍</a>
                    <button onclick="window.open('./user/regsite.php')" class="btn btn-danger">开通分站</button>
                    </div>
                </div>
            </div>
            <!--开通分站-->
            <!--抽奖-->
                <div class="tab-pane fade fade-up" id="gift">
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
                    <div id="km_inputsname"></div>
                    <div id="km_alert_frame" class="alert alert-success animation-pullUp" style="display:none;font-weight: bold;"></div>
                    <input type="submit" id="submit_card" class="btn btn-primary btn-block" value="立即购买">
                    <div id="result1" class="form-group text-center" style="display:none;">
                    </div>
                    </div>
                    <br />
                </div>
            <!--卡密下单-->
           <!--更多-->
            <div class="tab-pane fade fade-right" id="more">
                <div class="col-xs-6 col-sm-4 col-lg-4<?php if (empty($conf['appurl'])) {?> hide<?php }?>">
                    <a class="block block-link-hover2 text-center" href="<?php echo $conf['appurl']; ?>" target="_blank">
                        <div class="block-content block-content-full bg-success">
                            <i class="fa fa-cloud-download fa-3x text-white"></i>
                            <div class="font-w600 text-white-op push-15-t">APP下载</div>
                        </div>
                    </a>
                </div>

                <div class="col-xs-6 col-sm-4 col-lg-4<?php if (empty($conf['daiguaurl'])) {?> hide<?php }?>">
                    <a class="block block-link-hover2 text-center" href="./?mod=daigua">
                        <div class="block-content block-content-full bg-primary">
                            <i class="fa fa-rocket fa-3x text-white"></i>
                            <div class="font-w600 text-white-op push-15-t">QQ等级代挂</div>
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
            <div class="tab-pane fade fade-right" id="chat">
            <?php echo $conf['chatframe'] ?>
            </div>
            <!--聊天-->
        </div>
    </div>
<div class="block block-themed">
    <div class="block-header bg-info">
        <h3 class="block-title">用户反馈留言</h3>
    </div>
<marquee direction="up" behavior="scroll" loop="3" scrollamount="3" scrolldelay="10" align="top" bgcolor="#ffffff" height="200px" width="93%" hspace="20" vspace="10" onmouseover="this.stop()" onmouseout="this.start()" style="background-color: rgb(255, 255, 255); height: 200px; width: 93%; margin: 10px 20px;">

<div class="gg_info" style="margin: 0;color:red"><b>官方：不忘初心，方得始终！感谢一路相伴！</b></div>

<div class="gg_info" style="margin: 0;color:blue">用户203***130说：<b>没毛病老铁，我又来下单了</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户960***86说：<b>超级会员快满五个月了，感谢感谢</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户298***775说：<b>我搭建了个分站，请问一天赚36算少还是多？</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户89***120说：<b>放假啦，又来你这里接单赚钱咯</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户160***816说：<b>QQ会员已稳三个月，起来报道。</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户368***785说：<b>今天我分站提了50多块钱，学生放假就是爽</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户103***108说：<b>秒刷**是真的快，十万**一天就到了</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户60***216说：<b>超级会员已稳定6个月，不多bb，有事没事都会来你这里下两单</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户132***988说：<b>从你们网站刚开我就搭建了个分站，这么多天也赚了两千多了，你们挺诚信的</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户107***124说：<b>我是分站长，每天提现10+元，虽然不多但是起码是自己努力挣的钱！</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户139***201说：<b>今天在你网站进货，一天挣了39块钱啊</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户303***963说：<b>**的真的快，会员也稳定快三个月了</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户296***909说：<b>啥时候搞活动多送点福利呗</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户97***634说：<b>好便宜啊8毛一万**</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户395***824说：<b>豪华绿钻两个月了还没掉耶</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户386***163说：<b>我是分站站长，我要努力赚钱！</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户231***069说：<b>下面这个分享领赞活动我一天领了1万多赞啊</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户322***396说：<b>客服态度真的好好啊</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户266***864说：<b>相信你们平台会越来越好，加油！</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户186***768说：<b>超会已稳一个月，前来反馈。感谢平台</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户209***116说：<b>在你这里下单520**追到个女朋友！！！值。</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户368***423说：<b>今天活动好多啊，感觉要爱上你这个平台了</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户331***032说：<b>今天提了32元，美滋滋……</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户216***675说：<b>在你这搭建了个专业版分站，我要努力宣传！争取一天提现50+元！！！</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户206***311说：<b>这里东西质量真的不错，快刷**基本上秒刷，感谢站长提供平台！</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户97***097说：<b>老板，提现的56块钱秒到账，怎么做到的？</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户108***111说：<b>新手看价格，老手求品质，而牛逼的我搭建分站赚钱</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户768***346说：<b>感谢站长提供这么好的平台给我们接单，支持到底！！！</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户199***017说：<b>用这个接单卖给同学，还挺赚钱的耶！抱拳了</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户367***788说：<b>每天来领100赞，美滋滋(～￣▽￣)～</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户109***148说：<b>18块的买超会都三个月了还在，帮女朋友也开了个哈哈哈！</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户111***684说：<b>666，终于更新了抽奖</b></div><div class="gg_info" style="margin: 0;color:blue">用户203***337说：<b>卧槽，这个真人**速度真他妈快，3分钟刷了4万多</b></div><div class="gg_info" style="margin: 0;color:blue">用户292***724说：<b>非常不错</b></div><div class="gg_info" style="margin: 0;color:blue">用户217***359说：<b>多做这中活动</b></div><div class="gg_info" style="margin: 0;color:blue">用户136***498说：<b>希望能迅速赞</b></div><div class="gg_info" style="margin: 0;color:blue">用户212***285说：<b>hao</b></div><div class="gg_info" style="margin: 0;color:blue">用户318***115说：<b>应该尽量的保证质量，并设置分享有奖，这样，也吸引了客户</b></div><div class="gg_info" style="margin: 0;color:blue">用户159***280说：<b>每天送100**</b></div><div class="gg_info" style="margin: 0;color:blue">用户265***010说：<b>类似于QQ空间里说说的赞，能不能设置指定数量？希望采纳</b></div><div class="gg_info" style="margin: 0;color:blue">用户486***443说：<b>更好</b></div><div class="gg_info" style="margin: 0;color:blue">用户280***172说：<b>非常好</b></div><div class="gg_info" style="margin: 0;color:blue">用户282***989说：<b>好</b></div><div class="gg_info" style="margin: 0;color:blue">用户142***439说：<b>不错不错。太好了</b></div><div class="gg_info" style="margin: 0;color:blue">用户932***936说：<b>能按着数量刷就好了</b></div><div class="gg_info" style="margin: 0;color:blue">用户340***683说：<b>很好用很便宜!</b></div><div class="gg_info" style="margin: 0;color:blue">用户177***283说：<b>很不错的网站，加油</b></div><div class="gg_info" style="margin: 0;color:blue">用户212***285说：<b>好</b></div><div class="gg_info" style="margin: 0;color:blue">用户624***185说：<b>这个平台很不错，信誉很好</b></div><div class="gg_info" style="margin: 0;color:blue">用户316***940说：<b>应该把全民k歌刷花间单的</b></div><div class="gg_info" style="margin: 0;color:blue">用户148***683说：<b>希望可以多点福利，东西很便宜，很好。</b></div><div class="gg_info" style="margin: 0;color:blue">用户132***627说：<b>挺好的</b></div><div class="gg_info" style="margin: 0;color:blue">用户154***984说：<b>我帮你们的网站分享给了很多群，永远支持你们</b></div><div class="gg_info" style="margin: 0;color:blue">用户238***434说：<b>**</b></div><div class="gg_info" style="margin: 0;color:blue">用户239***848说：<b>炫酷点  再加点音乐就好了</b></div><div class="gg_info" style="margin: 0;color:blue">用户245***114说：<b>多搞一些低价产品，价格比其他网站略低就好了</b></div><div class="gg_info" style="margin: 0;color:blue">用户157***694说：<b>下单要快</b></div><div class="gg_info" style="margin: 0;color:blue">用户321***803说：<b>多做一些宣传</b></div><div class="gg_info" style="margin: 0;color:blue">用户213***668说：<b>666</b></div><div class="gg_info" style="margin: 0;color:blue">用户321***995说：<b>QQ云商城是全国最大的云商城平台,主打QQ钻业务,**刷粉丝软件,空间业务,进货价格便宜</b></div><div class="gg_info" style="margin: 0;color:blue">用户210***769说：<b>非常好，以后要来就来这里</b></div><div class="gg_info" style="margin: 0;color:blue">用户325***178说：<b>QQ云商城，<?php echo $_SERVER['HTTP_HOST']; ?>，24小时自助下单平台</b></div><div class="gg_info" style="margin: 0;color:blue">用户325***178说：<b>刷更多的赞</b></div><div class="gg_info" style="margin: 0;color:blue">用户168***382说：<b>666</b></div><div class="gg_info" style="margin: 0;color:blue">用户353***251说：<b>很有诚信，刷的很快，推荐这个平台</b></div><div class="gg_info" style="margin: 0;color:blue">用户852***349说：<b>要是能有宣传功能的话，这个平台肯定更受欢迎</b></div><div class="gg_info" style="margin: 0;color:blue">用户183***110说：<b>ui好一点，背景好一点，加油吧</b></div><div class="gg_info" style="margin: 0;color:blue">用户218***577说：<b>建议可以去发广告，宣传自己的网址哦，贴吧里还是有很多人玩的</b></div><div class="gg_info" style="margin: 0;color:blue">用户157***694说：<b>下单速度要快</b></div><div class="gg_info" style="margin: 0;color:blue">用户316***484说：<b>希望继续努力，还不错</b></div><div class="gg_info" style="margin: 0;color:blue">用户343***331说：<b>免费的再多点</b></div><div class="gg_info" style="margin: 0;color:blue">用户340***317说：<b>速度快</b></div><div class="gg_info" style="margin: 0;color:blue">用户356***078说：<b>24小时自助下单平台<?php echo $_SERVER['HTTP_HOST']; ?>，QQ云商城，<?php echo $conf['sitename'] ?></b></div><div class="gg_info" style="margin: 0;color:blue">用户155***605说：<b>很好啊</b></div><div class="gg_info" style="margin: 0;color:blue">用户954***293说：<b>云商城真是不错，一直用的这个网站，QQ云商城，<?php echo $_SERVER['HTTP_HOST']; ?>，云商城，搭建云商城</b></div><div class="gg_info" style="margin: 0;color:blue">用户314***137说：<b>希望多出来刷东西的</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户532***563说：<b>平台很棒啊，支持<?php echo $conf['sitename'] ?>，自助下单平台</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户532***563说：<b>24小时自助下单QQ**,**,**粉丝,**作品双击喜欢,**作品播放量,全民K歌粉丝 </b></div>

<div class="gg_info" style="margin: 0;color:blue">用户280***172说：<b></b></div><div class="gg_info" style="margin: 0;color:blue">用户111***684说：<b>价格再合理一点</b></div><div class="gg_info" style="margin: 0;color:blue">用户203***337说：<b>这个平台特别棒，，，，真的特别特别棒</b></div><div class="gg_info" style="margin: 0;color:blue">用户292***724说：<b>非常不错</b></div><div class="gg_info" style="margin: 0;color:blue">用户217***359说：<b>多做这中活动</b></div><div class="gg_info" style="margin: 0;color:blue">用户136***498说：<b>希望能迅速赞</b></div><div class="gg_info" style="margin: 0;color:blue">用户212***285说：<b>hao</b></div><div class="gg_info" style="margin: 0;color:blue">用户318***115说：<b>应该尽量的保证质量，并设置分享有奖，这样，也吸引了客户</b></div><div class="gg_info" style="margin: 0;color:blue">用户159***280说：<b>每天送100**</b></div><div class="gg_info" style="margin: 0;color:blue">用户265***010说：<b>类似于QQ空间里说说的赞，能不能设置指定数量？希望采纳</b></div><div class="gg_info" style="margin: 0;color:blue">用户486***443说：<b>更好</b></div><div class="gg_info" style="margin: 0;color:blue">用户280***172说：<b>非常好</b></div><div class="gg_info" style="margin: 0;color:blue">用户282***989说：<b>好</b></div><div class="gg_info" style="margin: 0;color:blue">用户142***439说：<b>不错不错。太好了</b></div><div class="gg_info" style="margin: 0;color:blue">用户932***936说：<b>能按着数量刷就好了</b></div><div class="gg_info" style="margin: 0;color:blue">用户340***683说：<b>很好用很便宜!</b></div><div class="gg_info" style="margin: 0;color:blue">用户177***283说：<b>很不错的网站，加油</b></div><div class="gg_info" style="margin: 0;color:blue">用户212***285说：<b>好</b></div><div class="gg_info" style="margin: 0;color:blue">用户624***185说：<b>这个平台很不错，信誉很好</b></div><div class="gg_info" style="margin: 0;color:blue">用户316***940说：<b>应该把全民k歌刷花间单的</b></div><div class="gg_info" style="margin: 0;color:blue">用户148***683说：<b>希望可以多点福利，东西很便宜，很好。</b></div><div class="gg_info" style="margin: 0;color:blue">用户132***627说：<b>挺好的</b></div><div class="gg_info" style="margin: 0;color:blue">用户154***984说：<b>我帮你们的网站分享给了很多群，永远支持你们</b></div><div class="gg_info" style="margin: 0;color:blue">用户238***434说：<b>**</b></div><div class="gg_info" style="margin: 0;color:blue">用户239***848说：<b>炫酷点  再加点音乐就好了</b></div><div class="gg_info" style="margin: 0;color:blue">用户245***114说：<b>多搞一些低价产品，价格比其他网站略低就好了</b></div><div class="gg_info" style="margin: 0;color:blue">用户157***694说：<b>下单要快</b></div><div class="gg_info" style="margin: 0;color:blue">用户321***803说：<b>多做一些宣传</b></div><div class="gg_info" style="margin: 0;color:blue">用户213***668说：<b>666</b></div><div class="gg_info" style="margin: 0;color:blue">用户321***995说：<b>好了</b></div>
<div class="gg_info" style="margin: 0;color:blue">用户532***563说：<b>24小时自助下单平台,免登陆的虚拟业务在线自动处理平台,专业为QQ空间,全民K歌,**GIF,新浪微博,火山视频等业务提供服务,最大的空间业务平台 <?php echo $_SERVER['HTTP_HOST']; ?></b></div>
<div class="gg_info" style="margin: 0;color:blue">用户210***769说：<b>非常好，以后要来就来这里</b></div><div class="gg_info" style="margin: 0;color:blue">用户325***178说：<b>更好宣传</b></div><div class="gg_info" style="margin: 0;color:blue">用户325***178说：<b>刷更多的赞</b></div><div class="gg_info" style="margin: 0;color:blue">用户168***382说：<b>666</b></div><div class="gg_info" style="margin: 0;color:blue">用户353***251说：<b>很有诚信，刷的很快，推荐这个平台</b></div><div class="gg_info" style="margin: 0;color:blue">用户852***349说：<b>要是能有宣传功能的话，这个平台肯定更受欢迎</b></div><div class="gg_info" style="margin: 0;color:blue">用户183***110说：<b>ui好一点，背景好一点，加油吧</b></div><div class="gg_info" style="margin: 0;color:blue">用户218***577说：<b>建议可以去发广告，宣传自己的网址哦，贴吧里还是有很多人玩的</b></div><div class="gg_info" style="margin: 0;color:blue">用户157***694说：<b>下单速度要快</b></div><div class="gg_info" style="margin: 0;color:blue">用户316***484说：<b>希望继续努力，还不错</b></div><div class="gg_info" style="margin: 0;color:blue">用户343***331说：<b>免费的再多点</b></div><div class="gg_info" style="margin: 0;color:blue">用户340***317说：<b>速度快</b></div><div class="gg_info" style="margin: 0;color:blue">用户356***078说：<b>刷豪华黄钻</b></div><div class="gg_info" style="margin: 0;color:blue">用户155***605说：<b>很好啊</b></div><div class="gg_info" style="margin: 0;color:blue">用户954***293说：<b>好好努力</b></div><div class="gg_info" style="margin: 0;color:blue">用户314***137说：<b>希望多出来刷东西的</b></div>
</marquee>
</div>

<div class="block block-themed" <?php if ($conf['hide_tongji'] == 1) {?>style="display:none;"<?php }?>>
    <div class="block-header bg-success">
        <h3 class="block-title"><i class="fa fa-bar-chart-o"></i>&nbsp;&nbsp;数据统计</h3>
    </div>
<table class="table table-bordered">
<tbody>
<tr>
<td align="center">
<font size="2"><span id="count_yxts"></span>天<br><font color="#65b1c9"><i class="fa fa-shield fa-2x"></i></font><br>安全运营</font></td>
<td align="center"><font size="2"><span id="count_money"></span>元<br><font color="#65b1c9"><i class="fa fa-shopping-cart fa-2x"></i></font><br>交易总数</font></td>
<td align="center"><font size="2"><span id="count_orders"></span>笔<br><font color="#65b1c9"><i class="fa fa-check-square-o fa-2x"></i></font><br>订单总数</font></td>
</tr>
<tr>
<td align="center"><font size="2"><span id="count_site"></span>个<br><font color="#65b1c9"><i class="fa fa-sitemap fa-2x"></i></font><br>代理分站</font></td>
<td align="center"><font size="2"><span id="count_money1"></span>元<br><font color="#65b1c9"><i class="fa fa-pie-chart fa-2x"></i></font><br>今日交易</font></td>
<td align="center"><font size="2"><span id="count_orders2"></span>笔<br><font color="#65b1c9"><i class="fa fa-check-square fa-2x"></i></font><br>今日订单</font></td>
</tr>
</tbody>
</table>
</div>

<?php hook('bottom');?>

    <!--底部导航-->
    <div class="block">
            <div class="block-content text-center"><p><span style="font-weight:bold"><?php echo $conf['sitename'] ?> <i class="fa fa-heart text-danger"></i> 2019 | </span><a class="" href="#customerservice" style="font-weight:bold" data-toggle="modal">客服与帮助</span></a></p>
            </div>
    </div>
    <!--底部导航-->

        <!-- 全局底部代码 -->
<?php echo $conf['index_html_bottom'] ?>
    <!-- 全局底部代码 -->


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
<script src="<?php echo $cdnserver ?>assets/appui/js/app.js"></script>
<script type="text/javascript">
var isModal =<?php echo empty($conf['modal']) ? 'false' : 'true'; ?>;
var _modalType=<?php echo $conf['modal_type'] > 0 ? '1' : '0'; ?>;
var homepage = true;
var hashsalt =<?php echo $addsalt_js ?>;
</script>
<script src="<?php echo $cdnserver ?>assets/js/main.js?ver=<?php echo $jsver ?>"></script>
<?php hook('footer_after');?>
</body>
</html>