<?php
/**
 * 快捷模板管理列表
 * 星河云
 **/
include "../includes/common.php";
checkLogin();
$title = ' 快捷模板管理列表';
function display_status($status, $id)
{
    if ($status == 1) {
        return '<a class="btn btn-success btn-xs" href="JavaScript:setstatus(' . $status . ',' . $id . ')">使用中</a>';
    } else {
        return '<a class="btn btn-warning btn-xs" href="JavaScript:setstatus(' . $status . ',' . $id . ')">待使用</font>';
    }

}

$my = isset($_GET['my']) ? $_GET['my'] : null;
if ($my == 'deleteModel') {
    $id  = intval($_POST['id']);
    $sql = "delete from `pre_model` where `id`='$id'";
    if ($DB->query($sql)) {
        exit('{"code":0,"msg":"模板删除成功！"}');
    } else {
        exit('{"code":-1,"msg":"模板删除失败！' . $DB->error() . '"}');
    }
} elseif ($my == 'Model_status') {
    $id     = trim($_GET['id']);
    $status = trim($_GET['status']) == 1 ? 0 : 1;
    $sql    = "UPDATE `pre_model` set `status`='$status' where id='$id'";
    if ($DB->query($sql)) {
        exit('{"code":0,"msg":"状态修改成功！"}');
    } else {
        exit('{"code":-1,"msg":"状态修改失败！' . $DB->error() . '"}');
    }
} elseif ($my == 'eidt_Model_start') {
    $id    = trim(getParams("id"));
    $title = trim($_POST['title']);
    $text  = trim($_POST['text']);
    $sql   = "UPDATE `pre_model` set title=:title,`text`=:text where id=:id";
    if ($DB->query($sql, [':title' => $title, ':text' => $text, ':id' => $id])) {
        exit('{"code":0,"msg":"模板修改成功！"}');
    } else {
        exit('{"code":-1,"msg":"模板修改失败！' . $DB->error() . '"}');
    }
} elseif ($my == 'eidt_Model') {
    $id  = input('get.id', 1);
    $row = $DB->get_row("SELECT * from `pre_model` where id='$id' limit 1");
    include './head.php';

    echo '<div class="col-sm-12 col-md-12 center-block" style="float: none;padding-top: 10px;">
    <div class="block">
    <div class="block-title">
      <h3 class="panel-title">快捷回复模板配置修改</h3>
    </div>
    <div class="panel-body">
    	<div class="form-group">
	      <label class="col-sm-2 control-label">模板标题:</label>
	      <div class="col-sm-10 input-group">
	         <textarea class="form-control" name="Model_title" rows="3">' . htmlspecialchars($row['title']) . '</textarea>
	      </div>
	    </div><br/>
	    <div class="form-group">
	      <label class="col-sm-2 control-label">模板内容:</label>
	      <div class="col-sm-10 input-group">
	         <textarea class="form-control" name="Model_text" rows="10">' . htmlspecialchars($row['text']) . '</textarea>
	      </div>
	    </div><br/>
	    <div class="form-group">
	    <button type="submit" id="eidt_Model" class="btn btn-primary btn-block">确认修改</button>
	    </div><br/>
	 </div>

  </div>
  <hr>
  <div class="form-group">
		<a href="./model.php?my=Model" class="btn btn-danger btn-rounded msglist"><i class="fa fa-commenting"></i>&nbsp;模板列表</a>
		<a href="./" class="btn btn-info btn-rounded userhome" style="float:right;"><i class="fa fa-user"></i>&nbsp;后台主页</a>
  </div>';

    echo '<script>
	$(document).ready(function(){
		    $("#eidt_Model").click(function(){
			var title=$("textarea[name=\'Model_title\']").val();
			var text=$("textarea[name=\'Model_text\']").val();
			var id=\'' . $id . '\';
			if(title=="" || text==""){
			 layer.alert(\'模板标题或模板内容不能为空！\');
			  return false;
			}
			eidt_Model(title,text,id);
	    });
	});
	function eidt_Model(title,text,id){
		var ii=layer.load(2, {shade:[0.1,\'#fff\']});
		$.ajax({
			type : \'POST\',
			url : \'?my=eidt_Model_start\',
			dataType : \'json\',
			data : {title:title,text:text,id:id},
			success : function(data) {
				layer.close(ii);
				if(data.code == 0){
					layer.msg(data.msg);
				}
				else{
					layer.alert(data.msg);
				}
			},
			error:function(data){
				layer.msg(\'服务器错误\');
				return false;
			}
		});
	}
	</script>';

} elseif ($my == 'add_Model') {
    $title = trim($_POST['title']);
    $text  = trim($_POST['text']);
    $sql   = "INSERT into `pre_model` (`title`, `text`,`status`,`addtime`) values ( ?, ?, '1', ?)";
    if ($DB->query($sql, array($title, $text, $date))) {
        exit('{"code":0,"msg":"模板添加成功！"}');
    } else {
        exit('{"code":-1,"msg":"模板添加失败！' . $DB->error() . '"}');
    }
} elseif ($my == 'Model') {
    $numrows = $DB->count("SELECT count(*) from cmy_model where 1");
    include './head.php';

    echo '<div class="col-sm-12 col-md-12 center-block" style="float: none;padding-top: 10px;">
    <div class="block">
    <div class="block-title">
      <h3 class="panel-title">快捷模板列表</h3>
    </div>
    <div class="alert alert-info">
    	 模板标题尽量简短，5-8字左右即可！因为在处理页面需要显示快捷模板列表，过长将影响显示！<br>
    </div>
	<div class="form-inline">
      <div class="form-group">
	    <label>模板标题:</label>
	     <div class="form-group">
	   <textarea class="form-control" name="Model_title" cols="90" rows="1"></textarea>
	  </div>
	  </div>
	  <br><br>
	  <div class="form-group">
	    <label>模板内容:</label>
	     <div class="form-group">
	      <textarea class="form-control" name="Model_text" cols="90" rows="10"></textarea>
	  </div>
	  </div>
	  <button type="submit" id="add_model" class="btn btn-primary">新建模板</button>
	</div> <br>
<div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>模板ID</th><th>模板短标题</th><th>模板内容概览</th><th>使用状态</th><th>添加时间</th><th>操作</th></tr></thead>
          <tbody>';

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
    $rs     = $DB->query("SELECT * FROM cmy_model WHERE 1 order by id desc limit $offset,$pagesize");
    while ($res = $DB->fetch($rs)) {
        $title = strip_tags($res['title']);
        $text  = strip_tags($res['text']);
        if (strlen($title) > 15) {
            $title = mb_substr($title, 0, 15) . '...';

        }
        if (strlen($text) > 15) {
            $text = mb_substr($text, 0, 15) . '...';
        }
        echo '<tr><td>' . $res['id'] . '</td><td>' . $title . '</td><td><font color=blue>' . $text . '</font></td><td>' . display_status($res['status'], $res['id']) . '</td><td>' . $res['addtime'] . '</td><td><a href="./model.php?my=eidt_Model&id=' . $res['id'] . '" class="btn btn-primary btn-xs">编辑</a>&nbsp;<a href="JavaScript:void(0)" class="btn btn-danger btn-xs">删除</a></td></tr>';
    }

    echo ' </tbody>
        </table>
      </div>';
# 分页
    $PageList = new \core\Page($numrows, $pagesize, 0, $link);
    echo $PageList->showPage();

    echo '<script>
$(document).ready(function(){
    $("#add_model").click(function(){
		var title=$("textarea[name=\'Model_title\']").val();
		var text=$("textarea[name=\'Model_text\']").val();
		if(title==\'\' | text==\'\'){
		 layer.alert(\'标题或内容不能为空！\');
		  return false;
		}
		add_Model(title,text);
    });
});
function add_Model(title,text){
	var ii=layer.load(2, {shade:[0.1,\'#fff\']});
	$.ajax({
		type : \'POST\',
		url : \'?my=add_Model\',
		dataType : \'json\',
		data : {title:title,text:text},
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				layer.msg(data.msg);
				setTimeout(function(){
				window.location.reload();
				},1000);

			}
			else{
				layer.alert(data.msg);
			}
		},
		error:function(data){
			layer.msg(\'服务器错误\');
			return false;
		}
	});
}
function setstatus(status,id){
	var ii=layer.load(2, {shade:[0.1,\'#fff\']});
	$.ajax({
		type : \'GET\',
		url : \'?my=Model_status\',
		dataType : \'json\',
		data : {status:status,id:id},
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				layer.msg(data.msg);
				setTimeout(function(){
				window.location.reload();
				},1000);
			}
			else{
				layer.alert(data.msg);
			}
		},
		error:function(data){
			layer.msg(\'服务器错误\');
			return false;
		}
	});
}
function deleteModel(id){
	var ii=layer.load(2, {shade:[0.1,\'#fff\']});
	$.ajax({
		type : \'POST\',
		url : \'?my=deleteModel\',
		dataType : \'json\',
		data : {id:id},
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
                layer.msg(data.msg);
				setTimeout(function(){
				window.location.reload();
				},1000);
			}
			else{
				layer.alert(data.msg);
			}
		},
		error:function(data){
			layer.msg(\'服务器错误\');
			return false;
		}
	});
}
</script>';

} elseif ($my == 'getReply') {
    $id  = trim($_POST['id']);
    $row = $DB->get_row("select * from cmy_model where id='$id' limit 1");
    if ($row) {
        $reply = $row['text'];
        $reply = str_replace(array("\r\n", "\r", "\n"), "", $reply);

        exit('{"code":0,"msg":"快捷回复内容获取成功！","reply":"' . htmlspecialchars($reply) . '"}');
        //exit('{"code":0,"msg":"快捷回复内容获取成功！","reply":"'.$row['text'].'"}');
    } else {
        exit('{"code":-1,"msg":"快捷回复内容获取失败！' . $DB->error() . '"}');
    }
}

echo '</div>
  </div>';

include './footer.php';
