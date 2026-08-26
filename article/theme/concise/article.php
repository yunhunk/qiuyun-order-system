<?php
if (!defined('IN_CRONLITE')) {
    exit();
}

if ($_GET['id'] && $row) {
    $seotitle       = $row['title'] . '-' . $row['seotitle'];
    $seokeywords    = $row['seokeywords'];
    $seodescription = $row['title'] . ',' . $row['seotitle'];
} else {
    $seotitle       = '网论坛首页 网资讯 网教程';
    $seokeywords    = 'qq网,网教程,哪个网好用,网,,qq,**网址,刷粉网址';
    $seodescription = '网论坛首页，为你提供优质的网使用教程，和一些注意事项！帮助大家不被骗子网圈钱';
}
?>
<!--
 # 站内通知前台版By 星河
-->
<!DOCTYPE html>
<html lang="zh-cn">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
  <title><?php echo $seotitle ?>-<?php echo $row['sitename'] ?></title>
  <meta name="keywords" content="<?php echo $seokeywords ?>">
  <meta name="description" content="<?php echo $seodescription ?>">
  <link href="//lib.baomitu.com/twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="//lib.baomitu.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
  <!--[if lt IE 9]>
    <script src="<?php echo $cdnpublic ?>html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="<?php echo $cdnpublic ?>respond.js/1.4.2/respond.min.js"></script>
  <![endif]-->
<style>
#activity-name{
   font-size: 18px;
   font-size: 1.8rem;
}
body{
background:#ecedf0 url("<?php echo $background_image ?>") fixed;
<?php echo $repeat ?>}

.onclick{cursor: pointer;touch-action: manipulation;}
.block > div > a:hover{
   color：yellow;
   text-decoration：underline
}
#img-content img{
   max-width:100%;
}
</style>
</head>
<body>
<br/>
<div class="col-xs-12 col-sm-10 col-md-8 col-lg-6 center-block" style="float: none;">
<div class="panel panel-default">
  <div class="panel-body" style="text-align: center;">
    <img src="/<?php echo $logo ?>" style="max-width: 100%;">
  </div>
  <div class="btn-group btn-group-justified">
    <div class="btn-group">
      <a class="btn btn-default" data-toggle="modal" href="http://<?php echo $hosturl ?>/"><font color="#ff0000"><i class="fa fa-home"></i> <span style="font-weight:bold">返回网站</span></font></a>
    </div>
    <div class="btn-group">
      <a class="btn btn-default" data-toggle="modal" href="http://<?php echo $hosturl ?>/article/index.php"><i class="fa fa-bars"></i> <span style="font-weight:bold">文章首页</span></a>
    </div>
    <div class="btn-group">
        <a class="btn btn-default" data-toggle="modal" href="http://<?php echo $hosturl ?>/user/regsite.php"><font color="#ff0000"><i class="fa fa-bolt"></i><span style="font-weight:bold">搭建分站</span></font></a>
    </div>
   </div>
