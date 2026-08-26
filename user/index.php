<?php
require '../includes/common.php';
if ($isLogin2 == 1) {} else {
    $goto = @getHostUrl();
    exit("<script language='javascript'>window.location.href='./login.php?goto=" . $goto . "';</script>");
}

if ($_GET['mod'] == 'faka') {
    exit("<script language='javascript'>window.location.href='../?mod=faka&id={$_GET['id']}&skey={$_GET['skey']}';</script>");
}

if (intval($conf['template_layout_off']) == 0 && false != ($path = \core\Template::checkUserTpl('index'))) {
    include_once $path;
    die;
}

$title = '平台首页';
include 'head.php';

$kind = isset($_GET['kind']) ? $_GET['kind'] : 1;

$select = '';
$select .= '<option value="1" price="' . $conf['fenzhan_price'] . '">普及版</option>';
$select .= '<option value="2" price="' . $conf['fenzhan_price2'] . '">专业版</option>';

function getCzTips()
{
    global $conf;
    $str  = '';
    $arr1 = explode(',', $conf['fz_fanli_list']);
    $arr2 = [];
    $arr3 = [];
    foreach ($arr1 as $key => $value) {
        $arr4 = explode('|', $value);
        if (count($arr4) == 2) {
            $arr3[$arr4[0]] = $arr4[1];
            array_push($arr2, $arr4[0]);
        }
    }
    $arr2 = sortByDesc($arr2);

    foreach ($arr2 as $key => $value) {
        $str .= '凡充值<span style="color:red">满' . $value . '</span>元返现<span style="color:red">' . $arr3[$value] . '%</span><br/>';
    }
    return $str;
}

$czTips = getCzTips();
$rand   = '/?' . rand(1, 999);
?>
<link rel="stylesheet" href="<?php echo $cdnpublic ?>toastr.js/latest/css/toastr.min.css">
<style>
img.logo{width:14px;height:14px;margin:0 5px 0 3px;}
.span_position{display:inline;background:red;border-radius:50%;width:10px;height:10px;position:absolute}
</style>
<div class="wrapper">
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style=" float: none;">
	<?php if ($userrow['pwd'] == '123456' || $userrow['pwd'] == $userrow['user']) {?>
		<div class="alert alert-info">
		安全提示：您的密码过于简单，请修改较复杂一点的密码！建议不要和账号相同&nbsp;<a href="./uset.php?mod=user" class="btn btn-primary btn-xs">点我修改</a>
	   </div>
    <?php }?>

    <?php if ($userrow['tel'] == '' && $conf['fenzhan_tel'] == 1) {?>
		<div class="alert alert-info">
		安全提示：您未绑定手机号码，请及时绑定！手机号可用于修改密码、找回密码等重要操作&nbsp;<a href="./uset.php?mod=user" class="btn btn-primary btn-xs">点我绑定</a>
	   </div>
    <?php }?>
</div>
<!-- model -->
<div class="modal fade" align="left" id="userjs" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span>
			</button>
			<h4 class="modal-title">在线充值余额</h4>
		</div>
		<div class="modal-body text-center">
	<?php if ($czTips != ''): ?>
		<div class="alert alert-info">
		<h4 style="color:red; margin: 6px;" class="text-center">当前已开启充值返现，规则如下：</h4>
		<?php echo $czTips; ?>
		</div>
	<?php endif;?>
<?php
if ($conf['fenzhan_chongzhi_alert']) {
    echo '<div class="alert alert-success">' . $conf['fenzhan_chongzhi_alert'] . '</div>';
}?>
			<b>我当前的账户余额：<span style="font-size:16px; color:#FF6133;"><?php echo priceFormat(getUserRmb()) ?></span> 元</b>
			<hr>
			<input type="text" class="form-control" name="value" autocomplete="off" placeholder="输入要充值的余额"><br>
<?php
if ($conf['alipay_api']) {
    echo '<button type="submit" class="btn btn-default" id="buy_alipay"><img src="../assets/icon/alipay.ico" class="logo">支付宝</button>&nbsp;';
}

