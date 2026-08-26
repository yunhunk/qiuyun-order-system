<?php

/**
 * 邮件接口
 **/

include "../includes/common.php";
$title = '邮件接口';

checkLogin();

$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
if ($act == "set") {

    $device = input('device');

    saveSetting('ems_device', $device);

    $ret = $CACHE->clear();
    if ($ret) {
        exit('{"code":0,"msg":"成功"}');
    } else {
        exit('{"code":-1,"msg":"失败, ' . $DB->error() . '"}');
    }
}

include './head.php';

\core\Ems::getPluginList(true);

$sql = " `type`='mails'";

?>

<div class="col-xs-12 col-sm-12 col-lg-12 center-block" style="float: none;padding-top:10px;">
    <!-- 插件配置 -->
   <div class="modal fade" id="config_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">插件配置</h4>
            </div>
            <div class="modal-body">
					<form id="form-config">
                        <input type="hidden" id="plugin_id" name="plugin_id" value=""/>
                        <div id="form-config-list">

                        </div>
                    </form>
					<div class="form-group">
					    <a id="onConfigSave" class="btn btn-primary btn-block" >保存配置</a>
					    <br>
					    <a class="btn btn-danger btn-block" id="submit" data-dismiss="modal">取消</a>
					</div>
            </div>
        </div>
    </div>
</div>
    <!-- 插件配置 end -->

    <!-- 插件测试-->
   <div class="modal fade" id="test_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">发送测试</h4>
            </div>
            <div class="modal-body">
					<form id="form-test">
                        <input type="hidden" id="plugin_id" name="plugin_id" value=""/>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">接收邮箱:</div>
                                <input class="form-control" value="" name="email">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">邮件模板:</div>
                                <select class="form-control" name="template_id" id="template_id" defaut="utf-8">

                                </select>
                            </div>
                        </div>
                        <div class="form-group" id="display_body" style="display: none;">
                            <div class="input-group">
                                <div class="input-group-addon">自定义内容:</div>
                                <textarea class="form-control" rows="3" value="" name="body"></textarea>
                            </div>
                        </div>
                    </form>
					<div class="form-group">
					    <a id="onTestSend" class="btn btn-primary btn-block" >立即发送</a>
					    <br>
					    <a class="btn btn-danger btn-block" id="submit" data-dismiss="modal">关闭窗口</a>
					</div>
            </div>
        </div>
    </div>
</div>
    <!-- 插件配置 end -->
 <div class="block">
    <div class="block-title">
      <h3 class="panel-title"><?php echo $title; ?></h3>
    </div>
       <div class="table-responsive">
        <table class="table table-striped" style="word-break: break-all;">
          <thead><th>插件名称</th><th>插件ID别名</th><th>作者</th><th>使用说明</th><th>状态</th><th>操作</th></thead>
          <tbody>
<?php
$numrows  = $DB->count("SELECT count(*) from `pre_plugin` WHERE " . $sql);
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

$rs = $DB->query("SELECT * FROM `pre_plugin` WHERE {$sql} order by id desc limit $offset,$pagesize");

while ($res = $DB->fetch($rs)) {
    ?>
    <tr>
        <td><?php echo $res['name']; ?></td>
        <td><?php echo $res['dirname']; ?></td>
        <td><?php echo $res['author']; ?></td>
        <td> <a href="<?php echo $res['link']; ?>" target="_blank">使用说明</a> </td>
        <td>
            <span id="change" title="点击切换"  data-device="<?php echo $res['dirname']; ?>" class="btn btn-<?php echo $res['dirname'] == $conf['ems_device'] ? 'success' : 'danger' ?> btn-xs"><?php echo $res['dirname'] == $conf['ems_device'] ? '启用' : '停用' ?></span>
        <td>
            <span id="onConfigEdit" title="点击配置插件"  data-id="<?php echo $res['id']; ?>" class="btn btn-success btn-xs">配置插件</span>
            <span id="onTestSendOpen" title="点击测试发送"  data-id="<?php echo $res['id']; ?>" class="btn btn-primary btn-xs">测试发送</span>
        </td>
    </tr>
<?php
}

