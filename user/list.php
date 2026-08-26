<?php
/**
 * 订单列表
 * 星河云
 **/
include "../includes/common.php";
$title = '订单管理';
include './head.php';
if ($isLogin2 == 1) {} else {
    $goto = @getHostUrl();
    exit("<script language='javascript'>window.location.href='./login.php?goto=" . $goto . "';</script>");
}

?>
<div class="wrapper">
	<div class="col-sm-12">
		<div class=" col-sm-12 alert alert-info" style="    margin-bottom: 10px;">
			为保护客户订单隐私，您只能查看自己下单的订单详情和发货内容
		</div>
	</div>
  <div class="col-sm-12">
<div class="panel panel-default">
<!-- model-->
<div class="modal fade" id="search" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
aria-hidden="true" style="display: none;">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">
					<span aria-hidden="true">
						&times;
					</span>
					<span class="sr-only">
						Close
					</span>
				</button>
				<h4 class="modal-title" id="myModalLabel">
					订单状态说明
				</h4>
			</div>
			<li class="list-group-item">
				<font color="blue">
					<b>
						待处理
					</b>
				</font>
				：说明订单还未开始处理，请耐心等待处理！
			</li>
			<li class="list-group-item">
				<font color="green">
					<b>
						已完成
					</b>
				</font>
				：说明提提交到服务器了，请耐心等待刷完！
				<br>
			</li>
			<li class="list-group-item">
				<font color="red">
					<b>
						异　常
					</b>
				</font>
				：说明下单账号信息错误，请联系客服处理！
			</li>
			<li class="list-group-item">
				<font color="orange">
					<b>
						处理中
					</b>
				</font>
				：说明您的订单正在处理中,耐心等待哦！
			</li>
			<li class="list-group-item">
				<font color="#FF6A6A">
					<b>
					  待退款
					</b>
				</font>
				：说明订单无法处理,请联系平台客服处理退款
			</li>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">
					关闭
				</button>
			</div>
		</div>
	</div>
</div>
<!-- model end-->

<?php
function display_zt($zt)
{
    if ($zt == 1) {
        return '<font color=green>已完成</font>';
    } elseif ($zt == 2) {
        return '<font color=orange>正在处理</font>';
    } elseif ($zt == 3) {
        return '<font color=red>异常</font>';
    } elseif ($zt == 4) {
        return '<font color=grey>已退款</font>';
    } elseif ($zt == 10) {
        return '<font color=#8E9013>待退款</font>';
    } else {
        return '<font color=blue>待处理</font>';
    }

}

$rs = $DB->query("SELECT * FROM pre_tools WHERE active=1");
while ($res = $DB->fetch($rs)) {
    $pre_func[$res['tid']] = $res['name'];
}

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

