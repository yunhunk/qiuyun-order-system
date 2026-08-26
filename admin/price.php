<?php
/**
 * 商品价格加价模板管理
 **/
include "../includes/common.php";
checkLogin();

checkAuthority('prices');

$title = ' 商品价格加价模板管理';

function getKind($kind)
{
    if ($kind == 0) {
        return '<span class="btn btn-info btn-xs">系统默认</span>';
    } else if ($kind == 1) {
        return '<span class="btn btn-primary btn-xs">累加加价</span>';
    } else if ($kind == 2) {
        return '<span class="btn btn-success btn-xs">百分比加价</span>';
    } else {
        return '<span class="btn btn-info btn-xs">系统默认</span>';
    }
}

function getVal($kind, $price)
{
    if ($kind == 0) {
        return '默认';
    } else if ($kind == 1) {
        return '+' . $price . '元';
    } else if ($kind == 2) {
        return '+' . $price . '%';
    } else {
        return '默认';
    }
}

$my = isset($_GET['my']) ? $_GET['my'] : null;
if ($my == 'add') {
    $name = trim($_POST['name']);
    $row  = $DB->get_row("SELECT * FROM pre_price WHERE name= ? limit 1", [$name]);
    if ($row) {
        exit('{"code":-1,"msg":"该模板已添加过！"}');
    }

    $kind = intval(trim($_POST['kind']));
    $p_0  = floatval($_POST['p_0']);
    $p_1  = floatval($_POST['p_1']);
    $p_2  = floatval($_POST['p_2']);
    $zid  = 1;
    if ($name == '') {
        exit('{"code":-1,"msg":"模板名称不能为空"}');
    }
    $sql = "insert into `pre_price` (`zid`,`kind`,`name`,`p_0`,`p_1`,`p_2`,`addtime`) values ('" . $zid . "'," . $kind . ",'" . $name . "'," . $p_0 . "," . $p_1 . "," . $p_2 . ",'" . $date . "')";
    if ($DB->query($sql)) {
        unset($_SESSION['priceselect']);
        exit('{"code":0,"msg":"添加成功！"}');
    } else {
        exit('{"code":-1,"msg":"添加失败，错误返回=>' . $DB->error() . '"}');
    }
} else if ($my == 'inputPriceReg') {
    $id  = intval($_GET['id']);
    $row = $DB->get_row("select * from pre_price where id='$id' limit 1");
    if (!$row) {
        exit('{"code":-1,"msg":"当前加价模板不存在！"}');
    }

    $result = array("code" => 0, "msg" => "succ", "data" => $row);
    exit(json_encode($result));
} else if ($my == 'edit') {
    $id   = intval(trim($_POST['id']));
    $kind = intval(trim($_POST['kind']));
    $p_0  = floatval($_POST['p_0']);
    $p_1  = floatval($_POST['p_1']);
    $p_2  = floatval($_POST['p_2']);
    $name = trim($_POST['name']);
    if ($name == '') {
        exit('{"code":-1,"msg":"模板名称不能为空"}');
    }
    $sql = "update `pre_price` set kind=" . $kind . ",p_0=" . $p_0 . ",p_1=" . $p_1 . ",p_2=" . $p_2 . ",name='" . $name . "' where id=" . $id;
    if ($DB->query($sql)) {
        unset($_SESSION['priceselect']);
        exit('{"code":0,"msg":"编辑加价模板成功！"}');
    } else {
        exit('{"code":-1,"msg":"编辑加价模板失败，错误返回=>' . $DB->error() . '"}');
    }

} elseif ($my == 'delReg') {
    $id  = intval($_GET['prid']);
    $row = $DB->get_row("select * from pre_price where id='{$id}' limit 1");
    if (!$row) {
        exit('{"code":-1,"msg":"当前加价模板不存在！","id":"' . $id . '"}');
    }

    $sql = "delete from pre_price where id='{$id}' limit 1";
    if ($DB->query($sql)) {
        exit('{"code":0,"msg":"删除加价模板成功！"}');
    } else {
        exit('{"code":-1,"msg":"删除加价模板失败，错误返回=>' . $DB->error() . '","id":"' . $id . '"}');
    }
}

include './head.php';

