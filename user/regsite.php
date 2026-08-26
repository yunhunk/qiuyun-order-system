<?php
/**
 * 自助开通分站
 **/
$is_defend = true;
include "../includes/common.php";
if ($isLogin2 == 1 && $userrow['power'] > 0) {
    @header('Content-Type: text/html; charset=UTF-8');
    if ($userrow['power'] == 2) {
        exit("<script language='javascript'>alert('安全提醒！您已开通过最高版本分站');window.location.href='./';</script>");
    } else {
        exit("<script language='javascript'>window.location.href='./upsite.php';</script>");
    }
} elseif ($conf['fenzhan_buy'] == 0) {
    @header('Content-Type: text/html; charset=UTF-8');
    showmsg('当前站点未开启自助开通分站功能！', 3, true, true);
}

$title = '自助开通分站';
include './head2.php';
// $addsalt             = md5(mt_rand(0, 999) . time());
// $_SESSION['addsalt'] = $addsalt;
$addsalt = md5(rand(1111, 9999) . x_real_ip() . time());
session_set($addsalt, 600);
$x          = new \core\HieroGlyphy();
$addsalt_js = $x->hieroglyphyString($addsalt);

$kind = isset($_GET['kind']) ? $_GET['kind'] : 0;

if ($is_fenzhan == true && $siterow['power'] == 2 && !empty($siterow['ktfz_domain'])) {
    $domains = explode(',', $siterow['ktfz_domain']);
} else {
    $domains = explode(',', $conf['fenzhan_domain']);
}
$select = '';
foreach ($domains as $domain) {
    $select .= '<option value="' . $domain . '">' . $domain . '</option>';
}

if ($isLogin2 == 1) {
    $user = $userrow['user'];
    $pwd  = $userrow['pwd'];
    $qq   = $userrow['qq'];
} else {
    $user = isset($_POST['user']) ? $_POST['user'] : '';
    $pwd  = isset($_POST['pwd']) ? $_POST['pwd'] : '';
    $qq   = isset($_POST['qq']) ? $_POST['qq'] : '';
}
if (empty($select)) {
    showmsg('请先到后台分站设置，填写可选分站域名', 3);
}

