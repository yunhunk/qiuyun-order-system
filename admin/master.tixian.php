<?php
/**
 * 余额提现处理
 **/

include "../includes/common.php";

use core\Db;

$title = '供货商提现';
checkLogin();

checkAuthority('tixian');

$my = isset($_GET['my']) ? $_GET['my'] : null;
if ($my == 'getSkimg') {
    $zid = trim($_POST['zid']);
    $row = $DB->get_row("SELECT * from pre_master where zid=:zid limit 1", [':zid' => $zid]);
    if ($conf['file_type'] == 1 && stripos($conf['file_ftp_url'], '://') !== false && $conf['file_ftp_server'] != "" && $conf['file_ftp_username'] != "") {
        exit('{"code":0,"skimg":"' . $row['skimg'] . '"}');
    } else
    if ($row['skimg'] && is_file(ROOT . '/' . $row['skimg'])) {
        exit('{"code":0,"skimg":"' . $row['skimg'] . '"}');
    } elseif (is_file('../assets/img/skimg/sk_' . $zid . '.png')) {
        exit('{"code":0,"skimg":"../assets/img/skimg/sk_' . $zid . '.png"}');
    } elseif (!empty($row['skimg'])) {
        exit('{"code":0,"skimg":"' . $row['skimg'] . '"}');
    } else {
        exit(json_encode(["code" => -1, "msg" => "收款码不存在！"]));
    }
} elseif ($my == "info") {
    $id = input('id', 1);
    if (!$id) {
        json('ID不能为空');
    }

    $row = $DB->get_row("SELECT * from pre_master_tixian where `id`='" . $id . "' limit 1");
    if ($row) {
        json_success('成功', $row);
    } else {
        json('该数据不存在 =>' . $id);
    }
} elseif ($my == "getTixian") {
    //查看提现信息
    checkAuthority('tixian');
    $id   = intval(input('get.id'));
    $rows = $DB->get_row("SELECT * from pre_master_tixian where id='$id' limit 1");
    if (!$rows) {
        exit('{"code":-1,"msg":"当前提现记录不存在！"}');
    }

    $data = '<div class="form-group"><div class="input-group"><div class="input-group-addon">提现方式</div><select class="form-control" id="pay_type" default="' . $rows['pay_type'] . '"><option value="0">支付宝</option><option value="1">微信</option><option value="2">QQ钱包</option></select></div></div>';
    $data .= '<div class="form-group"><div class="input-group"><div class="input-group-addon">提现账号</div><input type="text" id="pay_account" value="' . $rows['pay_account'] . '" class="form-control" required/></div></div>';
    $data .= '<div class="form-group"><div class="input-group"><div class="input-group-addon">提现姓名</div><input type="text" id="pay_name" value="' . $rows['pay_name'] . '" class="form-control" required/></div></div>';
    $data .= '<input type="submit" id="save" onclick="saveInfo(' . $id . ')" class="btn btn-primary btn-block" value="保存">';
    $result = array("code" => 0, "msg" => "succ", "data" => $data);
    exit(json_encode($result));
} elseif ($my == "editTixian") {
    //修改提现信息
    checkAuthority('tixian');
    $id          = intval(input('post.id'));
    $pay_type    = trim(daddslashes(input('post.pay_type')));
    $pay_account = trim(daddslashes(input('post.pay_account')));
    $pay_name    = trim(daddslashes(input('post.pay_name')));
    $sds         = $DB->query("UPDATE `pre_master_tixian` SET `pay_type`='$pay_type',`pay_account`='$pay_account',`pay_name`='$pay_name' where `id`='$id'");
    if ($sds) {
        exit('{"code":0,"msg":"修改记录成功！"}');
    } else {
        exit('{"code":-1,"msg":"修改记录失败！' . $DB->error() . '"}');
    }

} elseif ($my == "set_tixian_note") {
    $id = input('id', 1);
    if (!$id) {
        json('ID不能为空');
    }
    $result = input('result', 1);
    $row    = $DB->get_row("SELECT * from pre_master_tixian where `id`='" . $id . "' limit 1");
    if ($row) {
        $update = Db::name('master_tixian')->where(['id' => $id])->update([
            'note' => $result,
        ]);
        if ($update !== false) {
            json_success('成功', $row);
        } else {
            json('数据库错误, ' . Db::error());
        }
    } else {
        json('该数据不存在 =>' . $id);
    }
} elseif ($my == 'back') {
    $id  = intval($_POST['id']);
    $row = $DB->get_row("SELECT * from pre_master_tixian where id='" . $id . "' limit 1");
    if ($row) {
        $bz = input('post.bz', 1);
        $DB->query("UPDATE pre_master_tixian set `status`=2,`note`='{$bz}' where id='$id'");
        $row2 = $DB->get_row("SELECT * from pre_master where `zid`='" . $row['zid'] . "' limit 1");
        if (!$row2) {
            exit('{"code":-1,"msg":"该提现记录的所属站点不存在！"}');
        }

        $DB->query("UPDATE pre_master set `income`=`income`+" . $row['money'] . " where zid='" . $row['zid'] . "'");
        $bz = '提现已驳回到账户余额，当前账户余额' . ($row['money'] + $row2['income']) . '元！' . ($bz != "" ? '驳回原因（' . $bz . '）' : null);
        addMasterPointLogs($row['zid'], $row['money'], '驳回', $bz, null);
        exit('{"code":0,"msg":"驳回操作成功"}');
    } else {
        exit('{"code":-1,"msg":"该提现记录不存在！"}');
    }
} elseif ($my == 'edit') {
    $id        = intval($_POST['id']);
    $realmoney = intval($_POST['realmoney']);
    $DB->query("UPDATE pre_master_tixian set `realmoney`=" . $realmoney . " where id='" . $id . "'");
    exit('{"code":0,"msg":"修改提现数据成功"}');
} elseif ($my == 'operation') {
    $ids = input('ids', 1);
    if (!$ids) {
        exit('{"code":-1,"msg":"要操作的ID列表不能为空"}');
    }
    $count  = count($ids);
    $status = input('status', 1);

    switch ($status) {
        case 3:
            Db::name('master_tixian')->where(['id' => ['in', $ids]])->update(['status' => 3]);
            break;
        case 1:
            Db::name('master_tixian')->where(['id' => ['in', $ids]])->update([
                'status'  => 1,
                'endtime' => $date,
            ]);
            break;
        case 0:
            Db::name('master_tixian')->where(['id' => ['in', $ids]])->update(['status' => 0]);
            break;
        case 4:
            Db::name('master_tixian')->where(['id' => ['in', $ids]])->delete();
            break;
        default:
            exit('{"code":-1,"msg":"不支持的批量操作类型"}');
            break;
    }
    exit('{"code":0,"msg":"批量修改' . $count . '条提现成功"}');
}