if ($conf['qqpay_api']) {
    echo '<button type="submit" class="btn btn-default" id="buy_qqpay"><img src="../assets/icon/qqpay.ico" class="logo">QQ钱包</button>&nbsp;';
}

if ($conf['wxpay_api']) {
    echo '<button type="submit" class="btn btn-default" id="buy_wxpay"><img src="../assets/icon/wechat.ico" class="logo">微信支付</button>&nbsp;';
}

?>
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModa4" id="alink" style="visibility: hidden;"></button>
<hr><small style="color:#999;">付款后自动充值，刷新此页面即可查看余额。<br>注意！充值的余额不可用于提现</small>
		</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
      </div>
	</div>
</div>
</div>
<!-- model end -->
<!-- model -->
<div class="modal fade" align="left" id="ktfz" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">关闭</span>
			</button>
			<h4 class="modal-title">在线开通分站</h4>
		</div>
		<div class="modal-body">
			<div class="alert alert-success">
			普及版分站价格: <?php echo $conf['fenzhan_price'] ?> 元 (普通密价下单，支持设置出售价格，不支持搭建下级分站)<br/>
	        专业版分站价格: <?php echo $conf['fenzhan_price2'] ?>元 (高级密价下单，支持设置出售价格，支持搭建下级分站)<br/>
		   </div>
			<div class="form-group">
                <div class="input-group">
                    <div class="input-group-addon">开通版本</div>
                     <select id="ktfz_kind" onchange="getPrice(this)" default="<?php echo $kind ?>" class="form-control">
                     	<?php echo $select; ?>
                     </select>
                </div>
            </div><br>
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-addon">网站名称</div>
                    <input type="text" id="sitename" class="form-control">
                </div>
                 <pre><font color="red">（<i class="glyphicon glyphicon-question-sign"></i>）例如某某货源站，某某卡网等，根据你爱好取</font></pre>
            </div><br>
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-addon">花费金额</div>
                    <input type="text" id="ktfz_price" class="form-control" disabled>
                </div>
            </div><br>
            <div class="form-group">
                <a class="btn btn-success btn-rounded" id="regsite" onclick="regsite()">立即开通</a>
                <a id="siterowmit" class="btn btn-primary btn-rounded" data-dismiss="modal" style="float:right;">取消开通</a>
            </div>
		</div>
	</div>
</div>
</div>

<div>
<div class="col-lg-4 col-md-6 col-sm-12">
  <div class="panel" style="background:#fff;border-radius:14px;box-shadow:0 6px 24px rgba(0,0,0,0.08);overflow:hidden;border:none!important;">
      <div class="panel-heading font-bold" style="background:linear-gradient(135deg,#5aac98,#488c7a)!important;padding:16px;color:#fff!important;">
              <div class="widget-content clearfix" style="display:flex;align-items:center;">
        <div class="col-lg-4 col-md-5 col-sm-5" style="padding:0;flex:0 0 72px;">
          <img src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $userrow['qq'] ?>&amp;spec=100" alt="Avatar"
               width="66" class="img-circle img-thumbnail img-thumbnail-avatar pull-left" style="border-radius:50%;border:3px solid rgba(255,255,255,0.4);">
        </div>
        <div class="col-lg-8 col-md-7 col-sm-7" style="padding-left:10px;">
          <span class="text-muted  text-left" style="display:block;">
            <span style="color: #fff;font-size:14px;">
              <i class="fa fa-question-circle" data-tip="余额是通过充值得来的" onclick="layer.alert('余额是通过充值得来的,只支持消费');"></i>
              <b>账户余额：<?php echo priceFormat(getUserRmb()) ?>元</b>
            </span><br>
            <span style="color: #fff;font-size:14px;">
              <i class="fa fa-question-circle" data-tip="你下级消费或在线开通分站赚取的利润，叫做提成" onclick="layer.alert('你下级消费或在线开通分站赚取的利润，叫做提成');"></i>
              <b>账户提成：<font color=red><?php echo priceFormat($userrow['point']) ?></font>元</b>
            </span>
            <br>
            <span class="text-muted text-center" style="margin-top:8px;display:block;">
              <a href="#userjs" data-toggle="modal" class="btn btn-xs btn-success" style="background:#1fb48b;border-color:transparent;color:#fff;"><b>充值余额</b></a>
              <a href="tixian.php" class="btn btn-xs btn-info" style="background:#2fa6f7;border-color:transparent;color:#fff;">申请提现</a>&nbsp;
            </span>
          </span>
        </div>
      </div>
    </div>

