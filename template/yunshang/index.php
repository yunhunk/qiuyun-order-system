

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
  <title><?php echo $hometitle ?></title>
<link rel="shortcut icon" href="/favicon.ico">
<link rel="bookmark" href="/favicon.ico">
  <meta name="keywords" content="<?php echo $conf['keywords'] ?>">
  <meta name="description" content="<?php echo $conf['description'] ?>">
  <link href="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/plugins.css">
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/main.css">
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/oneui.css">
  <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/css/common.css?ver=<?php echo VERSION ?>">
  <script src="<?php echo $cdnpublic ?>modernizr/2.8.3/modernizr.min.js"></script>
  <!--[if lt IE 9]>
    <script src="<?php echo $cdnpublic ?>html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="<?php echo $cdnpublic ?>respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
<style>
.shuaibi-tip {
    background: #fafafa repeating-linear-gradient(-45deg,#fff,#fff 1.125rem,transparent 1.125rem,transparent 2.25rem);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
    margin: 20px 0px;
    padding: 15px;
    border-radius: 5px;
    font-size: 14px;
    color: #555555;
}
</style>
<?php echo $background_css ?>
</head>
<body>
<?php if ($background_image) {?>
<img src="<?php echo $background_image; ?>" alt="Full Background" class="full-bg full-bg-bottom animated pulse " ondragstart="return false;" oncontextmenu="return false;">
<?php }?>
<br>
<?php include TEMPLATE_ROOT . 'default/head.inc.php';?>
<div class="col-xs-12 col-sm-10 col-md-8 col-lg-5 center-block" style="float: none;">

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
    <div class="widget-content themed-background-flat text-center" style="background-image:url(assets/simple/img/head4.png);background-size: 100% 100%;">
        <a href="javascript:void(0)">
			<img src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq'] ?>&spec=100" alt="Avatar" width="80" style="height: auto filter: alpha(Opacity=80);-moz-opacity: 0.80;opacity: 0.80;" class="img-circle img-thumbnail img-thumbnail-avatar-1x animated zoomInDown">
        </a>
    </div>
<!--logo-->
   <center>
<h3>     <a href="javascript:void(alert('<?php echo $conf['sitename'] ?>，建议收藏到浏览器书签哦！'));"><b><?php echo $conf['sitename'] ?></b></a></h3>
</center>

<!--logo下面按钮-->
				    <center>
   <h5><div color="wrap"><img src="">全网低价货源-信誉保证-欢迎对接<img src="">
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

</h5></font></center>




	<div class="widget-content text-center">
		<div class="text-center text-muted">
			<div class="btn-group btn-group-justified">
				<div class="btn-group">


					<a class="btn btn-default" data-toggle="modal" href="#anounce"><i class="fa fa-bullhorn"></i>&nbsp;<span style="font-weight:bold">平台公告</span></a>
					</div>
				<?php if ($conf['appurl']) {?>
			<a href="<?php echo $conf['appurl']; ?>" target="_blank" class="btn btn-effect-ripple btn-default"><i class="fa fa-android"></i> <span style="font-weight:bold">客户端</span></a>
			<?php } else {?>

			<!--<a href="#lxkf" target="_blank" data-toggle="modal" class="btn btn-default"><i class="fa fa-qq"></i>&nbsp;<span style="font-weight:bold">客服</span></a>。-->



	 	<a href="./sup" target="_blank" data-toggle="modal" class="btn btn-default"><i class="fa fa-briefcase"></i>&nbsp;<span style="font-weight:bold">我要供货</span></a>
			<?php }?>
                <div class="btn-group">
                 <a class="btn btn-default" data-toggle="modal" href="user/login.php"><i class="fa fa-users fa-1x"></i>&nbsp;站长登录</a>
				</div>
             </div>
		</div>



	<div class="btn-group">
                <a class="btn btn-default" href="<?php echo !empty($siterow['kfwx']) ? $siterow['kfwx'] : $DB->getColumn("SELECT v FROM pre_config WHERE k=:key LIMIT 1", [':key' => 'kfwx']) ?>" target="_blank">&nbsp;&nbsp;&nbsp;<i class="fa fa-comment text-dark">
                </i>&nbsp;&nbsp;专业客服团队-24小时在线解决售后问题&nbsp;&nbsp;&nbsp;</a>
			 </div>



<p id="contentA" style="display: none;"><?php echo $conf['Link'] ?></p>
    <div id="contentB" class="main" style="width: 100%;margin-top:10px;">
      <span><b><a href="<?php echo $conf['Link'] ?>" target="_blank" style="color:#2F4F4F;"><em class="fa fa-fw fa-volume-up"></em>Link导航站-永不失效</a></b></span>
    </div>

    <script>
        function hideContentBBasedOnA() {
            var contentA = document.getElementById('contentA');
            var textA = contentA.textContent || contentA.innerText;
            var contentB = document.getElementById('contentB');
            if (textA === 'Loading...') {
                contentB.style.display = 'none';
            } else if (textA === '') {
                contentB.style.display = 'none';
            } else   {
                contentB.style.display = 'block';
            }
        }
        document.addEventListener('DOMContentLoaded', hideContentBBasedOnA);
    </script>




	</div>

<center>

         	</div>
<div class="block full2">
<!--TAB标签开始-->


	<div class="block-title">
        <ul class="nav nav-tabs" data-toggle="tabs">
		<li class="active" style="width: 25%;" align="center">
							<a href="#shop" data-toggle="tab">
								<img border="0" width="22" src="assets/beautify/img/gg-re.jpg">
								下单
							</a>
						</li>
            <li style="width: 25%; font-size: 13px;" align="center"><a href="#search" data-toggle="tab" id="tab-query"><span style="font-weight:bold"><i class="fa fa-search"></i> 查询</span></a></li>
	<li style="width: 25%;" align="center"><a href="#Substation"><font color="#FF4000"><i class="fa fa-location-arrow fa-spin"></i> <b>分站</b></font></a></li>
			<?php if ($conf['gift_open'] == 1 && $conf['fenzhan_buy'] == 0) {?><li style="width: 25%; font-size: 13px;" align="center"><a href="#gift" data-toggle="tab"><span style="font-weight:bold"><i class="fa fa-gift fa-fw"></i> 抽奖</span></a></li><?php }?>
			<li style="width: 25%; font-size: 13px;" align="center"><a href="#more" data-toggle="tab"><span style="font-weight:bold"><i class="fa fa-folder-open"></i> 更多</span></a></li>
        </ul>
    </div>
<!--TAB标签结束-->

    <div class="tab-content">
<!--在线下单-->
<div class="tab-pane active" id="shop">