$mod = isset($_GET['mod']) ? $_GET['mod'] : null;
if ($mod == "prjklogs") {
    echo '
    <div class="col-xs-12 col-md-12 col-lg-12" style="padding-top:10px;float: none;">
    <div class="block">
        <div class="block-title">
        <h3>监控日志</h3>
         <span style="margin-left:10px;display:inline-block">
            <a href="./price.php" style="margin-left:5px;" class="btn btn-success btn-xs">加价模板</a>&nbsp;
			<a href="./set.php?mod=pricejk" style="margin-left:5px;" class="btn btn-primary btn-xs">监控设置</a>
	     </span>
        </div>
        <div class="">
        <div id="alert_frame" class="alert" style="font-weight: bold;background-color: #B4EEB4;">
				1.详细步骤：点击"添加新的分类"来添加需要监控的分类，更新价格只能监控亿乐社区和九五社区的<br>
				2.监控地址：<a href="http://' . $_SERVER['HTTP_HOST'] . '/cron/priceCron.php?act=priceCron&key=' . ($conf['cronkey'] ? $conf['cronkey'] : '请先到后台-网站基本信息-设置监控密钥') . '">http://' . $_SERVER['HTTP_HOST'] . '/cron/priceCron.php?act=priceCron&key=' . ($conf['cronkey'] ? $conf['cronkey'] : '请先到后台-网站基本信息-设置监控密钥') . '</a><br>
		</div>
	    <div class="table-responsive">
	        <table class="table table-striped">
	          <thead><tr><th>ID</th><th>详情</th><th>时间</th></tr></thead>
	          <tbody>';
    $numrows  = $DB->count("SELECT count(*) FROM cmy_tools_log_pricejk");
    $pagesize = 15;
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
    $rs     = $DB->query("SELECT * FROM `pre_tools_log_pricejk` order by id desc limit $offset,$pagesize");
    while ($res = $DB->fetch($rs)) {
        echo '<tr><td><b>' . $res['id'] . '</b></td><td>' . htmlspecialchars($res['content']) . '</td><td>' . $res['addtime'] . '</td><td>-</td></tr>';
    }
    echo ' </tbody>
					    </table>
		 </div>
		 </div>
		 </div>
		 </div>';
    #分页
    $pageList = new \core\Page($numrows, $pagesize, 0, $link);
    echo $pageList->showPage();
    exit;
} else {
    echo <<<model
<style>
.form-group{margin-bottom:15px}
</style>
<div class="col-sm-12 col-md-12 center-block" style="float: none;padding-top:10px;">

    <!------添加加价模板 ------->
   <div class="modal fade" id="add_model" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">添加加价模板</h4>
            </div>
            <div class="modal-body">
					<div class="form-group">
					  <div class="input-group">
					  <div class="input-group-addon">模板名称:</div>
					  <input type="text" name="text" placeholder="模板名称" id="add_name" value="" class="form-control" /></div>
					</div>
					<div class="form-group">
					<div class="input-group">
					  <div class="input-group-addon" id="inputname">加价方式:</div>
					  <select class="form-control" name="kind" onchange="setkind(this.value)" id="add_kind">
					    <option value="0">0_点我选择</option>
					    <option value="1">1_直接累加</option>
					    <option value="2">2_百分比加价</option></select>
					</div>
					</div>
					<div id="add_p_box" class="p_box" style="display:none">
					<div class="form-group">
					  <div class="input-group">
					    <div class="input-group-addon">出售价加价:</div>
					    <input type="text" name="text" placeholder="出售价加价" id="add_p_0" value="" class="form-control" />
					    <span class="input-group-addon p_0_name">%</span></div>
					</div>
					<div class="form-group">
					  <div class="input-group">
					    <div class="input-group-addon">普及版加价:</div>
					    <input type="text" name="text" placeholder="普及版加价" id="add_p_1" value="" class="form-control" />
					    <span class="input-group-addon p_1_name">%</span></div>
					</div>
					<div class="form-group">
					  <div class="input-group">
					    <div class="input-group-addon">专业版加价:</div>
					    <input type="text" name="text" placeholder="专业版加价" id="add_p_2" value="" class="form-control" />
					    <span class="input-group-addon p_2_name">%</span></div>
					</div>
					</div>
					<div class="form-group">
					   <div class="input-group">
					   <div class="input-group-addon">参考成本价格:</div>
					   <input type="text" class="form-control" id="add_price1" name="add_price1" value="">
					   <span onclick="readPrice('add')" class="input-group-addon">点我查看</span>
					   </div>
					   <pre>填入商品的大概成本价格，下方会自动展示当前设置的对于价格</pre>
					</div>
					<table class="table table-striped table-bordered table-condensed">
					<tbody>
					<tr align="center"><td colspan="3">以下为参考实际价格，请将大致成本价格填入上方输入框</td></tr>
					<tr align="center"><td>参考销售价格</td><td>参考普及版价格</td><td>参考专业版价格</td></tr>
					<tr>
					<td><input type="text" id="add_price" name="price" value="" class="form-control input-sm"/></td>
					<td><input type="text" id="add_cost" name="cost" value="" class="form-control input-sm" placeholder=""/></td>
					<td><input type="text" id="add_cost2" name="cost2" value="" class="form-control input-sm" placeholder=""/></td>
					</tr>
					</table>
					<div class="form-group">
					    <input id="addprid" type="submit" value="确定添加" class="btn btn-primary btn-block" />
					    <br>
					    <a class="btn btn-danger btn-block" id="submit" data-dismiss="modal">取消添加</a>
					</div>
            </div>
        </div>
    </div>
</div>
    <!------添加加价模板 END-------->
 <!------修改加价模板 ------->
   <div class="modal fade" id="edit_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">编辑砍价商品</h4>
            </div>
            <div class="modal-body">
                <input type="text" id="edit_prid" class="form-control hide" hidden="hidden"/>
                <div class="form-group">
				  <div class="input-group">
				  <div class="input-group-addon">模板名称:</div>
				  <input type="text" name="text" placeholder="模板名称" id="edit_name" value="" class="form-control" /></div>
				</div>
				<div class="form-group">
				<div class="input-group">
				  <div class="input-group-addon" id="inputname">加价方式:</div>
				  <select class="form-control" onchange="setkind(this.value)" id="edit_kind">
				    <option value="0">0_点我选择</option>
				    <option value="1">1_直接累加</option>
				    <option value="2">2_百分比加价</option></select>
				</div>
				</div>
				<div id="edit_p_box" class="p_box" style="display:none">
				<div class="form-group">
				  <div class="input-group">
				    <div class="input-group-addon">出售价加价:</div>
				    <input type="text" name="text" placeholder="出售价加价" id="edit_p_0" value="" class="form-control" />
				    <span class="input-group-addon p_0_name">%</span></div>
				</div>
				<div class="form-group">
				  <div class="input-group">
				    <div class="input-group-addon">普及版加价:</div>
				    <input type="text" name="text" placeholder="普及版加价" id="edit_p_1" value="" class="form-control" />
				    <span class="input-group-addon p_1_name">%</span></div>
				</div>
				<div class="form-group">
				  <div class="input-group">
				    <div class="input-group-addon">专业版加价:</div>
				    <input type="text" name="text" placeholder="专业版加价" id="edit_p_2" value="" class="form-control" />
				    <span class="input-group-addon p_2_name">%</span></div>
				</div>
				</div>
				<div class="form-group">
				   <div class="input-group">
				   <div class="input-group-addon">参考成本价格:</div>
				   <input type="text" class="form-control" id="edit_price1" name="edit_price1" value="">
				   <span onclick="readPrice('edit')" class="input-group-addon">点我查看</span>
				   </div>
				   <pre>填入商品的大概成本价格，下方会自动展示当前设置的对于价格</pre>
				</div>
				<table class="table table-striped table-bordered table-condensed">
				<tbody>
				<tr align="center"><td colspan="3">以下为参考实际价格，请将大致成本价格填入上方输入框</td></tr>
				<tr align="center"><td>参考销售价格</td><td>参考普及版价格</td><td>参考专业版价格</td></tr>
				<tr>
				<td><input type="text" id="edit_price" name="price" value="" class="form-control input-sm"/></td>
				<td><input type="text" id="edit_cost" name="cost" value="" class="form-control input-sm" placeholder=""/></td>
				<td><input type="text" id="edit_cost2" name="cost2" value="" class="form-control input-sm" placeholder=""/></td>
				</tr>
				</table>
				<div class="form-group">
				    <input id="editPrid" type="submit" value="确定修改" class="btn btn-primary btn-block" />
				    <br>
				   <a class="btn btn-danger btn-block" id="submit" data-dismiss="modal">取消修改</a>
				</div>
            </div>
        </div>
    </div>
</div>
    <!------修改加价模板 END-------->

model;
}