<?php
$thtime          = date("Y-m-d") . ' 00:00:00';
$lastday         = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';

// 提成/消费汇总（今日/昨日）
$income_today    = $DB->getColumn("SELECT sum(point) FROM pre_points WHERE zid='{$userrow['zid']}' AND action='提成' AND addtime>'$thtime'");
$outcome_today   = $DB->getColumn("SELECT sum(point) FROM pre_points WHERE zid='{$userrow['zid']}' AND action='消费' AND addtime>'$thtime'");
$income_lastday  = $DB->getColumn("SELECT sum(point) FROM pre_points WHERE zid='{$userrow['zid']}' AND action='提成' AND addtime<'$thtime' AND addtime>'$lastday'");
$outcome_lastday = $DB->getColumn("SELECT sum(point) FROM pre_points WHERE zid='{$userrow['zid']}' AND action='消费' AND addtime<'$thtime' AND addtime>'$lastday'");

// 订单和收益汇总（今日/本月/昨日/累计）
$today_order     = $DB->getColumn("SELECT count(*) FROM pre_orders WHERE addtime>='$thtime' AND zid='{$userrow['zid']}'");
$today_all       = $DB->getColumn("SELECT count(*) FROM pre_orders WHERE zid='{$userrow['zid']}'");
$today_point     = $DB->getColumn("SELECT sum(point) FROM pre_points WHERE action='提成' and addtime>='$thtime' AND zid='{$userrow['zid']}'");
$yesterday_time  = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';
$yesterday_order = $DB->getColumn("SELECT count(*) FROM pre_orders WHERE addtime<'$thtime' AND addtime>'$yesterday_time' AND zid='{$userrow['zid']}'");
$point_all       = $DB->getColumn("SELECT sum(point) FROM pre_points WHERE action='提成' AND zid='{$userrow['zid']}'");
$yesterday_point = $DB->getColumn("SELECT sum(point) FROM pre_points WHERE action='提成' and addtime<'$thtime' AND addtime>'$yesterday_time' AND zid='{$userrow['zid']}'");

$days = [];
$orderData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date("Y-m-d", strtotime("-$i day"));
    $count = $DB->getColumn("SELECT count(*) FROM pre_orders WHERE zid='{$userrow['zid']}' AND DATE(addtime)='{$date}'");
    $days[] = $date;
    $orderData[] = $count ? (int)$count : 0;
}
?>


<table class="table table-bordered" style="margin:0;border:none;">
<tbody>
<tr>
    <th class="text-center nickname" style="border:none;">
        <font color="#a9a9a9">用户名</font><br><font size="4"><?php echo $userrow['user'] ?></font>
    </th>
    <th class="text-center" style="border:none;">
        <font color="#a9a9a9">UID</font><br><font size="4"><?php echo $userrow['zid'] ?></font>
    </th>
</tr>
</tbody>
</table>

<style>
.panel .table { margin-bottom:0!important; }
.panel .btn { border-radius:10px!important; padding:8px 6px!important; font-size:13px!important; }
.panel .table td, .panel .table th { vertical-align:middle!important; }
@media (max-width:768px){ .panel .btn { font-size:12px!important; padding:6px 6px!important; } }
</style>

<?php
?>