<!--安卓教程-->
   <div class="modal fade DkkajcmdJZRB" align="left" id="jc1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
          <div class="modal-dialog DkkajcmdJZRB">
            <div class="modal-content DkkajcmdJZRB">
              <div class="modal-header DkkajcmdJZRB">
                <button type="button" class="close" data-dismiss="modal">
                  <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  <span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">安卓常见问题汇总</h4></div>
              <div class="modal-body DkkajcmdJZRB">
                <div class="tab-pane fade in DkkajcmdJZRB" id="faq">
                  <div class="panel-group DkkajcmdJZRB" id="accordion">
                    <div class="panel panel-info DkkajcmdJZRB">
                      <a class="cm-kfqy-warning collapsed" data-toggle="collapse" data-parent="#accordion" href="#a1">
                      <b>安卓辅助科技教程：点击查看</b>
                      </a>
                      <div id="a1" class="panel-collapse collapse DkkajcmdJZRB" style="height: 0px;">
                        <div class="panel-body DkkajcmdJZRB">
      <font style="font-weight: bold; color: #E53333;">一体直装使用教程：</font><br>
      1.卸载正版游戏，下载网盘里面修改过的游戏安装包，安装好以后先不要启动修改过的游戏。<br><br>
2.先全给权限(浮窗和储存权限是必须要给的)然后再启动修改过的游戏，输入卡密即可。<br><br>
      <font style="font-weight: bold; color: #E53333;">容器直装使用教程：</font><br>
      1.卸载正版游戏，下载网盘里面修改过的游戏安装包，安装好以后先不要启动修改过的游戏。<br><br>
2.把启动器的权限还有修改过的和平精英的权限全部允许了(浮窗和储存权限是必须要给的)<br><br>
3.然后打开启动器，输入卡密，输入卡密以后自动启动修改过的游戏。<br><br>
      <font style="font-weight: bold; color: #E53333;">启动器直装使用教程：</font><br>
      1.不用卸载正版游戏，就使用官方正版游戏即可。<br><br>
2.下载好网盘里的容器安装包，安装好后把容器的权限全部打开(浮窗和储存权限是必须要给的)<br><br>
3.然后打开容器，输入激活码，再把你要玩的游戏放入容器当中，然后在容器中打开游戏。<br><br>
      <font style="font-weight: bold; color: #E53333;">安卓Root和内核使用教程：</font><br>
      1.玩Root和内核的大多都是老手，无需过多介，该给的权限给好，内核该刷就刷。<br><br>
2.如果不懂如何Root的，可以去淘宝找人给你刷（20~30块钱），刷好了 就可以使用Root和内核辅助了。<br><br>
3.Root和内核辅助相比直装稳定程度会更好，功能也会更牛逼，主要是稳定。<br><br>
4.在每个辅助外挂的下载网盘里都有对应的教程，个别多多少少有差异，看你购买的对应的教程即可。<br>
    </div>
  </div>
</div>
                    <div class="panel panel-warning DkkajcmdJZRB">
                      <a class="cm-kfqy collapsed" data-toggle="collapse" data-parent="#accordion" href="#a2">
                        <b>非正版应用/微信签名不一致：点击查看</b>
                      </a>
                      <div id="a2" class="panel-collapse collapse DkkajcmdJZRB" style="height: 0px;">
                        <div class="panel-body DkkajcmdJZRB">
                        1：QQ登录提示非正版应用：使用一键上号的上号器或卸载QQ扫码上号<br><br>
                        2：WX登录提示签名不一致：扫码登录即可或扫码VX小号一键上号<br></div></div></div>
                    <div class="panel panel-warning DkkajcmdJZRB">
                      <a class="cm-kfqy" data-toggle="collapse" data-parent="#accordion" href="#a3">
                        <b>软件报毒安装不上怎么办：点击查看</b>
                      </a>
                      <div id="a3" class="panel-collapse collapse DkkajcmdJZRB" style="height: 0px;">
                        <div class="panel-body DkkajcmdJZRB">
                        1.先把手机应用宝卸载了(没有请无视)，然后去设置里面把＇禁止安装恶意软件＇关闭，然后断网安装。<br><br>
                        2.不会的话请百度搜索教程。删除其他辅助软件卸载可能包名重复了导致无法安装。需要把正版游戏卸载了再进行安装<br>
</div></div></div>
                    <div class="panel panel-warning DkkajcmdJZRB">
                      <a class="cm-kfqy" data-toggle="collapse" data-parent="#accordion" href="#a4">
                        <b>机器码错误/请在原设备登录：点击查看</b>
                      </a>
                      <div id="a4" class="panel-collapse collapse DkkajcmdJZRB" style="height: 0px;">
                        <div class="panel-body DkkajcmdJZRB">
                        1.出现这些情况自己更换了登录设备的设备码导致，手机刷机和更换虚拟机更换框架都会导致设备码不一样<br><br>
                        2.桌面和虚拟机的设备码也不一样最常见的就是桌面登录了卡密，然后去虚拟机里面登录，或者更换虚拟机使用，更换框架使用<br><br>
                        3.有一些直装软件卸载重装也会设备码错误<br><br>
                        4.卡密绑定设备只会绑定第一次登入的机型，这种问题先看看软件有没有自带解绑按钮或解绑链接，如果没有带上订单号问客服能不能解绑，如果不能解绑请自行承担<br></div></div></div>
                    <div class="panel panel-warning DkkajcmdJZRB">
                      <a class="cm-kfqy" data-toggle="collapse" data-parent="#accordion" href="#a5">
                        <b>国际服提示一串英文代码：点击查看</b>
                      </a>
                      <div id="a5" class="panel-collapse collapse DkkajcmdJZRB" style="height: 0px;">
                        <div class="panel-body DkkajcmdJZRB">
                        1.先打开游戏，中间出现英文的时候，再退出<br><br>
                        2.使用MT管理器找到下载的obb浏览器下载选择用MT管理器打开，然后随便找个文件夹往里面放一下，找到android进去找到obb然后再找com.tencent.ig，找到obb直接选择移动进去然后重启游戏<br><br>
                        3.如果还是出现那玩意的话大退一下，如果还是不行，那就是obb下载错了！
<br></div></div></div>
                    <div class="panel panel-warning DkkajcmdJZRB">
                      <a class="cm-kfqy" data-toggle="collapse" data-parent="#accordion" href="#ac5">
                        <b>闪退问题解决办法：点击查看</b>
                      </a>
                      <div id="ac5" class="panel-collapse collapse DkkajcmdJZRB" style="height: 0px;">
                        <div class="panel-body DkkajcmdJZRB">