echo '
<div class="col-xs-12 col-md-12 col-lg-12" style="padding-top:10px;float: none;">
    <div class="block">
        <div class="block-title"><h3>加价模板列表</h3></div>
        <div class="">
				<div class="form-inline" style="margin:8px auto;">
					<a data-toggle="modal" data-target="#add_model" style="margin-left:12px;" class="btn btn-success">添加模板</a>&nbsp;
					<a href="./set.php?mod=pricejk" style="margin-left:5px;" class="btn btn-primary">监控设置</a>&nbsp;
					<a href="?mod=prjklogs" style="margin-left:5px;" class="btn btn-info">监控日志</a>
				</div>
			    <div class="table-responsive">
			        <table class="table table-striped">
			          <thead><tr><th>ID</th><th>模板名称</th><th>出售价加价</th><th>专业版加价</th><th>旗舰版加价</th><th>模板属性</th><th>添加时间</th><th>操作</th></tr></thead>
			          <tbody>';
$numrows  = $DB->count("SELECT count(*) FROM pre_price");
$pagesize = 15;
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
$rs     = $DB->query("SELECT * FROM pre_price WHERE 1 order by id desc limit $offset,$pagesize");
while ($res = $DB->fetch($rs)) {
    echo '<tr><td>' . $res['id'] . '</td><td>' . $res['name'] . '</td><td>' . getVal($res['kind'], $res['p_0']) . '</td><td>' . getVal($res['kind'], $res['p_1']) . '</td><td>' . getVal($res['kind'], $res['p_2']) . '</td><td>' . getKind($res['kind']) . '</td><td>' . $res['addtime'] . '</td><td><a href="JavaScript:(0)" onclick="inputPriceReg(' . $res['id'] . ')" class="btn btn-primary btn-xs">编辑</a>&nbsp;&nbsp;<a href="./shoplist.php?prid=' . $res['id'] . '"  class="btn btn-success btn-xs">商品</a>&nbsp;&nbsp;<a href="JavaScript:void(0)" onclick="delReg(' . $res['id'] . ')" class="btn btn-danger btn-xs">删除</a></td></tr>';
}

