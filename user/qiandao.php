<?php
$is_defend = true;
include '../includes/common.php';
if ($isLogin2 == 1) {} else {
    $goto = @getHostUrl();

    exit("<script language='javascript'>window.location.href='./login.php?goto=" . $goto . "';</script>");
}

if ($conf['qiandao_open'] != 1) {
    include 'head.php';
    echo '<div class="wrapper">
    <div class="col-sm-12 col-md-8 col-lg-6 center-block" style="float: none;">';
    showmsg($conf['qiandao_close_alert'] ? $conf['qiandao_close_alert'] : '系统未开启签到活动，请以后再来哦', 3);
}

$_SESSION['isqiandao'] = $userrow['zid'];

$day     = date("Y-m-d");
$lastday = date("Y-m-d", strtotime("-1 day"));
if ($row = $DB->get_row("SELECT * FROM pre_qiandao WHERE `zid`= ? and `date`= ? order by id desc limit 1", array($userrow['zid'], $day))) {
    $isqiandao = true;
    $continue  = $row['continue'];
} else {
    if ($row = $DB->get_row("SELECT * FROM pre_qiandao WHERE `zid`= ? and `date`= ? order by id desc limit 1", array($userrow['zid'], $lastday))) {
        $continue = $row['continue'];
    } else {
        $continue = 0;
    }
    $isqiandao = false;
}

$rs    = $DB->query("SELECT * FROM pre_qiandao order by id desc limit 10");
$qdrow = array();
while ($res = $DB->fetch($rs)) {
    if (count($qqrow) < 5) {
        $qqrow[] = $res['qq'];
    }
    $qdrow[] = $res;
}

$title = '每日签到';
include 'head.php';
?>
<style>
.img-circle{width: 15%!important;}
</style>
<div class="wrapper">
<div class="col-sm-12 col-md-8 col-lg-6 center-block" style="float: none;">
			<div class="panel panel-primary">
			<div class="list-group-item reed text-center" style="background: linear-gradient(to right,#14b7ff,#b221ff);"><h3 class="panel-title"><font color="#fff"><i class="fa fa-check-square-o"></i>&nbsp;&nbsp;<b>每日签到</b></font></h3></div>
			<div class="card" style="position: relative;">
				<img class="" style="width: 100%;height: 175px;" src="../assets/img/qiandao.jpg">
				<div style="top: 0;left: 0;padding: 15px;position: absolute;">
					<iframe width="300" scrolling="no" height="60" frameborder="0" allowtransparency="true" src="//i.tianqi.com/index.php?c=code&id=12&icon=1&num=2&site=12"></iframe>
				</div>
				<div style="bottom: 0;right: 0;padding: 15px;position: absolute;">
					<h5 class="widget-heading">
						<font color="#fff" style="text-shadow: black 3px 3px 3px;font-size: 22px;">
							<strong>总奖励：<span id="rewardcount">0.00</span>元</strong>
						</font>
					</h5>
					<h5 class="widget-heading block-right">
						<font color="#fff" style="text-shadow: black 3px 3px 3px;font-size: 22px;">
							<strong><i class="fa fa-calendar-check-o"></i> 连续签到<?php echo $continue ?>天</strong>
						</font>
					</h5>
				</div>
			</div>
			<div class="panel-footer">
				<button type="button" class="btn btn-info btn-block" id="qiandao" style="width: 69%;display: inline-block;" ><span style="font-size:16px" ><b><i class="fa fa-check-square"></i> <?php echo $isqiandao == true ? '今天已签到' : '立即签到'; ?></b></span></button>
				<a  href="#fxhy" data-toggle="modal" title="点击分享本站"><button type="button" class="btn btn-danger btn-block" style="width: 29%;display: inline-block;margin-top: -0.1em;"><span style="font-size:16px" ><b><i class="fa fa-external-link"></i> 分享</b></span></button></a>
			</div>
			</div>

		</div>
	</div>
</div>
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
                    <i class="fa fa-times-circle"></i>
                  </span>
                  <span class="sr-only">Close</span></button>将网站分享给好友</div>
              <li class="list-group-item">
                <div class="input-group">
                  <div class="input-group-addon">广告语</div>
                  <textarea id="fxggc" class="form-control" rows="5" cols="30" readonly="" unselectable="on">
这里签到奖励现金哦！
快来和我一起领取吧！
网址:http://<?php echo $_SERVER['HTTP_HOST'] ?>/?<?php echo mt_rand(1, 999) ?>
每天免费领取100名片攒
建议收藏网站可天天领取
</textarea></div>
              </li>
              <li class="list-group-item">
                <button data-clipboard-target="#fxggc" class="btn btn-sm btn-block btn-success fenx">点击一键复制分享语</button></li>
              <li class="list-group-item">将网站分享给你的好友，有机会获取10W**或者永久超级会员哟！</li>
            </div>
          </div>
        </div>
        <!--复制广告词分享结束-->
<script src="<?php echo $cdnpublic ?>clipboard.js/1.7.1/clipboard.min.js"></script>
<script>
var clipboard = new Clipboard('.fenx');
clipboard.on('success', function(e) {
  	layer.msg("复制成功,快去分享给朋友一起来领免费**吧！", {icon: 1});
});
clipboard.on('error', function(e) {
     layer.msg("复制失败，请长按链接后手动复制", {icon: 2});
});
$(document).ready(function(){
	$("#qiandao").click(function(){
		$.ajax({
		 type: "get",
		 url: "ajax.php?act=qiandao",
		 dataType: "json",
		 success: function(data){
			if(data.code == 0){
				layer.alert(data.msg,{icon:6},function(){
					window.location.reload();
				})
			}else{
				layer.alert(data.msg,{icon:5})
			}
		 },
		 error: function(){
			layer.alert('签到失败，请稍后刷新重试！');
		 }
	   });
	});
	$.ajax({
		type : "GET",
		url : "ajax.php?act=qdcount",
		dataType : 'json',
		async: true,
		success : function(data) {
			$('#count1').html(data.count1);
			$('#count2').html(data.count2);
			$('#count3').html(data.count3);
			$('#rewardcount').html(data.rewardcount);
		}
	});
})
</script>