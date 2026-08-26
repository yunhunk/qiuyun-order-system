<?php
# 文章插件 By 星河
include "../includes/common.php";
checkLogin();

$my    = isset($_GET['my']) ? daddslashes($_GET['my']) : null;
$title = "站内文章";
$act   = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
if ($act == "setActive") {
    $id     = intval(input('get.id'));
    $active = intval(input('get.active'));
    $sql    = "UPDATE `pre_message` set active='" . $active . "' where id='" . $id . "'";
    if ($DB->query($sql)) {
        $result = ['code' => 0, 'msg' => '操作成功'];
    } else {
        $result = ['code' => -1, 'msg' => '操作失败！' . $DB->error()];
    }
    exit(json_encode($result));
} elseif ($act == "setTop") {
    $id  = intval(input('get.id'));
    $top = intval(input('get.top'));
    $sql = "UPDATE `pre_message` SET `top`='{$top}' where `id`='{$id}'";
    if (false !== $DB->exec($sql)) {
        $result = ['code' => 0, 'msg' => '操作成功'];
    } else {
        $result = ['code' => -1, 'msg' => '操作失败！' . $DB->error()];
    }
    exit(json_encode($result));
}

checkAuthority('message');
include './head.php';

$editor_load = true;

$fl = '<option value="1">平台公告</option><option value="2">业务推荐</option>';
echo '<div class="col-sm-12 col-md-12 col-lg-12 center-block" style="float: none;padding-top:10px;">    ';
if ($my == "add_submit") {
    $cid            = intval(input('post.cid'));
    $type           = intval(input('post.type'));
    $title          = input('post.title', 1);
    $seotitle       = input('post.seotitle', 1);
    $seokeywords    = input('post.seokeywords', 1);
    $seodescription = input('post.seodescription', 1);
    $content        = trim($_POST['content']);
    $count          = intval(input('post.count'));
    $sql            = "INSERT into `pre_message` (`cid`,`type`,`title`,`seotitle`,`seokeywords`,`seodescription`,`content`,`count`,`addtime`,`active`) VALUES('" . $cid . "','" . $type . "','" . $title . "','" . $seotitle . "','" . $seokeywords . "','" . $seodescription . "','" . $content . "','" . $count . "','" . $date . "','1')";
    if ($id = $DB->insert($sql)) {
        $DB->exec("UPDATE `pre_message` SET `sort`='{$id}' WHERE `id`='{$id}'");
        showmsg("发布成功！<br><a href='./message.php'><<<返回文章列表</a>", 1);
    } else {
        showmsg("发布失败！" . $DB->error(), 4);
    }
} elseif ($my == "del") {
    $id  = intval($_GET['id']);
    $sql = "delete FROM `pre_message` where id='" . $id . "'";
    if ($DB->query($sql)) {
        showmsg("删除成功！", 1);
    } else {
        showmsg("删除失败！" . $DB->error(), 4);
    }
} elseif ($my == "edit_submit") {
    $id             = intval(input('get.id'));
    $cid            = intval(input('post.cid'));
    $type           = intval(input('post.type'));
    $title          = input('post.title', 1);
    $seotitle       = input('post.seotitle', 1);
    $seokeywords    = input('post.seokeywords', 1);
    $seodescription = input('post.seodescription', 1);
    $content        = trim($_POST['content']);
    $count          = intval(input('post.count'));
    $sql            = "UPDATE cmy_message set type= ?,cid= ?,title= ?,seotitle= ?,seokeywords= ?,seodescription= ?,content= ?,`count`= ? where id= ?";
    if ($DB->query($sql, [$type, $cid, $title, $seotitle, $seokeywords, $seodescription, $content, $count, $id])) {
        showmsg("编辑成功！<br><a href='./message.php'><<<返回文章列表</a>", 1);
    } else {
        showmsg("编辑失败！" . $DB->error(), 4);
    }
} elseif ($my == "edit") {
    $editor_load = true;
    $id          = intval(input('get.id'));
    $row         = $DB->get_row("select * from cmy_message where id='$id' limit 1");
    if (!$row) {
        showmsg("该文章不存在！" . $DB->error(), 4);
    }
    echo '
    <div class="block">
    <div class="block-title"><h3 class="panel-title">编辑文章-' . $row['title'] . '</h3></div>
      <div class="">
     <form action="./message.php?my=edit_submit&id=' . intval($_GET['id']) . '" method="post" class="form-horizontal" role="form">
    <div class="form-group">
      <label class="col-sm-2 control-label">文章标题</label>
      <div class="col-sm-10"><input type="text" name="title" value="' . $row['title'] . '" class="form-control"/></div>
    </div><br/>
    <div class="form-group">
      <label class="col-sm-2 control-label">SEO标题</label>
      <div class="col-sm-10"><input type="text" name="seotitle" value="' . $row['seotitle'] . '" class="form-control"/></div>
    </div><br/>
    <div class="form-group">
      <label class="col-sm-2 control-label">SEO关键词</label>
      <div class="col-sm-10"><input type="text" name="seokeywords" value="' . $row['seokeywords'] . '" class="form-control"/></div>
    </div><br/>
    <div class="form-group">
      <label class="col-sm-2 control-label">SEO描述</label>
      <div class="col-sm-10"><input type="text" name="seodescription" value="' . $row['seodescription'] . '" class="form-control"/></div>
    </div><br/>
    <div class="form-group">
      <label class="col-sm-2 control-label">已查看人数</label>
      <div class="col-sm-10"><input type="text" name="count" value="' . $row['count'] . '" class="form-control"/></div>
    </div><br/>
    <div class="form-group">
      <label class="col-sm-2 control-label">文章类别</label>
      <div class="col-sm-10">
        <select name="cid" class="form-control" default="' . $row['cid'] . '">' . $fl . '</select>
      </div>
    </div><br/>
    <div class="form-group">
      <label class="col-sm-2 control-label">接收用户类别</label>
      <div class="col-sm-10"><select class="form-control" default="' . $row['type'] . '" name="type"><option value="0">全部用户</option><option value="1">普通用户</option><option value="2">所有分站站长</option><option value="3">普及版站长</option><option value="4">专业版站长</option></select></div>
    </div><br/>
    <div class="form-group">
      <label class="col-sm-2 control-label">文章内容</label>
      <div class="col-sm-10">
      <div id="editorBox"></div>
      <textarea name="content" class="hide textDom">' . $row['content'] . '</textarea>
      </div>
    </div><br/>
    <div class="form-group">
      <div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="提交编辑" class="btn btn-primary form-control"/><br/>
     </div>
    </div>
  </form>
  <br/><a href="./message.php">>>返回文章列表</a>
</div>
</div>
<script>
var boxType=1;
</script>
';

} elseif ($my == "add") {

    echo '<div class="block">
         <div class="block-title"><h3 class="panel-title">发布新文章</h3></div>
          <div class="">
        <form action="./message.php?my=add_submit" method="post" class="form-horizontal" role="form">
        <div class="form-group">
          <label class="col-sm-2 control-label">文章标题</label>
          <div class="col-sm-10"><input type="text" name="title" value="" class="form-control"/></div>
        </div><br/>
        <div class="form-group">
          <label class="col-sm-2 control-label">SEO标题</label>
          <div class="col-sm-10"><input type="text" name="seotitle" value="" class="form-control"/></div>
        </div><br/>
        <div class="form-group">
          <label class="col-sm-2 control-label">SEO关键词</label>
          <div class="col-sm-10"><input type="text" name="seokeywords" value="" class="form-control"/></div>
        </div><br/>
        <div class="form-group">
          <label class="col-sm-2 control-label">SEO描述</label>
          <div class="col-sm-10"><input type="text" name="seodescription" value="" class="form-control"/></div>
        </div><br/>
        <div class="form-group">
          <label class="col-sm-2 control-label">已查看人数</label>
          <div class="col-sm-10"><input type="text" name="count" value="" class="form-control"/></div>
        </div><br/>
        <div class="form-group">
          <label class="col-sm-2 control-label">文章类别</label>
          <div class="col-sm-10">
            <select name="cid" class="form-control">' . $fl . '</select>
          </div>
        </div><br/>
        <div class="form-group">
          <label class="col-sm-2 control-label">接收用户类别</label>
          <div class="col-sm-10"><select class="form-control" name="type"><option value="0">全部用户</option><option value="1">普通用户</option><option value="2">所有分站站长</option><option value="3">普及版站长</option><option value="4">专业版站长</option></select></div>
        </div><br/>
        <div class="form-group">
          <label class="col-sm-2 control-label">文章内容</label>
          <div class="col-sm-10">
          <div id="editorBox"></div>
          <textarea name="content" class="hide textDom"></textarea>
          </div>
        </div><br/>
        <div class="form-group">
          <div class="col-sm-offset-2 col-sm-10"><input type="submit" name="submit" value="立即发布" class="btn btn-primary form-control"/><br/>
         </div>
        </div>
      </form>
      <br/><a href="./message.php">>>返回文章列表</a>
    </div>
    </div>
    <script>
    var boxType=1;
    </script>
    ';
} else {
    $sql     = "1";
    $numrows = $DB->count("SELECT count(*) from cmy_message");
    $con     = '系统共有 <b>' . $numrows . '</b> 个站内文章。';
    $link    = "";
    echo '
<!--modal-->
<div class="modal fade" align="left" id="help" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">文章伪静态使用说明</h4>
      </div>
      <div class="modal-body">
    <H4 style="color:red">#Nginx服务器</H4>
    <pre>
<code>
#文章插件伪静态
location  /article/ {
 rewrite ^/article/(\d+).html /article/index.php?id=$1 last;
 rewrite ^/article/index.html /article/index.php last;
}

#网站首页伪静态
location  / {
 rewrite ^/(\d+).html(.*?) /index.php?mod=article&id=$1 last;
 rewrite ^/articlelist.html(.*?) /index.php?mod=articlelist$1 last;
 rewrite ^/classList/(\d+).html(.*?) /index.php?act=classList&cid=$1 last;
 rewrite ^/detail/(\d+).html(.*?) /index.php?act=detail&tid=$1 last;
 rewrite ^/act/([\w\-]+).html(.*?) /index.php?act=$1 last;
 rewrite ^/index.html(.*?) /index.php last;
}
</code>
</pre>
将以上代码，复制到宝塔网站列表->设置->伪静态中保存即可
<br><br>
<H4 style="color:red">#Apache或虚拟主机</H4>
    <pre>
<code>
#开启伪静态路由规则
RewriteEngine on

#文章伪静态路由
RewriteRule ^article/(\d+).html article/index.php?id=$1  [L]
RewriteRule ^article/index.html article/index.php  [L]

#首页伪静态路由
RewriteRule ^(\d+).html(.*?) index.php?mod=article&id=$1  [L]
RewriteRule ^articlelist.html(.*?) index.php?mod=articlelist$1  [L]
RewriteRule ^classList/(\d+).html(.*?) index.php?act=classList&cid=$1  [L]
RewriteRule ^detail/(\d+).html(.*?) index.php?act=detail&tid=$1  [L]
RewriteRule ^act/([\ww\-]+).html(.*?) /index.php?act=$1  [L]
RewriteRule ^index.html(.*?) /index.php$1  [L]
RewriteRule ^index.html /index.php  [L]
</code>
</pre>
宝塔：将以上代码，复制到宝塔网站列表->设置->伪静态中保存即可
其他：将以上代码，复制到伪静态配置中、或php配置文件保存即可
<br><br>
</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!--modal end-->

<div class="modal fade" align="left" id="search" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">文章列表说明</h4>
      </div>
      <div class="modal-body">
访问 <a href="/?mod=articlelist" target="_blank">/?mod=articlelist</a> 即可进入文章列表，部分首页模板没有访问入口的可以自行加入该链接。文章列表只显示接收用户为全部用户类型的消息。
</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
      </div>
    </div>
  </div>
</div>
';

    echo '<div class="block">
<div class="block-title clearfix">
<h2>' . $con . '</h2>
</div>
<a href="./message.php?my=add" class="btn btn-primary"><i class="fa fa-plus"></i>&nbsp;发布新文章</a>&nbsp;<a href="#help" data-toggle="modal" data-target="#help" class="btn btn-default">伪静态规则</a>&nbsp;<a href="./set.php?mod=message" class="btn btn-success">文章相关设置</a>
      <form>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>ID</th><th>文章标题</th><th>类别</th><th>排序</th><th>发布时间</th><th>已查阅人数</th><th>状态</th><th>置顶</th><th>操作</th></tr></thead>
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
    $rs     = $DB->query("SELECT * FROM cmy_message WHERE {$sql} order by top DESC,sort ASC,id DESC limit $offset,$pagesize");
    while ($res = $DB->fetch($rs)) {
        if ($res['active'] == 1) {
            $active = '<span class="btn btn-xs btn-success" onclick="setActive(' . $res['id'] . ',0)">显示</span>';
        } else {
            $active = '<span class="btn btn-xs btn-danger" onclick="setActive(' . $res['id'] . ',1)">隐藏</span>';
        }

        if ($res['top'] == 1) {
            $top = '<a href="javascript:;" class="btn btn-xs btn-success setTop" data-id="' . $res['id'] . '" data-top="0" title="已置顶"><i class="fa fa-arrow-up"></a>';
        } else {
            $top = '<a href="javascript:;" class="btn btn-xs btn-gray setTop" data-id="' . $res['id'] . '" data-top="1"><i class="fa fa-arrow-up" title="未置顶"></a>';
        }

        echo '<tr><td><input type="checkbox" name="checkbox[]" id="list1" value="' . $res['id'] . '">&nbsp;<b>' . $res['id'] . '</b></td><td>' . $res['title'] . '</td><td>' . ($res['cid'] == 1 ? "平台公告" : "业务推荐") . '</td><td><a class="btn btn-xs sort_btn" title="移到顶部" onclick="sort(' . $res['id'] . ',0)"><i class="fa fa-long-arrow-up"></i></a><a class="btn btn-xs sort_btn" title="移到上一行" onclick="sort(' . $res['id'] . ',1)"><i class="fa fa-chevron-circle-up"></i></a><a class="btn btn-xs sort_btn" title="移到下一行" onclick="sort(' . $res['id'] . ',2)"><i class="fa fa-chevron-circle-down"></i></a><a class="btn btn-xs sort_btn" title="移到底部" onclick="sort(' . $res['id'] . ',3)"><i class="fa fa-long-arrow-down"></i></a></td>
   <td>' . $res['addtime'] . '</td><td>' . $res['count'] . '</td><td>' . $active . '</td><td>' . $top . '</td><td><a target="_blank" href="../article/index.php?id=' . $res['id'] . '" class="btn btn-xs btn-success">预览</a>&nbsp;<a href="./message.php?my=edit&id=' . $res['id'] . '" class="btn btn-info btn-xs">编辑</a>&nbsp;<a href="./message.php?my=del&id=' . $res['id'] . '" class="btn btn-xs btn-danger" onclick="return confirm(\'你确实要删除此记录吗？\');">删除</a></td></tr>';
    }

    echo <<<'html1'
            </tbody>
        </table>
        <div style="height: 100px;display: block;"></div>
html1;
    if ($is_mb == false) {
        echo '<footer class="navbar-fixed-bottom">
            <div class="paging-navbar">';
    } else {
        echo '<footer>
            <div class="" style="width: 98%;display:block;background-color: #fff;border-color: 1px 2px #cfdadd;position: fixed;bottom: 0;left: 15px;"><div style="padding:5px 8px;">';
    }
    echo <<<'html2'
<div class="form-inline">
            <input type="hidden" name="result_all" id="result_all"/>&nbsp;
            <input name="chkAll1" type="checkbox" id="chkAll1" onclick="check1(this.form.list1)" value="checkbox">&nbsp;反选&nbsp;
            <select name="checkStatus"><option selected>选择批量操作</option><option value="4">批量删除</option><option value="3">批量显示</option><option value="2">批量隐藏</option></select>&nbsp;
            <button type="button" onclick="operation()" class="btn btn-primary">确定</button>
            <div class="form-group" style="margin-left:10px">
html2;
#分页
    $pageList = new \core\Page($numrows, $pagesize, 0, $link);
    echo $pageList->showPage();

    echo "     </div>
    </div>
    </form>
  </div>
</div>";
}
echo '
<script type="text/javascript">
"use strict";
var checkList = [] || new Array();
$(document).on("click", ".setTop", function(event) {
  event.preventDefault();
  /* Act on the event */
  var id = $(this).data("id");
  var top = $(this).data("top");
  $.ajax({
        type : \'GET\',
        url : \'?act=setTop&id=\'+id+\'&top=\'+top,
        dataType : \'json\',
        success : function(data) {
            window.location.reload()
        },
        error:function(data){
            layer.msg(\'服务器错误\');
            return false;
        }
    });
});
function check1(field) {
    var checkbox = field || document.getElementsByName(\'checkbox[]\');
    for (var i = 0; i < checkbox.length; i++) {
        if (checkbox[i].checked === false) {
            checkbox[i].checked = true;
        } else {
            checkbox[i].checked = false;
        }
    }
}

function getVals() {
    var checkbox = document.getElementsByName(\'checkbox[]\');
    checkList = [];
    for (var i = 0; i < checkbox.length; i++) {
        if (checkbox[i].checked) {
            checkList.push(checkbox[i].value);
        }
    }
    //console.log(checkList);
}

function sort(id,sort) {
    $.ajax({
        type : "GET",
        url : \'ajax.php?act=setArticleSort&id=\'+id+\'&sort=\'+sort,
        dataType : \'json\',
        success : function(data) {
            window.location.reload();
        },
        error:function(data){
            layer.msg(\'服务器错误\');
            return false;
        }
    });
}

function operation() {
    getVals();
    var aid = $("select[name=\'checkStatus\']").val();
    var ii = layer.load(2, {
        shade: [0.1, \'#fff\']
    });
    $.ajax({
        type : "POST",
        url : \'ajax.php?act=article_operation\',
        dataType : \'json\',
        data : {
            checkbox : checkList,
            aid : aid
        },
        success : function(data) {
            layer.close(ii);
            if(data.code==0){
                layer.msg(data.msg,{
                  time:1*1000,
                  end:function(){
                    window.location.reload();
                  }
                });
            }
        },
        error:function(data){
            layer.close(ii);
            layer.msg(\'服务器错误\');
            return false;
        }
    });
}

function setActive(id,active) {
    $.ajax({
        type : \'GET\',
        url : \'?act=setActive&id=\'+id+\'&active=\'+active,
        dataType : \'json\',
        success : function(data) {
            window.location.reload()
        },
        error:function(data){
            layer.msg(\'服务器错误\');
            return false;
        }
    });
}

function show(id) {
    $.ajax({
        type : \'GET\',
        url : \'ajax.php?act=getMessage&id=\'+id,
        dataType : \'json\',
        success : function(data) {
            if(data.code==0){
                layer.open({
                  type: 1,
                  skin: \'layui-layer-lan\',
                  anim: 2,
                  shadeClose: true,
                  title: \'查看站内文章\',
                  content: \'<div class="widget"><div class="widget-content widget-content-mini themed-background-muted text-center"><b>\'+data.title+\'</b><br/><small><font color="grey">管理员  \'+data.date+\'</font></small></div><div class="widget-content">\'+data.content+\'</div></div>\'
                });
            }else{
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

include_once 'footer.php';
