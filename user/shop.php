<?php
/**
 * 自助下单
 **/
include "../includes/common.php";
$title = '自助下单';
include './head.php';
if ($isLogin2 == 1) {} else {
    $goto = @getHostUrl();

    exit("<script language='javascript'>window.location.href='./login.php?goto=" . $goto . "';</script>");
}

$usershop = true;

// $addsalt             = md5(mt_rand(0, 999) . time());
// $_SESSION['addsalt'] = $addsalt;
$addsalt = md5(rand(1111, 9999) . x_real_ip() . time());
session_set($addsalt, 600);
$x          = new \core\HieroGlyphy();
$addsalt_js = $x->hieroglyphyString($addsalt);
?>
<style>
.onclick{cursor: pointer;touch-action: manipulation;}
.border-t{border-top: 1px solid #e9e9e9;}
.border-b{border-bottom: 1px solid #e9e9e9;}
.layui-fixbar{position:fixed;right:15px;bottom:15px;z-index:999999;margin:0;padding:0}
.layui-fixbar li{list-style:none;width:50px;height:50px;line-height:50px;margin-bottom:1px;text-align:center;cursor:pointer;font-size:30px;background-color:#9F9F9F;color:#fff;border-radius:2px;opacity:.95}
.nav-counter{position:absolute;font-size:16px;top:-1px;right:1px;height:20px;width:20px;line-height:20px;padding:0 6px;color:#fff;text-align:center;background:#e23442;border-radius:50%;background-image:-webkit-linear-gradient(top,#e8616c,#dd202f);background-image:-moz-linear-gradient(top,#e8616c,#dd202f);background-image:-o-linear-gradient(top,#e8616c,#dd202f);background-image:linear-gradient(to bottom,#e8616c,#dd202f);-webkit-box-shadow:inset 0 0 1px 1px rgba(255,255,255,.1),0 1px rgba(0,0,0,.12);box-shadow:inset 0 0 1px 1px rgba(255,255,255,.1),0 1px rgba(0,0,0,.12)}
.cmds-bg{
	background:url(<?php echo $background_image; ?>) no-repeat;
	background-size:cover;
    position: absolute;
    width: 100%;
    height: 100%;
    padding: 0;
    margin: 0;
    top: 0;
}
</style>
<?php include TEMPLATE_ROOT . 'default/head.inc.php';?>
<!-- model -->
<div class="modal fade" align="left" id="showOrder" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span>
			</button>
			<h4 class="modal-title text-center"><span style="color: red">请收藏本站网址到浏览器书签-方便查单</span></h4>
		</div>
		<div class="modal-body " id="showOrder_content">

		</div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" data-dismiss="modal">关闭窗口</button>
	      </div>
	</div>
</div>
</div>
<!-- model end -->
<div class="wrapper" >
	<div class="col-sm-12 center-block" style="float: none;">
		<div class="row">
			<!--下单-->
			<div class="col-sm-12 col-md-7 col-lg-6">
				<div class="panel panel">
					<div class="panel-heading font-bold" style="background: linear-gradient(to right,#54FF9F,#FFE4E1);color: white;">
						余额：<?php echo priceFormat(getUserRmb()) ?>元
						<span class="pull-right">
							<a href="./index.php#chongzhi" class="btn btn-info btn-sm">充值</a>
						</span>
					</div>
					<div class="nav-tabs-alt">
						<ul class="nav nav-tabs nav-justified" data-toggle="tabs">
							<li class="active">
								<a href="#onlinebuy" data-toggle="tab">
									在线下单
								</a>
							</li>
							<li>
								<a href="#query" data-toggle="tab" id="tab-query">
									查询订单
								</a>
							</li>
							<li>
								<a href="#message" data-toggle="tab" id="tab-message">
									商品通知
								</a>
							</li>
						</ul>
						<div class="modal-body">
							<div id="myTabContent" class="tab-content">
								<div class="tab-pane fade in active" id="onlinebuy">
									<?php include TEMPLATE_ROOT . 'default/shop.inc.php';?>
								</div>
								<div class="tab-pane fade in" id="query">
									<?php include TEMPLATE_ROOT . 'default/query.inc.php';?>
								</div>
								<div class="tab-pane fade in" id="message">
									 <table class="table table-striped b-t b-light">
		                             <thead><th>操作</th><th>通知标题</th><th>接收时间</th><th>阅读状态</th></tr></thead>
		                             <tbody>
								    <?php
$msgcount = $DB->count("SELECT count(*) FROM cmy_message WHERE cid=2 and active=1");
$msgread  = explode(',', $userrow['msgread']);
$limit    = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$rs       = $DB->query("SELECT * FROM cmy_message WHERE cid=2 and active=1 ORDER BY id DESC LIMIT 0,$limit");
$msgrow   = array();
while ($res = $DB->fetch($rs)) {
    if (in_array($res['id'], $msgread)) {
        $res['read'] = true;
    } else {
        $res['read'] = false;
    }

    $msgrow[] = $res;
}
foreach ($msgrow as $row) {
    echo '
										<tr class="onclick ' . ($row['read'] ? '' : 'warning') . '"  >
										<td><a class="btn btn-info btn-xs" href="./message.php?my=msginfo&id=' . $row['id'] . '">查看</a></td>
										<td>' . $row['title'] . '</td>
										<td>' . $row['addtime'] . '</td>
										<td>' . ($row['read'] ? '<span class="label label-success">已读</span>' : '<span class="label label-warning">未读</span>') . '</td>
									</tr>';
}
if ($msgcount == 0) {
    echo '<tr><td class="text-center"><font color="grey">商品通知空空如也</font></td></tr>';
}
?>
						          </tbody>
						        </table>

								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- / 下单-->
			<!-- 价格变动-->
			<?php if ($conf['pricejk_show_close'] != 1): ?>
			<div class="col-sm-12 col-md-5 col-lg-6">
				<div class="panel panel">
					<div class="panel-heading font-bold" style="background: linear-gradient(to right,#E91E63,#E91E63);color: white;">
						价格变动区
					</div>
					<div class="panel-body">
						<div class="table-responsive">
				        <table class="table table-striped">
				          <thead><tr><th>商品名称</th><th>类型</th><th>变动后</th><th>浮动值</th><th>时间</th></tr></thead>
				          <tbody>
				          <?php
$sql = ' 1';
$rs  = $DB->select("SELECT A.*,B.`cid` FROM `pre_tools_log` AS A left join `pre_tools` AS B ON(A.`tid`=B.`tid`) WHERE{$sql} order by id desc limit 50");
if (is_array($rs)) {
    foreach ($rs as $key => $res) {
        if ($res['after'] > $res['before']) {
            $value = '<font color="red">' . round($res['after'] - $res['before'], 2) . '</font>';
            //$type  = '&nbsp;<i class="fa fa-long-arrow-up" style="color:red"></i>';
            $type = '<font color="red">上涨</font>';
        } elseif ($res['after'] < $res['before']) {
            $value = '<font color="green">' . round($res['before'] - $res['after'], 2) . '</font>';
            $type  = '<font color="green">下降</font>';
            //$type  = '&nbsp;<i class="fa fa-long-arrow-up" style="color:green"></i>';
        } else {
            $value = '-';
            $type  = '-';
        }
        if (!checkmobile() && mb_strlen($res['name']) > 25) {
            $res['name'] = mb_substr($res['name'], 0, 25) . '...';
        }
        $price = sprintf('%.2f', $res['after']);
        echo '<tr><td style="word-break: break-all;"><a href="./shop.php?cid=' . $res['cid'] . '&tid=' . $res['tid'] . '"><span style="color:#9C27B0;margin-right:3px;">[购买]</span>' . $res['name'] . '</a></td><td>' . $type . '</td><td>' . $price . '</td><td>' . $value . '</td><td>' . date("Y-m-d H:i:s", $res['addtime']) . '</td></tr>';
    }
}
?>
							 </tbody>
        				</table>
					</div>
				</div>
			</div>
			<?php endif?>
			<!-- / 价格变动-->
		</div>
	</div>
</div>
<script src="<?php echo $cdnpublic ?>jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>
<script src="<?php echo $cdnpublic ?>jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script src="<?php echo $cdnpublic ?>layer/3.4.0/layer.js"></script>
<script type="text/javascript">
var defaultimg='/assets/img/Product/default.png';
var isModal=false;
var homepage=false;
var hashsalt=<?php echo $addsalt_js ?>;
$(function() {
<?php if ($conf['shoppingcart'] == 1) {?>
$.ajax({
	type : "GET",
	url : "../ajax.php?act=cart_info",
	dataType : 'json',
	async: true,
	success : function(data) {
		if(data.count != null && data.count>0){
			$('#cart_count').html(data.count);
			$('#alert_cart').show();
		}
	}
});
<?php }?>
});

function show(id) {
	$.ajax({
		type : 'GET',
		url : 'ajax.php?act=msginfo&id='+id,
		dataType : 'json',
		success : function(data) {
			if(data.code==0){
				layer.open({
				  type: 0,
				  anim: 2,
				  btn: ['关闭窗口'],
				  btnAlign:'c',
				  shadeClose: true,
				  title: '查看消息内容',
				  content: '<div class="msg-head"><h4><b>'+data.title+'</b></h4><small><font color="grey">管理员  '+data.date+'</font></small></div><div class="msg-body">'+data.content+'</div>',
				  end: function(){
					  layer.closeAll();
				  }
				});
			}else{
				layer.alert(data.msg);
			}
		},
		error:function(data){
			layer.msg('服务器错误');
			return false;
		}
	});
}
</script>
<script src="../assets/js/usershop.js?ver=<?php echo $jsver ?>"></script>
<?php include 'footer.php'?>