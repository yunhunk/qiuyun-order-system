<?php
/**
 * 自助续期站点
 **/
include "../includes/common.php";
$title = '自助续期站点';
if ($isLogin2 == 1) {} else {
    exit("<script language='javascript'>window.location.href='./login.php?goto=" . $goto . "';</script>");
}

if ($userrow['power'] == 2) {
    if ($conf['fenzhan_renew_type'] != 1) {
        $price = $conf['fenzhan_price2'];
    } else {
        $price = $conf['fenzhan_cost2'];
    }
} else {
    if ($conf['fenzhan_renew_type'] != 1) {
        $price = $conf['fenzhan_price'];
    } else {
        $price = $conf['fenzhan_cost'];
    }

    if ($userrow['upzid'] > 1 && $conf['fenzhan_renew_type'] == 1) {
        $ktfz_price = $DB->get_column("select ktfz_price from pre_site where zid='{$userrow['upzid']}' limit 1");
        if ($ktfz_price && $ktfz_price > 0) {
            $price = $ktfz_price;
        }
    }
}
$fenzhan_expiry = $conf['fenzhan_expiry'] > 0 ? $conf['fenzhan_expiry'] : 12;
if ($userrow['endtime'] > date("Y-m-d")) {
    $endtime = date("Y-m-d", strtotime("+ {$fenzhan_expiry} months", strtotime($userrow['endtime'])));
} else {
    $endtime = date("Y-m-d", strtotime("+ {$fenzhan_expiry} months"));
}

$act = isset($_GET['act']) ? $_GET['act'] : null;
if ($act == "renew") {
    if ($price > getUserRmb()) {
        exit('{"code":-2,"msg":"余额不足，请先充值"}');
    }

    $sql = $DB->query("update `pre_site` set `endtime`='$endtime',`money`=`money`-'{$price}' where `zid`='{$userrow['zid']}'");
    if ($sql) {
        addPointLogs($userrow['zid'], $price, '消费', '自助续期站点，当前余额' . (getUserRmb() - $price) . '元', null);
        exit('{"code":0,"msg":"续费成功！"}');
    } else {
        exit('{"code":-1,"msg":"续费失败，错误码：' . $DB->error() . '"}');
    }
}

include './head.php';
?>
<div class="wrapper">
<div class="col-sm-12 col-md-8 col-lg-6 center-block" style="float: none;">
<?php
if ($userrow['power'] == 0) {
    showmsg('你没有权限使用此功能！', 3);
}
?>

<div class="panel panel-default">
    <div class="panel-heading font-bold" style="background-color: #9999CC;color: white;">自助续期站点</h2>
		</div>
		<div class="panel-body">
			<div class="form-group">
				<div class="input-group">
					<div class="input-group-addon">
						当前到期时间
					</div>
					<input name="endtime" class="form-control" value="<?php echo $userrow['endtime'] ?>" disabled/>
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
					<div class="input-group-addon">
						续期后到期时间
					</div>
					<input name="nendtime" class="form-control" value="<?php echo $endtime ?>" disabled/>
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
					<div class="input-group-addon">
						续期所需
					</div>
					<input name="need" class="form-control" value="<?php echo $price ?>" disabled/>
					<div class="input-group-addon">
						元
					</div>
				</div>
			</div>
			<div class="form-group text-center">
			<a onclick="submit()" class="btn btn-success">立即购买</a>
			</div>
		</div>
	</div>
  </div>
</div>
<script type="text/javascript">
function submit(){
    var ii = layer.load(1, {shade: [0.1, '#fff']});
    $.ajax({
        type: 'POST',
        url: "?act=renew",
		timeout: 20000,
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code==0){
				layer.msg(data.msg);
				setTimeout(function () {
					window.location.reload();
				},1000);
			}
			else{
				layer.alert(data.msg);
			}
		},
		error:function(data){
			layer.close(ii);
			layer.alert('服务器错误，请联系站长<?php echo $conf['zz_zzqq'] ?>解决!');
			return false;
		}
    });
}
</script>
<?php include 'footer.php'?>