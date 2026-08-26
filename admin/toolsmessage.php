<?php

use core\Db;

include "../includes/common.php";
checkLogin();

$my = isset($_GET['my']) ? daddslashes($_GET['my']) : null;

if ($my == 'getNowMessage') {
    $date = input('date');
    if (!$date) {
        $date = null;
    }

    json_success('成功', [
        'html' => createToolsLogsHtml($date),
        'date' => $date,
        'sql'  => \core\Db::getLastSql(),
    ]);
}
$title = "商品动态";

include './head.php';

$editor_load = true;

$fl = '<option value="1">平台公告</option><option value="2">业务推荐</option>';
echo '<div class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top:10px;">    ';
if ($my == "add_submit") {
    $time    = input('post.time', 1);
    $content = input('post.content', 0, 0);
    if (!$content) {
        showmsg("动态内容不能为空", 2);
    }
    $data = [
        'time'    => $time ? $time : date('Y-m-d'),
        'content' => $content,
        'addtime' => $date,
    ];
    if ($id = Db::name('tools_message')->insert($data)) {
        showmsg("新增成功！<br><a href='./toolsmessage.php'><<<返回动态列表</a>", 1);
    } else {
        showmsg("新增失败！" . $DB->error(), 4);
    }
} elseif ($my == "del") {
    $id  = intval($_GET['id']);
    $sql = "DELETE FROM `pre_tools_message` where id='" . $id . "'";
    if ($DB->query($sql)) {
        showmsg("删除成功！", 1);
    } else {
        showmsg("删除失败！" . $DB->error(), 4);
    }
} elseif ($my == "edit_submit") {
    $id = input('id', 1);

    $rows = Db::name('tools_message')->where(['id' => $id])->find();
    if (!$rows) {
        showmsg("该动态记录不存在", 2);
    }

    $time    = input('post.time', 1);
    $content = input('post.content', 0, 0);

    if (!$content) {
        showmsg("动态内容不能为空", 2);
    }
    $data = [
        'time'    => $time ? $time : date('Y-m-d'),
        'content' => $content,
        'addtime' => $date,
    ];
    $update = Db::name('tools_message')->where(['id' => $id])->update($data);
    if ($update !== false) {
        showmsg("修改成功！<br><a href='./toolsmessage.php'><<<返回动态列表</a>", 1);
    } else {
        showmsg("修改失败！" . $DB->error(), 4);
    }
} elseif ($my == "edit") {
    $editor_load = true;
    $id          = intval(input('get.id'));
    $row         = $DB->get_row("SELECT * from pre_tools_message where id='$id' limit 1");
    if (!$row) {
        showmsg("该文章不存在！" . $DB->error(), 4);
    }
    echo '
    <div class="block">
    <div class="block-title">
        <h3 class="panel-title">编辑商品动态-' . $row['title'] . '</h3>
        <a class="btn btn-primary getNowMessage"><i class="fa fa-plus"></i>&nbsp;一键获取</a>
    </div>
      <div class="">
     <form action="./toolsmessage.php?my=edit_submit&id=' . intval($_GET['id']) . '" method="post" class="form-horizontal" role="form">
    <div class="form-group">
      <label class="col-sm-2 control-label">日期</label>
      <div class="col-sm-10"><input id="time" type="date" name="time" value="' . $row['time'] . '" class="form-control"/></div>
    </div><br/>
    <div class="form-group">
      <label class="col-sm-2 control-label">内容</label>
      <div class="col-sm-10">
      <div id="editorBox"></div>
      <textarea name="content" class="hide textDom">' . $row['content'] . '</textarea>
      </div>
    </div><br/>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="提交编辑" class="btn btn-primary form-control"/><br/>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <br/><a href="./toolsmessage.php">>>返回动态列表</a><br/> <br/>
        </div>
    </div>
  </form>
</div>
</div>
<script>
var boxType=1;
</script>
';

} elseif ($my == "add") {

    echo '<div class="block">
            <div class="block-title">
                <h3 class="panel-title">发布新商品动态</h3>
                <a class="btn btn-primary getNowMessage"><i class="fa fa-plus"></i>&nbsp;一键获取</a>
            </div>
          <div class="">
        <form action="./toolsmessage.php?my=add_submit" method="post" class="form-horizontal" role="form">
        <div class="form-group">
          <label class="col-sm-2 control-label">日期</label>
          <div class="col-sm-10">
             <input type="text" id="time" name="time" value="' . date('Y-m-d') . '" class="form-control" placeholder="留空获取当天时间"/>
          </div>
        </div><br/>
        <div class="form-group">
          <label class="col-sm-2 control-label">内容</label>
          <div class="col-sm-10">
          <div id="editorBox"></div>
          <textarea name="content" class="hide textDom"></textarea>
          </div>
        </div><br/>
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="立即发布" class="btn btn-primary form-control"/><br/>
         </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <br/><a href="./toolsmessage.php">>>返回动态列表</a><br/> <br/>
            </div>
        </div>
      </form>
    </div>
    </div>
    <script>
    var boxType=1;
    </script>
    ';
} else {
    $sql     = "1";
    $numrows = $DB->count("SELECT count(*) from pre_tools_message");
    $con     = '系统共有 <b>' . $numrows . '</b> 个商品动态。';
    $link    = "";

    echo '<div class="block">
<div class="block-title clearfix">
<h2>' . $con . '</h2>
</div>
<a href="./toolsmessage.php?my=add" class="btn btn-primary"><i class="fa fa-plus"></i>&nbsp;发布新动态</a>
      <form>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>ID</th><th>日期</th><th>添加时间</th><th>操作</th></tr></thead>
          <tbody>';

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
    $rs     = $DB->query("SELECT * FROM pre_tools_message WHERE {$sql} order by id DESC limit $offset,$pagesize");
    while ($res = $DB->fetch($rs)) {

        echo '<tr><td><b>' . $res['id'] . '</b></td>
        <td>' . $res['time'] . '</td>
         <td>' . $res['addtime'] . '</td><td><a target="_blank" href="../toollogs.php" class="btn btn-xs btn-success">查看</a>&nbsp;<a href="./toolsmessage.php?my=edit&id=' . $res['id'] . '" class="btn btn-info btn-xs">编辑</a>&nbsp;<a href="./toolsmessage.php?my=del&id=' . $res['id'] . '" class="btn btn-xs btn-danger" onclick="return confirm(\'你确实要删除此记录吗？\');">删除</a></td></tr>';
    }

    echo <<<'html1'
            </tbody>
        </table>
        <div style="height: 100px;display: block;"></div>
html1;
#分页
    $pageList = new \core\Page($numrows, $pagesize, 0, $link);
    echo $pageList->showPage();

    echo "     </div>
    </div>
    </form>
  </div>
</div>";
}

