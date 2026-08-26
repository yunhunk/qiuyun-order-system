
<?php

/**
 * 邮件发送记录
 **/
include "../includes/common.php";
$title = '邮件发送记录';
checkLogin();

$act = isset($_GET['act']) ? daddslashes(input('get.act')) : null;

if ($act == 'del') {
    $id   = input('id', 1);
    $exec = $DB->exec("DELETE FROM `pre_ems` where `id`='{$id}'");
    if ($exec !== false) {
        $result = ['code' => 0, 'msg' => '删除成功'];
    } else {
        $result = ['code' => 0, 'msg' => '删除失败, ' . $DB->error()];
    }
    exit(json_encode($result, 256));
}

include './head.php';
?>

<style>
.mails-body{
    width:420px;
}

.mails-body div{
    max-width:420px !important;
    padding: 12px 15px  !important;
}

.box .logo_top{
    width: 125px  !important;
    margin: 0 auto !important;
}

.box .logo_top img{
    width: 100%;
    margin: 0 auto !important;
}
</style>

<?php
if (isset($_GET['uid']) && $_GET['uid'] > 0) {
    $sql = " `uid`='" . input('uid') . "'";
} elseif (isset($_GET['device']) && $_GET['device']) {
    $sql = " `device`='" . input('device') . "'";
} elseif (isset($_GET['email']) && $_GET['email']) {
    $sql = " `email`='" . input('email') . "'";
} elseif (isset($_GET['event']) && $_GET['event']) {
    $sql = " `event`='" . input('event') . "'";
} elseif (isset($_GET['kw']) && $_GET['kw']) {
    $kw  = input('kw');
    $sql = " `event`='" . $kw . "' OR `uid`='" . $kw . "'  OR `device`='" . $kw . "'  OR `email`='" . $kw . "'";
} else {
    $sql = " 1";
}

echo '<div class="col-xs-12 col-sm-12 col-lg-12 center-block" style="float: none;padding-top:10px;">
 <div class="block">
    <div class="block-title">
      <h3 class="panel-title">' . $title . '</h3>
    </div>
        <form method="GET" class="form-inline">
		  <div class="form-group">
		    <label><h3>搜索记录</h3></label>
		    <input type="text" class="form-control" style="width: 220px;" id="search" name="kw" placeholder="请输入手机号或者用户UID">
			<button type="submit" class="btn btn-primary">搜索</button>&nbsp;
            <a id="onDels" class="btn btn-danger">批量删除</a>&nbsp;
		  </div>
		</form>
       <div class="table-responsive">
        <table class="table table-striped" style="word-break: break-all;">
          <thead><th>ID</th><th>会员ID</th><th>驱动插件</th><th>事件</th><th>邮箱</th><th>验证码</th><th>邮件内容</th><th>发送结果</th><th>创建时间</th></thead>
          <tbody>';

$numrows  = $DB->count("SELECT count(*) from `pre_ems` WHERE " . $sql);
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

$rs = $DB->query("SELECT * FROM `pre_ems` WHERE {$sql} order by id desc limit $offset,$pagesize");
while ($res = $DB->fetch($rs)) {
    echo '<tr id="tr_' . $res['id'] . '">
        <td style="min-width:90px;"><div class="checkbox-inline checkbox-md"><input class="" type="checkbox" name="checkbox[]" id="list1" value="' . $res['id'] . '">' . $res['id'] . '</td>
        <td style="min-width:90px;">' . $res['uid'] . '</td>
        <td style="min-width:90px;">' . $res['device'] . '</td>
        <td style="min-width:90px;">' . $res['event'] . '</td>
        <td style="min-width:170px;">' . $res['email'] . '</td>
        <td style="min-width:90px;">' . $res['code'] . '</td>
        <td style="min-width:200px;"><div class="mails-body">' . $res['content'] . '</div></td>
        <td style="min-width:150px;">' . $res['result'] . '</td>
        <td style="min-width:185px;">' . date('Y-m-d H:i:s', $res['createtime']) . '</td>
        <td style="min-width:120px;">
            <span id="onDel" title="删除" data-id="' . $res['id'] . '" class="btn btn-danger btn-xs">删除</span>
        </td>
    </tr>';
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

function check1(field) {
    var checkbox = field || document.getElementsByName('checkbox[]');
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


$(document).on('click', '#onDels', function () {
    // 保存插件配置
    getVals();
    var ids = checkList;

    if (ids.length ==0) {
        layer.msg('未选择数据');
        return;
    }

    for (let index = 0; index < ids.length; index++) {
        let id = ids[index];
        let ii = layer.load(2, {
            shade: [0.1, "#fff"]
        });
        $.ajax({
            type: "POST",
            url: "?act=del",
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
});

$(document).on('click', '#onDel', function () {
    // 保存插件配置
    var id = $(this).data('id');
    var ii = layer.load(2, {
        shade: [0.1, "#fff"]
    });
    $.ajax({
        type: "POST",
        url: "?act=del",
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