if (array_key_exists('kw', $_GET) && '' !== ($kw = input('get.kw', 1)) || isset($_GET['type']) && intval($_GET['type']) > -1) {
    $type = intval(input('get.type', 1));
    if (isset($_GET['type']) && intval($_GET['type']) > -1) {
        if (!empty($kw)) {
            $link     = '&kw=' . $_GET['kw'] . '&type=' . $type;
            $sql      = "SELECT * FROM pre_orders WHERE (`input`=:input or `id`=:id or `payorder`=:payorder) and zid=:zid order by id desc limit :offset, :pagesize";
            $sql_data = array(
                ':input'    => $kw,
                ':id'       => $kw,
                ':payorder' => $kw,
                ':zid'      => $userrow['zid'],
                ':offset'   => $offset,
                ':pagesize' => $pagesize,
            );
            $numrows = $DB->count("SELECT count(*) from pre_orders WHERE zid=:zid AND (`input`=:input or `id`=:id or `payorder`=:payorder) order by id desc limit :offset, :pagesize", $sql_data);
            $con     = '
    <div class="panel-heading font-bold" style="background-color: #9999CC;color: white;">订单管理</div>
	<div class="well well-sm" style="margin: 0;">包含 ' . $kw . ' 且 ' . display_zt($type) . ' 的共有 <b>' . $numrows . '</b> 个订单 &nbsp;[<a href="list.php" style="color:#f45a37">查看全部</a>]</div>
	<div class="wrapper">';
        } else {
            $link     = '&kw=&type=' . $type;
            $sql      = "SELECT * FROM pre_orders WHERE `status`=:status AND zid=:zid order by id desc limit :offset, :pagesize";
            $sql_data = array(
                ':status'   => $type,
                ':zid'      => $userrow['zid'],
                ':offset'   => $offset,
                ':pagesize' => $pagesize,
            );
            $numrows = $DB->count("SELECT count(*) from pre_orders WHERE `status`=:status AND zid=:zid  order by id desc limit :offset, :pagesize", $sql_data);
            $con     = '
	<div class="panel-heading font-bold" style="background-color: #9999CC;color: white;">订单管理</div>
	<div class="well well-sm" style="margin: 0;"> ' . display_zt($type) . ' 的共有 <b>' . $numrows . '</b> 个订单 &nbsp;[<a href="list.php" style="color:#f45a37">查看全部</a>]</div>
	<div class="wrapper">';
        }

    } else {
        $con = '
	<div class="panel-heading font-bold" style="background-color: #9999CC;color: white;">订单管理</div>
	<div class="well well-sm" style="margin: 0;">包含 ' . $_GET['kw'] . ' 的共有 <b>' . $numrows . '</b> 个订单 &nbsp;[<a href="list.php" style="color:#f45a37">查看全部</a>]</div>
	<div class="wrapper">';
        $link     = '&kw=' . $kw;
        $sql      = "SELECT * FROM pre_orders WHERE zid=:zid AND (`input`=:input or `id`=:id or `payorder`=:payorder)   order by id desc limit :offset, :pagesize";
        $sql_data = array(
            ':input'    => $kw,
            ':id'       => $kw,
            ':payorder' => $kw,
            ':zid'      => $userrow['zid'],
            ':offset'   => $offset,
            ':pagesize' => $pagesize,
        );

    }
    try {
        $rs = $DB->query($sql, $sql_data);
    } catch (\PDOException $e) {
        showmsg("查询订单数据时错误，" . $e->getMessage(), 3);
    }

} else {
    $numrows = $DB->count("SELECT count(*) from pre_orders where zid='{$userrow['zid']}'");
    $ondate  = $DB->count("select count(*) from pre_orders where status=1 and zid='{$userrow['zid']}'");
    $ondate2 = $DB->count("select count(*) from pre_orders where status=2 and zid='{$userrow['zid']}'");
    $sql     = " zid='{$userrow['zid']}'";
    $con     = '
	<div class="panel-heading font-bold" style="background-color: #9999CC;color: white;">订单查询</div>
	<div class="well well-sm" style="margin: 0;">共有 <b>' . $numrows . '</b> 个订单，其中已完成的有 <b>' . $ondate . '</b> 个，正在处理的有 <b>' . $ondate2 . '</b> 个。</div>
	<div class="wrapper">';
    try {
        $rs = $DB->query("SELECT * FROM pre_orders WHERE zid= ? order by id desc limit ?, ?", array($userrow['zid'], $offset, $pagesize));
    } catch (\PDOException $e) {
        showmsg("查询订单数据时错误，" . $e->getMessage(), 3);
    }
}
$con .= '
<form action="list.php" method="GET" class="form-inline">
  <div class="form-group">
    <label>搜索订单</label>
    <input type="text" class="form-control" name="kw" placeholder="请输入下单账号">
  </div>
  <div class="form-group">
    <select name="type" class="form-control" default="' . (isset($_GET['type']) ? $_GET['type'] : '-1') . '"><option value="-1">所有状态</option><option value="0">待处理</option><option value="2">正在处理</option><option value="1">已完成</option><option value="3">订单异常</option><option value="4">已退款</option><option value="10">待退款</option></select>
  </div>
  <button type="submit" class="btn btn-primary">搜索</button>&nbsp;
  <a href="#" data-toggle="modal" data-target="#search" id="search" class="btn btn-success"><i class="fa fa-exclamation-circle"></i>&nbsp;订单状态问题</a>
</form>
 ';

echo $con;
?>
			<div class="table-responsive">
				<table class="table table-striped b-t b-light">
					<thead>
						<tr>
							<th>
								操作
							</th>
							<th>
								订单号
							</th>
							<th>
								商品名称
							</th>
							<th>
								下单信息
							</th>
							<th>
								份数
							</th>
							<th>
								下单时间
							</th>
							<th>
								状态
							</th>
						</tr>
					</thead>
					<tbody>

<?php

while ($res = $DB->fetch($rs)) {
    $input = $res['input'];
    if ($conf['order_id_type'] == 1) {
        $orderid = $res['payorder'];
    } else {
        $orderid = $res['id'];
    }

    if ($res['userid'] != $userrow['zid']) {
        $count = $DB->count("SELECT count(*) FROM `pre_faka` WHERE `orderid`= ?", [$res['id']]);
        if ($count > 0) {
            //卡密商品禁止查看下单信息
            $input = '******';
        }
        $skey = "";
    } else {
        $skey = getOrderSkey($res, 'get');
    }

    echo '<tr>
	<td>' . ($skey != "" ? '<a data-id="' . $orderid . '" data-skey="' . $skey . '" href="javascript:;" title="查看订单详细" class="btn btn-info btn-xs showOrder">详细</a>' : '<a href="javascript:;" class="btn btn-info btn-xs" disabled>详细</a>') . '
	' . ($res['userid'] == $userrow['zid'] && $res['status'] == 3 ? '&nbsp;&nbsp;<a data-id="' . $res['id'] . '" href="javascript:;" title="修改订单数据" class="btn btn-primary btn-xs inputOrder">修改</a>' : null) . '
	</td>
							<td>
								' . $res['payorder'] . '
							</td>
							<td>
								' . $pre_func[$res['tid']] . '
							</td>
							<td>
								' . $input . '
							</td>
							<td>
								' . $res['value'] . '
							</td>
							<td>
								' . $res['addtime'] . '
							</td>
							<td>
								<font color=green>
									' . display_zt($res['status']) . '
								</font>
							</td>
						</tr>';
}
?>
					</tbody>
				</table>
			</div>
			<center>
<?php
#分页
$PageList = new \core\Page($numrows, $pagesize, 0, $link);
echo $PageList->showPage();
?>
	</div>
</div>
<script src="../assets/js/list.js?ver=<?php echo $conf['version'] . rand(1, 999) ?>"></script>
<?php include 'footer.php'?>