1.权限都给，不要禁止权限，悬浮窗口别禁止<br><br>
2.手机有任何自带加速游戏的都别开<br><br>
3.重装一次试试，别覆盖安装，卸载干净重启再次安装尝试；如任何方法都尝试，依旧不行就是不支持你的机型！<br></div></div></div>
                    <div class="panel panel-warning DkkajcmdJZRB">
                      <a class="cm-kfqy" data-toggle="collapse" data-parent="#accordion" href="#ac6">
                        <b>输入卡密提示服务器连接超时：点击查看</b>
                      </a>
                      <div id="ac6" class="panel-collapse collapse DkkajcmdJZRB" style="height: 0px;">
                        <div class="panel-body DkkajcmdJZRB">
1.软件开发商关闭服务器在更新或维护导致暂时无法登入！<br><br>
2.软件服务器被打导致短时间无法登录，耐心等待恢复！<br></div></div></div>
<div class="panel panel-warning DkkajcmdJZRB">
                      <a class="cm-kfqy" data-toggle="collapse" data-parent="#accordion" href="#ac7">
                        <b>关于sh文件怎么打开操作：点击查看</b>
                      </a>
                      <div id="ac7" class="panel-collapse collapse DkkajcmdJZRB" style="height: 0px;">
                        <div class="panel-body DkkajcmdJZRB">
                        1.首先手机需Root权限
2.从网盘打开sh文件➯之后mt找到data目录➯手机设置➯关于手机➯详细参数➯内核版本➯找到对应内核版本的驱动➯之后把挂.sh文件解压到data/长按属性/660/600改成777/之后点击文件执行<br></div></div></div>
                    </div>
          <div class="modal-footer DkkajcmdJZRB">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
      </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--安卓教程-->
      
      <!--苹果教程-->
        <div class="modal fade DkkajcmdJZRB" align="left" id="jc2" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
          <div class="modal-dialog DkkajcmdJZRB">
            <div class="modal-content DkkajcmdJZRB">
              <div class="modal-header DkkajcmdJZRB">
                <button type="button" class="close" data-dismiss="modal">
                  <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  <span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">苹果常见问题汇总</h4></div>
              <div class="modal-body DkkajcmdJZRB">
                <div class="tab-pane fade in DkkajcmdJZRB" id="faq">
                  <div class="panel-group DkkajcmdJZRB" id="accordion1">
                    <div class="panel panel-info DkkajcmdJZRB">
                      <a class="cm-kfqy-warning collapsed" data-toggle="collapse" data-parent="#accordion1" href="#a6">
                      <b>苹果腐竹介绍：点击查看</b>
                      </a>
                      <div id="a6" class="panel-collapse collapse DkkajcmdJZRB" style="height: 0px;">
                        <div class="panel-body DkkajcmdJZRB"><font color="#E53333"><span style="font-weight:bold">苹果辅助分为以下几种：</span></font><br><br>
<font color="#E53333"><span style="font-weight:bold">1.越狱腐竹：</span></font>使用正版游戏即可)<br><br>
<font color="#E53333"><span style="font-weight:bold">2.免签腐竹：</span></font>需删除正版游戏，直接下载修改后的游戏，登入即可)<br><br>
<font color="#E53333"><span style="font-weight:bold">3.自签腐竹：</span></font>如果免签无法安装则自备签名.寻找淘宝购买全能签或轻松签-拥有签名之后导入腐竹的ipa安装包进行签名校验安装至手机上即可<br>
<font color="#E53333"><span style="font-weight:bold">3.巨魔注入：</span></font>巨魔商店乃是IOS新型安装方式则更加稳定淘宝有售卖！<br></div></div></div>
                    <div class="panel panel-warning DkkajcmdJZRB">
                      <a class="cm-kfqy collapsed" data-toggle="collapse" data-parent="#accordion1" href="#a7">
                        <b>越狱使用教程：点击查看</b>
                      </a>
                      <div id="a7" class="panel-collapse collapse DkkajcmdJZRB" style="height: 0px;">
                        <div class="panel-body DkkajcmdJZRB">
①：用到越狱的肯定是老手了就不多做介绍，在你买的腐竹网盘里都有对应的教程，看教程安装即可。<br></div></div></div>
                    <div class="panel panel-warning DkkajcmdJZRB">
                      <a class="cm-kfqy" data-toggle="collapse" data-parent="#accordion1" href="#a8">
                        <b>免签使用教程：点击查看</b>
                      </a>
                      <div id="a8" class="panel-collapse collapse DkkajcmdJZRB" style="height: 0px;">
                        <div class="panel-body DkkajcmdJZRB">
1.卸载正版游戏，去复制订单内的下载链接，到safari浏览器打开(即iOS自带浏览器)<br><br>
2.下载地址粘贴打开后直接点击一键安装即可，然后打开游戏输入激活码激活即可。<br><br>
</div></div></div>
                    <div class="panel panel-warning DkkajcmdJZRB">
                      <a class="cm-kfqy" data-toggle="collapse" data-parent="#accordion1" href="#a9">
                        <b>自签使用教程：点击查看</b>
                      </a>
                      <div id="a9" class="panel-collapse collapse DkkajcmdJZRB" style="height: 0px;">
                        <div class="panel-body DkkajcmdJZRB">
①：如果免签则无法成功安装。此时需要自备【全能签】<br><br>
②：如果免签无法安装则必须使用全能签进行导入安装.淘宝有售卖.买不买随笔<br><br>
③：购买全能签或轻松签-拥有签名之后导入腐竹的ipa安装包进行签名校验安装至手机上即可。<br><br>
</div></div></div>
                   <div class="panel panel-warning DkkajcmdJZRB">
                      <a class="cm-kfqy" data-toggle="collapse" data-parent="#accordion1" href="#ac9">
                        <b>免越狱问题以及安装失败解决办法：点击查看</b>
                      </a>
                      <div id="ac9" class="panel-collapse collapse DkkajcmdJZRB" style="height: 0px;">
                        <div class="panel-body DkkajcmdJZRB">