include './head.php';

echo '<div class="col-md-12 center-block" style="float: none;padding-top: 10px">';

if ($conf['master_tixian_open'] == 0) {
    showmsg('系统还未开启供货商提现功能, 请你先开启！<br/><br/> <a style="color: #6db5ef;" href="./master.set.php">现在去开启</a>');
}

function display_zt($zt, $note = '')
{
    if ($zt == 1) {
        return '<font color=green>已完成</font>';
    } elseif ($zt == 2) {
        return '<span data-tip="驳回原因: ' . $note . '"><font color=orange>已驳回</font></span>';
    } elseif ($zt == 3) {
        return '<span style="color:#644d8c">处理中</span>';
    } elseif ($zt == 4) {
        return '<span style="color:green">已撤销</span>';
    } else {
        return '<font color=blue>未完成</font>';
    }
}

function display_type($type)
{
    if ($type == 1) {
        return '<font color="green">微信</font>';
    } elseif ($type == 2) {
        return '<font color="blue">QQ钱包</font>';
    } elseif ($type == 4) {
        return '<font color="brown">银行卡</font>';
    } else {
        return '<font color="blue">支付宝</font>';
    }
}

$my = trim(isset($_GET['my']) ? $_GET['my'] : null);
if ($my == 'delete') {
    $id  = intval($_GET['id']);
    $sql = "DELETE FROM pre_master_tixian WHERE id='$id'";
    $DB->query($sql);
    exit("<script language='javascript'>alert('删除成功！');javascript:history.go(-1);</script>");
} elseif ($my == 'complete') {
    $id = intval($_GET['id']);
    $DB->query("UPDATE pre_master_tixian set status=1,endtime=NOW() where id='$id'");
    exit("<script language='javascript'>alert('已变更为已提现状态');javascript:history.go(-1);</script>");
} elseif ($my == 'reset') {
    $id = intval($_GET['id']);
    $DB->query("UPDATE pre_master_tixian set status=0 where id='$id'");
    exit("<script language='javascript'>alert('已变更为未提现状态');javascript:history.go(-1);</script>");
} elseif ($my == 'handle') {
    $id = intval($_GET['id']);
    $DB->query("UPDATE pre_master_tixian set `status`=3 where id='$id'");
    exit("<script language='javascript'>alert('已变更为处理中状态');javascript:history.go(-1);</script>");
} elseif ($my == 'qq') {
    $sql  = " `pay_type`='2'";
    $link = '&my=qq';
} elseif ($my == 'no') {
    $sql  = " `status`='0'";
    $link = '&my=no';
} elseif ($my == 'wx') {
    $sql  = " `pay_type`='1'";
    $link = '&my=wx';
} elseif ($my == 'zfb') {
    $sql  = " `pay_type`='0'";
    $link = '&my=zfb';
} else {
    $kw     = input('get.kw');
    $status = input('get.status');

    if ($status == '') {
        $status = -1;
    } else {
        $status = intval($status);
    }

    $type = input('get.type');
    if ($type == '') {
        $type = -1;
    } else {
        $type = intval($type);
    }

    $zid = input('get.zid');
    $zid = intval($zid);

    $sqls = [];
    if ($kw) {
        $sqls[] = " (`pay_account` LIKE '%{$kw}%' or `pay_name` LIKE '%{$kw}%' or `zid`='{$kw}')";
    }

    if ($type > -1) {
        $sqls[] = " `pay_type`={$type}";
    }

    if ($status > -1) {
        $sqls[] = " `status`={$status}";
    }

    if ($zid > 0) {
        $sqls[] = " `zid`={$zid}";
    }

    $link = '&my=search&kw=' . $_GET['kw'];

    if ($sqls) {
        $sql = trim(implode(' AND ', $sqls));
    } else {
        $sql = " 1";
    }
}
$numrows    = $DB->count("SELECT count(*) from pre_master_tixian WHERE{$sql}");
$countRmb   = round($DB->count("SELECT sum(realmoney) from pre_master_tixian WHERE `status`=1"), 2);
$countRmbNo = round($DB->count("SELECT sum(realmoney) from pre_master_tixian WHERE `status`=0"), 2);
$count5     = round($DB->count("SELECT sum(realmoney) from pre_master_tixian WHERE 1"), 2);
$count0     = round($DB->count("SELECT count(id) from `pre_master_tixian` WHERE 1"), 2);
$count1     = round($DB->count("SELECT count(id) from `pre_master_tixian` WHERE status=1"), 2);
$count2     = round($DB->count("SELECT count(id) from `pre_master_tixian` WHERE status=0"), 2);
$count3     = round($DB->count("SELECT count(id) from `pre_master_tixian` WHERE status=2"), 2);
$count4     = round($DB->count("SELECT count(id) from `pre_master_tixian` WHERE status=3"), 2);

