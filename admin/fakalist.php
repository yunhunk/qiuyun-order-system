<?php
include '../includes/common.php';
$title = '分站管理';
checkLogin();

checkAuthority('fakas');
include './head.php';

echo '
    <div class="col-md-12 center-block" style="float: none;padding-top:10px">
';
$my = (isset($_GET['my']) ? $_GET['my'] : null);

$select       = '';
$rs           = $DB->query("SELECT * FROM cmy_class WHERE upcid is null OR upcid=0 order by sort asc");
$select       = '<option value="0">未分类</option>';
$cmy_class[0] = '未分类';
while ($res = $DB->fetch($rs)) {
    $cmy_class[$res['cid']] = $res['name'];
    $select .= '<option value="' . $res['cid'] . '">' . $res['name'] . '</option>';
    $subClass = $DB->count("SELECT count(*) FROM cmy_class WHERE upcid='" . $res['cid'] . "' order by sort asc");
    if ($subClass > 0) {
        $rs2 = $DB->query("SELECT * FROM cmy_class WHERE active=1 and upcid='" . $res['cid'] . "' order by sort asc");
        while ($res2 = $DB->fetch($rs2)) {
            $cmy_class[$res2['cid']] = $res2['name'];
            $select .= '<option value="' . $res2['cid'] . '">----' . $res2['name'] . '</option>';
        }
    }
}

$select2 = '<option value="-1">所有归属</option>';
$select2 .= '<option value="0">主站商品</option>';
$select2 .= '<option value="1">供货商商品</option>';

$sqls = [];

if (isset($_GET['cid']) && $_GET['cid'] > 0) {
    $sqls[] = ' is_curl=4 and cid=' . $_GET['cid'];
    $link   = '&cid=' . $_GET['cid'];
}

$type = input('type');

if ($type != '' && $type > -1) {
    if ($type == 0) {
        $sqls[] = ' `zid`<=1 ';
    } else {
        $sqls[] = ' `zid`>1 ';
    }
    $link = '&type=' . $_GET['type'];
} else {
    $type = -1;
}

if ($sqls) {
    $sql = trim(implode(' AND ', $sqls));
} else {
    $sql = ' is_curl=4';
}

$numrows = $DB->count('SELECT count(*) from cmy_tools where ' . $sql);
echo '
    <div class="block">
    <div class="block-title"><h3 class="panel-title">虚拟商品库存管理</h3></div>
    <div class="alert alert-info">系统共有 <b>' . $numrows . '</b> 个库存<br/>
    </div>
    <form method="GET" class="form-inline">
    <div class="form-group">
    <select name="cid" class="form-control" default="';
echo intval($_GET['cid']);
echo '">';
echo $select;
echo '</select>
    <select name="type" class="form-control" default="';
echo $type;
echo '">';
echo $select2;
echo '</select>
    <button type="submit" class="btn btn-success">查询筛选</button>&nbsp;
    <a href="./fakakms.php?my=add" class="btn btn-primary">添加卡密</a>&nbsp;
    </div>
    </form>
    ';
echo ' <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>ID</th><th>商品归属</th><th>商品名称</th><th>剩余卡密</th><th>已售出</th><th>状态</th><th>操作</th></tr></thead>
          <tbody>
';
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
$rs     = $DB->query('SELECT * FROM cmy_tools WHERE' . $sql . ' order by tid desc limit ' . $offset . ',' . $pagesize);
while ($res = $DB->fetch($rs)) {
    $sendNum = $DB->count("SELECT count(*) FROM cmy_faka WHERE tid='" . $res['tid'] . "' and orderid>1");
    $haveNum = $DB->count("SELECT count(*) FROM cmy_faka WHERE tid='" . $res['tid'] . "' and orderid<1");
    echo '
    <tr>
    <td><input type="checkbox" name="checkbox[]" id="list1" value="' . $res['tid'] . '" onClick="unselectall1()"><b>' . $res['tid'] . '</b></td>
    <td>' . ($res['zid'] > 0 ? '<a class="a-blur" href="./master.user.php?zid=' . $res['zid'] . '">供货商' . $res['zid'] . '</a>' : '主站商品') . '</td>
    <td>' . $res['name'] . '</td>
    <td>' . $haveNum . '</td><td>' . $sendNum . '</td><td><span class="btn btn-xs btn-' . ($res['active'] == 1 ? 'success' : 'warning') . '" onclick="setActive(' . $res['tid'] . ',' . $res['active'] . ')">' . ($res['active'] == 1 ? '上架中' : '已下架') . '</span></td><td><a href="./fakakms.php?tid=' . $res['tid'] . '" class="btn btn-info btn-xs">查看卡密</a>&nbsp;<a href="./fakakms.php?my=add&tid=' . $res['tid'] . '" class="btn btn-success btn-xs">加卡</a>&nbsp;<a href="./list.php?tid=' . $res['tid'] . '" class="btn btn-warning btn-xs">订单</a>&nbsp;<a href="./shopedit.php?my=delete&tid=' . $res['tid'] . '" class="btn btn-xs btn-danger" onclick="return confirm(\'你确实要删除此商品吗？\');">删除</a></td></tr>
';
}
echo '          </tbody>
        </table>
      </div>

';

# 分页
$PageList = new \core\Page($numrows, $pagesize, 0, $link);
echo $PageList->showPage();

echo "    </div>
  </div>
<script>

function setActive(tid,active) {
    \$.ajax({
        type : 'GET',
        url : 'ajax.php?act=setTools&tid='+tid+'&active='+active,
        dataType : 'json',
        success : function(data) {
            window.location.reload();
        },
        error:function(data){
            layer.msg('服务器错误');
            return false;
        }
    });
}

</script>";

include_once 'footer.php';
