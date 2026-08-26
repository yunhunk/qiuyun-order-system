<?php
/**
 * 社区/**对接日志
 **/
include "../includes/common.php";
$title = '短信验证码发送日志';
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
          <thead><th style="min-width: 80px;">Id</th><th>站点ID</th><th style="min-width: 80px;">类型</th><th>手机号</th><th>验证码</th><th style="max-width: 700px;">详情</th><th>ip</th><th style="min-width:170px;">时间</th></thead>
          <tbody>';

$numrows  = $DB->count("SELECT count(*) from pre_codelog WHERE " . $sql);
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
$rs     = $DB->query("SELECT * FROM pre_codelog WHERE " . $sql . " order by id desc limit $offset,$pagesize");
while ($res = $DB->fetch($rs)) {
    if (is_numeric($res['userid']) && (int) $res['userid'] > 0) {
        $res['zid'] = $res['userid'];
    } else {
        $res['zid'] = '——';
    }
    echo '<tr><td>' . $res['id'] . '<td><a href="sitelist.php?zid=' . $res['zid'] . '">' . $res['zid'] . '</td><td>' . $res['name'] . '</td><td>' . $res['tel'] . '</td><td>' . $res['code'] . '</td><td style="max-width: 700px;">' . $res['bz'] . '</td><td>' . $res['ip'] . '</td><td style="min-width:170px;">' . $res['addtime'] . '</td></tr>';
}

echo ' </tbody>
        </table>
      </div>';

#分页
$pageList = new \core\Page($numrows, $pagesize, 0, $link);
echo $pageList->showPage();

echo '</div>';

include_once 'footer.php';