<table class="table" style="margin-top:6px;">
  <tbody>
   <tr>
        <th class="text-center nickname" style="vertical-align:middle;">
            <font color="#a9a9a9">今日订单</font><br><font size="3"><?php echo $today_order ?>个</font>
         </th>
        <th class="text-center nickname" style="vertical-align:middle;">
            <font color="#a9a9a9">本月订单</font><br><font size="3"><?php echo $today_all ?>个</font>
        </th>
        <th class="text-center">
            <font color="#a9a9a9">今日收益</font><br><font size="3"><?php echo round($today_point, 2) ?>元</font>
        </th>
    </tr>

    <tr>
        <th class="text-center nickname" style="vertical-align:middle;">
            <font color="#a9a9a9">昨日订单</font><br><font size="3"><?php echo $yesterday_order ?>个</font>
        </th>
        <th class="text-center">
            <font color="#a9a9a9">本月收益</font><br><font size="3"><?php echo round($point_all, 2) ?>元</font>
        </th>
        <th class="text-center">
            <font color="#a9a9a9">昨日收益</font><br><font size="3"><?php echo round($yesterday_point, 2) ?>元</font>
        </th>
    </tr>
  </tbody>
</table>

<table class="table" style="margin-top:8px;">
  <tbody>
    <tr>
      <td colspan="1"><a href="./shop.php" class="btn btn-primary btn-block"><i class="fa fa-shopping-cart"></i><br/><b>低价下单</b></a></td>
      <?php if ($conf['qiandao_open'] == 1) {?>
      <td colspan="1"><a href="./qiandao.php" class="btn btn-success btn-block"><i class="fa fa-check-square"></i><br/><b>每日签到</b></a></td>
      <?php } else {?>
      <td colspan="1"><a href="#userjs" data-toggle="modal" class="btn btn-success btn-block"><i class="fa fa-money"></i><br/><b>充值余额</b></a></td>
      <?php }?>
      <td colspan="1"><a href="message.php" class="btn btn-primary btn-block"><i class="fa fa-bullhorn"></i><br/><b>站内通知</b><span id="message_count"></span></a></td>
    </tr>
    <tr>
      <td colspan="1"><a href="<?php echo $userrow['power'] > 0 ? './shop.php?chadan=1' : '../?chadan=1'; ?>" class="btn btn-info btn-block"><i class="fa fa-search"></i><br/><b>自助查单</b></a></td>
      <td colspan="1"><a href="./workorder.php" class="btn btn-warning btn-block"><i class="fa fa-check-square-o"></i><br/><b>我的工单</b></a></td>
      <td><a href="record.php" class="btn btn-info btn-block"><i class="fa fa-hashtag"></i><br/><b>收支明细</b></a></td>
    </tr>
    <?php if ($userrow['power'] > 0) {?>
    <tr>
      <td colspan="1"><a href="shoplist.php" class="btn btn-primary btn-block"><i class="fa fa-list-alt"></i><br/><b>商品管理</b></a></td>
      <td colspan="1"><a href="list.php" class="btn btn-info btn-block"><i class="fa fa-list"></i><br/><b>订单记录</b></a></td>
      <?php if ($userrow['power'] == 2) {?>
      <td colspan="1"><a href="sitelist.php" class="btn btn-primary btn-block"><i class="fa fa-sitemap"></i><br/><b>分站管理</b></a></td>
      <?php } else {?>
      <td colspan="1"><a href="login.php?logout" class="btn btn-danger btn-block"><i class="fa fa-sign-out"></i><br/><b>安全退出</b></a></td>
      <?php }?>
    </tr>
    <?php }?>

    <tr>
      <td><a href="../sup" class="btn btn-success btn-block"><i class="fa fa-briefcase"></i><br/><b>供货管理</b></a></td>
      <td><a href="../toollogs.php" class="btn btn-danger btn-block"><i class="fa fa-calendar"></i><br/><b>上架日志</b></a></td>
      <?php if ($userrow['power'] == 0) {?>
      <td><a href="list.php" class="btn btn-success btn-block"><i class="fa fa-list"></i><br/><b>订单记录</b></a></td>
      <?php } else {?>
      <td><a href="rank.php" class="btn btn-success btn-block"><i class="fa fa-users"></i><br/><b>分站排行</b></a></td>
      <?php }?>
    </tr>
  </tbody>