echo '
    <div class="block">
    <div class="block-title">
        <h3 class="panel-title">
            <form method="get" class="form-inline">
                ' . $title . '
                <a onclick="window.location.href=\'master.tixian.php\'" style="margin-left:5px;" class="btn btn-warning hidden-xs btn-xs">全部(' . $count0 . ')</a>
                <a onclick="window.location.href=\'master.tixian.php?status=0\'" style="margin-left:5px;" class="btn btn-primary hidden-xs btn-xs">待处理(' . $count2 . ')</a>
                <a onclick="window.location.href=\'master.tixian.php?status=3\'" style="margin-left:5px;margin-right:5px;" class="btn btn-info hidden-xs btn-xs">处理中(' . $count4 . ')</a>
                <a onclick="window.location.href=\'master.tixian.php?status=2\'" style="margin-left:5px;margin-right:5px;" class="btn btn-danger hidden-xs btn-xs">已驳回(' . $count3 . ')</a>
                <a onclick="window.location.href=\'master.tixian.php?status=1\'" style="margin-left:5px;margin-right:5px;" class="btn btn-success hidden-xs btn-xs">已完成(' . $count1 . ')</a>
            </form>
        </h3>
    </div>
    <div class="alert alert-info">系统共有 <b>' . $numrows . '</b> 笔提现。累计提现' . $count5 . '元，累计已处理' . $countRmb . '元，未提现' . $countRmbNo . '元<br/>
    </div>
    <form method="get" class="form-inline">
		<input type="hidden" name="zid" value="' . @$_GET['zid'] . '">
		<div class="input-group xs-mb-15">
			<input type="text" placeholder="请输入要搜索的提现账号或者姓名！" name="kw"  value="' . $kw . '"
				   class="form-control text-center">
		</div>
        <div class="form-group">
            <select class="form-control" name="status" default="' . $status . '" id="close" placeholder="全部状态">
                <option value="-1">全部状态</option>
                <option value="0">待处理</option>
                <option value="1">已完成</option>
                <option value="2">已驳回</option>
            </select>
        </div>
        <div class="form-group">
            <select class="form-control" name="type" default="' . $type . '" id="active" placeholder="选择提现方式">
                <option value="-1">全部方式</option>
                <option value="0">支付宝</option>
                <option value="1">微信支付</option>
                <option value="2">QQ钱包</option>
                <option value="4">银行卡</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">搜索</button>
        <a href="./master.tixian.php" class="btn btn-warning">重置</a>
        <a id="alipaycsv" class="btn btn-default">支付宝批量转账模板</a>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">批量操作 <span class="caret"></span></button>
            <ul class="dropdown-menu">
                <li><a href="javascript:operation(3)">改处理中</a></li>
                <li><a href="javascript:operation(1)">改已完成</a></li>
                <li><a href="javascript:operation(0)">改待处理</a></li>
                <li><a href="javascript:operation(4)">删除所选</a></li>
            </ul>
        </div>
	</form>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th><input name="chkAll1" type="checkbox" id="chkAll1" />&nbsp;ID</th><th>供货商ID</th><th>金额</th><th>实际到账</th><th>提现方式</th><th>提现账号</th><th>姓名</th>' . ($conf['fenzhan_skimg'] == 1 ? '<th>收款图</th>' : null) . '<th>申请时间</th><th>完成时间</th><th>状态</th><th>操作</th></tr></thead>
          <tbody>';

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