?>
<script src="../assets/public/layDate/5.0.9/laydate.js" type="text/javascript"></script>
<script type="text/javascript">
    "use strict";
    var checkList = [] || new Array();
    $(document).on("click", ".setTop", function (event) {
        event.preventDefault();
        /* Act on the event */
        var id = $(this).data("id");
        var top = $(this).data("top");
        $.ajax({
            type: 'GET',
            url: '?act=setTop&id=' + id + '&top=' + top,
            dataType: 'json',
            success: function (data) {
                window.location.reload()
            },
            error: function (data) {
                layer.msg('服务器错误');
                return false;
            }
        });
    });

    function show(id) {
        $.ajax({
            type: 'GET',
            url: 'ajax.php?act=getMessage&id=' + id,
            dataType: 'json',
            success: function (data) {
                if (data.code == 0) {
                    layer.open({
                        type: 1,
                        skin: 'layui-layer-lan',
                        anim: 2,
                        shadeClose: true,
                        title: '查看站内文章',
                        content: '<div class="widget"><div class="widget-content widget-content-mini themed-background-muted text-center"><b>' + data.title + '</b><br/><small><font color="grey">管理员  ' + data.date + '</font></small></div><div class="widget-content">' + data.content + '</div></div>'
                    });
                } else {
                    layer.alert(data.msg);
                }
            },
            error: function (data) {
                layer.msg('服务器错误');
                return false;
            }
        });
    }

    $(document).ready(function () {
            laydate.render({
            elem: "#time"
            ,theme: "molv"
            ,type: "date"
            });

        $(document).on('click','.getNowMessage', function () {
            var date = $("#time").val();
            $.ajax({
                type: 'GET',
                url: '?my=getNowMessage&date=' + date,
                dataType: 'json',
                success: function (res) {
                    if (res.code == 0) {
                        $(".textDom").html(res.data.html);
                        editorChange();
                    } else {
                        layer.alert(res.msg);
                    }
                },
                error: function (data) {
                    layer.msg('服务器错误');
                    return false;
                }
            });
        });
    });


</script>

<?
include_once 'footer.php';