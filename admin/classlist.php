 <?php
include '../includes/common.php';
checkLogin();
$title = '分类管理';
$my    = isset($_GET['my']) ? $_GET['my'] : null;
if ($my == 'classimg') {
    $title2 = '修改分类图片';
} elseif ($my == 'sub_class') {
    $row = $DB->get_row('select * from pre_class where cid=\'' . intval($_GET['cid']) . '\' limit 1');
    if (!$row) {
        showmsg("该分类不存在！", 3);
    }

    $title2 = '<a href="./classlist.php">分类首页</a>&nbsp;/&nbsp;<a href="./classlist.php?cid=' . intval($_GET['cid']) . '">' . $row['name'] . '</a>的下级分类管理';
} else {
    $title2 = '分类管理';
}
checkAuthority('class');

include_once "head.php";

echo '  <div class="col-sm-12 col-md-12  col-lg-12 center-block" style="float: none;padding-top:10px ">
<div class="block">
    <div class="block-title">
    <h2>' . $title2 . '</h2> &nbsp;<span onclick="javascript:listTable()" data-tip="点击刷新分类列表" class="btn btn-success btn-xs" title="刷新分类列表">刷新分类</span><a href="./classlist2.php"  class="btn btn-info btn-xs" title="新版分类管理">新版分类管理</a>
    </div>
      <div id="listTable"></div>
    </div>
  </div>
</div>
<script src="./assets/js/classlist.js' . $jsver . '"></script>
';
include_once 'footer.php';