$rs = $DB->query("SELECT * FROM pre_master_tixian WHERE{$sql} ORDER BY id DESC limit $offset,$pagesize");
while ($res = $DB->fetch($rs)) {

    echo '<tr id="tr_' . $res['id'] . '"><td><div class="checkbox-inline checkbox-md"><input class="" type="checkbox" name="checkbox[]" id="list1" value="' . $res['id'] . '"><b>' . $res['id'] . '</b></td><td><a href="sitelist.php?zid=' . $res['zid'] . '" style="color:red">' . $res['zid'] . '</a></td><td>' . $res['money'] . '</td><td><span  onclick="editMoney(' . $res['id'] . ')">' . $res['realmoney'] . '</span></td><td>' . display_type($res['pay_type']) . '</td><td><span onclick="inputInfo(' . $res['id'] . ')" title="修改信息">' . $res['pay_account'] . '</span></td><td><span onclick="inputInfo(' . $res['id'] . ')" title="修改信息">' . $res['pay_name'] . '</span></td>' . ($conf['fenzhan_skimg'] == 1 ? '<td><a onclick="skimg(' . $res['zid'] . ')">点击查看</a></td>' : null) . '<td>' . $res['addtime'] . '</td><td>' . ($res['status'] == 1 ? $res['endtime'] : null) . '</td><td>' . ($res['status'] != 2 ? display_zt($res['status'], $res['note']) : '<a class="setResult" data-id="' . $res['id'] . '">' . display_zt($res['status'], $res['note']) . '</a>') . '</td><td>' . ($res['status'] == 0 ? '<a href="./master.tixian.php?my=complete&id=' . $res['id'] . '" class="btn btn-success btn-xs">完成</a>&nbsp;<a href="javaScript:void(0)" class="btn btn-xs btn-warning" onclick="backTixian(' . $res['id'] . ');">驳回</a>&nbsp;<a href="./master.tixian.php?my=handle&id=' . $res['id'] . '" class="btn btn-info btn-xs">处理中</a>&nbsp;' : ($res['status'] == 1 ? '<a href="./master.tixian.php?my=reset&id=' . $res['id'] . '" class="btn btn-warning btn-xs">撤销</a>' : '<a href="./master.tixian.php?my=complete&id=' . $res['id'] . '" class="btn btn-success btn-xs">完成</a>&nbsp;')) . '&nbsp;<a href="./master.tixian.php?my=delete&id=' . $res['id'] . '" class="btn btn-xs btn-danger" onclick="return confirm(\'你确实要删除此记录吗？\');">删除</a></td></tr>';
}

