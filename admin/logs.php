<?php

/**
 * 对接下单日志
 **/
include "../includes/common.php";
$title = '对接下单日志';
checkLogin();

include './head.php';

if (isset($_GET['orderid']) && $_GET['orderid'] > 0) {

    $sql = " orderid='" . $_GET['orderid'] . "'";
} elseif (isset($_GET['id']) && $_GET['id'] > 0) {
    $sql = " orderid='" . $_GET['id'] . "'";
} else {
    $sql = " 1";
}

echo '<div class="col-xs-12 col-sm-12 col-lg-12 center-block" style="float: none;padding-top:10px;">
 <div class="block">
    <div class="block-title">
      <h3 class="panel-title">' . $title . '</h3>
    </div>
       <div class="table-responsive">
        <table class="table table-striped" style="word-break: break-all;">
          <thead><th>订单编号</th><th>Id</th><th>站点ID</th><th>类型</th><th>详情</th><th>结果</th><th>时间</th></thead>
          <tbody>';

$numrows  = $DB->count("SELECT count(*) from cmy_logs WHERE " . $sql);
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

$rs = $DB->query("SELECT * FROM cmy_logs WHERE {$sql} order by id desc limit $offset,$pagesize");
while ($res = $DB->fetch($rs)) {
    echo '<tr><td style="min-width:90px;">' . ($res['orderid'] > 0 ? '<a href="list.php?id=' . $res['orderid'] . '" class="btn btn-success btn-xs">' . $res['orderid'] . '</a>' : '——') . '</td><td>' . $res['id'] . '<td><a href="sitelist.php?zid=' . $res['zid'] . '">' . $res['zid'] . '</td><td>' . $res['action'] . '</td><td style="">' . htmlspecialchars($res['param']) . '</td><td>' . $res['result'] . '</td><td style="min-width:170px;">' . $res['addtime'] . '</td></tr>';
}

echo ' </tbody>
        </table>
      </div>';

#分页
$pageList = new \core\Page($numrows, $pagesize, 0, $link);
echo $pageList->showPage();

echo '</div>';
