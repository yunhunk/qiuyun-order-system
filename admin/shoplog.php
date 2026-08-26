<?php
/**
 * 商品日志
 **/
include "../includes/common.php";
checkLogin();
$title = '商品日志';
checkAuthority('super');
include './head.php';

echo '<div class="col-md-12 center-block" style="float: none;">';

if (isset($_GET['tid'])) {
    $tid  = intval($_GET['tid']);
    $sql  = " `tid`='" . $tid . "'";
    $link = '&tid=' . $tid;
} else {
    $sql = " 1";
}

$numrows = $DB->count("SELECT count(*) from `pre_tools_log` WHERE {$sql}");

echo '<div class="row">
  <div class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top: 10px;">
<div class="block">
     <div class="block-title"><h2>商品变动日志</h2></div>
      <div class="table-responsive">
        <form method="get">
      <div class="input-group xs-mb-15">
        <input type="text" placeholder="请输入商品ID" name="tid"

             class="form-control text-center"
             required>
        <span class="input-group-btn">
        <button type="submit" class="btn btn-primary">立即搜索</button>
        </span>
      </div>
    </form>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>-</th><th>商品ID</th><th>商品名称</th><th>类型</th><th>变动前</th><th>变动后</th><th>详情</th><th>时间</th></tr></thead>
          <tbody>';

$pagesize = 30;
$pages    = ceil($numrows / $pagesize);
$page     = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset   = $pagesize * ($page - 1);
$rs       = $DB->query("SELECT * FROM `pre_tools_log` WHERE{$sql} order by id desc limit $offset,$pagesize");
while ($res = $DB->fetch($rs)) {
    if ($res['after'] > $res['before']) {
        $icon = '&nbsp;<i class="fa fa-long-arrow-up" style="color:red"></i>';
    } elseif ($res['after'] < $res['before']) {
        $icon = '&nbsp;<i class="fa fa-long-arrow-up" style="color:green"></i>';
    }
    echo '<tr><td><b>' . $res['id'] . '</b></td><td><a href="shoplist.php?tid=' . $res['tid'] . '">' . $res['tid'] . '</a></td><td>' . $res['name'] . '</td><td>' . $res['action'] . '</td><td>' . $res['before'] . '</td><td>' . $res['after'] . $icon . '</td><td>' . $res['desc'] . '</td><td>' . date("Y-m-d H:i:s", $res['addtime']) . '</td></tr>';
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
';

include_once 'footer.php';
