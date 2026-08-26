<?php

/**
 * 邮件模板
 **/

include "../includes/common.php";
$title = '邮件模板';
checkLogin();

include './head.php';

$sql = " 1";

?>

<div class="col-xs-12 col-sm-12 col-lg-12 center-block" style="float: none;padding-top:10px;">
    <!-- 模板编辑/添加-->
   <div class="modal fade" id="save_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">模板编辑</h4>
            </div>
            <div class="modal-body">
					<form id="form-test">
                        <input type="hidden" id="id" name="id" value=""/>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">模板名称:</div>
                                <input class="form-control" value="" name="name">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">事件类型:</div>
                                <select class="form-control" name="event">
                                    <option value="send_code">发送验证码</option>
                                    <option value="findpwd">找回密码</option>
                                    <option value="register">注册账号</option>
                                    <option value="login">登录账号</option>
                                    <option value="safecheck">安全验证</option>
                                    <option value="change_pwd">修改密码</option>
                                    <option value="change_info">修改资料</option>
                                    <option value="change_mobile">修改手机号</option>
                                    <option value="change_email">修改邮箱</option>
                                    <option value="buyorder">下单成功</option>
                                    <option value="successorder">交易成功</option>
                                    <option value="ydlogin">异地登录</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">邮件标题:</div>
                                <input class="form-control" value="" name="subject">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">邮件内容:</div>
                                <div class="" id="editorBox"></div>
                                <textarea class="form-control hide textDom" name="body" rows="3" placeholder="当选择该商品时自动显示, 支持HTML代码"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">模板备注:</div>
                                <input class="form-control" value="" name="remark">
                            </div>
                        </div>
                    </form>
					<div class="form-group">
					    <a id="onSaveTpl" class="btn btn-primary btn-block" >保存模板</a>
					    <br>
					    <a class="btn btn-danger btn-block" id="submit" data-dismiss="modal">关闭窗口</a>
					</div>
            </div>
        </div>
    </div>
</div>
    <!-- 模板编辑 end -->
 <div class="block">
    <div class="block-title">
      <h3 class="panel-title"><?php echo $title; ?></h3>
    </div>
       <div class="table-responsive">
        <table class="table table-striped" style="word-break: break-all;">
          <thead><th>模板名称</th><th>事件名</th><th>标题</th><th>备注</th><th>状态</th><th>操作</th></thead>
          <tbody>
<?php
$numrows  = $DB->count("SELECT count(*) from `cmy_ems_tpl` WHERE " . $sql);
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

$rs = $DB->query("SELECT * FROM `cmy_ems_tpl` WHERE {$sql} order by id desc limit $offset,$pagesize");

while ($res = $DB->fetch($rs)) {
    ?>
    <tr id="tr_<?php echo $res['id']; ?>">
        <td><?php echo $res['name']; ?></td>
        <td><?php echo $res['event']; ?></td>
        <td><?php echo $res['subject']; ?></td>
        <td><?php echo $res['remark']; ?></td>
        <td>
            <span id="change" title="点击切换"  data-id="<?php echo $res['id']; ?>" class="btn btn-<?php echo $res['status'] == 1 ? 'success' : 'danger' ?> btn-xs"><?php echo $res['status'] == 1 ? '正常' : '禁用' ?></span>
        <td>
            <span id="onEditOpen" title="编辑" data-id="<?php echo $res['id']; ?>" class="btn btn-success btn-xs">编辑</span>
            <span id="onDel" title="删除"  data-id="<?php echo $res['id']; ?>" class="btn btn-danger btn-xs">删除</span>
        </td>
    </tr>
<?php
}

echo ' </tbody>
        </table>
      </div>';

#分页
$pageList = new \core\Page($numrows, $pagesize, 0, $link);
echo $pageList->showPage();

echo '</div>';

echo '<script src="' . $cdnserver . 'assets/public/wangEditor/3.1.1/wangEditor.js?' . $jsver . '" type="text/javascript"></script>
	  <script src="' . $cdnserver . 'assets/public/wangEditor/3.1.1/plugin.js?' . $jsver . '" type="text/javascript"></script>
	  ';

echo '
    <script src="' . $cdnserver . 'assets/public/wangEditor/3.1.1/wangEditor.js?' . $jsver . '" type="text/javascript"></script>
    <script src="' . $cdnserver . 'assets/public/wangEditor/3.1.1/plugin.js?' . $jsver . '" type="text/javascript"></script>
    ';
echo '
<script type="text/javascript">
';
echo '
	var $is_mb = \'' . ($is_mb === true ? '1' : '0') . '\';
	var E = window.wangEditor ? window.wangEditor : null;
	var editor2,editor3,content,content2;
	';
