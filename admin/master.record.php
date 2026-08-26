<?php
/**
 * 收支明细
 **/
include "../includes/common.php";
checkLogin();
$title = '商户收支明细';
$act   = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
if ($act == "delRecord") {
    $id  = intval($_POST['id']);
    $sql = "DELETE FROM `pre_master_points` WHERE id='" . $id . "'";
    if ($DB->query($sql)) {
        exit('{"code":0,"msg":"删除成功！"}');
    } else {
        exit('{"code":-1,"msg":"删除失败！' . $DB->error() . '"}');
    }
}

checkAuthority('super');
include './head.php';

echo '<div class="col-md-12 center-block" style="float: none;">';

if (isset($_GET['zid']) && $_GET['zid']) {
    $zid  = input('get.zid', 1);
    $sql  = " zid='" . $zid . "'";
    $link = '&zid=' . $zid;
} elseif (isset($_GET['orderid'])) {
    $orderid = trim(addslashes($_GET['orderid']));
    $sql     = " orderid='" . $orderid . "'";
} elseif (isset($_GET['kw']) && $_GET['kw'] != "") {
    $kw  = trim(addslashes($_GET['kw']));
    $sql = " (action='" . $kw . "' or id='" . $kw . "' or zid='" . $kw . "' or orderid='" . intval($kw) . "')";
} else {
    $zid = 0;
    $sql = " action!='价格监控'";
}
$thtime          = date("Y-m-d") . ' 00:00:00';
$lastday         = date("Y-m-d", strtotime("-1 day")) . ' 00:00:00';
$income_today    = $DB->count("SELECT sum(`point`) FROM pre_master_points WHERE `action`='提成' AND {$sql} AND addtime>'{$thtime}'");
$outcome_today   = $DB->count("SELECT sum(`point`) FROM pre_master_points WHERE (`action`='消费' OR `action`='缴纳') AND{$sql} AND addtime>'$thtime'");
$income_lastday  = $DB->count("SELECT sum(`point`) FROM pre_master_points WHERE `action`='提成' AND{$sql} AND addtime<'$thtime' AND addtime>'{$lastday}'");
$outcome_lastday = $DB->count("SELECT sum(`point`) FROM pre_master_points WHERE (`action`='消费' OR `action`='缴纳') AND{$sql} AND addtime<'$thtime' AND addtime>'$lastday'");

$numrows = $DB->count("SELECT count(*) from `pre_master_points` WHERE{$sql}");

echo '
<style>
.table-bz{word-wrap: break-word;}
</style>
<div class="row">
  <div class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top: 10px;">
<div class="block">
     <div class="block-title"><h2>' . ($zid > 0 ? "供货商ZID:<b>" . $zid . "</b> " : "全部供货商") . '收支明细</h2></div>
      <div class="table-responsive">
<table class="table table-bordered">
<tbody>
<tr height="25">
<td align="center"><font color="#808080"><b><span class="glyphicon glyphicon-tint"></span>今日收益</b></br>' . round($income_today, 2) . '元</font></td>
<td align="center"><font color="#808080"><b><i class="glyphicon glyphicon-check"></i>今日消费</b></br></span>' . round($outcome_today, 2) . '元</font></td>
<td align="center"><font color="#808080"><b><span class="glyphicon glyphicon-tint"></span>昨日收益</b></br>' . round($income_lastday, 2) . '元</font></td>
<td align="center"><font color="#808080"><b><i class="glyphicon glyphicon-check"></i>昨日消费</b></br></span>' . round($outcome_lastday, 2) . '元</font></td>
</tr>
</tbody>
</table>
      <form method="get">
    <div class="input-group xs-mb-15">
      <input type="text" placeholder="请输入要搜索明细的供货商ID或明细类型！" name="kw"
           class="form-control text-center"
           required>
      <span class="input-group-btn">
      <button type="submit" class="btn btn-primary">立即搜索</button>
      </span>
    </div>
  </form>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>ID</th><th>订单ID</th><th>供货商ID</th><th>类型</th><th>金额</th><th style="max-width:300px">详情</th><th>时间</th><th>操作</th></tr></thead>
          <tbody>';

$pagesize = 30;
$pages    = ceil($numrows / $pagesize);
$page     = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset   = $pagesize * ($page - 1);

$rs = $DB->query("SELECT * FROM pre_master_points WHERE{$sql} order by id desc limit $offset,$pagesize");
while ($res = $DB->fetch($rs)) {
    echo '<tr><td><b>' . $res['id'] . '</b></td><td>' . ($res['orderid'] > 0 ? '<a href="./list.php?id=' . $res['orderid'] . '" class="btn btn-success btn-xs">' . $res['orderid'] . '</a>' : '——') . '</td><td><a href="master.user.php?zid=' . $res['zid'] . '">' . $res['zid'] . '</a></td><td>' . $res['action'] . '</td><td><font color="' . (in_array($res['action'], array('提成', '赠送', '退款', '退回', '解冻', '充值', '加款')) ? 'red' : 'green') . '">' . $res['point'] . '</font></td><td class="table-bz"><div class="">' . $res['bz'] . '</div></td><td>' . $res['addtime'] . '</td><td><a onclick="delRecord(' . $res['id'] . ')" class="btn btn-danger btn-sm">删除</a></td></tr>';
}

echo '</tbody>
        </table>
      </div>';

#分页
$pageList = new \core\Page($numrows, $pagesize, 0, $link);
echo $pageList->showPage();
#分页

echo ' </div>
  </div>
 </div>
</div>
<script>
function delRecord(id){
    var ii = layer.load(2, {shade:[0.1,\'#fff\']});
    $.ajax({
      type : \'POST\',
      url : \'?act=delRecord\',
      data: "id="+id,
      dataType : \'json\',
      success : function(data) {
        layer.close(ii);
        if(data.code == 0){
          layer.msg(data.msg);
          setTimeout(window.location.reload(),1000);
        }
        else{
          layer.alert(data.msg);
        }
      },
      error:function(data){
        layer.close(ii);
        layer.msg(\'服务器错误\');
        return false;
      }
    });
}
</script>
';

include_once 'footer.php';
