<?php
/**
 * 用户管理
 **/
include "../includes/common.php";
$title = '用户管理';
include './head.php';
if ($isLogin2 == 1) {} else {
    $goto = @getHostUrl();

    exit("<script language='javascript'>window.location.href='./login.php?goto=" . $goto . "';</script>");
}

?>
<div class="wrapper">
<div class="col-sm-12">
    <div class="panel panel-default">
<div class="modal fade" align="left" id="search" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">搜索用户</h4>
      </div>
      <div class="modal-body">
      <form action="userlist.php" method="GET">
<input type="text" class="form-control" name="kw" placeholder="请输入用户名或QQ"><br/>
<input type="submit" class="btn btn-primary btn-block" value="搜索"></form>
</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<?php
if ($userrow['power'] < 1) {
    showmsg('你没有权限使用此功能！', 3);
}
$my = isset($_GET['my']) ? $_GET['my'] : null;

$pagesize = 30;
$pages    = ceil($numrows / $pagesize);
$page     = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset   = $pagesize * ($page - 1);

$numrows = $DB->count("SELECT count(*) from cmy_site where upzid= ? and power=0", array($userrow['zid']));
if (isset($_GET['zid'])) {
    $zid = intval($_GET['zid']);
    $rs  = $DB->query("SELECT * FROM cmy_site WHERE zid= ? and upzid= ? and power=0 order by zid desc limit ?, ?", array($zid, $userrow['zid'], $offset, $pagesize));
} elseif (isset($_GET['kw'])) {
    $kw = daddslashes($_GET['kw']);
    $rs = $DB->query("SELECT * FROM cmy_site WHERE (user= ? or qq= ?) and upzid= ? and power=0 order by zid desc limit ?, ?", array($kw, $kw, $userrow['zid'], $offset, $pagesize));
} else {
    $rs = $DB->query("SELECT * FROM cmy_site WHERE upzid= ? and power=0 order by zid desc limit ?, ?", array($userrow['zid'], $offset, $pagesize));
}

$con = '你共有 <b>' . $numrows . '</b> 个下级用户<br/><a href="#" data-toggle="modal" data-target="#search" id="search" class="btn btn-success">搜索</a>';

echo '<div class="alert" style="background-color: #9999CC;color: white;">';
echo $con;
echo '</div>';

?>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>UID</th><th>用户名</th><th>QQ</th><?php echo $conf['fenzhan_readmoney'] == 1 ? '<th>余额</th>' : ''; ?><th>注册时间</th><th>操作</th></tr></thead>
          <tbody>
<?php

while ($res = $DB->fetch($rs)) {
    echo '<tr><td><b>' . $res['zid'] . '</b></td><td>' . $res['user'] . '</td><td>' . $res['qq'] . '</td>' . ($conf['fenzhan_readmoney'] == 1 ? '<td>' . $res['money'] . '</td>' : '') . '<td>' . $res['addtime'] . '</td><td><a href="./userlist.php?my=edit&zid=' . $res['zid'] . '" class="btn btn-info btn-xs" disabled>编辑</a>&nbsp;<a href="./userlist.php?my=delete&zid=' . $res['zid'] . '" class="btn btn-xs btn-danger" onclick="return confirm(\'你确实要删除此站点吗？\');" disabled>删除</a></td></tr>';
}
?>
          </tbody>
        </table>
      </div>
<?php

#分页
$pageList = new \core\Page($numrows, $pagesize, 0, $link);
echo $pageList->showPage();
?>
    </div>
  </div>

  <?php include 'footer.php'?>