echo '</tbody>
        </table>
    </div>';
#分页
$pageList = new \core\Page($numrows, $pagesize, 0, $link);
echo $pageList->showPage();

echo '</div>
  </div>';
?>

<script type="text/html" id="alipayBatch">
    <div class="panel-body">
        <div class="form-group input-datepicker">
            <label>转账哪一天:</label><br>
            <input class="form-control" id="myDate" type="date" name="startTime"
                   value="<?php echo date("Y-m-d", strtotime("-1 day")) ?>" autocomplete="off">
        </div>
        <div class="form-group">
            <label>总金额:</label><br>
            <div class="input-group">
                <input class="form-control" type="text" name="money" value="计算中..." autocomplete="off" disabled>
                <span class="input-group-addon">元</span>
            </div>
        </div>
        <div class="form-group">
            <label>总笔数:</label><br>
            <div class="input-group">
                <input class="form-control" type="number" name="number" value="计算中..." autocomplete="off" disabled>
                <span class="input-group-addon">笔</span>
            </div>
        </div>
        <div class="form-group">
            <label>转账备注:</label><br>
            <input class="form-control" type="text" name="remark" value="" placeholder="选填">
        </div>
        <input type="button" id="submit" class="btn btn-primary btn-block" value="下载模板">
        <hr/>
        <b>提示：</b><br/>1.仅限<u><a href="https://b.alipay.com/page/bizfund-bulk-payment/file-batch/upload"
                                     target="_blank">向支付宝账号转账</a>
        </u><br/>2.总金额、总笔数转账时可进行二次校验<br/>3.下载后状态均改为<font color="orange">处理中</font>
    </div>
</script>
<script>
"use strict";
var checkList = [] || new Array();

function check1(el) {
    var checkbox = document.querySelectorAll('#'+el);
    for (var i = 0; i < checkbox.length; i++) {
        if (checkbox[i].checked === false) {
            checkbox[i].checked = true;
        } else {
            checkbox[i].checked = false;
        }
    }
}

function getVals() {
    var checkbox = document.getElementsByName('checkbox[]');
    checkList = [];
    for (var i = 0; i < checkbox.length; i++) {
        if (checkbox[i].checked) {
            checkList.push(checkbox[i].value);
        }
    }
}
function inputInfo(id) {
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : 'GET',
		url : '?my=tixian&id='+id,
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				layer.open({
				  type: 1,
				  title: '修改数据',
				  skin: 'layui-layer-rim',
				  content: data.data
				});
			}else{
				layer.alert(data.msg);
			}
		},
		error:function(data){
			layer.msg('服务器错误');
			return false;
		}
	});
}
function saveInfo(id) {
	var pay_type=$("#pay_type").val();
	var pay_account=$("#pay_account").val();
	var pay_name=$("#pay_name").val();
	if(pay_account=='' || pay_name==''){layer.alert('请确保每项不能为空！');return false;}
	$('#save').val('Loading');
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : "POST",
		url : "?my=editTixian",
		data : {id:id,pay_type:pay_type,pay_account:pay_account,pay_name:pay_name},
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				layer.msg('保存成功！');
				window.location.reload();
			}else{
				layer.alert(data.msg);
			}
			$('#save').val('保存');
		}
	});
}