echo '
function editorInit() {
	if ($(\'.textDom\').length>0 && "function" === typeof window.wangEditor) {
	    if ($(\'.textDom\').length > 0) {
			content = $(\'.textDom\');
		}
		$("#editorBox").show();
		editor2 = new E(\'#editorBox\');
		if(typeof boxType != "undefined"){
			$(\'#editorBox\').css({\'display\':\'block\',\'width\':\'100%\',\'float\':\'none\'});
		}
		editor2.customConfig.uploadImgServer = \'upload.php\';
		editor2.customConfig.uploadImgTimeout = 60 * 1000;
		editor2.customConfig.uploadFileName = \'filedata\';
		editor2.customConfig.mobile = $is_mb;
		editor2.customConfig.uploadImgHooks = {
		    success: function (xhr, editor, result) {
		    	   if (result.errno==0) {
		             layer.msg("图片插入成功");
		    	   }
		    	   else{
		    	   	    if (!result.msg) {
			             	result.msg="上传图片失败！请检查";
			    	    }
			    	    layer.alert(result.msg);
		    	   }
		    },
		    timeout: function (xhr, editor) {
		        layer.alert("图片上传超时，如文件过大请尝试剪切或压缩到较小后再试！");
		    },
		    fail: function (xhr, editor, result) {
				 if("object" == typeof result && "undefined" !=typeof result.msg){
				 	layer.alert(result.msg);
				 }
				 else{
				 	layer.alert("上传失败，请在浏览器按F12切换到network截图【上传请求的结果】在工单反馈");
				 }
		    }
		}
		editor2.customConfig.onchange = function (newHtml) {
		    if (typeof content =="object") {
		    	!content.val(""+newHtml) && layer.msg("编辑内容同步失败，请截图并提供详细描述提交工单反馈");
		    	return;
		    }
		    layer.msg("编辑内容同步失败，请截图并提供详细描述提交工单反馈");
		    return;
		}
		editor2.customConfig.onblur = function (newHtml) {
		    // 检测无用html内容
            if (editor2.txt.isEmptyHtml(newHtml)) {
                newHtml = "";
                editor2.txt.html("");
                editor2.txt.change();
            }
		    return;
		}

		if(typeof(editorMenu)=="object" && editorMenu instanceof Array){
			editor2.customConfig.menus = editorMenu;
		}
		editor2.create();
		if (content.val()!="") {
			editor2.txt.html(content.val());
		}
		else{
			editor2.txt.html("");
		}

		E.viewSource.init(editor2);

	}

	//商品页面附加富文本
	if ($(\'.textDom2\').length>0 && "function" === typeof window.wangEditor) {
		if ($(\'.textDom2\').length>1) {
			content2 = $(\'.textDom2\')[0];
		}
		else{
			content2 = $(\'.textDom2\');
		}

		editor3 = new E(\'#editorBox2\');
		if(typeof boxType != "undefined"){
			$(\'#editorBox2\').css({\'display\':\'block\',\'width\':\'100%\',\'float\':\'none\'});
		}
		editor3.customConfig.uploadImgServer = \'upload.php\';
		editor3.customConfig.uploadImgTimeout = 60 * 1000;
		editor3.customConfig.uploadFileName = \'filedata\';
		editor3.customConfig.mobile = $is_mb;
		editor3.customConfig.uploadImgHooks = {
		    success: function (xhr, editor, result) {
		    	   if (result.errno==0) {
		             layer.msg("图片插入成功");
		    	   }
		    	   else{
		    	   	   if (!result.msg) {
				            result.msg="上传图片失败！请检查";
				    	   }
				    	   layer.alert(result.msg);
		    	   }
		    },
		    timeout: function (xhr, editor) {
		        layer.alert("图片上传超时，如文件过大请尝试剪切或压缩到较小后再试！");
		    },
		    fail: function (xhr, editor, result) {
		        layer.alert("图片插入返回状态异常，请反馈作者处理！<br>返回信息：" + result);
		    }
		}
		if(typeof(editorMenu)=="object" && editorMenu instanceof Array){
			editor3.customConfig.menus = editorMenu;
		}
		editor3.customConfig.onchange = function (newHtml) {
		    if (typeof content2 =="object") {
		    	!content2.val(""+newHtml) && layer.msg("编辑内容同步失败，请截图并提供详细描述提交工单反馈");
		    	return;
		    }
		    layer.msg("编辑内容同步失败，请截图并提供详细描述提交工单反馈");
		    return;
		}
		editor3.customConfig.onblur = function (newHtml) {
		    // 检测无用html内容
            if (editor3.txt.isEmptyHtml(newHtml)) {
                newHtml = "";
                editor3.txt.html("");
                editor3.change();
            }
		    return;
		}
		editor3.create();
		if (content2.val()!="") {
			editor3.txt.html(content2.val());
		}
		else{
			editor3.txt.html("");
		}


		E.viewSource.init(editor3);

	}

	if( "function" !== typeof window.wangEditor ){
		if ($(\'.textDom\').length > 0) {
			if("function" == typeof($(\'.textDom\').removeClass)){
				$(\'.textDom\').removeClass("hide").addClass("form-control").attr("rows", "6").show();
			}
			else{
				$(\'.textDom\').attr("class", "form-control textDom").attr("rows", "6").show();
			}
		}

		if ($(\'.textDom2\').length > 0) {
			if("function" == typeof($(\'.textDom2\').removeClass)){
				$(\'.textDom2\').removeClass("hide").addClass("form-control").attr("rows", "6").show();
			}
			else{
				$(\'.textDom2\').attr("class", "form-control textDom2").attr("rows", "6").show();
			}
		}
	}

}
';
echo '
window.onload=function(){
	var items = $("select[default]");
	for (i = 0; i < items.length; i++) {
	    $(items[i]).val($(items[i]).attr("default")||0);
	    if(typeof(changeEvent)=="undefined" || changeEvent!==false){
	    	$(items[i]).change();
	    }
	};
    editorInit();
}
';

echo '
function editorChange(){
	if(typeof(editor2) =="object" && typeof(editor2.txt.html) =="function"){
		//将商品简介同步到表单
		var text = $(content).val();
		if(text != ""){
        	editor2.txt.html(text, false);
        }
	}

	if(typeof editor3 =="object" && typeof editor3.txt.html =="function"){
		//将提示内容同步到表单
		var text = $(content2).val();
        if(text != ""){
        	editor3.txt.html(text, false);
        }
	}
}
$(document).on("click", ".emptyText", function(event) {
    event.preventDefault();
    /* Act on the event */
    $(content).val("");
    editor2.txt.html("", false);
});
$(document).on("click", ".emptyText2", function(event) {
    event.preventDefault();
    /* Act on the event */
    $(content2).val("");
    editor3.txt.html("", false);
});
</script>
';
?>

<script>
$(document).on('click', '#onEditOpen', function () {
    // 发送测试
    var id = $(this).data('id');
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: "POST",
        url: "ajax.php?act=getMailsTplInfo",
        data: {id: id, type:'mails'},
        dataType: 'json',
        success: function (res) {
            layer.close(ii);
            if (res.code == 0) {
                $("#id").val(id);
                $("#save_modal").modal('show');
                $('#save_modal').on('shown.bs.modal', function () {
                    // 写入数据
                    $("[name=name]").val(res.data.name);
                    $("[name=event]").attr('default', res.data.event);
                    $("[name=subject]").val(res.data.subject);
                    $("[name=body]").val(res.data.body);
                    $("[name=remark]").val(res.data.remark);
                    selectRender();
                    setTimeout(editorInit, 100);
                });
                $('#save_modal').on('hidden.bs.modal', function () {
                    // 还原
                    $("[name=body]").val('');
                    editorChange();
                });
            } else {
                layer.alert(res.msg);
            }
            return;
        },
        error: function (data) {
            layer.close(ii);
            layer.msg('服务器错误。请稍后再试');
            return false;
        }
    });
});


$(document).on('click', '#onSaveTpl', function () {
    // 保存插件配置
    var postArr =  $("#form-test").serializeArray();
    var post = {};
    $.each(postArr, function (indexInArray, valueOfElement) {
        post[valueOfElement.name] = valueOfElement.value
    });
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    var  id=  $("#plugin_id").val();
    $.ajax({
        type: "POST",
        url: "ajax.php?act=onSaveTpl",
        data:  Object.assign(post, { plugin_id: id, action: 'mails'}) ,
        dataType: 'json',
        success: function (res) {
            layer.close(ii);
            if (res.code == 0) {
                layer.msg(res.msg);
                $("#save_modal").modal('hide');
            } else {
                layer.alert(res.msg);
            }
            return;
        },
        error: function (data) {
            layer.close(ii);
            layer.msg('服务器错误。请稍后再试');
            return false;
        }
    });
});

$(document).on('click', '#onDel', function () {
    // 保存插件配置
    var id = $(this).data('id');
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: "POST",
        url: "ajax.php?act=onEmsTplDel",
        data: {id: id},
        dataType: 'json',
        success: function (res) {
            layer.close(ii);
            if (res.code == 0) {
                layer.msg(res.msg);
                $("#tr_"+ id).remove();
            } else {
                layer.alert(res.msg);
            }
            return;
        },
        error: function (data) {
            layer.close(ii);
            layer.msg('服务器错误。请稍后再试');
            return false;
        }
    });
});

function selectRender(){
    var items = $("select[default]");
    for (var i = 0; i < items.length; i++) {
        $(items[i]).val($(items[i]).attr("default") || 0);
    }
}
</script>
<?php include 'footer.php';?>