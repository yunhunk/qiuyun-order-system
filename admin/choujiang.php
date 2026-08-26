<?php
include '../includes/common.php';
$title = '抽奖商品列表';
checkLogin();
checkAuthority('super');

include './head.php';

echo '
    <div class="col-md-12 center-block" style="float: none;padding-top:10px">
';
$my = (isset($_GET['my']) ? $_GET['my'] : null);

$select       = '';
$rs           = $DB->query('SELECT * FROM pre_class WHERE active=1 order by sort asc');
$select       = '<option value="-1">请选择分类</option>';
$pre_class[0] = '未分类';
while ($res = $DB->fetch($rs)) {
    $pre_class[$res['cid']] = $res['name'];
    $select .= '<option value="' . $res['cid'] . '">' . $res['name'] . '</option>';
}

$sql = ' 1';

$numrows = $DB->count('SELECT count(*) from pre_gift where ' . $sql);
echo '
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">添加奖项</h4>
            </div>
            <div class="modal-body">
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">奖品名称</div>
						<input type="text" id="name" class="form-control" placeholder="请填写奖项名称">
					</div>
				</div>
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">中奖几率</div>
						<input type="number" id="rate" class="form-control" placeholder="请填写中奖几率(百分比)">
					</div>
				</div>
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">对应分类</div>
						<select id="cid" class="form-control">
						' . $select . '
						</select>
					</div>
				</div>
				<div class="form-group" id="display_sub_cid" style="display:none">
                    <div class="input-group">
                        <div class="input-group-addon">二级分类</div>
                        <select id="sub_cid" class="form-control"></select>
                    </div>
                </div>
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">对应商品</div>
						<select id="tid" class="form-control" onChange="getPrice(1)"><option value="0">请选择商品</option></select>
					</div>
				</div>
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">商品价格</div>
						<input type="text" id="price" class="form-control" placeholder="" disabled>
					</div>
				</div>
				<div class="form-group">
					<a class="btn btn-info btn-block" id="submit" data-dismiss="modal">确定添加</a>
				</div>
			</div>
        </div>
    </div>
</div>
<div class="modal fade" id="edit_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="edit_title">编辑奖项</h4>
            </div>
            <div class="modal-body">
			<input type="hidden" id="edit_val">
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">奖品名称</div>
						<input type="text" id="edit_name" class="form-control" placeholder="请填写奖项名称">
					</div>
				</div>
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">中奖几率</div>
						<input type="number" id="edit_rate" class="form-control" placeholder="请填写中奖几率(百分比)">
					</div>
				</div>
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">对应分类</div>
						<select id="edit_cid" class="form-control" default="">
						' . $select . '
						</select>
					</div>
				</div>
				<div class="form-group" id="display_edit_sub_cid" style="display:none">
                    <div class="input-group">
                        <div class="input-group-addon">二级分类</div>
                        <select id="edit_sub_cid" class="form-control"></select>
                    </div>
                </div>
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">对应商品</div>
						<select id="edit_tid" class="form-control" onChange="getPrice(2)"><option value="0">请选择商品</option></select>
					</div>
				</div>
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">商品价格</div>
						<input type="text" id="edit_price" class="form-control" placeholder="" disabled>
					</div>
				</div>
				<div class="form-group">
					<a class="btn btn-info btn-block" onclick="edit_ok($(\'#edit_val\').val())">确定修改</a>
				</div>
			</div>
        </div>
    </div>
</div>
    <div class="block">
    <div class="block-title"><h3 class="panel-title">抽奖商品管理</h3></div>
    <div class="alert alert-info">系统共有 <b>' . $numrows . '</b> 个抽奖商品<br/>
    </div>
    <form method="GET" class="form-inline">
    <div class="form-group">
    <a class="btn btn-primary" data-toggle="modal" data-target="#myModal">添加一个奖项</a>&nbsp;
    <a class="btn btn-success" href="choujiang_list.php">查看中奖记录</a>&nbsp;
    <a class="btn btn-info" href="set.php?mod=choujiang">抽奖系统设置</a>
    </div>
    </form>
    ';
echo ' <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>奖品名称</th><th>对应商品</th><th>中奖几率</th><th>操作</th></tr></thead>
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
$rs     = $DB->query('SELECT * FROM pre_gift WHERE' . $sql . ' order by tid desc limit ' . $offset . ',' . $pagesize);
while ($res = $DB->fetch($rs)) {
    $tool = $DB->get_row("select * FROM pre_tools WHERE tid='" . $res['tid'] . "'");
    echo '<tr><td>' . $res['name'] . '</td><td>' . $tool['name'] . '</td><td>' . $res['rate'] . '%</td><td><a href="javascript:void(0)" onclick="editmember(' . $res['id'] . ')" class="btn btn-info btn-xs">编辑</a>&nbsp;<a href="javascript:void(0)" class="btn btn-xs btn-danger" onclick="del_member(' . $res['id'] . ')">删除</a></td></tr>';
}
echo '          </tbody>
        </table>
      </div>