var backTixianLay;
var editMoneyLay;
function backTixian(id){
	backTixianLay=layer.open({
		type: 1,
		title: '提现驳回原因填写',
		anim: 1,
		skin: 'layui-layer-lan',
		content: '<div class="col-xs-12 col-md-12 col-lg-12 center-block" style="float: none;padding:5px"><div class="panel panel-body"></div><div class="form-group"><div class="input-group"><textarea type="text" cols="43" rows="4" class="bz" id="bz" placeholder="驳回原因"><?php echo $conf['fenzhan_tixian_refund'] ?></textarea></div><br/></div><div class="form-group"><input type="submit" value="确定驳回" onclick="qrbackTixian('+id+')" class="btn btn-primary btn-block"/><br><input type="submit" value="取消驳回" onclick="layer.close(backTixianLay)" class="btn btn-info btn-block"/></div><hr></div></div></div></div>'
	});
}

function qrbackTixian(id){
	var bz=$("#bz").val();
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : "POST",
		url : "?my=back",
		data : {id:id,bz:bz},
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				layer.close(backTixianLay);
				layer.msg(data.msg);
				$("#tr_"+id).hide();

			}else{
				layer.alert(data.msg);
			}
		}
	});

}

function editMoney(id) {
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : "POST",
		url : "?my=info",
		dataType : 'json',
		data : {id:id},
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				editMoneyLay=layer.open({
					type: 1,
					title: '提现金额修改-',
					anim: 1,
					skin: 'layui-layer-lan',
					content: '<div class="col-xs-12 col-md-12 col-lg-12 center-block" style="float: none;padding:5px"><div class="panel panel-body"></div><div class="form-group"><div class="input-group"><input class="form-control" type="text" value="'+data.data.pay_name+'" disabled/></div><br/></div><div class="form-group"><div class="input-group"><input class="form-control" type="text" id="realmoney_'+id+'" value="'+data.data.realmoney+'" placeholder="实际到账金额"/></div><br/></div><div class="form-group"><input type="submit" value="确定修改" onclick="qreditMoney('+id+')" class="btn btn-primary btn-block"/><br><input type="submit" value="取消修改" onclick="layer.close(editMoneyLay)" class="btn btn-info btn-block"/></div><hr></div></div></div></div>'
				});
			}else{
				layer.alert(data.msg);
			}
		}
	});
}

function qreditMoney(id){
	var realmoney=$('#realmoney_'+id).val();
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : "POST",
		url : "?my=edit",
		dataType : 'json',
		data : {id:id,realmoney:realmoney},
		success : function(data) {
		    layer.close(ii);
			if(data.code == 0){
				layer.close(editMoneyLay);
				layer.msg(data.msg);
			}
			else{
				layer.alert(data.msg);
			}
		}
	});

}

/**
 * 批量操作
 *
 */
function operation(status){
    getVals();
    var ids = checkList;
    if (ids.length ==0) {
        layer.msg('未选择任何数据');
        return;
    }

    var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : "POST",
		url : "?my=operation",
		data : {ids: ids, status: status},
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
                layer.msg(data.msg);
                setTimeout(function (){
					window.location.reload()
				},1000);
			}else{
				layer.alert(data.msg);
			}
		}
	});

}

function skimg(zid){
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : "POST",
		url : "?my=getSkimg",
		data : {zid:zid},
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				var imgpath=data.skimg;
				if (imgpath.indexOf("http")==(-1)) {
                     imgpath="../"+imgpath;
                }
				layer.open({
					type: 1,
					area: ['360px', '400px'],
					title: '站点'+zid+'的收款图查看',
					shade: 0.3,
					anim: 1,
					shadeClose: true, //开启遮罩关闭
					content: '<center><img width="300px" src="'+imgpath+'"></center>'
				});
			}else{
				layer.alert(data.msg);
			}
		}
	});
}

function searchItem(){
    var kw = $("[name='kw']").val();
    var status = $("[name='status']").val();
    var type = $("[name='type']").val();
    window.location.href="./master.tixian.php?kw=" + kw + '&type=' + type + '&status=' + status  ;
    return false;
}