echo ' </tbody>
				    </table>
			      </div>';
#分页
$pageList = new \core\Page($numrows, $pagesize, 0, $link);
echo $pageList->showPage();

echo <<<JavaScript
<script>
$("#addprid").click(function () {
    var name  = $("#add_name").val();
    var kind  = $("#add_kind").val();
    var p_0 = $("#add_p_0").val();
    var p_1  = $("#add_p_1").val();
    var p_2 = $("#add_p_2").val();
    if (kind<1) {
		layer.alert('加价方式不能为空！');
		return false;
	}
	else if (kind>0) {
		if (p_0===null || p_1===null || p_2===null) {
		    layer.alert('请确保加价规则内容不能为空');
		    return false;
		}
	}

	var ii = layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type : 'POST',
        url : '?my=add',
        data:'name='+name+'&kind='+kind+'&p_0='+p_0+'&p_1='+p_1+'&p_2='+p_2,
        dataType : 'json',
        success : function(data) {
          layer.close(ii);
          if(data.code == 0){
            layer.msg(data.msg);
             setTimeout(function (){
               window.location.reload();
            },800);
          }else{
            layer.alert(data.msg);
          }
        },
        error:function(data){
          layer.msg('服务器错误');
          return false;
        }
    });

});

$("#editPrid").click(function () {
	var prid  = $("#edit_prid").val();
    var name  = $("#edit_name").val();
    var kind  = $("#edit_kind").val();
    var p_0 = $("#edit_p_0").val();
    var p_1  = $("#edit_p_1").val();
    var p_2 = $("#edit_p_2").val();
    if (kind<1) {
		layer.alert('加价方式不能为空！');
		return false;
	}
	else if (kind>0) {
		if (p_0===null || p_1===null || p_2===null) {
		    layer.alert('请确保加价规则内容不能为空');
		    return false;
		}
	}

	var ii = layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type : 'POST',
        url : '?my=edit',
        data:'name='+name+'&kind='+kind+'&p_0='+p_0+'&p_1='+p_1+'&p_2='+p_2+'&id='+prid,
        dataType : 'json',
        success : function(data) {
          layer.close(ii);
          if(data.code == 0){
            layer.msg(data.msg);
            setTimeout(function (){
               window.location.reload();
            },800);
          }else{
            layer.alert(data.msg);
          }
        },
        error:function(data){
          layer.msg('服务器错误');
          return false;
        }
    });

});

