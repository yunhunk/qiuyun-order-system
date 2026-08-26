<?php
include '../includes/common.php';
checkLogin();
$title = '卡密列表';

checkFileSize();

checkAuthority('fakas');

include './head.php';

echo '
   <div class="col-md-12 center-block" style="float: none;padding-top:10px">
<div class="modal fade" align="left" id="search" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">搜索卡密</h4>
      </div>
      <div class="modal-body">
      <form action="fakakms.php" method="GET">
<input type="hidden" name="tid" value="';
error_reporting(0);
echo $_GET['tid'];
echo '"><br/>
<input type="text" class="form-control" name="kw" placeholder="请输入卡号或密码"><br/>
<input type="submit" class="btn btn-primary btn-block" value="搜索"></form>
</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
';
$rs           = $DB->query("SELECT * FROM pre_class WHERE upcid is null OR upcid=0 order by sort asc");
$select       = '<option value="0">未分类</option>';
$pre_class[0] = '未分类';
while ($res = $DB->fetch($rs)) {
    $pre_class[$res['cid']] = $res['name'];
    $select .= '<option value="' . $res['cid'] . '">' . $res['name'] . '</option>';
    $subClass = $DB->count("SELECT count(*) FROM pre_class WHERE upcid='" . $res['cid'] . "' order by sort asc");
    if ($subClass > 0) {
        $rs2 = $DB->query("SELECT * FROM pre_class WHERE active=1 and upcid='" . $res['cid'] . "' order by sort asc");
        while ($res2 = $DB->fetch($rs2)) {
            $pre_class[$res2['cid']] = $res2['name'];
            $select .= '<option value="' . $res2['cid'] . '">----' . $res2['name'] . '</option>';
        }
    }
}
$my = (isset($_GET['my']) ? $_GET['my'] : null);
if ($my == 'add') {
    if (isset($_GET['tid'])) {
        $tid      = intval($_GET['tid']);
        $row      = $DB->get_row('select * from pre_tools where tid=\'' . $tid . '\' limit 1');
        $shopname = '<option value="' . $tid . '">' . $row['name'] . '</option>';
        $cid      = $row['cid'];
    } else {
        $cid = 0;
    }
    echo '<div class="block">
    <div class="block-title"><h3 class="panel-title">添加卡密</h3></div>
<div class="">
<form action="./fakakms.php?my=add_submit" method="POST" onsubmit="return checkAdd()">
<input type="hidden" name="backurl" value="';
    echo $_SERVER['HTTP_REFERER'];
    echo '"/>
<div class="form-group">
    <div class="input-group">
        <span class="input-group-addon">
            选择商品
        </span>
        <select id="cid" class="form-control" default="';
    echo $cid;
    echo '">';
    echo $select;
    echo '</select>
        <select id="tid" name="tid" class="form-control" default="';
    echo $tid;
    echo '">';
    echo $shopname;
    echo '</select>
    </div>
</div>
<div class="form-group">
    <div class="input-group">
        <span class="input-group-addon">
            卡密列表
        </span>
        <textarea class="form-control" id="kms" name="kms" rows="8" placeholder="一行一张卡"></textarea>
    </div>
</div>
<div class="form-group">
    <div class="input-group">
        <span class="input-group-addon"><label><input id="is_check_repeat" name="is_check_repeat" type="checkbox" value="1">检查重复的卡密</label></span>
    </div>
</div>
<div class="form-group">
    <button type="submit" class="btn btn-primary btn-block">确认提交</button>
    <button type="reset" class="btn btn-default btn-block">重新填写</button>
</div>
</form>
</div>
<div class="panel-footer">
<span class="glyphicon glyphicon-info-sign"></span>
注意：卡密格式：卡号+空格+密码，一行一张卡，如：ABCDEFG 123456789
</div>
</div>
<a href="';
    echo 'fakalist.php';
    echo '" class="btn btn-default btn-block">>>返回库存管理</a>
';
} elseif ($my == 'add_submit') {
    $tid             = intval($_POST['tid']);
    $kms             = $_POST['kms'];
    $is_check_repeat = $_POST['is_check_repeat'];
    $succ            = 0;
    $warn            = 0;
    if (($tid == null || $kms == null)) {
        showmsg('请确保各项不能为空！', 3);
    } else {
        $kms = str_replace(array(
            0 => "\r\n",
            1 => "\r",
            2 => "\n",
        ), '[br]', $kms);
        $match = explode('[br]', $kms);
        $c     = 0;
        foreach ($match as $val) {
            $km_arr = explode(' ', $val);
            $km     = trim($km_arr[0]);
            $pw     = trim($km_arr[1]);
            if ($km != '') {
                $c++;
                if ($is_check_repeat == 1) {
                    if (!$DB->get_row('select * from pre_faka where km=\'' . $km . '\' limit 1')) {
                        $sql = $DB->query('INSERT INTO `pre_faka` (`tid`,`km`,`pw`,`addtime`) VALUES (\'' . $tid . '\',\'' . $km . '\',\'' . $pw . '\',NOW())');
                        if ($sql) {
                            $succ++;
                        } else {
                            $warn++;
                        }
                    }
                } else {
                    $sql = $DB->query('INSERT INTO `pre_faka` (`tid`,`km`,`pw`,`addtime`) VALUES (\'' . $tid . '\',\'' . $km . '\',\'' . $pw . '\',NOW())');
                    if ($sql) {
                        $succ++;
                    } else {
                        $warn++;
                    }
                }
            }
        }
        if ($succ > 0) {
            $stock = \core\Db::name('faka')->where([
                'tid'     => $tid,
                'orderid' => ['<=', 0],
            ])->count('kid');

            $data = [
                'cardstime'  => time(),
                'stock_open' => 1,
                'stock'      => $stock,
            ];

            \core\Db::name('tools')->where(['tid' => $tid])->update($data);

            showmsg('导入卡密成功！共' . $c . '个卡密，成功导入' . $succ . '个，失败' . $warn . '个<br/>', 0, './fakakms.php?my=add&tid=' . $tid);
        } else {
            showmsg('导入卡密失败！[errorCode：' . $DB->error() . ']', 3, './fakakms.php?my=add&tid=' . $tid);
        }
    }
} elseif ($my == 'del') {
    $id  = $_GET['id'];
    $sql = $DB->query('DELETE FROM pre_faka WHERE kid=\'' . $id . '\'');
    exit('<script language=\'javascript\'>history.go(-1);</script>');
} elseif ($my == 'qk') {
    $tid = intval($_GET['tid']);
    echo '<div class="panel panel-primary">
<div class="panel-heading w h"><h3 class="panel-title">清空卡密</h3></div>
<div class="panel-body box">
您确认要清空该商品的所有卡密吗？清空后无法恢复！<br><a href="./fakakms.php?my=qk2&tid=' . $tid . '">确认</a> | <a href="javascript:history.back();">返回</a></div></div>';
} elseif ($my == 'qk2') {
    $tid = intval($_GET['tid']);
    echo '<div class="panel panel-primary">
<div class="panel-heading w h"><h3 class="panel-title">清空卡密</h3></div>
<div class="panel-body box">';
    if ($DB->query('DELETE FROM pre_faka WHERE tid=\'' . $tid . '\'') == true) {
        echo '<div class="box">清空成功.</div>';
    } else {
        echo '<div class="box">清空失败.</div>';
    }
    echo '<hr/><a href="./fakakms.php?tid=' . $tid . '">>>返回卡密列表</a></div></div>';
} elseif ($my == 'qkuse') {
    $tid = intval($_GET['tid']);
    echo '<div class="panel panel-primary">
<div class="panel-heading w h"><h3 class="panel-title">清空卡密</h3></div>
<div class="panel-body box">
您确认要清空所有卡密吗？清空后无法恢复！<br><a href="./fakakms.php?my=qkuse2&tid=' . $tid . '">确认</a> | <a href="javascript:history.back();">返回</a></div></div>';
} elseif ($my == 'qkuse2') {
    $tid = intval($_GET['tid']);
    echo '<div class="panel panel-primary">
<div class="panel-heading w h"><h3 class="panel-title">清空卡密</h3></div>
<div class="panel-body box">';
    if ($DB->query('DELETE FROM pre_faka WHERE tid=\'' . $tid . '\' and orderid!=0') == true) {
        echo '<div class="box">清空成功.</div>';
    } else {
        echo '<div class="box">清空失败.</div>';
    }
    echo '<hr/><a href="./fakakms.php?tid=' . $tid . '">>>返回卡密列表</a></div></div>';
} elseif ($my == 'del2') {
    $checkbox = $_POST['checkbox'];
    $i        = 0;
    foreach ($checkbox as $kid) {
        $DB->query('DELETE FROM pre_faka WHERE kid=\'' . $kid . '\' limit 1');
    }
    exit('<script language=\'javascript\'>alert(\'成功删除' . $i . '张卡密\');history.go(-1);</script>');
} else {
    if (isset($_GET['tid'])) {
        $tid = intval($_GET['tid']);
        $row = $DB->get_row('select * from pre_tools where tid=\'' . $tid . '\' limit 1');
        if (!$row) {
            showmsg('商品不存在', 3);
        }
    }

    if (isset($_GET['kw'])) {
        $sql  = ' `tid`=\'' . $tid . '\' and (`km`=\'' . $_GET['kw'] . '\' or `pw`=\'' . $_GET['kw'] . '\')';
        $link = '&tid=' . $tid . '&kw=' . $_GET['kw'];
    } elseif (isset($_GET['orderid']) && $_GET['orderid'] > 0) {
        $orderid = intval($_GET['orderid']);
        $sql     = " orderid='" . $orderid . "'";
        $link    = '&orderid=' . $orderid;
    } else {
        $sql  = ' `tid`=\'' . $tid . '\'';
        $link = '&tid=' . $tid;
    }

    $numrows = $DB->count('SELECT count(*) from pre_faka WHERE' . $sql);
    echo '<div class="block">
    <div class="block-title"><h3 class="panel-title">
        ';
    echo $row['name'];
    echo ' - 卡密库存列表</h3>
    </div>
    <div class="panel-body">
    <a href="fakakms.php?my=add&tid=';
    echo $tid;
    echo '" class="btn btn-success">加卡</a>
  <a href="fakakms.php?my=qk&tid=';
    echo $tid;
    echo '" class="btn btn-danger">清空</a>
  <a href="fakakms.php?my=qkuse&tid=';
    echo $tid;
    echo '" class="btn btn-danger">清空已使用</a>
  <a onclick="delChecked()" class="btn btn-danger">删除选中</a>
  <a href="#" data-toggle="modal" data-target="#search" id="search" class="btn btn-primary">搜索</a>
  <a href="./download.php?act=kms&tid=' . $tid . '" class="btn btn-info">导出全部</a>
  <a href="./download.php?act=kms&tid=' . $tid . '&use=0" class="btn btn-success">导出未使用</a>
  <a href="./download.php?act=kms&tid=' . $tid . '&use=1" class="btn btn-warning">导出已使用</a>
  </div>
    <form name="form1" method="post" action="fakakms.php?my=del2">
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>卡号</th><th>密码</th><th>状态</th><th>添加时间</th><th>使用时间</th><th>操作</th></tr></thead>
          <tbody>
          <tr><td><input type="checkbox" onClick="check1()">反选</td><td>——</td><td>——</td><td>——</td><td>——</td><td>——</td></tr>
';
    $pagesize = 30;
    $pages    = intval($numrows / $pagesize);
    if ($numrows % $pagesize) {
    }
    if (isset($_GET['page'])) {
        $page = intval($_GET['page']);
    } else {
        $page = 1;
    }
    $offset = $pagesize * ($page - 1);
    $rs     = $DB->query('SELECT * FROM pre_faka WHERE' . $sql . ' order by kid desc limit ' . $offset . ',' . $pagesize);
    while ($res = $DB->fetch($rs)) {
        if ($res['usetime'] != null && $res['orderid'] > 0) {
            $isuse = '<font color="red">已使用</font>(' . $res['orderid'] . ')';
        } else {
            $isuse = '<font color="green">未使用</font>';
        }
        echo '<tr><td><input type="checkbox" name="checkbox[]" id="list1" value="' . $res['kid'] . '" onClick="unselectall1()"><b>' . $res['km'] . '</b></td><td>' . $res['pw'] . '</td><td>' . $isuse . '</td><td>' . $res['addtime'] . '</td><td>' . $res['usetime'] . '</td><td><a href="./fakakms.php?my=del&id=' . $res['kid'] . '" class="btn btn-xs btn-danger" onclick="return confirm(\'你确实要删除此卡密吗？\');">删除</a></td></tr>';
    }

    echo '          </tbody>
        </table>

        </div>
        </form>
        ';

    # 分页
    $PageList = new \core\Page($numrows, $pagesize, 0, $link);
    echo $PageList->showPage();

}

