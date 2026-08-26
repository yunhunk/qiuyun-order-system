<?php
/**
 * 自助升级站点
 **/
include "../includes/common.php";
if ($isLogin2 == 1) {} else {
    exit("<script language='javascript'>window.location.href='./login.php?goto=" . $goto . "';</script>");
}

$title = '自助提成转到余额';
if (daddslashes($_GET['act']) == 'changeInput') {
    if ($conf['fenzhan_point_to_money'] != 1) {
        $result = array('code' => -1, 'msg' => '该功能系统已关闭！');
    } else {
        $money = round(daddslashes($_POST['money']), 2);
        if ($money > $userrow['point']) {
            $result = array('code' => -1, 'msg' => '您当前的提成不足' . $money . '元！');
        } elseif ($money <= 0) {
            $result = array('code' => -1, 'msg' => '转入金额不正确！');
        } else {
            $DB->exec("update `pre_site` set `point`=`point`-:param1,`money`=`money`+:param2 where `zid`=:zid", [':param1' => $money, ':param2' => $money, ':zid' => $userrow['zid']]);
            addPointLogs($userrow['zid'], $money, '转入', '自助申请提成转入到余额' . $money . '元！剩余' . ($userrow['point'] - $money) . '元');
            $result = array('code' => 0, 'msg' => '' . $money . '元提成转入到余额成功！');
        }
    }
    exit(json_encode($result));
}

include './head.php';
if ($conf['fenzhan_point_to_money'] != 1) {
    showmsg('该功能系统已关闭！', 3);
    die;
}
?>
<div class="wrapper">
<div class="col-sm-12 col-md-10 col-lg-7" style="float: none;margin: 0 auto">
  <div class="panel panel-default">
	<div class="panel-heading font-bold" style="background-color: #9999CC;color: white;"><?php echo $title ?><span class="pull-right"><a href="./index.php" class="btn btn-info btn-xs">后台首页</a></span></div>
		<div class="panel-body">
			<ul class="list-group no-radius">
              <li class="list-group-item">
                说明:如果你需要使用提成来下单，可使用此功能把提成转到余额
              </li>
            </ul>
			<div class="form-group">
	            <div class="input-group"><div class="input-group-addon">剩余提成</div>
		            <input type="text" class="form-control" value="<?php echo $userrow['point'] ?>元" disabled>
		            <div class="input-group-addon">元</div>
		        </div>
		    </div>
	        <div class="form-group">
	            <div class="input-group"><div class="input-group-addon">转入金额</div>
	            <input type="text" id="money" class="form-control" value="" placeholder="输入要转到余额的金额">
	        </div></div>
			<a onclick="changeInput()" class="btn btn-primary btn-block">立即转移</a>
		</div>
	</div>
   </div>
  </div>
<script type="text/javascript">
function changeInput(){
	var money=$("#money").val();
	if (''==money) {
		return layer.alert("转入金额不能为空！");
	}
	var ii = layer.load(1);
	$.ajax({
		type : 'POST',
		url  : '?act=changeInput',
		data : "money="+money,
		dataType : 'json',
		success : function(data) {
		      layer.close(ii);
		      if(data.code==0){
		          layer.alert(data.msg, {
		          	icon:6,
		          	yes:function(){
		          		window.location.reload();
		          	}
		          });
		      }
		      else{
		          layer.alert(data.msg);
		      }
		},
		error:function(data){
		  layer.close(ii);
		  return falselayer.msg('服务器错误，请联系平台站长<?php echo $conf['zzqq'] ?>处理！');
		}
	});
}
</script>
</body>
</html>