function selectRender(){
    var items = $("select[default]");
    for (var i = 0; i < items.length; i++) {
        $(items[i]).val($(items[i]).attr("default") || 0);
    }
}

function objectToQueryStringFixed(obj) {
    let parts = [];
    for (const [key, value] of Object.entries(obj)) {
        if (Array.isArray(value)) {
            // 遍历数组，为每个元素生成一个键值对
            for (const item of value) {
                parts.push(`${encodeURIComponent(key)}[]=${encodeURIComponent(item)}`);
            }
        } else {
            // 不是数组，直接生成键值对
            parts.push(`${encodeURIComponent(key)}=${encodeURIComponent(value)}`);
        }
    }
    return parts.join('&');
}

function alipayBatch() {
    layer.open({
        type: 1,
        title: '支付宝批量转账',
        shade: 0.1,
        shadeClose: true,
        area: [$(window).width() > 480 ? '350px' : '90%'],
        closeBtn: 2,
        content: $("#alipayBatch").html(),
        success: function (index, layero) {
            function loadMoney(date) {
                $.ajax({
                    type: 'POST',
                    url: 'ajax.php?act=master_tixian_today_alipay',
                    data: {date},
                    dataType: 'json',
                    success: function (data) {
                        $("input[name='money']").val(data.money);
                        $("input[name='number']").val(data.number);
                    },
                    error: function (data) {
                        layer.msg('服务器错误');
                    }
                });
            }

            document.getElementById('myDate').addEventListener('change', function () {
                loadMoney(this.value.replace("/", "-"))
            });
            loadMoney(null);
            $("#submit").click(function () {
                if ($("input[name='number']").val() < 1) {
                    layer.msg('笔数为0无需转账');
                    return;
                }

                window.location.href = 'download_tixian.php?type=alipay&table=master_tixian&remark=' + $("input[name='remark']").val() + '&date=' + $("#myDate").val();
                layer.alert("确认下载完成后点确定以刷新数据", {icon: 1}, function () {
                    searchItem();
                    layer.closeAll()
                });
            });
        }
    });
}

// 监听自动搜索
$(document).ready(function () {
    var changeIndex = 0, changeIndex2=0;
    $(document).on('change', "[name='status']", function () {
        changeIndex++;
        if (changeIndex>1) {
            searchItem();
        }
    });
    $(document).on('change', "[name='type']", function () {
        changeIndex2++;
        if (changeIndex2>1) {
            searchItem();
        }
    });

    // 下载批量转账模板
    $(document).on('click', ".setResult", function () {
        var ii = layer.load(2, {shade:[0.1,'#fff']});
        var id=  $(this).data('id');
         $.ajax({
            type: 'GET',
            url: '?my=info&id=' + id,
            dataType: 'json',
            btn: ['关闭'],
            success: function (data) {
                layer.close(ii);
                if (data.code == 0) {
                    var pro = layer.prompt({
                        title: '填写提现驳回原因',
                        value: data.data.note,
                        formType: 2
                    }, function (text, index) {
                        var ii = layer.load(2, {shade: [0.1, '#fff']});
                        $.ajax({
                            type: 'POST',
                            url: '?my=set_tixian_note',
                            data: {id: id, result: text},
                            dataType: 'json',
                            success: function (data) {
                                layer.close(ii);
                                if (data.code == 0) {
                                    layer.close(pro);
                                    layer.msg('填写成功', {time: 500, icon: 1});
                                } else {
                                    layer.alert(data.msg);
                                }
                            },
                            error: function (data) {
                                layer.msg('服务器错误');
                                return false;
                            }
                        });
                    });
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.msg('服务器错误');
                return false;
            }
        });
    });

    // 全选
    $(document).on('click', '#chkAll1', function () {
        check1('list1');
    });

    // 下载批量转账模板
    $(document).on('click', "#alipaycsv", function () {
        alipayBatch();
        // getVals();
        // var ids = checkList;
        // if (ids.length == 0) {
        //     layer.msg('未选择任何数据');
        //     return;
        // }
        // var params = {
        //     ids: ids
        // }
        // window.location.href ='./master.tixian.php?my=csv&'+ objectToQueryStringFixed(params);
    });
    selectRender();
});

</script>

<?php include_once 'footer.php';?>