</table>
</div>
</div>


<div class="col-lg-4 col-md-6 col-sm-12">
  <div class="panel" style="background:#fff;border-radius:16px;box-shadow:0 6px 20px rgba(0,0,0,0.08);overflow:hidden;animation:fadeUp 0.8s ease forwards;opacity:0;transform:translateY(30px);">
    <div style="background:linear-gradient(135deg,#6fc7b5,#5aa792);padding:20px;color:#fff;text-align:center;">
      <h4 style="margin:0;font-weight:600;">站点信息</h4>
    </div>
    <ul class="list-group" style="border:none;padding:0;margin:0;">
      <?php if ($userrow['power'] > 0) {?>
        <li class="list-group-item" style="display:flex;justify-content:space-between;align-items:center;animation:float 3s ease-in-out infinite;border:none;padding:12px 20px;">
          <span style="font-weight: bold;">店内通知：你当前有 <b style="color:orange;" id="tiaosu">0</b> 条未读信息</span>
          <a href="./message.php" class="btn btn-primary btn-sm" style="border-radius:12px;padding:4px 12px;font-weight:500;">立即查看</a>
        </li>

        <li class="list-group-item" style="display:flex;justify-content:space-between;align-items:center;animation:float 3s ease-in-out infinite;border:none;padding:12px 20px;">
          <span style="font-weight: bold;">店铺①号：<a href="<?php echo parse_site_url($userrow['siteurl']) ?>" target="_blank"><?php echo parse_site_url($userrow['siteurl'], false) ?></a></span>
          <a href="changeurl.php" class="btn btn-info btn-sm" style="border-radius:12px;padding:4px 12px;font-weight:500;">自助修改</a>
        </li>

        <?php if ($userrow['siteurl2'] || $conf['fenzhan_editd_open'] == 1): ?>
        <li class="list-group-item" style="display:flex;justify-content:space-between;align-items:center;animation:float 3s ease-in-out infinite;border:none;padding:12px 20px;">
          <span style="font-weight: bold;">店铺②号：<a href="<?php echo parse_site_url($userrow['siteurl2']); ?>" target="_blank"><?php echo $userrow['siteurl2'] ? parse_site_url($userrow['siteurl2'], false) : '还未绑定' ?></a></span>
          <?php if (!$userrow['siteurl2']) {?>
            <a href="./ndomain.php" class="btn btn-info btn-sm" style="border-radius:12px;padding:4px 12px;font-weight:500;">立即绑定</a>
          <?php } else {?>
            <a href="changeurl.php" class="btn btn-info btn-sm" style="border-radius:12px;padding:4px 12px;font-weight:500;">换绑域名</a>
          <?php }?>
        </li>
        <?php endif;?>

        <?php if ($conf['dwz_api']) {?>
        <li class="list-group-item" style="display:flex;justify-content:space-between;align-items:center;animation:float 3s ease-in-out infinite;border:none;padding:12px 20px;">
          <span style="font-weight: bold;">防红链接：<a href="javascript:;" id="copy-btn" data-clipboard-text="">Loading...</a></span>
          <span>
            <button class="btn btn-secondary btn-sm" id="recreate_url" style="border-radius:12px;padding:4px 12px;font-weight:500;">重新生成</button>
            <a href="javascript:void(0);" onclick="layer.alert('防红链接：该链接可以在QQ直接打开的您的网站，方便推广！<br />Tips：点击短网址即可复制哦~',{icon: 3,title: '小提示',skin: 'layui-layer-molv layui-layer-wxd'});" class="btn btn-info btn-sm" style="border-radius:12px;padding:4px 12px;font-weight:500;">说明</a>
          </span>
        </li>
        <?php }?>

        <li class="list-group-item" style="display:flex;justify-content:space-between;align-items:center;animation:float 3s ease-in-out infinite;border:none;padding:12px 20px;">
          <span style="font-weight: bold;">店铺名称：<b style="color:#14b7ff;"><?php echo $userrow['sitename'] ?></b></span>
          <a href="uset.php?mod=site" class="btn btn-success btn-sm" style="border-radius:12px;padding:4px 12px;font-weight:500;">编辑店名</a>
        </li>

        <li class="list-group-item" style="display:flex;justify-content:space-between;align-items:center;animation:float 3s ease-in-out infinite;border:none;padding:12px 20px;">
          <span style="font-weight: bold;">店铺类型：<?php echo ($userrow['power'] == 2 ? '<b style="color:#e74c3c;">旗舰版</b>' : '<b style="color:#e74c3c;">专业版</b>') ?></span>
          <?php if ($conf['fenzhan_upgrade'] > 0 && $userrow['power'] == 1) {?>
            <a href="upsite.php" class="btn btn-danger btn-sm" style="border-radius:12px;padding:4px 12px;font-weight:500;">升级站点</a>
          <?php } else {?>
            <a href="./sitelist.php" class="btn btn-danger btn-sm" style="border-radius:12px;padding:4px 12px;font-weight:500;">下级管理</a>
          <?php }?>
        </li>

        <?php if ($conf['fenzhan_expiry'] > 0) {?>
        <li class="list-group-item" style="display:flex;justify-content:space-between;align-items:center;animation:float 3s ease-in-out infinite;border:none;padding:12px 20px;">
          <span style="font-weight: bold;">到期时间：<b style="color:orange;"><?php echo $userrow['endtime'] ?></b></span>
          <a href="renew.php" class="btn btn-primary btn-sm" style="border-radius:12px;padding:4px 12px;font-weight:500;">立即续期</a>
        </li>
        <?php }?>

        <li class="list-group-item" style="animation:float 3s ease-in-out infinite;border:none;padding:12px 20px;">
          当前状态：<?php echo ($conf['fenzhan_expiry'] > 0 && $userrow['endtime'] < $date ? '<b style="color:#e74c3c;">已到期</b>' : '<b style="color:#2ecc71;">正常运行</b>'); ?>
        </li>

      <?php } else { ?>
        <li class="list-group-item" style="animation:float 3s ease-in-out infinite;border:none;padding:12px 20px;text-align:center;">
          你还未开通分站<br/>
          <a onclick="regSite()" class="btn btn-primary btn-sm" style="border-radius:16px;padding:6px 20px;font-weight:500;margin-top:8px;">点此开通分站</a>
        </li>
      <?php } ?>
    </ul>
  </div>