?>
<style type="text/css">
.cmds-bg{
    position: fixed;
    width: 100%;
    height: 100%;
    padding: 0;
    margin: 0;
    top: 0;
}
</style>
<img style="" src="<?php echo $background_image; ?>" alt="Full Background" class="cmds-bg" ondragstart="return false;" oncontextmenu="return false;">
<div class="col-xs-12 col-sm-10 col-md-8 col-lg-4 center-block " style="float: none;">
  <br />
    <div class="widget-content themed-background-flat text-center"  style="background-image: url(<?php echo $cdnserver ?>assets/simple/img/userbg.jpg);background-size: 100% 100%;" >
			<img  class="img-circle"src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq']; ?>&spec=100" alt="Avatar" alt="avatar" height="60" width="60" />
			<p></p>
	 <h4 style="color: #fff;">分站自助搭建</h4>
	 <p></p><p></p>
	</div>
   <div class="block" style="background-color: #fff;">
	    <div class="block-title">
	    	 <div class="" style="float: right !important;margin-top:8px;margin-right:8px">
                <a href="/"  class="btn btn-info btn-xs">返回首页</a>
	         </div>
	    	<h3 class="panel-title">在线开通分站</h3>
	    </div>
		<ul class="nav nav-tabs nav-tabs-alt">
		<li class="active" style="width:33.3333333%"><a href="#ktfz" data-toggle="tab" aria-expanded="true"><center>开通分站</center></a></li>
		<li style="width:33.3333333%" class=""><a href="#fzjs" data-toggle="tab" aria-expanded="false"><center>版本区别</center></a></li>
		<li style="width:33.3333333%" class=""><a href="#fzbb" data-toggle="tab" aria-expanded="false"><center>详细介绍</center></a></li>
		</ul>
   <div id="myTabContent" class="tab-content">
   <div class="tab-pane fade active in dasd" id="ktfz">
	<br>
	<div class="">
				<div class="alert alert-success">
					专业版分站开通价格: <?php echo $conf['fenzhan_price'] ?> 元 (普通密价下单，不支持搭建下级分站)<br/>
			        旗舰版分站开通价格: <?php echo $conf['fenzhan_price2'] ?>元 (高级密价下单，支持搭建下级分站并设置价格)<br/>
				</div>
		        <div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">
							选择版本
						</div>
						<select name="kind" onchange="" class="form-control"><option value="1" <?php if ($kind == 1) {?>selected<?php }?>>普及版<?php echo $conf['fenzhan_price'] ?>元(可赚用户差价+低价下单)</option><option value="2" <?php if ($kind == 2) {?>selected<?php }?>>旗舰版<?php echo $conf['fenzhan_price2'] ?>元(可赚用户差价+下级分站差价)</option></select>
					</div>
					<font color="red">（<i class="glyphicon glyphicon-question-sign"></i>）选择好需要的版本，旗舰版比普及版买东西更便宜</font>
				</div>
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">
							二级域名
						</div>
						<div class="input-group" style="width: 100%;">
							<input type="text" onkeyup="value=value.replace(/[^\w\.\/]/ig,'')" name="qz" class="form-control" required data-parsley-length="[2,8]"
									   placeholder="输入想要的前缀">
						</div>
						<select name="domain" class="form-control"><?php echo $select ?></select>
					</div>
					<font color="red">（<i class="glyphicon glyphicon-question-sign"></i>）仅支持小写字母或数字，例如网站名称牛牛商城，填66sc！填好QQ，可以点击随机生成</font>
				</div>
				<div class="form-group hide">
					<div class="input-group">
						<div class="input-group-addon">
							专属网址
						</div>
						<input type="text" name="fzurl" class="form-control" placeholder="请先填写域名前缀" disabled="disabled">
					</div>
					<font color="red">（<i class="glyphicon glyphicon-question-sign"></i>）前缀填写后此处将显示您的平台网址</font>
				</div>
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">
							登录用户名
						</div>
						<input type="text" id="user" name="user" class="form-control" required placeholder="输入你要注册的用户名" value="<?php echo $user ?>" <?php if ($isLogin2) {?>disabled="disabled"<?php }?>>
					</div>
					<font color="red">（<i class="glyphicon glyphicon-question-sign"></i>）建议填写您的QQ号，方便记住！可使用字母数字小数点等任意组合，5-12位</font>
				</div>
				<div class="form-group">
					<div class="input-group"><div class="input-group-addon">登录密码</div>
					<input type="text" name="pwd" id="pwd" value="<?php echo $pwd ?>" class="form-control" required <?php if ($isLogin2) {?>disabled="disabled"<?php }?>/>
				</div>
				<font color="red">（<i class="glyphicon glyphicon-question-sign"></i>）可使用字母数字小数点等任意组合，6-16位</font>
				</div>
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">
							绑定ＱＱ
						</div>
						<input type="number" id="qq" name="qq" class="form-control" required data-parsley-length="[5,12]" placeholder="输入你的QQ号" value="<?php echo $qq ?>">
					</div>
					<font color="red">（<i class="glyphicon glyphicon-question-sign"></i>）填写你的QQ号码，发布联系和找回密码用</font>
				</div>
				<div class="form-group">
					<div class="input-group"><div class="input-group-addon">网站名称</div>
						<input type="text" name="name" id="name" value="" data-parsley-length="[4,10]" class="form-control" required/>
					</div>
					<font color="red">（<i class="glyphicon glyphicon-question-sign"></i>）例如超惠云商城等，5~10个字左右，尽量简短</font>
				</div>

				<input type="submit" id="submit_buy" class="btn btn-primary btn-block" value="立即开通">
									<div id="result4" class="form-group text-center" style="display:none;">
				</div>
			<hr>
			<div class="form-group">
				<a href="findpwd.php" class="btn btn-success btn-rounded findpwd"><i class="fa fa-unlock"></i>&nbsp;找回密码</a>
				<a href="login.php" class="btn btn-primary btn-rounded" style="float:right;"><i class="fa fa-user"></i>&nbsp;返回登录</a>
			</div>
	</div>
	</div>
   <div class="tab-pane fade" id="fzjs">
      <br>
		<div class="block">
		            <div class="table-responsive">
                <table class="table table-borderless table-vcenter">
                    <thead>
                        <tr>
                            <th style="width: 100px;">功能</th>
                            <th class="text-center" style="width: 20px;">普及版/旗舰版</th>
                        </tr>
                    </thead>
					<tbody>
						<tr class="active">
                            <td>专属平台</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
                        <tr class="">
                            <td>三种在线支付接口</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>

							</td>
                        </tr>
						<tr class="success">
                            <td>专属网站域名</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
						<tr class="">
                            <td>赚取用户提成</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
						<tr class="info">
                            <td>赚取下级分站提成</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-danger"><i class="fa fa-close"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
						<tr class="">
                            <td>设置商品提成</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
						<tr class="warning">
                            <td>设置下级分站商品提成</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-danger"><i class="fa fa-close"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
            </tr>
						<tr class="">
                            <td>搭建下级分站</td>
                            <td class="text-center">
								<span class="btn btn-effect-ripple btn-xs btn-danger"><i class="fa fa-close"></i></span>
								<span class="btn btn-effect-ripple btn-xs btn-success"><i class="fa fa-check"></i></span>
							</td>
                        </tr>
                    </tbody>
                </table>
            </div>
         </div>
