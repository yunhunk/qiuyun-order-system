<?php
include "../includes/common.php";
checkLogin();
$title = "工单列表";

$my = isset($_GET['my']) ? daddslashes($_GET['my']) : null;
if ($my == "del") {
    $id  = intval($_GET['id']);
    $row = $DB->get_row("select * from pre_workorder where id='$id' limit 1");
    if ($row) {
        $sql = "DELETE FROM pre_workorder WHERE id='{$id}'";
        if ($DB->query($sql)) {
            $result = array("code" => 0, "msg" => "删除成功！");
        } else {
            $result = array("code" => -1, "msg" => '删除失败！' . $DB->error());
        }
    } else {
        $result = array("code" => -1, "msg" => '该工单不存在！');
    }
    exit(json_encode($result));
} elseif ($my == "setActive") {
    $id     = intval($_GET['id']);
    $status = intval($_GET['status']);
    $row    = $DB->get_row("select * from pre_workorder where id='$id' limit 1");
    if ($row) {
        $sql = "update pre_workorder set status='{$status}'  WHERE id='{$id}'";
        if ($DB->query($sql)) {
            $result = array("code" => 0, "msg" => "操作成功！");
        } else {
            $result = array("code" => -1, "msg" => '操作失败！' . $DB->error());
        }
    } else {
        $result = array("code" => -1, "msg" => '该工单不存在！');
    }
    exit(json_encode($result));
}

checkAuthority('works');
include './head.php';

echo '<div class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top:10px;">	';

$sql     = "1";
$numrows = $DB->count("SELECT count(*) from pre_workorder");
$link    = "";
$dcl     = $DB->count("SELECT count(*) from pre_workorder where status=0");
$clz     = $DB->count("SELECT count(*) from pre_workorder where status=2");
$ywc     = $DB->count("SELECT count(*) from pre_workorder where status=1");

echo '<div class="block">
		<div class="block-title clearfix">
		<h2>工单列表&nbsp;&nbsp;<a href="javascript:listTable(\'start\')" class="btn btn-primary btn-xs">全部(' . $numrows . ')</a>&nbsp;<a href="javascript:listTable(\'status=0\')" class="btn btn-info btn-xs">待处理(' . $dcl . ')</a>&nbsp;<a href="javascript:listTable(\'status=2\')" class="btn btn-warning btn-xs">处理中(' . $clz . ')</a>&nbsp;<a href="javascript:listTable(\'status=1\')" class="btn btn-success btn-xs">已完成(' . $ywc . ')</a></h2>
		</div>
		<div id="listTable"></div>
		</div>
  </div>
</div>
<script src="./assets/js/workorder.js' . $jsver . '"></script>
';

include_once 'footer.php';