1.打开网址点击下载如免签名无法下载提示验证不完整无法打开App 则需要自签，自签怎么签（全能签）淘宝有卖买不买随你<br><br>
2.首先要留有足够空间（20G以上）--卸载原先的游戏/删除黑图标--清理浏览器数据--还原所有设置--重新安装，如果还不行就切换一下网络重新试一遍，所有问题基本解决！还不行的就点一下签名旁边的“重置”，重新打开安装一遍。有时候提示卡密不存在并非是卡密错误，切换一下网络登录几乎可以解决百分之八十，极个别登录不了提示卡密不存在说明服务器正在被打等等他恢复在登陆就好了！<br><br>
3.iOS进游戏有菜单不显示功能大退重新进入即可，还不行就关机重启<br><br>
4.iOS下载好不能安装/无法验证完整性：内存不够或者是他桌面上游戏没删除预留30g以上内存，重新点重置安装再下载一次即可<br><br>
5.输入卡密几秒后闪退：手机设置北京时间24小时制或检查是否下载错软件，如还未解决请携带订单号联系客服处理</div></div></div>
                   
                    </div>
                    
          <div class="modal-footer DkkajcmdJZRB">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
      </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--苹果教程-->
      <!--端游教程-->
        <div class="modal fade DkkajcmdJZRB" align="left" id="jc3" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
          <div class="modal-dialog DkkajcmdJZRB">
            <div class="modal-content DkkajcmdJZRB">
              <div class="modal-header DkkajcmdJZRB">
                <button type="button" class="close" data-dismiss="modal">
                  <span aria-hidden="true"><i class="fa fa-times-circle"></i></span>
                  <span class="sr-only">Close</span></button>
                <h4 class="modal-title" id="myModalLabel">其它常见问题汇总</h4></div>
              <div class="modal-body DkkajcmdJZRB">
                <div class="tab-pane fade in DkkajcmdJZRB" id="faq">
                  <div class="panel-group DkkajcmdJZRB" id="accordion2">
                    <div class="panel panel-info DkkajcmdJZRB">
                      <a class="cm-kfqy-warning collapsed" data-toggle="collapse" data-parent="#accordion2" href="#a10">
                      <b>端游及模拟器问题使用教程：点击查看</b>
                      </a>
                      <div id="a10" class="panel-collapse collapse DkkajcmdJZRB" style="height: 0px;">
                        <div class="panel-body DkkajcmdJZRB">
                        所有模拟器辅助解决一套流程先看使用的辅助使用说明跟问题解决办法去网盘下载（所有问题解决都在这里）解压包，看看自己软件支不支持自己的系统跟模拟器<br><br>
                        1.辅助软件保护➯2：管理员运行腐竹➯3：模拟器是否需要升级➯4：DX修复➯5：四件套安装➯6.网吧的用网吧解除禁止跟干掉网吧防火墙➯7.Win7的要用黑屏处理➯8.自己家里Win10一定要关闭防火墙，杀毒软件跟自带的实时保护【一定要永久关闭】，如果不会关闭的话去下载一个火绒安全杀毒软件，那么Win10自带的就会关闭了，然后启动电脑的时候别开火绒安全！<br><br>
                        问题解决工具包：https://wwd.lanzouv.com/b0200hhli<br><br>
                        修复工具合集点我查看：https://www.lanzoui.com/b0159y3zi <br></div></div></div>
                    <div class="panel panel-warning DkkajcmdJZRB">
                      <a class="cm-kfqy collapsed" data-toggle="collapse" data-parent="#accordion2" href="#a11">
                        <b>虚拟商品没收到卡密：点击查看</b>
                      </a>
                      <div id="a11" class="panel-collapse collapse DkkajcmdJZRB" style="height: 0px;">
                        <div class="panel-body DkkajcmdJZRB">1.下单必须填写正确的(手机号,QQ号,取卡密码,邮箱号)其中任意一种。
                          <br><br>2.预留的信息用于接收卡密以及查询订单。
                          <br><br>3.购买完请进入查单页面查询（点击详情）
                          <br><br>4.点击详情后会弹出卡密信息。<br></div></div></div>
                    <div class="panel panel-warning DkkajcmdJZRB">
                      <a class="cm-kfqy" data-toggle="collapse" data-parent="#accordion2" href="#a12">
                        <b>为什么下单了没有处理呢：点击查看</b>
                      </a>
                      <div id="a12" class="panel-collapse collapse DkkajcmdJZRB" style="height: 0px;">
                        <div class="panel-body DkkajcmdJZRB">1.由于本站80%以上的业务订单采用软件全自动处理，下单自动记录订单并排队处理，若订单超过6小时仍然显示待处理请联系客服！（注：是6小时显示未处理联系客服，不是6小时未到账联系客服！）<br><br>2.如果你购买的是钻类商品，一般说明上会写到账时间，点播和爆卡的比较慢，大概2~5天都很正常，官方的较快，当天到账！<br><br>3.如果你购买的是其他手工商品，说明会写操作说明或到账时间，请参照说明并如实操作，才能尽快给你完成订单。<br><br>
                        4.如果超过7天以上，请咨询客服是否出现维护等情况，可根据情况退单处理。<br></div></div>
                    </div>
                    <div class="panel panel-warning DkkajcmdJZRB">
                      <a class="cm-kfqy" data-toggle="collapse" data-parent="#accordion2" href="#a13">
                        <b>代刷业务很久没开刷：点击查看</b>
                      </a>
                      <div id="a13" class="panel-collapse collapse DkkajcmdJZRB" style="height: 0px;">
                        <div class="panel-body DkkajcmdJZRB">1.下单前先确认输的信息是否正确！<br><br>
                          2.请检查作品是否违规或者出现审核等无法正常观看的情况。<br><br>
                        3.请检查下单时的作品链接是否正确，超过24小时未开始联系客服处理。<br></div></div>
                    </div>
                    <div class="panel panel-warning DkkajcmdJZRB">
                      <a class="cm-kfqy" data-toggle="collapse" data-parent="#accordion2" href="#a14">
                        <b>QQ空间业务很久没开刷：点击查看</b>
                      </a>
                      <div id="a14" class="panel-collapse collapse DkkajcmdJZRB" style="height: 0px;">
                        <div class="panel-body DkkajcmdJZRB">1.空间权限设为所有人可见。
                          <br><br>2.空间被单封请勿下单（这种是QQ好友才能进去，而且好友是看不出来异常的，当陌生人进去就提示空间维护）<br>
                          <br>3.空间最好有2、3条说说。<br></div></div>
                    </div>
                    <div class="panel panel-warning DkkajcmdJZRB">
                      <a class="cm-kfqy" data-toggle="collapse" data-parent="#accordion2" href="#a15">
                        <b>全名k歌这些业务很久没开刷：点击查看</b>
                      </a>
                      <div id="a15" class="panel-collapse collapse DkkajcmdJZRB" style="height: 0px;">
                        <div class="panel-body DkkajcmdJZRB">1.下单前先确认输的信息是否正确！<br>
                          <br>2.请检查作品是否违规或者出现审核等无法正常观看的情况。<br><br>
                        3.请检查下单时的作品链接是否正确，超过24小时未开始联系客服处理。<br></div></div>
                    </div>
                   </div>
          <div class="modal-footer DkkajcmdJZRB">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
      </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!--端游教程-->
