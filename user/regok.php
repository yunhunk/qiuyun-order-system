<?php
/**
 * 开通成功页面
 **/
include "../includes/common.php";
$title = '开通分站成功';

include './head2.php';
?>
<style>
img.logo{width:14px;height:14px;margin:0 5px 0 3px;}
</style>
  <nav class="navbar navbar-fixed-top navbar-default">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
          <span class="sr-only">导航按钮</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="/user/" style="font-size: 15px;">云商城后台管理</a>
      </div><!-- /.navbar-header -->
      <div id="navbar" class="collapse navbar-collapse">
        <ul class="nav navbar-nav navbar-right">
          <li>
            <a href="./login.php"><span class="glyphicon glyphicon-user"></span> 登陆</a>
          </li>
          <li class="active"><a href="./reg.php"><span class="glyphicon glyphicon-globe"></span> 自助开通</a></li>
        </ul>
      </div><!-- /.navbar-collapse -->
    </div><!-- /.container -->
  </nav><!-- /.navbar -->
  <div style="padding-top:70px;">
    <div class="col-xs-12 col-sm-10 col-md-6 center-block" style="float: none;">
<?php
$weburl = str_replace('user/', '', $weburl);
if (isset($_GET['orderid'])) {
    $orderid = input('get.orderid', 1);
    $srow    = $DB->get_row("SELECT * FROM cmy_pay WHERE trade_no= ? limit 1", [$orderid]);
    if (!$srow || $srow['status'] == 0 || $srow['tid'] != -2) {
        showmsg('订单不存在或未完成支付！', 3);
    }

    if (!$cookiesid || $srow['userid'] != $cookiesid && $srow['userid'] != $userrow['zid']) {
        showmsg('仅限查看自己开通的分站信息', 3);
    }

    $input = explode('|', $srow['input']);
    $type  = $input[0];
    if ($type == 'update') {
        $zid = intval($input[1]);
        $row = $DB->get_row("SELECT * FROM `pre_site` WHERE zid= ? limit 1", [$zid]);
        if (!is_array($row)) {
            require_once SYSTEM_ROOT . 'ajax.class.php';
            try {
                processOrderAll($srow);
                $row = $DB->get_row("SELECT * FROM `pre_site` WHERE zid= ? limit 1", [$zid]);
            } catch (\Exception $e) {
                showmsg('分站开通失败，' . $DB->error() . '！请联系网站客服', 3);
            }
        }
        $kind    = intval($row['power']);
        $fzurl   = $row['siteurl'];
        $user    = $row['user'];
        $pwd     = $row['pwd'];
        $name    = $row['sitename'];
        $qq      = $row['qq'];
        $endtime = $row['endtime'];
    } else {
        $row     = $DB->get_row("SELECT * FROM `pre_site` WHERE user= ? limit 1", [$user]);
        $kind    = intval($input[1]);
        $fzurl   = daddslashes($input[2]);
        $user    = daddslashes($input[3]);
        $pwd     = daddslashes($input[4]);
        $name    = daddslashes($input[5]);
        $qq      = daddslashes($input[6]);
        $endtime = daddslashes($input[7]);
    }

    if (conf('fenzhan_html') == 1) {
        $DB->exec("UPDATE SET `pre_site` SET `modal`='" . $conf['modal'] . "',`bottom`='" . $conf['bottom'] . "',`alert`='" . $conf['alert'] . "' WHERE zid= ? limit 1", [$zid]);
    }

    $url = parse_site_url($fzurl, true);
    //fz_newSetPrice($row['zid'], $row);
} elseif (isset($_GET['zid'])) {
    $zid = intval($_GET['zid']);
    $row = $DB->get_row("SELECT * FROM cmy_site WHERE zid= ? limit 1", [$zid]);
    if (!$row || !$_SESSION['newzid'] || $_SESSION['newzid'] != $zid) {
        showmsg('你所开通的分站信息不存在！', 3);
    }

    $kind    = intval($row['power']);
    $fzurl   = $row['siteurl'];
    $user    = $row['user'];
    $pwd     = $row['pwd'];
    $name    = $row['sitename'];
    $qq      = $row['qq'];
    $endtime = $row['endtime'];
    $url     = parse_site_url($fzurl, true);

    if (conf('fenzhan_html') == 1) {
        $DB->exec("UPDATE SET `pre_site` SET `modal`='" . $conf['modal'] . "',`bottom`='" . $conf['bottom'] . "',`alert`='" . $conf['alert'] . "' WHERE zid= ? limit 1", [$zid]);
    }

    //fz_newSetPrice($row['zid'], $row);
} else {
    showmsg('缺少参数', 4);
}
?>
        <div class="panel panel-primary table-responsive">
            <div class="panel-heading">
                开通分站成功
            </div>
            <div class="panel-body">
            <div class="alert alert-success">
                恭喜你分站开通成功，请牢记以下信息
            </div>
        <li class="list-group-item"><b>分站网址：</b><a href="<?php echo $url ?>" target="_blank"><?php echo $url ?></a></li>
                <li class="list-group-item"><b>分站管理后台：</b><a href="<?php echo $url ?>user/" target="_blank"><?php echo $url ?>user/</a></li>
                <li class="list-group-item"><b>管理员用户名：</b><?php echo $user ?></a></li>
                <li class="list-group-item"><b>管理员密码：</b><?php echo $pwd ?></a></li>
            </div>
        </div>
    </div>
</div>