';

# 分页
$PageList = new \core\Page($numrows, $pagesize, 0, $link);
echo $PageList->showPage();

echo '    </div>
  </div>
<script>
var changeEvent=false;
$("#cishu_submit").click(function(){
	ii=layer.load(1,{shade:0.3});
	$.ajax({
		type:"get",
		url:"ajax.php?act=cishu&cishu="+$("#cishu").val()+"&gift_open="+$("#gift_open").val()+"&gift_log="+$("#gift_log").val()+"&cjmsg="+$("#cjmsg").val()+"&cjmoney="+$("#cjmoney").val(),
		dataType:"json",
		success:function(cishu){
			layer.close(ii);
			if(cishu.code==0){
				layer.msg(\'保存成功\',{icon:1,time:1000,shade:0.3});
			}else{
				layer.alert(cishu.msg);
			}
		}
	});
});
function editmember(id){
	ii=layer.load(1);
	$.ajax({
		type:"post",
		url:"ajax.php?act=edit_cj",
		data:{
			id:id
		},
		dataType:"json",
		success:function(edit){
			layer.close(ii);
			if(edit.code==0){
				$("#edit_val").val(edit.id);
				$("#edit_name").val(edit.name);
				$("#edit_rate").val(edit.rate);
				$("#edit_cid").val(edit.cid);
				$("#edit_tid").attr(\'default\',edit.tid);
				$("#edit_modal").modal(\'show\');
				$("#edit_cid").change();
			}else{
				layer.alert(edit.msg);
			}
		}
	});
}
function del_member(id){
	ii=layer.load(1);
	$.ajax({
		type:"post",
		url:"ajax.php?act=del_member",
		data:{
			id:id
		},
		dataType:"json",
		success:function(del){
			layer.close(ii);
			if(del.code==0){
				layer.msg(del.msg,{icon:1,time:1500,shade:0.3});
				$.ajax({
					type:"get",
					url:"choujiang.php",
					dataType:"html",
					success:function(html){
						$("#tab").html($(html).find(\'#tab\').html());
					}
				});
			}else{
				layer.alert(del.msg);
			}
		}
	});
}
function edit_ok(id){
	ii=layer.load(1);
	var name=$("#edit_name").val();
	var cid=$("#edit_cid").val();
	var tid=$("#edit_tid").val();
	var rate=$("#edit_rate").val();
	if(!name||tid==0 || rate==\'\'){
		layer.msg("请输入完整！",{icon:2,time:1000,shade:0.3});
		layer.close(ii);
		return false;
	}
	$.ajax({
		type:"post",
		url:"ajax.php?act=edit_cj_ok",
		data:{
			id:id,name:name,tid:tid,rate:rate
		},
		dataType:"json",
		success:function(add){
			$("#edit_modal").modal(\'hide\');
			if(add.code==0){
				layer.close(ii);
				layer.msg(add.msg,{icon:1,shade:0.3,time:1500});
				window.location.href="choujiang.php";
			}else{
				layer.close(ii);
				layer.alert(add.msg);
			}
		}
	});
}

function getPrice(type){
	if(type==1){
		var price = $("#tid option:selected").attr("price");
		$("#price").val(price);
	}
	else{
		var price = $("#edit_tid option:selected").attr("price");
		$("#edit_price").val(price);
	}
}


$("#cid").change(function () {
	var cid = $(this).val();
	var ii = layer.load(2, {shade:[0.1,\'#fff\']});
	$("#tid").empty();
	$("#tid").append(\'<option value="0">请选择商品</option>\');
	$.ajax({
		type : "GET",
		url : "../ajax.php?act=gettool&cid="+cid,
		dataType : \'json\',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				if(data.class){
                    $("#sub_cid").empty();
                    $("#sub_cid").append(\'<option value="0">点我选择二级分类</option>\');
                    var num = 0;
                    $.each(data.data, function (i, res) {
                        $("#sub_cid").append(\'<option value="\'+res.cid+\'">\'+res.name+\'</option>\');
                        num++;
                    });
                    $("#sub_cid").val(0);
                    $("#display_sub_cid").show();
                }
                else{
                    $("#display_sub_cid").hide();
                    $("#tid").empty();
                    $("#tid").append(\'<option value="0">点我选择商品</option>\');
                    var num = 0;
                    $.each(data.data, function (i, res) {
                        $("#tid").append(\'<option value="\'+res.tid+\'" price="\'+res.price+\'" price1="\'+res.price1+\'">\'+res.name+\'</option>\');
                        num++;
                    });
                    $("#tid").val(0);
                    if(num==0 && cid!=0)layer.alert(\'该分类下没有商品\');
                }
			}else{
				layer.alert(data.msg);
			}
		},
		error:function(data){
			layer.msg(\'服务器错误\');
			return false;
		}
	});
});

$("#edit_cid").change(function () {
	var cid = $(this).val();
	var ii = layer.load(2, {shade:[0.1,\'#fff\']});
	$("#edit_tid").empty();
	$("#edit_tid").append(\'<option value="0">请选择商品</option>\');
	$.ajax({
		type : "GET",
		url : "../ajax.php?act=gettool&cid="+cid,
		dataType : \'json\',
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				if(data.class){
                    $("#edit_sub_cid").empty();
                    $("#edit_sub_cid").append(\'<option value="0">点我选择二级分类</option>\');
                    var num = 0;
                    $.each(data.data, function (i, res) {
                        $("#edit_sub_cid").append(\'<option value="\'+res.cid+\'">\'+res.name+\'</option>\');
                        num++;
                    });
                    var sub_cid = $("#edit_sub_cid").attr("sub_cid");
                    if(sub_cid && sub_cid>0){
                        $("#edit_sub_cid").val(sub_cid);
                        $("#edit_sub_cid").change();
                    }
                    else{
                        $("#edit_sub_cid").val(0);
                    }
                    $("#display_edit_sub_cid").show();
                }
                else{
                    $("#display_edit_sub_cid").hide();
                    $("#edit_tid").empty();
                    $("#edit_tid").append(\'<option value="0">点我选择商品</option>\');
                    var num = 0;
                    var is_in=false;
                    var tid=$("#edit_tid").attr("tid");
                    $.each(data.data, function (i, res) {
                        if (res.tid==tid) {
                            is_in=true;
                        }
                        $("#edit_tid").append(\'<option value="\'+res.tid+\'" price="\'+res.price+\'" price1="\'+res.price1+\'">\'+res.name+\'</option>\');
                        num++;
                    });

                    if (is_in==true) {
                        $("#edit_tid").val(tid);
                        $("#edit_tid").change();
                    }
                    else{
                        $("#edit_tid").val(0);
                    }
                    if(num==0 && cid!=0)layer.alert(\'该分类下没有商品\');
                }
			}else{
				layer.alert(data.msg);
			}
		},
		error:function(data){
			layer.msg(\'服务器错误\');
			return false;
		}
	});
});

$("#sub_cid").change(function () {
    getSubTools($(this).val(),"");
});

$("#edit_sub_cid").change(function () {
    getSubTools($(this).val(),"edit_");
});


$("#submit").click(function(){
	ii=layer.load(1);
	var name=$("#name").val();
	var cid=$("#cid").val();
	var tid=$("#tid").val();
	var rate=$("#rate").val();
	if(!name){
		layer.msg("请输入完整！",{icon:2,time:1000,shade:0.3});
		layer.close(ii);
		return false;
	}
	$.ajax({
		type:"post",
		url:"ajax.php?act=add_member",
		data:{
			name:name,tid:tid,rate:rate
		},
		dataType:"json",
		success:function(add){
			if(add.code==0){
				$(".modal-backdrop").remove();
				layer.close(ii);
				layer.msg(add.msg,{icon:1,shade:0.3,time:1500});
				$.ajax({
					type:"get",
					url:"choujiang.php",
					dataType:"html",
					success:function(html){
						window.location.reload();
					}
				});
			}else{
				layer.close(ii);
				layer.alert(add.msg);
			}
		}
	});
});

function getSubTools(cid,obj){
    var ii = layer.load(2, {shade:[0.1,\'#fff\']});
    $.ajax({
        type : "GET",
        url : "../ajax.php?act=gettool",
        data : "cid="+cid,
        dataType : \'json\',
        timeout  :5000,
        success : function(data) {
            layer.close(ii);
            if(data.code == 0){
                if(obj!=""){
                    $("#edit_tid").empty();
                    $("#edit_tid").append(\'<option value="0">点我选择商品</option>\');
                }
                else{
                    $("#tid").empty();
                    $("#tid").append(\'<option value="0">点我选择商品</option>\');
                }

                var num = 0;
                var is_in=false;
                var tid=$("#edit_tid").attr("tid");
                $.each(data.data, function (i, res) {
                    if (res.tid==tid && obj!="") {
                        is_in=true;
                    }
                    $("#"+obj+"tid").append(\'<option value="\'+res.tid+\'" price="\'+res.price+\'" price1="\'+res.price1+\'">\'+res.name+\'</option>\');
                    num++;
                });

                if (is_in==true && obj!="") {
                    $("#"+obj+"tid").val(tid);
                    $("#"+obj+"tid").change();
                }
                else{
                    $("#"+obj+"tid").val(0);
                }
                if(num==0 && cid!=0)layer.alert(\'该分类下没有商品\');
            }else{
                layer.alert(data.msg);
            }
        },
        error:function(data){
            layer.close(ii);
            layer.msg(\'服务器错误或超时，请刷新重试！\');
            return false;
        }
    });
}

</script>';

include_once 'footer.php';