<div style="display: flex; justify-content: center; align-items: center; flex-wrap: nowrap;" class="DkkajcmdJZRB">
        <div hclass="panel-body border-t" href="#jc1" data-toggle="modal" style="width: 33.33%; text-align: center; padding: 10px; cursor: pointer; border: 0.5px solid #ddd; font-weight: bold; font-size: 14px; box-sizing: border-box;" class="DkkajcmdJZRB">
            <i class="fa fa-android" aria-hidden="true" style="color:#1bc754;"></i>&nbsp;<span style="color:#1bc754;">安卓教程</span>
        </div>
        <div class="panel-body border-t DkkajcmdJZRB" href="#jc2" data-toggle="modal" style="width: 33.33%; text-align: center; padding: 10px; cursor: pointer; border: 0.5px solid #ddd; font-weight: bold; font-size: 14px; box-sizing: border-box;">
            <i class="fa fa-apple" aria-hidden="true" style="color:#1bc754;"></i>&nbsp;<span style="color:#1bc754;">苹果教程</span>
        </div>
        <div class="panel-body border-t DkkajcmdJZRB" href="#jc3" data-toggle="modal" style="width: 33.33%; text-align: center; padding: 10px; cursor: pointer; border: 0.5px solid #ddd; font-weight: bold; font-size: 14px; box-sizing: border-box;">
            <i class="fa fa-desktop" aria-hidden="true" style="color:#1bc754;"></i>&nbsp;<span style="color:#1bc754;">端游教程</span>
        </div>
    </div>

    <center><div class="shuaibi-tip animated tada  text-center">
    <center>
    <b><font color="#EEB422">🏅（所有商品全天24小时智能发货）🏅</font></b>
    </center>
    <span style="font-size:10px;"><strong><span>
    <span style="color:#E53333;">稳如泰山</span>&nbsp;✦
    <span style="color:#CD9B1D;">信誉可查</span>&nbsp;✦
    <span style="color:#009900;">稳定运行</span>&nbsp;✦
    <span style="color:#EE33EE;">服务壹流</span>&nbsp;✦
    <span style="color:#F08080;">假卡必赔</span></span></strong></span>
    </div></center> 


<div  style=" z-index:9999; text-decoration:none; font-weight:bold; position: fixed; z-index: 999; Left: -6px; bottom: 256px; display: inline-block; width: 20px; border-top-left-radius: 10px; border-top-Left-radius: 5px; border-bottom-Left-radius: 5px; border-bottom-left-radius: 0px; color: white; font-size: 17px; line-height: 17px; box-shadow: rgb(100 149 237) 0px 0px 5px; word-wrap: break-word; padding: 8px 12px; border: 2px solid white; background: rgb(100 149 237);"><a href="/toollogs.php" target="_blank" style="position: relative;left: -7px;top: 2px; color:#ffffff;">上架通知</a ></div>



<div style=" z-index:9999; text-decoration:none; font-weight:bold; position: fixed; z-index: 999; Left: -6px; bottom: 146px; display: inline-block; width: 20px; border-top-left-radius: 10px; border-top-Left-radius: 5px; border-bottom-Left-radius: 5px; border-bottom-left-radius: 10px; color: white; font-size: 17px; line-height: 17px; box-shadow: rgb(205 92 92) 0px 0px 5px; word-wrap: break-word; padding: 8px 13px; border: 2px solid white; background: rgb(205 92 92);">
    <a href="https://spread.yx209.com/" style="position: relative;left: -7px;top: 2px; color:#ffffff;" target="_blank">商品推荐</a>
    </div>







<!--<center><strong><img border="0" width="30" src=""><span style="background-color:#FFE500;color:#E53333;">平台所有商品全天24小时自动发货</span><img border="0" width="30" src=""></strong></center>-->
<center><span style="font-size:11px;"><strong><span><span style="color:#E53333;">下单流程</span>: &nbsp;</span><span><span style="color:#0000EE;">选择分类</span>≯ &nbsp;</span><span style="color:#009900;">选择商品<span style="color:#E53333;">≯</span></span><span> &nbsp;</span><span style="color:#EE33EE;">填写信息</span><strong>≯ &nbsp;<span style="color:#006600;">下单成功</span></strong><strong><span>&nbsp; ≯ &nbsp;</span><span style="color:#64451D;">查询订单</span></strong></strong></span>
       <p></p>
      </center>


      <!--<center> <a href="./sup" target="_blank" class="btn btn-sm btn-success btn-xs">点击入驻供货</a>  <a href="./user/regsite.php" target="_blank" class="btn btn-sm btn-warning btn-xs">点击开通分站</a> </center>。-->



<?php include TEMPLATE_ROOT . 'default/shop.inc.php';?>


<marquee>
    	<b id="nr">诚信经营,价格最低,货源最全,卡密问题质保可退换,放心下单即可!!!</b>
    </marquee>


	</div>


<!--在线下单-->


<!--查询订单-->
    <div class="tab-pane" id="search">
              <table class="table table-striped table-borderless table-vcenter remove-margin-bottom">
         <tbody>
            <tr class="shuaibi-tip animation-bigEntrance">
                <td class="text-center" style="width: 100px;">
                    <img src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq'] ?>&spec=100" alt="avatar" class="img-circle img-thumbnail img-thumbnail-avatar">
                </td>
                <td>
                    <h4><strong>站长</strong></h4>
					<i class="fa fa-fw fa-qq text-primary"></i> <?php echo $conf['kfqq'] ?><br><i class="fa fa-fw fa-history text-danger"></i>售后订单问题请联系客服
                </td>
                <td class="text-right" style="width: 20%;">
                    <a href="#lxkf" target="_blank" data-toggle="modal" class="btn btn-sm btn-info">联系</a>
                </td>
            </tr>
         </tbody>
        </table>
		<br>
		<div class="col-xs-12 well well-sm animation-pullUp" <?php if (empty($conf['gg_search'])) {?>style="display:none;"<?php }?>>
			<?php echo $conf['gg_search'] ?>
		</div>
			<div class="form-group">
				<div class="input-group">
					<div class="input-group-btn">
						<select class="form-control" id="searchtype" style="padding: 6px 4px;width:90px"><option value="0">下单账号</option><option value="1">订单号</option></select>
					</div>
					<input type="text" name="qq" id="qq3" value="" class="form-control" placeholder="请输入要查询的内容（留空则显示最新订单）" onkeydown="if(event.keyCode==13){submit_query.click()}" required="">
					<span class="input-group-btn"><a tabindex="0" class="btn btn-default" role="button" data-container="body" data-toggle="popover" data-trigger="focus" data-placement="top" title="查询内容是什么？" data-content="请输入您下单时，在第一个输入框内填写的信息。如果您不知道下单账号是什么，可以不填写，直接点击查询，则会根据浏览器缓存查询！"><i class="glyphicon glyphicon-exclamation-sign"></i></a></span>
				</div>
			</div>
			<input type="submit" id="submit_query" class="btn btn-primary btn-block"
							value="立即查询">
			<font color="red">
				查单号:请输入您购买时候填写的QQ号，如果填写的时候忘记QQ号请点击立即查询即可！

									</font>
            <br>
			<div id="result2" class="form-group" style="display:none;">
              <center><small><font color="#ff0000">手机用户可以左右滑动</font></small></center>
				<div class="table-responsive">
					<table class="table table-vcenter table-condensed table-striped">
					<thead><tr><th>下单账号</th><th>商品名称</th><th>数量</th><th class="hidden-xs">购买时间</th><th>状态</th><th>操作</th></tr></thead>
					<tbody id="list">
					</tbody>
					</table>
				</div>
			</div>
   </div>
