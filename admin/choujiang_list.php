<?php
/**
 * 抽奖记录
 **/
include "../includes/common.php";
$title = '抽奖记录';
checkLogin();
checkAuthority('super');

include './head.php';

if (isset($_GET['orderid']) && $_GET['orderid'] > 0) {

    $sql = " orderid='" . $_GET['orderid'] . "'";
} else {
    $sql = " 1";
}
$name = "抽奖记录";

echo '<div class="col-xs-12 col-sm-12 col-lg-12 center-block" style="float: none;padding-top:10px;">
 <div class="block">
    <div class="block-title">
      <h3 class="panel-title">' . $name . '</h3>
    </div>
       <div class="table-responsive">
        <table class="table table-striped" style="word-break: break-all;">
          <thead><tr><th>站点ID</th><th>中奖商品ID</th><th>奖品名称</th><th>ip</th><th>订单号</th><th>中奖时间</th></tr></thead>
          <tbody>';

$numrows  = $DB->count("SELECT count(*) from pre_giftlog WHERE " . $sql);
$pagesize = 30;
$pages    = intval($numrows / $pagesize);
if ($numrows % $pagesize) {
    $pages++;
}
if (isset($_GET['page'])) {
    $page = intval($_GET['page']);
} else {
    $page = 1;
}
$offset = $pagesize * ($page - 1);

$rs = $DB->query("SELECT * FROM pre_giftlog WHERE " . $sql . " order by id desc limit $offset,$pagesize");
while ($res = $DB->fetch($rs)) {
    $tool = $DB->get_row("select * FROM pre_gift WHERE id='" . $res['gid'] . "'");

    echo '<tr><td>' . $res['zid'] . '</td><td><a href="shoplist.php?tid=' . $res['tid'] . '">' . ($tool['tid'] > 0 ? $tool['tid'] : '0') . '</a></td><td>' . ($tool['name'] ? $tool['name'] : '未中奖') . '</td><td>' . $res['ip'] . '</td><td>' . $res['tradeno'] . '</td><td>' . $res['addtime'] . '</td></tr>';
}
echo ' </tbody>
        </table>
      </div>';

#分页
$pageList = new \core\Page($numrows, $pagesize, 0, $link);
echo $pageList->showPage();
echo '</div>';

include_once 'footer.php';