</div>

<style>
@keyframes fadeUp {
  to { opacity:1; transform:translateY(0); }
}
@keyframes float {
  0%,100% { transform:translateY(0); }
  50% { transform:translateY(-3px); }
}
.btn-outline-primary { color:#14b7ff; border:1px solid #14b7ff; background:transparent; }
.btn-outline-primary:hover { background:#14b7ff; color:#fff; }
.btn-primary { color:#fff; border:none; background:linear-gradient(135deg, #14b7ff, #0fa5e9); transition:all 0.3s ease; }
.btn-primary:hover { background:linear-gradient(135deg, #0fa5e9, #0d8ed9); color:#fff; }
.btn-outline-info { color:#1abcfe; border:1px solid #1abcfe; background:transparent; }
.btn-outline-info:hover { background:#1abcfe; color:#fff; }
.btn-info { color:#fff; border:none; background:linear-gradient(135deg, #1abcfe, #18b0e8); transition:all 0.3s ease; }
.btn-info:hover { background:linear-gradient(135deg, #18b0e8, #159dd6); color:#fff; }
.btn-outline-success { color:#2ecc71; border:1px solid #2ecc71; background:transparent; }
.btn-outline-success:hover { background:#2ecc71; color:#fff; }
.btn-success { color:#fff; border:none; background:linear-gradient(135deg, #5aac98, #488c7a); transition:all 0.3s ease; }
.btn-success:hover { background:linear-gradient(135deg, #488c7a, #3d7a68); color:#fff; }
.btn-outline-danger { color:#e74c3c; border:1px solid #e74c3c; background:transparent; }
.btn-outline-danger:hover { background:#e74c3c; color:#fff; }
.btn-danger { color:#fff; border:none; background:linear-gradient(135deg, #e74c3c, #d64533); transition:all 0.3s ease; }
.btn-danger:hover { background:linear-gradient(135deg, #d64533, #c0392b); color:#fff; }
.btn-outline-secondary { color:#95a5a6; border:1px solid #95a5a6; background:transparent; }
.btn-outline-secondary:hover { background:#95a5a6; color:#fff; }
.btn-secondary { color:#fff; border:none; background:linear-gradient(135deg, #95a5a6, #849394); transition:all 0.3s ease; }
.btn-secondary:hover { background:linear-gradient(135deg, #849394, #738182); color:#fff; }
</style>

<!-- 统计图面板 -->
<div class="col-lg-8 col-md-12 col-sm-12">
  <div class="panel" style="background:#fff;border-radius:16px;box-shadow:0 6px 20px rgba(0,0,0,0.08);overflow:hidden;padding:15px;animation:fadeUp 0.8s ease forwards;opacity:0;transform:translateY(30px);">
    <h4 style="margin-bottom:15px;color:#14b7ff;"><i class="fa fa-line-chart"></i> 本月订单与收益趋势</h4>
    
    <span style="font-size: 12px; color: #777777; line-height: 1.4; font-style: italic;">点击【订单数/收益】可切换图表内容</span>
    
    <canvas id="statsChart" height="200"></canvas>
  </div>
</div>

<style>
@keyframes fadeUp {
  to { opacity:1; transform:translateY(0); }
}
@keyframes float {
  0%,100% { transform:translateY(0); }
  50% { transform:translateY(-4px); }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('statsChart').getContext('2d');
const statsChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['昨日', '今日', '本月总'],
        datasets: [
            {
                label: '订单数',
                data: [<?php echo $yesterday_order ?>, <?php echo $today_order ?>, <?php echo $today_all ?>],
                borderColor: '#14b7ff',
                backgroundColor: 'rgba(20,183,255,0.2)',
                tension: 0.4,
                fill: true,
                pointRadius:5,
                pointBackgroundColor:'#fff',
                pointBorderColor:'#14b7ff'
            },
            {
                label: '收益(元)',
                data: [<?php echo round($yesterday_point,2) ?>, <?php echo round($today_point,2) ?>, <?php echo round($point_all,2) ?>],
                borderColor: '#b221ff',
                backgroundColor: 'rgba(178,33,255,0.2)',
                tension: 0.4,
                fill: true,
                pointRadius:5,
                pointBackgroundColor:'#fff',
                pointBorderColor:'#b221ff'
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { labels: { color:'#333' } } },
        scales: {
            x: { grid:{display:false}, ticks:{color:'#666'} },
            y: { beginAtZero:true, grid:{color:'rgba(0,0,0,0.05)'}, ticks:{color:'#666'} }
        }
    }
});
</script>


<div id="mode">

</div>
</div>
</div>
</div>
<script src="<?php echo $cdnpublic ?>layer/2.3/layer.js"></script>
<script src="<?php echo $cdnpublic ?>clipboard.js/1.7.1/clipboard.min.js?<?php echo $jsver ?>"></script>
<script src="<?php echo $cdnpublic ?>toastr.js/latest/toastr.min.js"></script>
<script>
function dopay(type){
	var value=$("input[name='value']").val();
	if(value=='' || value==0){layer.alert('充值金额不能为空');return false;}
	$.get("ajax.php?act=recharge&type="+type+"&value="+value, function(data) {
		if(data.code == 0){
			window.location.href='../other/submit.php?type='+type+'&orderid='+data.trade_no;
		}else{
			layer.alert(data.msg);
		}
	}, 'json');
}
$(document).ready(function(){
var clipboard = new Clipboard('#copy-btn');
clipboard.on('success', function (e) {
	layer.msg('复制成功！', {icon: 1});
});
clipboard.on('error', function (e) {
	layer.msg('复制失败，请长按链接后手动复制', {icon: 2});
});

$("#buy_alipay").click(function(){
	dopay('alipay')
});
$("#buy_qqpay").click(function(){
	dopay('qqpay')
});
$("#buy_wxpay").click(function(){
	dopay('wxpay')
});
$("#recreate_url").click(function(){
	var self = $(this);
	if (self.attr("data-lock") === "true") return;
	else self.attr("data-lock", "true");
	var ii = layer.load(1, {shade: [0.1, '#fff']});
	$.get("ajax.php?act=create_url&force=1", function(data) {
		layer.close(ii);
		if(data.code == 0){
			layer.msg('生成链接成功');
			$("#copy-btn").html(data.url);
			$("#copy-btn").attr('data-clipboard-text',data.url);
		}else{

			layer.alert(data.msg);
		}
		self.attr("data-lock", "false");
	}, 'json');
});
if(window.location.hash=='#chongzhi'){
	$("#userjs").modal('show');
}
	$.ajax({
		type : "GET",
		url : "ajax.php?act=msg",
		dataType : 'json',
		async: true,
		success : function(data) {
			if(data.code==0){
				if(data.count>0){
					$("#tiaosu").text(data.count);
					$("#message_count").addClass('span_position');
					toastr.info('<a href="message.php">您有<b>'+data.count+'</b>条新消息，请注意查收！</a>', '消息提醒');
				}
				if(data.count2>0){
					$("#work_count").addClass('span_position');
					toastr.warning('<a href="workorder.php">您有<b>'+data.count2+'</b>个工单已被管理员回复！</a>', '工单提醒');
				}
				$("#income_today").html(data.income_today+'元');
			}
		}
	});
	$.ajax({
		type : "GET",
		url : "ajax.php?act=create_url",
		dataType : 'json',
		async: true,
		success : function(data) {
			if(data.code == 0){
				$("#copy-btn").html(data.url);
				$("#copy-btn").attr('data-clipboard-text',data.url);
			}else{
				$("#copy-btn").html(data.msg);
			}
		}
	});
});

function regSite(){
	layer.open({
		type: 0,
		title: ['请选择开通模式', 'color:red'],
		content: '<a href="#ktfz" onclick="regSiteShow()" data-toggle="modal" class="btn btn-success btn-block">快速自助开通</a><br><a href="./regsite.php" class="btn btn-primary btn-block">自定义自助开通</a>',
		btn: ['取消开通']
	});
}

function regSiteShow(){
    layer.closeAll();
}

function getFloat(number, n) {
	n = n ? parseInt(n) : 2;
	if (n <= 0) return Math.ceil(number)+'.00';
	number = Math.round(number * Math.pow(10, n)) / Math.pow(10, n);
	return number;
}

function getPrice(){
	$("#ktfz_price").val(getFloat($("#ktfz_kind option:selected").attr('price'),2));
}



function regsite(){
	var kind = $("#ktfz_kind").val() || 1;
	var sitename = $("#sitename").val();
	var ii = layer.load(1, {shade: [0.1, '#fff']});
	$.ajax({
		type : "POST",
		url : "ajax.php?act=ktfz_user&kind="+kind,
		data: "kind="+kind+"&sitename="+sitename,
		dataType : 'json',
		async: true,
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				layer.alert(data.msg,{
					end:function () {
						window.location.reload();
					}
				});
			}
			else if(data.code == -2){
				window.location.href='/user/login.php';
			}
			else{
				layer.alert(data.msg);
			}
		}
	});
}
getPrice();
</script>
<?php include 'footer.php'?>