</div>
    <br>		<div class="tab-pane fade" id="fzbb">

		<div class="block">
			<div class="panel-group" id="accordion">
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" class="collapsed"><i class="fa fa-hand-o-up fa-lg"></i>&nbsp;分站是怎么获取收益的？</a>
						</h4>
					</div>
					<div id="collapseOne" class="panel-collapse collapse" style="height: 0px;" aria-expanded="false">
						<div class="panel-body">
							其实很简单，你只需要把你的分站域名发给你的用户让他下单，一旦下单付款成功，你的账户就会增加你所赚差价的金额，自己是可以设置销售价格的哦！
						</div>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" class="collapsed" aria-expanded="false"><i class="fa fa-hand-o-up fa-lg"></i>&nbsp;赚到的钱在哪里？我如何得到？</a>
						</h4>
					</div>
					<div id="collapseTwo" class="panel-collapse collapse" style="height: 0px;" aria-expanded="false">
						<div class="panel-body">
							分站后台有完整的消费明细和余额信息，后台余额可供您在平台消费，满元可以在后台提现到QQ钱包微信或者支付宝中。
						</div>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a data-toggle="collapse" data-parent="#accordion" href="#collapseThree" class="collapsed" aria-expanded="false"><i class="fa fa-hand-o-up fa-lg"></i>&nbsp;需要我自己供货吗？哪来的商品货源？</a>
						</h4>
					</div>
					<div id="collapseThree" class="panel-collapse collapse" style="height: 0px;" aria-expanded="false">
						<div class="panel-body">
							所有商品全部由主站提供，您无需当心货源问题，所有订单由我们来处理，如果网站没有您想要的商品可联系主站客服添加即可！
						</div>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a data-toggle="collapse" data-parent="#accordion" href="#collapseFourth" class="collapsed" aria-expanded="false"><i class="fa fa-hand-o-up fa-lg"></i>&nbsp;这个和KM一样吗？有什么区别？</a>
						</h4>
					</div>
					<div id="collapseFourth" class="panel-collapse collapse" style="height: 0px;" aria-expanded="false">
						<div class="panel-body">
							完全不同，销售提成最高享受商品售价的30%，货源更精，无需注册,无需预存，在线支付，更简单快捷！
						</div>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
							<a data-toggle="collapse" data-parent="#accordion" href="#collapseFive" class="collapsed" aria-expanded="false"><i class="fa fa-hand-o-up fa-lg"></i>&nbsp;可以自己上架商品吗？可以修改售价吗？</a>
						</h4>
					</div>
					<div id="collapseFive" class="panel-collapse collapse" style="height: 0px;" aria-expanded="false">
						<div class="panel-body">
							为了更好的保证售后，所有分站暂时都不支持自己上架商品，但可以修改销售价格，我们会在这方面后期考虑做出更新服务！
						</div>
					</div>
				</div>
				<div class="panel panel-default">
					<div class="panel-heading">
						<h4 class="panel-title">
								<a data-toggle="collapse" data-parent="#accordion" href="#collapseSix" class="collapsed" aria-expanded="false"><i class="fa fa-hand-o-up fa-lg"></i>&nbsp;那么多网有分站，为什么选择你们呢？</a>
						</h4>
					</div>
					<div id="collapseSix" class="panel-collapse collapse" style="height: 0px;" aria-expanded="false">
						<div class="panel-body">
							全网最专业的商城系统，商品齐全、货源稳定、价格低廉、售后保障。实体工作室运营，拥有丰富的人脉和资源，我们的货源全部精挑细选全网性价比最高的，实时掌握市场的动态，加入我们，只要你坚持，你不用担心不赚钱，不用担心货源不好，更不用担心我们跑路，我们即使不敢保证你月入上万，在网上赚个零花钱绝对没问题！
						</div>
					</div>
				</div></div>
			</div>
		</div>
	</div>
	</div>
  </div>
<script>
var hashsalt=<?php echo $addsalt_js ?>;
</script>
<script src="../assets/js/regsite.js?<?php echo $jsver ?>"></script>