echo '    </div>
  </div>
</div>
<script>
var tid = ' . (isset($_GET['tid']) ? (int) $_GET['tid'] : 0) . ';

var checkboxlist=\'\';
function check1(field) {
    var checkbox=document.getElementsByName(\'checkbox[]\');
    checkboxlist=\'\';
    for (var i = 0; i <checkbox.length; i++) {
           if (checkbox[i].checked==true) {
            checkbox[i].checked=false;
           }
           else{
              checkbox[i].checked=true;
              if (checkboxlist==\'\') {
                checkboxlist=\'\'+checkbox[i].value;
              }
              else{
                checkboxlist=\',\'+checkbox[i].value;
              }
           }
     }
}

function unselectall1(that)
{
    checkboxlist=getVals() || \'\';
}


function getVals(){
   var checkbox=document.getElementsByName(\'checkbox[]\');
   idlist=\'\';
   //console.log(checkbox);
   for (var i = 0; i <checkbox.length; i++) {
       if (checkbox[i].checked) {
          if (idlist==\'\') {
              idlist=\'\'+checkbox[i].value;
          }
          else{
              idlist=idlist+\',\'+checkbox[i].value;
          }
       }
   }
   console.log(idlist);
   return idlist;
}

function checkAdd(){
    if($("#tid").val()==0||$("#tid").val()==null){
        layer.alert(\'请先选择商品\');return false;
    }
    if($("#kms").val()==\'\'){
        layer.alert(\'卡密列表不能为空\');return false;
    }
}

function delChecked(){
    var tid =' . (isset($tid) ? $tid : '0') . ';
    var list = getVals();
    if(list==""){
        return layer.alert(\'未选中任何记录\');
    }
    layer.alert("确定删除选中的卡密内容吗？",{
        btn:["确定","取消"],
        yes:function(){
            $.ajax({
                type : "POST",
                url  : "ajax.php?act=delFakaKms",
                data : "tid="+tid+"&list="+list,
                dataType : \'json\',
                success : function(data) {
                    if (data.code == 0) {
                        layer.msg(data.msg);
                        setTimeout(function(){
                          window.location.reload();
                        },1000);
                    }
                    else{
                        layer.alert(data.msg ? data.msg : \'操作失败\');
                    }
                },
                error:function(data){
                    layer.alert(\'服务器错误\');
                    return false;
                }
            });
        }
    });
}

$(document).ready(function(){
    $("#cid").change(function () {
        var cid = $(this).val();
        var ii = layer.load(2, {shade:[0.1,\'#fff\']});
        $("#tid").empty();
        $("#tid").append(\'<option value="0">请选择商品</option>\');
        $.ajax({
            type : "GET",
            url : "./ajax.php?act=getfakatool&cid="+cid,
            dataType : \'json\',
            success : function(data) {
                layer.close(ii);
                if(data.code == 0){
                    var num = 0;
                    $.each(data.data, function (i, res) {
                        $("#tid").append(\'<option value="\'+res.tid+\'">\'+res.name+\'</option>\');
                        num++;
                    });
                    if(typeof(tid)!=="undefined"){
                        $("#tid").val(tid);
                    }
                    else{
                        $("#tid").val(0);
                    }

                    if(num==0 && cid!=0)$("#tid").html(\'<option value="0">该分类下没有商品</option>\');
                }else{
                    layer.alert(data.msg);
                }
            },
            error:function(data){
                layer.msg(\'服务器错误\');
                return false;
            }
        });
        return false;
    });
    var items = $("select[default]");
    for (i = 0; i < items.length; i++) {
        $(items[i]).val($(items[i]).attr("default")||0);
    }
});

</script>';

include_once 'footer.php';
