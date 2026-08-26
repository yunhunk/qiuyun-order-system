<?php
/**
 * 收支明细
 **/
include "../includes/common.php";
$title = '收支明细';
include './head.php';
if ($isLogin2 == 1) {} else {
    $goto = @getHostUrl();

    exit("<script language='javascript'>window.location.href='./login.php?goto=" . $goto . "';</script>");
}

$thtime          = date("Y-m-d") . ' 00:00:00';
$lastday         = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';
$income_today    = $DB->count("SELECT sum(point) FROM cmy_points WHERE zid= ? AND action='提成' AND addtime> ?", [$userrow['zid'], $thtime]);
$outcome_today   = $DB->count("SELECT sum(point) FROM cmy_points WHERE zid= ? AND action='消费' AND addtime> ?", [$userrow['zid'], $thtime]);
$income_lastday  = $DB->count("SELECT sum(point) FROM cmy_points WHERE zid= ? AND action='提成' AND addtime< ? AND addtime> ?", [$userrow['zid'], $thtime, $lastday]);
$outcome_lastday = $DB->count("SELECT sum(point) FROM cmy_points WHERE zid= ? AND action='消费' AND addtime< ? AND addtime> ?", [$userrow['zid'], $thtime, $lastday]);
?>
<div class="wrapper">
  <div class="col-sm-12">
       <?php if ($userrow['power'] > 0) {?>
       <div class="alert alert-success">
         普通余额说明：指你通过充值可直接消费的普通账户余额，暂不可直接提现；<br>
         提成余额说明：此余额是你客户在你专属网站下单所赚得的提成账户余额，可直接提现，也可转到普通余额用于下单
       </div>
        <?php }?>
       <div class="panel panel-default">
       <div class="panel-heading font-bold" style="background-color: #9999CC;color: white;">收支明细</div>
<table class="table table-bordered">
<tbody>
<tr height="25">
<td align="center"><font color="#808080"><b><span class="glyphicon glyphicon-tint"></span>今日收益</b></br><?php echo round($income_today, 2) ?>元</font></td>
<td align="center"><font color="#808080"><b><i class="glyphicon glyphicon-check"></i>今日消费</b></br></span><?php echo round($outcome_today, 2) ?>元</font></td>
<td align="center"><font color="#808080"><b><span class="glyphicon glyphicon-tint"></span>昨日收益</b></br><?php echo round($income_lastday, 2) ?>元</font></td>
<td align="center"><font color="#808080"><b><i class="glyphicon glyphicon-check"></i>昨日消费</b></br></span><?php echo round($outcome_lastday, 2) ?>元</font></td>
</tr>
</tbody>
</table>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>ID</th><th>类型</th><th>金额</th><th>详情</th><th>时间</th></tr></thead>
          <tbody>
<?php
$numrows = $DB->count("SELECT count(*) from cmy_points WHERE zid= ?", array($userrow['zid']));

$pagesize = 30;
$pages    = ceil($numrows / $pagesize);
$page     = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset   = $pagesize * ($page - 1);

$rs = $DB->query("SELECT * FROM cmy_points WHERE zid= ? AND action!='价格监控' order by id desc limit ?, ?", array($userrow['zid'], $offset, $pagesize));
while ($res = $DB->fetch($rs)) {
    echo '<tr><td><b>' . $res['id'] . '</b></td><td>' . $res['action'] . '</td><td><font color="' . (in_array($res['action'], array('提成', '赠送', '退款', '退回', '充值', '加款')) ? 'red' : 'green') . '">' . priceFormat($res['point']) . '</font></td><td>' . $res['bz'] . '</td><td>' . $res['addtime'] . '</td></tr>';
}
?>
          </tbody>
        </table>
      </div>
<?php
#分页
$PageList = new \core\Page($numrows, $pagesize, 0, $link);
echo $PageList->showPage();
?>
   </div>
  </div>
 </div>
</div>
<?php include 'footer.php'?>