function inputPriceReg(id){
	var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : "POST",
		url : "?my=inputPriceReg&id="+id,
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code==0){
				$("#edit_prid").val(id);
				$("#edit_name").val(data.data.name);
				$("#edit_kind").val(data.data.kind);
				$("#edit_p_0").val(data.data.p_0);
				$("#edit_p_1").val(data.data.p_1);
				$("#edit_p_2").val(data.data.p_2);
				$("#edit_modal").modal("show");
				$("#edit_kind").change();

			}else{
			   layer.alert(data.msg);
			}
		},
		error: function(ret){
			conlose.log(ret);
			layer.close(ii);
			layer.alert("服务器请求超时，请稍后再试！");
		}
	});

}

function delReg(prid){
    var ii = layer.load(2, {shade:[0.1,'#fff']});
	$.ajax({
		type : "POST",
		url : "?my=delReg&prid="+prid,
		dataType : 'json',
		success : function(data) {
			layer.close(ii);
			if(data.code==0){
			    layer.msg(data.msg,{
			   	  end:function (){
			   	  	window.location.reload();
			   	  }
			   	});
			}else{
			   layer.alert(data.msg);
			}
		},
		error: function(ret){
			conlose.log(ret);
			layer.close(ii);
			layer.alert("服务器请求超时，请稍后再试！"+ret);
		}
	});
}

function readPrice(type){
	var price1  = getFloat($("#"+type+"_price1").val(), 2);
    var kind  =  getFloat($("#"+type+"_kind option:selected").val(), 0);
    var p_0 = getFloat($("#"+type+"_p_0").val(), 2);
    var p_1  = getFloat($("#"+type+"_p_1").val(), 2);
    var p_2 = getFloat($("#"+type+"_p_2").val(), 2);

    if (kind<1) {
		layer.alert('加价方式不能为空！');
		return false;
	}
	else if (kind>0) {
		if (p_0<=0 || p_1<=0 || p_2<=0) {
		    layer.alert('请确保各加价的内容不能为空');
		    return false;
		}
	}

	if(price1>0){
		var price = getFloat(kind==2?price1+price1*p_0/100:price1+p_0, 2);
		var cost = getFloat(kind==2?price1+price1*p_1/100:price1+p_1, 2);
		var cost2 = getFloat(kind==2?price1+price1*p_2/100:price1+p_2, 2);
		$("#"+type+"_price").val(price);
		$("#"+type+"_cost").val(cost);
		$("#"+type+"_cost2").val(cost2);
	}
	else{
		return layer.alert('成本价格不正确！');
	}
}

function getFloat(number, n) {
	n = n ? parseInt(n) : 2;
	if (n <= 0) return Math.ceil(number);
	number = Math.round(number * Math.pow(10, n)) / Math.pow(10, n);
	return number;
}

function IsPC() {
       var userAgentInfo = navigator.userAgent;
       var Agents = ['Android', 'iPhone',
           'SymbianOS', 'Windows Phone',
           'iPad', 'iPod'
       ];
       var flag = true;
       for (var i = 0; i < Agents.length; i++) {
           if (userAgentInfo.indexOf(Agents[i]) != -1) {
               flag = false;
               break;
           }
       }
       return flag;
}


function setkind(val){
	if (val==1){

		$(".p_0").attr('type','text');
		$(".p_0").attr('type','text');
		$(".p_0").attr('type','text');
		if($(".p_0").val()==""){
			$(".p_0").val('0.8');
			$(".p_1").val('0.6');
			$(".p_2").val('0.5');
		}

		$(".p_0_name").html('元');
		$(".p_1_name").html('元');
		$(".p_2_name").html('元');
		$(".p_box").show();
	}
	else if (val==2){
		$(".p_0").val('14');
		$(".p_0").attr('type','text');
		$(".p_1").val('10');
		$(".p_1").attr('type','text');
		$(".p_2").val('8');
		$(".p_2").attr('type','text');
		$(".p_0_name").html('%');
		$(".p_1_name").html('%');
		$(".p_2_name").html('%');
		$(".p_box").show();
	}
	else{
		$(".p_box").hide();
	}
}
</script>
JavaScript;

include 'footer.php';
