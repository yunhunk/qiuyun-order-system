<?php

/**
 * 邮件模板
 **/

use core\Db;

include "../includes/common.php";
$title = '邮件模板';
checkLogin();

include './head.php';

$plugin_id = input('get.plugin_id', 1, 1);
if ($plugin_id < 1) {
    $plugin_id = 0;
}

$plugin_row        = null;
$smsPluginsOptions = '';

$rs = $DB->select("SELECT * FROM `pre_plugin` WHERE `type`='sms' order by id desc");

if ($rs) {
    foreach ($rs as $key => $value) {
        if (!$plugin_id && $key == 0) {
            $plugin_id  = $value['id'];
            $plugin_row = $value;
        } else if ($plugin_id && $plugin_id == $value['id']) {
            $plugin_row = $value;
        }
        $smsPluginsOptions .= '<option value="' . $value['id'] . '">' . $value['name'] . '</option>';
    }
}

if ($plugin_row) {
    // 读取模板
    $class = \core\Sms::loadClass($plugin_row['dirname'], $plugin_row);
    $list  = $class->getTemplateList();
    $count = $DB->count("SELECT count(*) FROM `pre_sms_tpl` WHERE `device`='{$plugin_row['dirname']}'");
    if ($list) {

        if ($count == 0 || $count != count($list)) {
            // 删除模板
            $DB->exec("DELETE FROM `pre_sms_tpl` WHERE `device`='{$plugin_row['dirname']}'");

            foreach ($list as $key => $value) {
                $data = [
                    'name'       => $value['name'],
                    'content'    => $value['var'],
                    'event'      => $value['event'],
                    'type'       => $value['type'],
                    'remark'     => $value['name'],
                    'device'     => $plugin_row['dirname'],
                    'device_id'  => $plugin_id,
                    'createtime' => time(),
                ];

                $find = $DB->find("SELECT * FROM `pre_sms_tpl` WHERE `device`='{$plugin_row['dirname']}' AND `name`='{$value['name']}' AND `event`='{$value['event']}'");
                if (!$find) {
                    Db::name('sms_tpl')->insert($data);
                }
            }

        } else {
            foreach ($list as $key => $value) {
                $row = $DB->find("SELECT * FROM `pre_sms_tpl` WHERE `device`='{$plugin_row['dirname']}' AND `name`='{$value['name']}' AND `event`='{$value['event']}'");
                if (!$row) {
                    $data = [
                        'name'       => $value['name'],
                        'content'    => trim($value['var']),
                        'event'      => $value['event'],
                        'type'       => $value['type'],
                        'remark'     => $value['name'],
                        'device'     => $plugin_row['dirname'],
                        'device_id'  => $plugin_id,
                        'createtime' => time(),
                    ];
                    Db::name('sms_tpl')->insert($data);
                } elseif (trim($row['content']) != trim($value['var'])) {
                    // 模板内容更新 删除模板ID
                    $data = [
                        'name'        => $value['name'],
                        'content'     => trim($value['var']),
                        'event'       => $value['event'],
                        'condition'   => 0,
                        'template_id' => '',
                    ];

                    Db::name('sms_tpl')->where([
                        'id' => $row['id'],
                    ])->update($data);
                }
            }
        }
    }

    $sql = " `device`='{$plugin_row['dirname']}'";
} else {
    $sql = " 1";
}

?>

<style>
input[type="checkbox"]{
    width: 16px;
    height: 16px;
}
</style>

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
                        <input type="hidden" id="device" name="device" value=""/>
                        <input type="hidden" id="device_id" name="device_id" value=""/>
                        <input type="hidden" id="fail_msg" name="fail_msg" value=""/>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">模板名称:</div>
                                <input class="form-control" value="" name="name">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">模板类型:</div>
                                <select class="form-control" name="type">
                                    <option value="cn">国内</option>
                                    <option value="global">国际</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">事件类型:</div>
                                <select class="form-control" name="event">
                                    <option value="send_code">发送验证码</option>
                                    <option value="findpwd">找回密码</option>
                                    <option value="change_pwd">修改密码</option>
                                    <option value="register">注册账号</option>
                                    <option value="login">登录账号</option>
                                    <option value="change_mobile">修改手机号</option>
                                    <option value="change_email">修改邮箱</option>
                                    <option value="buyorder">下单成功</option>
                                    <option value="successorder">交易成功</option>
                                    <option value="ydlogin">异地登录验证</option>
                                    <option value="kucun">库存不足通知</option>
                                    <option value="logined_tips">登录提醒</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">短信内容:</div>
                                <textarea class="form-control" name="content" rows="3" placeholder="短信内容, 不支持html代码"></textarea>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">模板ID:</div>
                                <input class="form-control" value="" name="template_id">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">模板备注:</div>
                                <input class="form-control" value="" name="remark">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">启用状态:</div>
                                <select class="form-control" name="status">
                                    <option value="1">启用</option>
                                    <option value="0">禁用</option>
                                </select>
                            </div>
                        </div>
                            <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-addon">审核状态:</div>
                                <select class="form-control" name="condition">
                                    <option value="1">通过</option>
                                    <option value="2">失败</option>
                                    <option value="3">审核中</option>
                                    <option value="0">待提交</option>
                                </select>
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
        <form id="where" method="GET" class="form-inline">
            <select name="plugin_id" class="form-control" default="<?php echo $plugin_id; ?>"><option value="-1">请选择插件</option><?php echo $smsPluginsOptions; ?></select>
			<button type="submit" class="btn btn-primary">刷新</button>&nbsp;
            <a id="onBatchSubmitSmsCondition" class="btn btn-success">批量提交审核</a>&nbsp;
            <a id="onBatchUpdateSmsCondition" class="btn btn-info">批量更新审核</a>&nbsp;
            <a id="onBatchDel" class="btn btn-danger">批量删除选中</a>&nbsp;
		  </div>
		</form>
       <div class="table-responsive">
        <table class="table table-striped" style="word-break: break-all;">
          <thead><th><input name="chkAll1" type="checkbox" id="chkAll1" />&nbsp;ID</th><th>模板名称</th><th>类型</th><th>模板ID</th><th>事件名</th><th>备注</th><th>审核状态</th><th>操作</th></thead>
          <tbody>
