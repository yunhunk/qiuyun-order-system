<?php
/**
 * 分站排行
 **/
include "../includes/common.php";
$title = '今日分站排行';
include './head.php';
if ($isLogin3 == 1) {} else {
    $goto = @getHostUrl();
    @header('Location: ' . $weburl . 'login.php?goto=' . $goto);
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

?>
<div class="wrapper">
    <div class="col-lg-12">
<?php
if ($masterrow['power'] == 0) {
    showmsg('你没有权限使用此功能！', 3);
}

if ($conf['fenzhan_rank_open'] != 1) {
    showmsg('分站排行功能未开放！', 3);
}

$thtime  = date("Y-m-d") . ' 00:00:00';
$lastday = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';
$sql     = "";
if ($conf['fenzhan_rank_user'] != 1) {
    $sql = " and (select c.power from cmy_site as c where a.zid=c.zid)>0";
}
if ($_GET['last'] == 1) {
    $sql    = "SELECT a.zid,(select b.sitename from cmy_site as b where a.zid=b.zid) as sitename,count(id) as count,sum(money) as money from cmy_orders as a where addtime> ? and addtime< ? and zid>1 and (select c.is_rank from cmy_tools as c where a.tid=c.tid)=1" . $sql . " group by zid order by money desc limit 10";
    $addstr = '已发放奖励';
    $rs     = $DB->query($sql, array($lastday, $thtime));
} else {
    $sql    = "SELECT a.zid,(select b.sitename from cmy_site as b where a.zid=b.zid) as sitename,count(id) as count,sum(money) as money from cmy_orders as a where addtime> ? and zid>1 and (select c.is_rank from cmy_tools as c where a.tid=c.tid)=1" . $sql . " group by zid order by money desc limit 10";
    $addstr = '预计发放奖励';
    $rs     = $DB->query($sql, array($thtime));
}

?>
<div class="panel panel-default">
     <div class="panel-heading font-bold" style="background-color: #9999CC;color: white;">分站排行</div>
<ul class="nav nav-tabs">
<li class="<?php echo $_GET['last'] != 1 ? 'active' : null; ?>" style="width:50%"><a href="rank.php"><center>今日销售排行</center></a></li>
<li class="<?php echo $_GET['last'] == 1 ? 'active' : null; ?>" style="width:50%"><a href="rank.php?last=1"><center>昨日销售排行</center></a></li>
</ul>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th class="text-center">排名</th><th class="text-center">站点ID</th><th class="text-center">站点名称</th><th class="text-center">订单数</th><th class="text-center">销售金额</th><?php echo '<th class="text-center">' . $addstr . '</th>'; ?></tr></thead>
          <tbody>
<?php

$i = 1;
while ($res = $DB->fetch($rs)) {
    echo '<tr><td class="text-center"><span class="badge badge-danger">' . $i . '</span></td><td class="text-center"><b>' . $res['zid'] . '</b></td><td class="text-center">' . $res['sitename'] . '</td><td class="text-center">' . $res['count'] . '</td><td class="text-center">' . $res['money'] . '</td>';
    if ($conf['fenzhan_rank_limit'] > 0) {
        if ($i <= $conf['fenzhan_rank_limit']) {
            $reward = round($res['money'] * $conf['fenzhan_rank_rate'] / 100, 2);
        } else {
            $reward = 0;
        }

        echo '<td class="text-center">' . $reward . '</td>';
    }
    echo '</tr>';
    $i++;
}
?>
          </tbody>
        </table>
      </div>
<div class="panel-footer text-center" <?php if (!$conf['fenzhan_rank_limit']) {?>style="display:none;"<?php }?>>
<span class="glyphicon glyphicon-info-sign"></span>&nbsp;站长排行榜奖励会在每天0点后发放前一天的，奖励对象为销量排行榜<span style="color:red;font-size: 1.6rem">前<?php echo $conf['fenzhan_rank_limit'] ?>名</span>，当前额外提成奖励为销量的 <span style="color:red;font-size: 1.6rem"><?php echo $conf['fenzhan_rank_rate'] ?>%</span>！
</div>
    </div>
 </div>
</div>

</html>