echo ' </tbody>
        </table>
      </div>';

echo '</div>';
?>
<script>
$(document).on('click', '#onConfigSave', function () {
    // 保存插件配置
    var postArr =  $("#form-config").serializeArray();
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
        url: "ajax.php?act=onPluginConfigSave",
        data: {config: post , plugin_id: id},
        dataType: 'json',
        success: function (data) {
            layer.close(ii);
            if (data.code == 0) {
                layer.msg(data.msg);
                $("#config_modal").modal('hide');
            } else {
                layer.alert(data.msg);
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


$(document).on('click', '#onTestSendOpen', function () {
    // 发送测试
    var id = $(this).data('id');
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: "POST",
        url: "ajax.php?act=getMailsTplList",
        data: {plugin_id: id, type:'mails'},
        dataType: 'json',
        success: function (res) {
            layer.close(ii);
            if (res.code == 0) {
                $("#plugin_id").val(id);
                $("#test_modal").modal('show');
                $('#test_modal').on('shown.bs.modal', function () {
                    $("#template_id").empty();
                    $("#template_id").append("<option value='0'>自定义内容</option>");
                    $.each(res.data.list, function (index, item) {
                        $("#template_id").append("<option value="+item.id+">" +item.name+"</option>");
                    });

                    $(document).on('change',  "#template_id", function () {
                        var value = $(this).val();
                        if (value == '0') {
                            $("#display_body").show();
                        }
                    });
                    $("#template_id").val(0).change();
                })
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


$(document).on('click', '#onTestSend', function () {
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
        url: "ajax.php?act=onTestSend",
        data:  Object.assign(post, { plugin_id: id}) ,
        dataType: 'json',
        success: function (res) {
            layer.close(ii);
            if (res.code == 0) {
                layer.msg(res.msg);
                $("#test_modal").modal('hide');
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


$(document).on('click', '#onConfigEdit', function () {
    // 保存插件配置
    var id = $(this).data('id');
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: "POST",
        url: "ajax.php?act=getPluginConfigList",
        data: {plugin_id: id},
        dataType: 'json',
        success: function (res) {
            layer.close(ii);
            if (res.code == 0) {
                $("#plugin_id").val(id);
                var html = '';
                var config = res.data.config;
                $.each(res.data.list, function (index, item) {
                 var htmlItem = `<div class="form-group">
                            <div class="input-group">
                            <div class="input-group-addon">`+item.label+`:</div>`;
                    switch (item.type) {
                        case 'select':
                            var options = '';
                            $.each(item.options, function (i, row) {
                                    options+="<option value="+row.value+">" +row.label+"</option>";
                            });
                            htmlItem+= '<select class="form-control" name="'+item.field+'">'+options+'</select>';
                            break;
                        case 'string':
                            htmlItem+= '<input class="form-control" value="'+(item.value?item.value:'')+'" name="'+item.field+'"/>';
                            break;
                        default:
                            break;
                    }


                    htmlItem+=`</div>`;
                    if (item.tips) {
                        htmlItem+='<small>' + item.tips+'</small>';
                    }
                    htmlItem+=`</div>`;
                    html+=htmlItem;
                });
                $("#form-config-list").html(html);
                $("#config_modal").modal('show');
                $('#config_modal').on('shown.bs.modal', function () {
                    try {
                        $.each(config, function (index, val) {
                            $('[name="'+index+'"]').val(val);
                            $('[name="'+index+'"]').attr('defaut', val);
                        });
                    } catch (error) {

                    }
                    selectRender();
                })
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

$(document).on('click', '#change',  function () {
    var device = $(this).data('device');
    var ii = layer.load(2, {shade:[0.1,'#fff']});
    $.ajax({
        type : 'GET',
        url : '?act=set&device='+device,
        dataType : 'json',
        success : function(data) {
            layer.close(ii);
            window.location.reload();
        },
        error:function(data){
            layer.close(ii);
            layer.msg('服务器错误');
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