<!--查询订单-->


<?php if ($conf['fenzhan_buy'] == 1) {?><div class="tab-pane animation-fadeInQuick2" id="Substation">
<table class="table table-borderless table-pricing">
            <tbody>
                <tr class="active">
                    <td class="btn-effect-ripple" style="overflow: hidden; position: relative;width: 100%; height: 8em;display: block;color: white;margin: auto;background-color: lightskyblue;"><span class="btn-ripple animate" style="height: 546px; width: 546px; top: -212.8px; left: 56.4px;"></span>
                       <h3 style="width:100%;font-size: 1.6em;">
 </h3><h3 style="width:100%;font-size: 1.6em;">
                       <i class="fa fa-user-o fa-fw" style="margin-top: 0.7em;"></i><strong>普及版</strong> /<i class="fa fa-user-circle-o fa-fw"></i><strong>专业版</strong>
                       </h3>
                       <span style="width: 100%;text-align: center;margin-top: 0.8em;font-size: 1.1em;display: block;"><?php echo $conf['fenzhan_price'] ?>元 / <?php echo $conf['fenzhan_price2'] ?>元</span></td>
                </tr>
                <tr>
                    <td>一模一样的独立网站</td>
                </tr>
				<tr>
                    <td>站长后台和超低秘价</td>
                </tr>
              	<tr>
                    <td>余额提成满<?php echo $conf['tixian_min']; ?>元提现</td>
                </tr>
                <tr>
                    <td><strong>专业版可以吃下级分站提成</strong></td>
                </tr>
                <tr class="active">
                    <td>
						<a href="#userjs" data-toggle="modal" class="btn btn-effect-ripple  btn-info" style="overflow: hidden; position: relative;"><i class="fa fa-align-justify"></i><span class="btn-ripple animate" style="height: 100px; width: 100px; top: -24.8px; left: 11.05px;"></span> 版本介绍</a>
                        <a href="user/regsite.php" target="_blank" class="btn btn-effect-ripple  btn-danger" style="overflow: hidden; position: relative;"><i class="fa fa-arrow-right"></i> 马上开通</a>
                    </td>
                </tr>
            </tbody>
        </table>

	</div><?php }?>
<!--开通分站结束-->


<!--抽奖-->
    <?php if ($conf['gift_open'] == 1) {?><div class="tab-pane" id="gift">
		<div class="panel-body text-center">
		<div id="roll">点击下方按钮开始抽奖</div>
		<hr>
		<p>
		<a class="btn btn-info" id="start" style="display:block;">开始抽奖</a>
		<a class="btn btn-danger" id="stop" style="display:none;">停止</a>
		</p>
		<div id="result"></div><br>
		<div class="giftlist" style="display:none;"><strong>最近中奖记录</strong><ul id="pst_1"></ul></div>
		</div>
	</div><?php }?>
<!--抽奖-->

 <!--更多按钮开始-->
<div class="tab-pane" id="more">
    <div class="row">
		<?php if ($conf['gift_open'] == 1) {?><div class="col-sm-6">
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
        </div><?php }?>
		<?php if (!empty($conf['appurl']) && $conf['gift_open'] == 0) {?><div class="col-sm-6">
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
        </div><?php }?>
		<?php if (!empty($conf['invite_tid'])) {?><div class="col-sm-6">
            <a  href="./?mod=invite" target="_blank" class="widget">
                <div class="widget-content themed-background-warning text-right clearfix" style="color: #fff;">
                    <div class="widget-icon pull-left">
                        <i class="fa fa-paper-plane-o"></i>
                    </div>
                    <h2 class="widget-heading h3">
                        <strong>免费领赞</strong>
                    </h2>
                    <span>推广本站免费领取名片赞</span>
                </div>
            </a>
        </div><?php }?>
		<?php if (!empty($conf['cutshop_open'])) {?><div class="col-sm-6">
            <a  href="./?mod=cutshop" target="_blank" class="widget">
                 <div class="widget-content themed-background-success text-right clearfix" style="color:#fff;">
                    <div class="widget-icon pull-left">
                        <i class="fa fa-cutlery"></i>
                    </div>
                    <h2 class="widget-heading h4">
                        <strong>砍价商城</strong>
                    </h2>
                    <span>邀请好友帮砍免费拿</span>
                </div>
            </a>
        </div><?php }?>
		<?php if (!empty($conf['groupshop_open'])) {?><div class="col-sm-6">
            <a  href="./?mod=groupshop" target="_blank" class="widget">
                 <div class="widget-content themed-background-danger text-right clearfix" style="color:#fff;">
                    <div class="widget-icon pull-left">
                        <i class="fa fa-tags"></i>
                    </div>
                    <h2 class="widget-heading h4">
                        <strong>团购商城</strong>
                    </h2>
                    <span>邀请好友一起组团，优惠多多</span>
                </div>
            </a>
        </div><?php }?>
		<?php if (!empty($conf['seckill_open'])) {?><div class="col-sm-6">
            <a  href="./?mod=seckill" target="_blank" class="widget">
                 <div class="widget-content themed-background-success text-right clearfix" style="color:#fff;">
                    <div class="widget-icon pull-left">
                        <i class="fa fa-puzzle-piece"></i>
                    </div>
                    <h2 class="widget-heading h4">
                        <strong>秒杀专场</strong>
                    </h2>
                    <span>低价秒杀各种商品</span>
                </div>
            </a>
        </div><?php }?>
		<?php if (!empty($conf['package_open'])) {?><div class="col-sm-6">
            <a  href="./?mod=package" target="_blank" class="widget">
                 <div class="widget-content themed-background-info text-right clearfix" style="color:#fff;">
                    <div class="widget-icon pull-left">
                        <i class="fa fa-archive"></i>
                    </div>
                    <h2 class="widget-heading h4">
                        <strong>套餐优惠</strong>
                    </h2>
                    <span>套餐下单价格更优惠</span>
                </div>
            </a>
        </div><?php }?>
		<?php if (!empty($conf['coupon_open'])) {?><div class="col-sm-6">
            <a  href="./?mod=coupon" target="_blank" class="widget">
                 <div class="widget-content themed-background-warning text-right clearfix" style="color:#fff;">
                    <div class="widget-icon pull-left">
                        <i class="fa fa-credit-card"></i>
                    </div>
                    <h2 class="widget-heading h4">
                        <strong>领取优惠券</strong>
                    </h2>
                    <span>领取各种商品优惠券，优惠多多</span>
                </div>
            </a>
        </div><?php }?>
		<?php if (!empty($conf['daiguaurl'])) {?><div class="col-sm-6">
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
        </div><?php }?>
		<div class="col-sm-6">
            <a  href="./user/" target="_blank" class="widget">
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


