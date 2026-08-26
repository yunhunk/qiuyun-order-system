<?php
include '../includes/common.php';
$title = '数据备份管理';
checkLogin();
$backupDir = rtrim(__DIR__, '/') . '/backup/database';
$act       = isset($_GET['act']) ? daddslashes(input('get.act')) : null;
if ($act == 'recovery') {
    $filename = input('post.filename', 1);
    $filepath = $backupDir . '/' . $filename;
    if (is_file($filepath)) {
        $ok   = 0;
        $err  = '';
        $sqls = file_get_contents($filepath);
        $arr  = explode(";\r\n", $sqls);
        foreach ($arr as $key => $sql) {
            $sql = trim($sql, "\r");
            $sql = trim($sql, "\n");
            $sql = trim($sql, "\r\n");
            if ($sql != "") {
                if ($DB->query($sql)) {
                    $ok++;
                } else {
                    $err .= "<br/>原语句：" . $sql . "；错误信息：" . $DB->error();
                }
            }
        }
        if ($ok > 0) {
            $result = ['code' => 0, 'msg' => '导入成功，共执行' . $ok . '条语句！<br/>' . ($err ? '错误信息：<br/>' . $err : '')];
        } else {
            $result = ['code' => -1, 'msg' => '导入失败！<br/>错误信息：<br/>' . $err];
        }
    } else {
        $result = ['code' => -1, 'msg' => '该备份文件不存在！'];
    }
    exit(json_encode($result));
} elseif ($act == 'del') {
    $filename = input('post.filename', 1);
    $filepath = $backupDir . '/' . $filename;
    if (is_file($filepath)) {
        $scriptpath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
        $scriptpath = substr($scriptpath, 0, strrpos($scriptpath, '/'));
        $admin_path = substr($scriptpath, strrpos($scriptpath, '/') + 1);
        if (unlink($filepath)) {
            $result = ['code' => 0, 'msg' => '删除成功！'];
        } else {
            $result = ['code' => -1, 'msg' => '删除失败，请到[' . $admin_path . '/backup/database/]手动删除！'];
        }
    } else {
        $result = ['code' => -1, 'msg' => '该备份文件不存在，无需删除！'];
    }
    exit(json_encode($result));
}

include './head.php';

$list  = [];
$files = scandir($backupDir);
foreach ($files as $key => $filename) {
    if ($filename === "." || $filename === "..") {
        continue;
    }
    if (is_file($backupDir . '/' . $filename)) {
        $time   = str_replace('backup-', '', $filename);
        $time   = str_replace('.sql', '', $time);
        $list[] = [
            'name' => $filename,
            'time' => $time,
            'size' => round(filesize($backupDir . '/' . $filename) / 1024, 2) . 'KB',
        ];
    }
}


echo '<div class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top: 10px">
    <div class="block">
        <div class="block-title"><h3 class="panel-title">' . $title . '</h3></div>
        <div class="">
            <div class="alert" style="background-color: #F8F8FF;color: #0c69c6;">
              下列备份数据文件是之前全量克隆时自动备份的商品、社区、分类、加价模板数据文件，可随时恢复
            </div>
             <div class="table-responsive">
                <table class="table table-striped">
                  <thead><tr><th>-</th><th>文件名称</th><th>文件大小</th><th>备份时间</th><th>操作</th></tr></thead>
                  <tbody>
            ';
foreach ($list as $key => $row) {
    echo '<tr>
    <td>' . ($key + 1) . '</td>
    <td>' . $row['name'] . '</td>
    <td>' . $row['size'] . '</td>
    <td>' . $row['time'] . '</td>
    <td><a onclick="recoveryDb(\'' . $row['name'] . '\')" class="btn btn-success btn-xs">恢复</a>&nbsp;<a onclick="delDb(\'' . $row['name'] . '\')" class="btn btn-danger btn-xs">删除</a></td>
    </tr>
    ';
}
echo '
        </tbody>
        </table>
        </div>
        </div>
      </div>
    </div>';

echo '
<script type="text/javascript">
function recoveryDb(file){
    var ii = layer.load(2, {shade:[0.1,\'#fff\']});
    $.ajax({
        type : "POST",
        url : "?act=recovery",
        data : {
            filename:file
        },
        dataType : \'json\',
        success : function(data) {
            layer.close(ii);
            if(data.code == 0){
                layer.msg(data.msg);
            }else{
                layer.alert(data.msg);
            }
        } ,
        error:function(data){
            layer.close(ii);
            layer.msg(\'服务器请求连接超时\');
            return false;
        }
    });
}

function delDb(file){
    layer.confirm("是否确定删除？删除后将无法恢复", {icon: 3, title:"是否删除？"}, function(index){
       var ii = layer.load(2, {shade:[0.1,\'#fff\']});
        $.ajax({
            type : "POST",
            url : "?act=del",
            data : {
                filename:file
            },
            dataType : \'json\',
            success : function(data) {
                layer.close(ii);
                if(data.code == 0){
                    layer.msg(data.msg);
                }else{
                    layer.alert(data.msg);
                }
            } ,
            error:function(data){
                layer.close(ii);
                layer.msg(\'服务器请求连接超时\');
                return false;
            }
        });
        layer.close(index);
    });
}
</script>
';

include_once 'footer.php';