<?php
$numrows  = $DB->count("SELECT count(*) from `pre_sms_tpl` WHERE " . $sql);
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

$rs = $DB->query("SELECT * FROM `pre_sms_tpl` WHERE {$sql} order by id desc limit $offset,$pagesize");

while ($res = $DB->fetch($rs)) {
    ?>
    <tr id="tr_<?php echo $res['id']; ?>">
        <td><div class="checkbox-inline checkbox-md"><input class="" type="checkbox" name="checkbox[]" id="list1" value="<?php echo $res['id']; ?>"><b><?php echo $res['id']; ?></b></div></td>
        <td><?php echo $res['name']; ?></td>
        <td><?php echo $res['type'] == 'cn' ? '<span class="btn btn-primary btn-xs">国内</span>' : '<span class="btn btn-info btn-xs">国际</span>'; ?></td>
        <td><?php echo $res['template_id']; ?></td>
        <td><?php echo $res['event']; ?></td>
        <td><?php echo $res['remark']; ?></td>
        <td>
            <?php if ($res['condition'] == 1): ?>
            <span class="btn btn-success btn-xs">通过</span>
            <?php elseif ($res['condition'] == 2): ?>
            <span data-tip="<?php echo '失败信息: ' . $res['fail_msg'] ?>" class="btn btn-danger btn-xs">失败</span>
            <?php elseif ($res['condition'] == 3): ?>
            <span class="btn btn-primary  btn-xs">审核中</span>
            <?php else: ?>
            <span class="btn btn-info btn-xs">待提交</span>
            <?php endif;?>
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

?>

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

var changeNum =0 ;
$(document).on('change','[name="plugin_id"]', function () {
    changeNum++;
    if (changeNum>1) {
        var plugin_id = $(this).val();
        window.location.href = './sms.template.php?plugin_id=' + plugin_id ;
    }
});

$(document).on('click', '#onEditOpen', function () {
    // 发送测试
    var id = $(this).data('id');
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: "POST",
        url: "ajax.php?act=getSmsTplInfo",
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
                    $("[name=content]").val(res.data.content);
                    $("[name=remark]").val(res.data.remark);
                    $("[name=template_id]").val(res.data.template_id);
                    $("[name=device_id]").val(res.data.device_id);
                    $("[name=type]").attr('default', res.data.type);
                    $("[name=status]").attr('default', res.data.status);
                    $("[name=condition]").attr('default', res.data.condition);
                    $("[name=fail_msg]").val(res.data.fail_msg);
                    $("[name=device]").val(res.data.device);
                    $("[name=device_id]").val(res.data.device_id);

                    selectRender();
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
        data:  Object.assign(post, { plugin_id: id, action: 'sms'}) ,
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

// 批量提交
$(document).on('click', '#onBatchSubmitSmsCondition', function () {

    getVals();

    var ids = checkList;
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: "POST",
        url: "ajax.php?act=onBatchSubmitSmsCondition",
        data: {ids: ids},
        dataType: 'json',
        success: function (res) {
            layer.close(ii);
            if (res.code == 0) {
                layer.open({
                    content: res.msg,
                    btnAlign: 'c',
                    btn:['好的知道了'],
                    yes: function(index, layero){
                        console.log('layer', index );
                        setTimeout(window.location.reload, 200);
                    }
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
})

// 批量更新
$(document).on('click', '#onBatchUpdateSmsCondition', function () {

    getVals();
    var ids = checkList;
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: "POST",
        url: "ajax.php?act=onBatchUpdateSmsCondition",
        data: {ids: ids},
        dataType: 'json',
        success: function (res) {
            layer.close(ii);
            if (res.code == 0) {
                layer.open({
                    content: res.msg,
                    btnAlign: 'c',
                    btn:['好的知道了'],
                    yes: function(index, layero){
                        console.log('layer', index );
                        setTimeout(window.location.reload, 200);
                    }
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
})

// 批量删除
$(document).on('click', '#onBatchDel', function () {
    $.each(checkList, function (indexInArray, valueOfElement) {
        del(valueOfElement);
    });
})

$(document).on('click', '#onDel', function () {
    // 保存插件配置
    var id = $(this).data('id');
    del(id);
});

function del(id){
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: "POST",
        url: "ajax.php?act=onEmsTplDel",
        data: {id: id},
        dataType: 'json',
        async: true,
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
}

function selectRender(){
    var items = $("select[default]");
    for (var i = 0; i < items.length; i++) {
        $(items[i]).val($(items[i]).attr("default") || 0);
    }
}

$(document).ready(function () {

    $(document).on('click', '#chkAll1', function () {
        check1('list1');
    });

    selectRender();
});
</script>
<?php include 'footer.php';?>