</div>
<?php
if ($_GET['id']) {
    ?>
<!--公告开始
--><div class="panel panel-primary">
  <div class="panel-heading"><h3 class="panel-title" ><font color="#FFFFFF"><i class=""></i><b>最新公告</font>
    </b></h3></div>
  <div class="gonggao">
   <div id="page-content">

        <div id="img-content" class="rich_media_area_primary" style="display: block;padding: 8px 3px;padding-bottom:20px">
<!--top begin-->
          <h1 class="rich_media_title text-center" id="activity-name"><?php echo $row['title']; ?></h1>
              <div class="block" style="display: block;margin:5px auto">
                <span class="pull-left"><?php echo $conf['sitename']; ?></span>
                <span class="pull-right"><?php echo $row['addtime']; ?></span>
                <br>
              </div>
              <div class="block" style="display: block;padding: 2px 5px;">
             <?php
if ($active === false) {
        echo '<h4 style="color:red">该文章已被删除或不存在！</h4>';
    } else {
        echo $row['content'];
    }
    ?>
              </div>
              <hr>
              <div class="block" style="display: block;margin:8px auto;margin-bottom: 5px">
                <?if ($lasetId) {?>
                <div  style="display: block;">上一篇&nbsp;<a href="http://<?php echo $hosturl; ?>/article/?id=<?php echo $lasetId; ?>.html" title="<?php echo $lasetRow['title'] ?>"  style="color: blue"><?php echo $lasetRow['title']; ?></a></div>
                <?php } else {?>
                  <div style="display: block;">上一篇&nbsp;&nbsp;<a href="javascript:(0)" style="color: blue"><b>没有了</b></a></div>
                <?php }?>
                <?php if ($prevId) {?>
                  <div  style="display: block;">下一篇&nbsp;<a href="http://<?php echo $hosturl; ?>/article/?id=<?php echo $prevId; ?>.html" title="<?php echo $prevRow['title'] ?>"  style="color: blue"><?php echo $prevRow['title']; ?></a></div>
                <?php } else {?>
                   <div style="display: block;">下一篇&nbsp;&nbsp;<a href="javascript:(0)" style="color: blue"><b>没有了</b></a></div>
                <?php }?>
                <br>
            </div>
<!--content over-->
       </div>
</div>
</div>
</div>
<link href="//lib.baomitu.com/viewer.js/0.11.0/crocodoc.viewer.min.css" rel="stylesheet">
<script type="text/javascript" src="//lib.baomitu.com/viewer.js/0.11.0/crocodoc.viewer.min.js"></script>
<script type="text/javascript">

</script>
<?php
} else {

    $type = '0,1';
    if ($_GET['kw'] && $conf['message_search'] == 1) {
//搜索
        $kw       = trim(daddslashes($_GET['kw']));
        $msgcount = $DB->count("SELECT count(*) FROM pre_message WHERE type IN ($type) AND (`title` like ? OR content like ?) AND active=1", array('%' . $kw . '%', '%' . $kw . '%'));
        $rs       = $DB->query("SELECT * FROM pre_message WHERE type IN ($type) AND (`title` like ? OR content like ?) and active=1 ORDER BY sort asc", array('%' . $kw . '%', '%' . $kw . '%'));
    } else {
        $msgcount = $DB->count("SELECT count(*) FROM pre_message WHERE type IN ($type) AND active=1");
        $limit    = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
        if ($limit > $msgcount) {
            $limit = $msgcount;
        }
        $_COOKIE['limit'] = $limit;
        $rs               = $DB->query("SELECT * FROM pre_message WHERE type IN ($type) AND active=1 ORDER BY sort asc LIMIT 0, ?", array($limit));
    }
    $msgrow = array();
    if ($msgcount > 0) {
        $msgrow = $DB->fetchAll($rs);
    }
    ?>
   <div class="panel panel-body block">
    <div class="block-title">
        <h2><i class="fa fa-list"></i>&nbsp;&nbsp;<b>文章列表</b></h2>
    </div>
    <div class="form-group <?php if ($conf['message_search'] != 1) {?>hide<?php }?>">
      <div class="input-group"><div class="input-group-addon">搜索</div>
      <input type="text" name="kw" value="" class="form-control" placeholder="输入关键词" onkeydown="if(event.keyCode==13){doSearch.click()}" required/>
      <span class="input-group-addon btn" id="doSearch"><span class="glyphicon glyphicon-search" title="搜索"></span></span>
    </div></div>
    <?php if (isset($_GET['kw'])) {
        echo '包含搜索内容 <span style="color:red">' . trim(addslashes($_GET['kw'])) . '</span> 的文章共有 <b>' . $msgcount . '</b> 条。<br><br>';
    }?>
    <div class="table-responsive">
    <table class="table table-hover table-bordered">
      <tbody id="msglist">
    <?php
foreach ($msgrow as $row) {
        echo '<tr class="widget animation-fadeInQuick onclick" onclick="window.location.href=\'http://' . $hosturl . '/article/?id=' . $row['id'] . '.html\'"><td><a title="' . $row['title'] . '" href="http://' . $hosturl . '/article/?id=' . $row['id'] . '.html"><b class="pull-left">' . $row['title'] . '</b><br/><small class="pull-right"><span class="text-muted">' . $row['addtime'] . '</span></small></a></td></tr>';
    }

    if ($msgcount == 0) {
        echo '<tr><td class="text-center"><font color="grey">消息列表空空如也</font></td></tr>';
    }
    ?>
    </tbody>
      </table>
    <?php if ($msgcount > $limit && !isset($_GET['kw'])) {?>
    <center><a href="./?limit=<?php echo $_COOKIE['limit'] + 10; ?>" id="btnload">加载更多</a></center>
    <?php }?>
    </div>
    <hr>
    <div class="form-group">
    <a href="/" class="btn btn-primary btn-rounded"><i class="fa fa-home"></i>&nbsp;返回首页</a>
    <a href="/user/" class="btn btn-info btn-rounded" style="float:right;"><i class="fa fa-user"></i>&nbsp;用户登录</a>
    </div>
      </div>
</div>
<?php
}
?>
<center>
<?php echo $conf['msg_bottom'] ? $conf['msg_bottom'] : null ?>
</center>
</body>
<script src="<?php echo $cdnpublic ?>jquery/1.12.4/jquery.min.js"></script>
<script src="<?php echo $cdnpublic ?>layer/2.3/layer.js"></script>
<script>


var $_GET = (function(){
    var url = window.document.location.href.toString();
    var u = url.split("?");
    if(typeof(u[1]) == "string"){
        u = u[1].split("&");
        var get = {};
        for(var i in u){
            var j = u[i].split("=");
            get[j[0]] = j[1];
        }
        return get;
    } else {
        return {};
    }
})();

$(document).ready(function(){
  if($_GET['kw']){
    $("input[name='kw']").val(decodeURIComponent($_GET['kw']))
  }
  $("#doSearch").click(function () {
    var kw = $("input[name='kw']").val();
    if ('' == kw) {
      return layer.msg('请输入搜索内容');
    }
    window.location.href="./?kw="+encodeURIComponent(kw);
  });
});
</script>
</html>