</div>
</div>



<!--<a href="./user/regsite.php"><img src="https://pic.imgdb.cn/item/65095e19204c2e34d3eff205.gif"width="100%"></a><br/>-->


</style>











<div class="btn block btn-block">

    <b href="#faq14" data-toggle="collapse" class="fa fa-gift">&nbsp;&nbsp;点击搭建属于你自己的平台-你的能力决定你的收入！</b>

  <div id="faq14" class="accordion-body collapse"><br>
	<table class="table animated bounceIn" style="text-align: center;">
      <tr>
        <td>
          <h4>
            <span style="font-weight:bold">
              <font color="#FF8000">自</font>
              <font color="#EC6D13">购</font>
              <font color="#D95A26">省</font>
              <font color="#C64739">钱</font>
              <font color="#A0215F">，</font>
              <font color="#A0215F">推</font>
              <font color="#8D0E72">广</font>
              <font color="#5400AB">赚</font>
              <font color="#4100BE">钱</font></span>
          </h4>
        </td>
      </tr>
      <tr>
        <td>收益满<?php echo $conf['tixian_min']; ?>元即可申请提现</td>
      </tr>
      <tr>
        <td><strong>轻轻松松推广日赚500+不是梦</strong></td>
      </tr>
      <tr>
        <td><span class="glyphicon glyphicon-magnet"></span><strong>&nbsp;快加入我们成为大家庭中的一员吧</strong></td>
      </tr>
  </table>
  <a href="/user/regsite.php" class="btn btn-block btn-sm" style="background-color: #DCDCDC;color: #000000;"><b><span style="font-size: 16px">立即开通</b></a>
  </div>
  </div>














<div class="block block-themed">
	<div class="block-title">



<h3 class="panel-title fa fa-list-alt"><font>&nbsp;&nbsp;
<?php
$date = date('Y-m-d');
echo $date;
?>




<b>订单黑板报</b></a></span></h3></div>




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
        $name = '和平直装【火锅】天卡';
    } else if ($sim == '2') {
        $name = '地铁直装【极度未知】天卡';
    } else if ($sim == '3') {
        $name = '和平直装【太乙真人】天卡';
    } else if ($sim == '4') {
        $name = '和平直装【战斗机】天卡';
    } else if ($sim == '5') {
        $name = '和平内核【战斗机内核】周卡';
    } else if ($sim == '6') {
        $name = '王者直装【坚果】天卡';
    } else if ($sim == '7') {
        $name = '和平直装【战斗机】天卡';
    } else if ($sim == '8') {
        $name = '和平直装【战斗机】周卡';
    } else if ($sim == '9') {
        $name = rand(1000, 100000) . '和平直装【哮天犬】周卡';
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


<!--<a href="./user/regsite.php"><img src="assets/beautify/img/d8.gif"width="100%"></a>。-->
<!--文章列表-->


<div class="block block-themed">
	<div class="block-title">
		<h4><i class="fa fa-newspaper-o"></i> 文章列表</h4><img border="0" width="30" src="assets/beautify/img/gg-lb.jpg">
	</div>
	<?php foreach ($msgrow as $row) {
        echo '<a target="_blank" class="list-group-item" href="' . article_url($row['id']) . '"><span class="btn btn-' . $class_arr[($i++) % 5] . ' btn-xs">' . $i . '</span>&nbsp;' . $row['title'] . '</a>';
    }?>
	<a href="<?php echo article_url() ?>" title="查看全部文章" class="btn-default btn btn-block" target="_blank">查看全部文章</a>
</div>
<!--文章列表-->
<?php
}?>

<?php if (!$conf['hide_tongji']) {?>
<div class="panel panel-primary">
<div class="panel-heading">
    <h6 class="panel-title"><font color="#000000"><i class="fa fa-bar-chart-o"></i>&nbsp;&nbsp;<b>近30天数据统计</b></font></h6></div>
<table class="table table-bordered">
<tbody>
<tr>






<tr>
<td align="center"><font size="2"><b><font color="#009900">999+<span id="cou1nt_yxts"></span>关键词</font><b/><br><font color="#65b1c9"><img src="assets/beautify/img/gg-lb.jpg"/></i></font><br>百度收录</font></td>
<td align="center"><font size="2"><b><font color="#DC143C">999+<span id="cou1nt_yxts"></span>软妹币</font><b/><br><font color="#65b1c9"><img src="assets/beautify/img/gg-pmd.jpg"/></i></font><br>累计金额</font></td>
<td align="center"><font size="2"><b><font color=#8B4513>999+<span id="co1unt_yxts"></span>次好评</font><b/><br><font color="#65b1c9"><img src="assets/beautify/img/gg-lb.jpg"/></i></font><br>用户好评</font></td>






</tr>
</tbody>
</table>

<?php }?>

    <!--底部导航-->




    <div class="panel panel-default">
        <center>
            <div class="panel-body"><span style="font-weight:bold"><?php echo $conf['sitename'] ?>
            <i class="fa fa-heart text-danger"></i> <?php echo date("Y") ?> | </span> </span><a href="./"><span style="font-weight:bold"><?php echo $_SERVER['HTTP_HOST'] ?></span></a><br/><?php echo $conf['footer'] ?>

                </div>
	<center><img src="assets/beautify/img/gg-txrz.jpg" height="26px">
	<img src="assets/beautify/img/gg-cxwz.jpg" height="26px">
	<img src="assets/beautify/img/gg-hyff.jpg" height="26px">
	<img src="assets/beautify/img/gg-cyjm.jpg" height="26px"></center>
    </div>


            </div>

    </div>


        <!--<div  style="z-index:9999; text-decoration:none; font-weight:bold; position: fixed; z-index: 999; Left: -6px; bottom: 168px; display: inline-block; width: 20px; border-top-left-radius: 10px; border-top-Left-radius: 5px; border-bottom-Left-radius: 5px; border-bottom-left-radius: 10px; color: white; font-size: 17px; line-height: 17px; box-shadow: rgb(102, 102, 102) 0px 0px 5px; word-wrap: break-word; padding: 8px 12px; border: 2px solid white; background: rgb(242, 12, 12);"><a href="https://zt.sykt2022.top/" target="_blank" style="position: relative;left: -7px;top: 2px; color:#ffffff;">每日效果图</a ></div>-->


