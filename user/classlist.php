<?php
/**
 * 分类管理
 **/
include "../includes/common.php";
$title = '分类管理';
include './head.php';
if ($isLogin2 == 1) {} else {
    exit("<script language='javascript'>window.location.href='./login.php?goto=" . $goto . "';</script>");
}

?>
<div class="wrapper">
  <div class="col-sm-12 center-block" style="float: none;">


<?php
if ($userrow['power'] == 0) {
    showmsg('你没有权限使用此功能！', 3);
}
$classhide = explode(',', $userrow['class']);
?>
<div class="panel panel-default">
    <div class="panel-heading font-bold" style="background-color: #9999CC;color: white;">
        <h3 class="panel-title"><font color="#fff">商品分类管理</font></h3>
    </div>
    <div class="panel-body">
    	<div class="form-inline">
    		<div class="form-group">
    			<a class="btn btn-success btn-sm" href="javascript:;" id="setActiveAll">全部设置显示</a>
    		</div>
    	</div>
    	<div class="table-responsive">
	        <table class="table table-striped">
	          <thead><tr><th>分类名称</th><th>是否显示</th></tr></thead>
	          <tbody><form id="classlist">
	<?php

$rs = $DB->select("SELECT * FROM cmy_class WHERE `active`=1 AND (`upcid`=0 OR `upcid` is null)  ORDER BY sort ASC");
if ($rs) {
    foreach ($rs as $key => $res) {
        echo '<tr><td><input type="text" class="form-control input-sm" name="name' . $res['cid'] . '" value="' . $res['name'] . '" placeholder="分类名称" disabled></td><td>' . (in_array($res['cid'], $classhide) ? '<span class="btn btn-sm btn-warning" onclick="setActive(' . $res['cid'] . ',1)">隐藏</span>' : '<span class="btn btn-sm btn-success" onclick="setActive(' . $res['cid'] . ',0)">显示</span>') . '</td></tr>';
        $rs2 = $DB->select("SELECT * FROM cmy_class WHERE `active`=1 AND `upcid`='{$res['cid']}'  ORDER BY sort ASC");
        if ($rs2) {
            foreach ($rs2 as $key => $res2) {
                echo '<tr><td><input type="text" class="form-control input-sm" name="name' . $res2['cid'] . '" value="|—' . $res2['name'] . '" placeholder="子分类名称" disabled></td><td>' . (in_array($res2['cid'], $classhide) || in_array($res['cid'], $classhide) ? '<span class="btn btn-sm btn-warning" onclick="setActive(' . $res2['cid'] . ',1)">隐藏</span>' : '<span class="btn btn-sm btn-success" onclick="setActive(' . $res2['cid'] . ',0)">显示</span>') . '</td></tr>';
            }
        }
    }

}

?>
				</form>
	          </tbody>
	        </table>
	      </div>
    </div>

    </div>
<script src="<?php echo $cdnpublic ?>layer/2.3/layer.js"></script>
<script>
$(document).on('click', '#setActiveAll', function(event) {
	event.preventDefault();
	/* Act on the event */
	$.ajax({
		type : 'GET',
		url : 'ajax.php?act=setActiveAll&active=1',
		dataType : 'json',
		success : function(data) {
			if (data.code == 0){
			 	layer.msg('成功',{
				end: function () {
						window.location.reload()
					}
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
});
function setActive(cid,active) {
	$.ajax({
		type : 'GET',
		url : 'ajax.php?act=setClass&cid='+cid+'&active='+active,
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
</script></div>
</div>
<?php include 'footer.php'?>