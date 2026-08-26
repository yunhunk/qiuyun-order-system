<?php

/**

 * 订单列表

 **/

include "../includes/common.php";

checkLogin();

checkAuthority('works');

function display_zt($zt, $id = 0)
{
    if ($zt == 1) {
        return '<font color=green>已完成</font>';
    } elseif ($zt == 2) {
        return '<font color=orange>处理中</font>';
    } else {
        return '<font color=blue>待处理</font>';
    }

}

if (isset($_GET['status'])) {
    $status = intval($_GET['status']);
    $sql    = " `status`='{$status}'";
    $link   = '&status=' . $status;
} else {
    $sql = " 1";
}

?>

	  <form name="form1" id="form1">

	  <div class="table-responsive">

<?php echo $con ?>

        <table class="table table-striped table-bordered table-vcenter">

         <thead><tr><th>ID</th><th>站点ID</th><th width="85px">商品ID</th><th width="85px">供货商</th><th>类型</th><th>订单号</th><th>问题描述</th><th>联系方式</th><th>状态</th><th>操作时间</th><th>操作</th></tr></thead>
          <tbody>

<?php

$numrows = $DB->count("SELECT count(*) from cmy_workorder WHERE {$sql}");

$pagesize = $conf['index_pagesize'] ? $conf['index_pagesize'] : 30;

$pages = ceil($numrows / $pagesize);

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

$offset = $pagesize * ($page - 1);

$rs = $DB->query("SELECT * FROM cmy_workorder WHERE{$sql} order by id desc limit $offset,$pagesize");

while ($res = $DB->fetch($rs)) {
    if ($res['qq']) {
        $qq = $res['qq'];
    } else {
        $qq = '无';
    }

    $order = $DB->find("SELECT * FROM cmy_orders WHERE `id`='{$res['orderid']}'");
    if ($order) {
        $tool = $DB->find("SELECT * FROM cmy_tools WHERE `tid`='{$order['tid']}'");
    } else {
        $tool = null;
    }

    echo '<tr id="tr_' . $res['id'] . '"><td><input type="checkbox" name="checkbox[]" id="list1" value="' . $res['id'] . '">&nbsp;<b>' . $res['id'] . '</b></td>
    <td><a href="./sitelist.php?zid=' . $res['zid'] . '" target="_blank">' . $res['zid'] . '</a></td>
    <td><a href="./shoplist.php?tid=' . $order['tid'] . '" target="_blank">' . $order['tid'] . '</a></td>
    <td style="max-width: 100px;">' . ($tool && $tool['zid'] > 0 ? '<a href="./master.user.php?zid=' . $tool['zid'] . '" class="label label-success">' . $tool['zid'] . '</a>' : '<span class="label label-primary">主站商品</span>') . '</td>
    <td>业务补单</td><td><a href="./list.php?id=' . $res['orderid'] . '" target="_blank">' . $res['orderid'] . '</a></td><td><a href="javascript:orderItem(' . $res['id'] . ')">' . $res['name'] . '</a></td><td>' . $qq . '</td><td>' . display_zt($res['status']) . '</td><td>提交时间：' . $res['addtime'] . '<br>最后处理：' . (!empty($res['endtime']) ? $res['endtime'] : '') . '</td><td>' . ($res['status'] == 1 ? '<a href="javascript:setActive(' . $res['id'] . ',2)" class="btn btn-warning btn-xs">撤销</a>&nbsp;' : '<a href="javascript:setActive(' . $res['id'] . ',1)" class="btn btn-primary btn-xs">完结</a>&nbsp;') . '<a href="javascript:orderItem(' . $res['id'] . ')" class="btn btn-info btn-xs">查看</a>&nbsp;<a onclick="delworkorder(' . $res['id'] . ')" class="btn btn-xs btn-danger">删除</a></td></tr>';
}

?>
          </tbody>

        </table>

<input name="chkAll1" type="checkbox" id="chkAll1" onClick="this.value=check1(this.form.list1)" value="checkbox">&nbsp;反选&nbsp;
<input type="hidden" name="content"/>
<select name="status"><option selected>批量操作</option><option value="0">置为待处理</option><option value="1">置为已完成</option><option value="2">批量回复</option><option value="4">批量删除</option></select>
<button type="button" onclick="change()">确定</button>

      </div>
	 </form>

<?php
# 分页
$PageList = new \core\Page($numrows, $pagesize, 1, $link);
echo $PageList->showPage();
?>