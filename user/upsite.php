<?php
/**
 * 自助升级站点
 **/
include "../includes/common.php";
$title = '自助升级站点';
include './head.php';
if ($isLogin2 == 1) {} else {
    $goto = @getHostUrl();

    exit("<script language='javascript'>window.location.href='./login.php?goto=" . $goto . "';</script>");
}

?>
<div class="wrapper">
<?php
if (!$conf['fenzhan_upgrade']) {
    showmsg('当前站点未开启此功能');
}

if ($userrow['power'] == 0) {
    showmsg('你没有权限使用此功能！', 3);
    die;
}
$price = getUpdateSitePirce(2, $userrow['zid']);
if ($userrow['upzid'] > 1) {
    $upsite = $DB->get_row("SELECT zid,power,ktfz_price2 from pre_site where zid= ? limit 1", [$userrow['zid']]);
    if ($upsite && $upsite['power'] == 2) {
        $tc_point = sprintf("%.2f", $price - $conf['fenzhan_cost2']);
    }
}
if ($_GET['act'] == 'submit') {
    if ($price > getUserRmb()) {
        exit("<script language='javascript'>alert('你的余额不足，请充值！');window.location.href='./index.php#chongzhi';</script>");
    } elseif ($price < 0) {
        exit("<script language='javascript'>alert('升级价格配置异常，请联系客服');history.go(-1);</script>");
    }
    $DB->query("UPDATE `pre_site` set `power`=2,`money`=`money`- ? where `zid`= ?", array($price, $userrow['zid']));
    addPointLogs($userrow['zid'], $price, '消费', '升级到旗舰版分站，当前余额' . (getUserRmb() - $price) . '元');
    if (isset($tc_point) && $tc_point > 0) {
        $DB->query("UPDATE `pre_site` set `money`=`money`+ ? where `zid`= ?", array($tc_point, $upsite['zid']));
        addPointLogs($upsite['zid'], $tc_point, '提成', '你网站的用户升级分站获得' . $tc_point . '元提成');
    }
    exit("<script language='javascript'>alert('恭喜你成功升级站点版本！');window.location.href='index.php';</script>");
}
?>
<div class="col-sm-6">
  <div class="panel panel-default">
    <div class="panel-heading font-bold" style="background-color: #9999CC;color: white;">旗舰版介绍</div>
      <ul class="list-group no-radius">
              <li class="list-group-item">
                旗下分站管理功能,赚取下级分站提成
              </li>
              <li class="list-group-item">
                可自定义分站开通价格,下级商品成本价格
              </li>
              <li class="list-group-item">
                赠送专属APP客户端
              </li>
              <li class="list-group-item">
                更多特权开发中...
              </li>
            </ul>
  </div>
</div>
<div class="col-sm-6">
  <div class="panel panel-default">
  <div class="panel-heading font-bold" style="background-color: #9999CC;color: white;">自助升级站点<span class="pull-right">余额：0.02元</span></div>
    <div class="panel-body">
      <div class="form-group">
        <div class="input-group">
          <div class="input-group-addon">
            升级版本
          </div>
          <select name="kind" class="form-control"><option value="2">旗舰版</option></select>
        </div>
      </div>
      <div class="form-group">
        <div class="input-group">
          <div class="input-group-addon">
            升级所需
          </div>
          <input name="need" class="form-control" value="<?php echo $price; ?>" disabled="">
          <div class="input-group-addon">
            元
          </div>
        </div>
      </div>
      <a class="btn btn-primary btn-block" href="?act=submit">立即升级</a>
    </div>
  </div>
   </div>
  </div>

  <?php include 'footer.php';?>