<!--每日推荐-->
<div class="custom-richtext" >



    	<!--域名访问次数统计-->
	<style>
        img.hidden {
            visibility: hidden;
        }
    </style>
	<img class="hidden" src="https://api.shserve.cn/api/fwltj?name=<?php echo $_SERVER['HTTP_HOST'] ?>&theme=rule34">
	<!--域名访问次数统计-->
    <!--底部导航-->
</div>

 <!--客服介绍开始-->
<div class="modal fade col-xs-12" align="left" id="lxkf" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">  <br>  <br>
  <div class="modal-dialog panel panel-primary  animation-fadeInQuick2">
    <div class="modal-content">
         <div class="list-group-item reed" style="background:linear-gradient(120deg, #5ED1D7 10%, #71D7A2 90%);">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"></span><span class="sr-only">Close</span></button>
    <center><h4 class="modal-title" id="myModalLabel"><b><font color="#fff">客服与帮助</font></b></h4></center></div>
        <div class="modal-body" id="accordion">
                <div class="panel panel-default" style="margin-bottom: 6px;">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">为什么订单显示已完成了却一直没到账？</a>
					</h4>
				</div>
				<div id="collapseOne" class="panel-collapse in" style="height: auto;">
					<div class="panel-body">
					    </i>&nbsp;<font color=#000000><span style="font-weight:bold">
					订单显示（已完成）就证明已经提交到服务器内！<br>
					如果长时间没到账请联系客服处理！<br>
					下单立即发货，订单长时间显示（待处理）请联系客服！

					</div>
				</div>
			</div>
			<div class="panel panel-default" style="margin-bottom: 6px;">
				<div class="panel-heading">
					<h4 class="panel-title">
						<a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" class="collapsed">购买了如何查询订单？</a>
					</h4>
				</div>
				<div id="collapseTwo" class="panel-collapse collapse" style="height: 0px;">
					<div class="panel-body">
					请在查询处用自己的下单账号查询，浏览器购买的话留空则显示最新订单
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
					<div class="font-w600 push-5">订单售后客服</div>
					<div class="text-muted"><b>QQ：<?php echo $conf['kfqq'] ?></b>
					</div>
			   </div>
			   </div>
			</li>
			<li class="list-group-item">
			想要快速回答你的问题就请把问题描述讲清楚!<br>
			下单账号+业务名称+问题，直奔主题，按顺序回复!<br>
			有问题直接留言，请勿打语音否则直接无视。<br>
			</li>
			</ul>
</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
</div>
    </div>
  </div>
</div>


<!--联系客服结束-->


  <!--分站介绍开始-->
<div class="modal fade" align="left" id="userjs" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
  <div class="modal-dialog">
    <div class="modal-content">
		         <div class="list-group-item reed" style="background:linear-gradient(120deg, #FE2EF7 10%, #71D7A2 90%);">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true"></span><span class="sr-only">Close</span></button>
    <center><h4 class="modal-title" id="myModalLabel"><b><font color="#fff">版本介绍</font></b></h4></center>
		</div>
        <div class="modal-body">
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
                            <td>独立网站/专属后台</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-success" style="overflow: hidden; position: relative;"><i class="fa fa-check"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success" style="overflow: hidden; position: relative;"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
						<tr class="">
                            <td>低价拿货/调整价格</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-success" style="overflow: hidden; position: relative;"><i class="fa fa-check"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success" style="overflow: hidden; position: relative;"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
						<tr class="info">
                            <td>搭建分站/管理分站</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-danger" style="overflow: hidden; position: relative;"><i class="fa fa-close"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success" style="overflow: hidden; position: relative;"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
						<tr class="">
                            <td>超低密价/高额提成</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-danger" style="overflow: hidden; position: relative;"><i class="fa fa-close"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success" style="overflow: hidden; position: relative;"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
						<tr class="danger">
                            <td>赠送专属APP</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-danger" style="overflow: hidden; position: relative;"><i class="fa fa-close"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success" style="overflow: hidden; position: relative;"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
                    </tbody>
                </table>
            </div>
          </div>
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
		</div>
    </div>
  </div>
</div>
<!--分站介绍结束-->

<!--音乐代码-->
<div id="audio-play" <?php if (empty($conf['musicurl'])) {?>style="display:none;"<?php }?>>
  <div id="audio-btn" class="on" onclick="audio_init.changeClass(this,'media')">
    <audio loop="loop" src="<?php echo $conf['musicurl'] ?>" id="media" preload="preload"></audio>
  </div>
</div>
<!--音乐代码-->
<script src="<?php echo $cdnpublic ?>jquery/1.12.4/jquery.min.js"></script>
<script src="<?php echo $cdnpublic ?>jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>
<script src="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="<?php echo $cdnpublic ?>jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="<?php echo $cdnpublic ?>clipboard.js/1.7.1/clipboard.min.js"></script>
<script src="<?php echo $cdnpublic ?>layer/3.4.0/layer.js"></script>
<script src="<?php echo $cdnserver ?>assets/appui/js/app.js"></script>
<!-- DT Time -->
<script type="text/javascript">
var isModal=<?php echo empty($conf['modal']) ? 'false' : 'true'; ?>;
var homepage=true;
var hashsalt=<?php echo $addsalt_js ?>;
$(function() {
	$("img.lazy").lazyload({effect: "fadeIn"});
});
</script>
<script src="assets/js/main.js?ver=<?php echo VERSION + 1 ?>"></script>
<?php if ($conf['classblock'] == 1 || $conf['classblock'] == 2 && checkmobile() == false) {
    include TEMPLATE_ROOT . 'default/classblock.inc.php';
}
?>
</body>
</html>