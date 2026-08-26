<?php
$is_defend = true;
require '../includes/common.php';
if ($isLogin2 == 1) {} else {
    $goto = @getHostUrl();

    exit("<script language='javascript'>window.location.href='./login.php?goto=" . $goto . "';</script>");
}

if ($userrow['power'] == 2) {
    $type = '0,2,4';
} elseif ($userrow['power'] == 1) {
    $type = '0,2,3';
} else {
    $type = '0,1';
}

$act = input('act');
if ($act == 'allread') {
    $rs  = $DB->select("SELECT id FROM pre_message WHERE type IN ($type) AND active=1");
    $ids = [];
    if ($rs) {
        foreach ($rs as $key => $value) {
            $ids[] = $value['id'];
        }
    }
    $msgread = implode(',', $ids);
    $DB->query("update `pre_site` set `msgread`='{$msgread}' where `zid`=:zid", [':zid' => $userrow['zid']]);
    exit(json_encode(['code' => 0, 'msg' => 'succ'], 256));
}

$msgcount = $DB->count("SELECT count(*) FROM pre_message WHERE type IN ($type) AND active=1");
$msgread  = explode(',', $userrow['msgread']);
$limit    = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$rs       = $DB->query("SELECT * FROM pre_message WHERE type IN ($type) AND active=1 ORDER BY sort ASC,id DESC,addtime DESC LIMIT 0,:limit", [':limit' => $limit]);
$msgrow   = array();
while ($res = $DB->fetch($rs)) {
    if (in_array($res['id'], $msgread)) {
        $res['read'] = true;
    } else {
        $res['read'] = false;
    }

    $msgrow[] = $res;
}

$title = '消息列表';
$my    = isset($_GET['my']) ? $_GET['my'] : null;
include 'head.php';
$readlist = explode(',', $userrow['msgread']);
if ($my == 'msginfo') {
    echo '
    <div class="wrapper">
           <div class="col-sm-12">';
    $id  = intval($_GET['id']);
    $row = $DB->get_row("select * from pre_message where id=:id limit 1", [':id' => $id]);
    if ($row) {
        if (!in_array($row['id'], $readlist)) {
            array_push($readlist, $row['id']);
            $msgread = implode(',', $readlist);
            $msgread = trim($msgread, ',');
            $DB->query("update `pre_site` set `msgread`=:msgread where `zid`=:zid", [':msgread' => $msgread, ':zid' => $userrow['zid']]);
            $DB->query("update `pre_message` set `count`=`count`+1 where `id`= ?", [$id]);
        }
        ?>
<style type="text/css">
div.alert {
   display: block;
}

div.alert-content img {
    max-width: 100%;
}
</style>
<div class="panel panel-success table-responsive">
  <div class="panel-heading font-bold" style="background-color: #9999CC;color: white;">
    文章详情页
  </div>
  <div class="panel-body">
    <h4 style="color:blue;font-size:20px;" class="text-center"><?php echo $row['title'] ?></h4>
    <center><span class="fa fa-eye " style="color:red"></span>&nbsp;&nbsp;<?php echo $row['count']; ?></center>
    <div class="alert alert-content" style="width:95%;margin:12.5px auto;" class="text-center">
  <?php echo $row['content'] ?>
  </div>
  <div>
     <div style="float:left;width:50%;display:inline-block;" class="text-left">
        来自管理员</font>
     </div>
     <div style="float:left;width:50%;display:inline-block;" class="text-right">
       <font><?php echo $row['addtime']; ?></font>
     </div>
     <div style="float:left;width:33.33%;display:inline-block;" class="text-left hide">
         <font>管理员<?php echo ''; ?></font>
     </div>
    </div>
    <hr style="margin-top: 50px;border: 1px solid #eee;">
  <div class="form-group">
      <a onclick="javaScript:history.back(-1);" class="btn btn-info btn-rounded userhome"><i class="fa fa-user"></i>&nbsp;返回上页</a>
      <a href="./message.php" class="btn btn-danger btn-rounded msglist" style="float:right;"><i class="fa fa-commenting"></i>&nbsp;查看更多</a>
  </div>
  </div>
  </div>

  <?php if (!in_array($row['id'], $readlist)) {?>
      <script>
      $(document).ready(function(){
        $(".msglist").html('<i class="fa fa-commenting"></i>&nbsp;请先查阅');
        $(".userhome").html('<i class="fa fa-user"></i>&nbsp;3秒后恢复');
        $(".msglist").attr('href','javascript:return false;');
        $(".userhome").attr('href','javascript:return false;');
        setTimeout(function(){
          $(".msglist").html('<i class="fa fa-commenting"></i>&nbsp;查看更多');
            $(".userhome").html('<i class="fa fa-user"></i>&nbsp;返回上页');
          $(".msglist").attr('href','./message.php');
          $(".userhome").attr('href','./');

        },3000);
      });
      </script>
      <?php
}
    } else {

        echo '<div class="panel panel-primary table-responsive">
  <div class="panel-heading text-center">404提示页面</div>
  <div class="panel-body">
    <h4 style="color:blue;font-size:20px;" class="text-center">页面未找到，请确认后再试！</h4>
  </div>
  </div>
  <div class="form-group">
    <a href="./message.php" class="btn btn-danger btn-rounded msglist"><i class="fa fa-commenting"></i>&nbsp;消息列表</a>
    <a href="./" class="btn btn-info btn-rounded userhome" style="float:right;"><i class="fa fa-user"></i>&nbsp;后台首页</a>
  </div>
  ';
    }
    exit;
}
?>
<style>
.msg-head{text-align: center;min-width: 360px;padding: 7px;background-color: #f9f9f9 !important;}
.msg-body{padding: 15px;margin-bottom: 20px;}
</style>
<div class="wrapper">
<div class="col-sm-12">
<div class="panel panel-default">
<div class="panel-heading font-bold" style="background-color: #9999CC;color: white;">
    <div> <span style="font-size: 20px;">消息列表</span>&nbsp;<a id="allread" class="btn btn-success btn-xs">全部已读</a></div>
</div>
<div class="well well-sm" style="margin: 0;">
    我共收到 <b><?php echo $msgcount ?></b> 个消息
</div>
<div class="table-responsive">
        <table class="table table-striped b-t b-light">
          <thead><th>操作</th><th>通知标题</th><th>接收时间</th><th>阅读状态</th></tr></thead>
<?php
foreach ($msgrow as $row) {
    echo '
  <tr class="onclick ' . ($row['read'] ? '' : 'warning') . '"  >
  <td><a href="./message.php?my=msginfo&id=' . $row['id'] . '" class="btn btn-info btn-xs">查看</a></td>
  <td>' . $row['title'] . '</td>
  <td>' . $row['addtime'] . '</td>
  <td>' . ($row['read'] ? '<span class="label label-success">已读</span>' : '<span class="label label-warning">未读</span>') . '</td>
</tr>';
}
if ($msgcount == 0) {
    echo '<tr><td class="text-center"><font color="grey">消息列表空空如也</font></td></tr>';
}
?>
          <tbody>
          </tbody>
        </table>
    <?php if ($msgcount > $limit) {?>
    <div class="list-group-item"><center><a href="?limit=<?php echo $limit + 10; ?>" id="btnload">加载更多</a></center></div>
    <?php }?>
      </div>
</div>
</div>
</div>
<?php include 'footer.php'?>

<script>
$(document).on('click', '#allread',  function () {
    $.ajax({
        type: "get",
        url: "?act=allread",
        data: {},
        dataType: "json",
        success: function (response) {
            if (response.code==0) {
                window.location.reload();
            }
        }
    });
});
</script>