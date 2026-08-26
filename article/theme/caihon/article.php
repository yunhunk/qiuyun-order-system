<?php
if (!defined('IN_CRONLITE')) {
    exit();
}
if (isset($_GET['id'])) {
    if (!$active) {
        if (file_exists($file_404)) {
            include $file_404;
        } else {
            sysmsg('该文章不存在！<br><a href="/"><<<返回网站首页</a>');
        }
        die;
    }

    if (defined('HTTPS_ROOT') && HTTPS_ROOT) {
        $this_url = 'https://' . $hosturl . '/article/?id=' . $row['id'] . '.html';
    } else {
        $this_url = 'http://' . $hosturl . '/article/?id=' . $row['id'] . '.html';
    }

    $seotitle       = $row['title'] . '-' . $row['seotitle'];
    $seokeywords    = $row['seokeywords'];
    $seodescription = $row['title'] . ',' . $row['seotitle'];
    $tags           = '';
    if (!empty($row['seokeywords'])) {
        $array = explode(',', $row['seokeywords']);
        foreach ($array as $value) {
            $tags .= '<a href="' . $this_url . '" class="tags">' . $value . '</a>&nbsp;|&nbsp;';
        }
        $tags = trim($tags, '&nbsp;|&nbsp;');
    }
    ?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
    <title><?php echo $seotitle ?>-<?php echo $row['sitename'] ?></title>
    <meta name="keywords" content="<?php echo $seokeywords ?>">
    <meta name="description" content="<?php echo $seodescription ?>">
    <link href="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/plugins.css">
    <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/main.css">
    <script src="<?php echo $cdnpublic ?>modernizr/2.8.3/modernizr.min.js"></script>
    <!--[if lt IE 9]>
      <script src="<?php echo $cdnpublic ?>html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="<?php echo $cdnpublic ?>respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
        .article-content img {
            max-width: 100% !important;
        }
     body{
        background:#ecedf0 url("<?php echo $background_image ?>") fixed;
        <?php echo $repeat ?>
    }
    </style>
</head>
<body>
<br/>
<div class="col-xs-12 col-sm-10 col-md-8 col-lg-6 center-block" style="float: none;">
    <div class="block">
        <div class="block-title">
            <h2><i class="fa fa-list"></i>&nbsp;&nbsp;<b>文章内容</b></h2>
        </div>
<ol class="breadcrumb">
    <li>
        <a href="../">首页</a>
    </li>
    <li>
        <a href="./index.php">文章列表</a>
    </li>
    <li class="active"><?php echo $row['title'] ?></li>
    </ol>
    <div class="text-center">
    <h3><strong><?php echo $row['title'] ?></strong></h3>
    <span class="text-muted"><i class="glyphicon glyphicon-time"></i>&nbsp;<?php echo $row['addtime'] ?>&nbsp;&nbsp;&nbsp;<i class="fa fa-mouse-pointer" aria-hidden="true"></i>&nbsp;<?php echo $row['count'] ?></span>
    </div><hr/>
    <div class="article-content">
    <?php echo $row['content'] ?>
    </div>
    <div class="form-group">
        <p><br></p>
       <p class="<?php if (empty($tags)) {?>hide<?php }?>" style="margin: 0;padding: 0">本页标签: <?php echo $tags ?></p>
       <p style="margin: 0;padding: 0">本文链接: <a href="<?php echo $this_url ?>" class="tags"><?php echo $this_url ?></a></p>
     </div>

        <div style="margin-bottom: 10px;margin-top: 10px;">
            <p style="margin: 0;">
                上一篇：<?php echo empty($lasetId) ? '没有了~' : ('<a href="../article/?id=' . $lasetRow['id'] . '.html">' . $lasetRow['title'] . '</a>'); ?>
            </p>
            <p style="margin: 0;">
                下一篇：<?php echo empty($prevId) ? '没有了~' : ('<a href="../article/?id=' . $prevRow['id'] . '.html">' . $prevRow['title'] . '</a>'); ?>
            </p>
        </div>
            <hr>
            <div class="form-group">
            <a href="../" class="btn btn-primary btn-rounded"><i class="fa fa-home"></i>&nbsp;返回首页</a>
            <a href="../article/index.php" class="btn btn-info btn-rounded" style="float:right;"><i class="fa fa-list"></i>&nbsp;返回列表</a>
            </div>
        </div>
      </div>
    </div>
  </div>
<?php
} else {
    $type = @getArticleTypeSign();
    $kw   = !empty($_GET['kw']) ? input('get.kw', 1) : null;
    if ($kw && $conf['message_search'] == 1) {
        $pagesize = 10;
        $pages    = ceil($msgcount / $pagesize);
        $page     = isset($_GET['page']) ? intval(getParams('page', true)) : 1;
        $offset   = $pagesize * ($page - 1);
        $msgcount = $DB->count("SELECT count(*) FROM pre_message WHERE type IN ($type) AND (`title` like ? OR content like ?) AND active=1", array('%' . $kw . '%', '%' . $kw . '%'));
        $rs       = $DB->query("SELECT * FROM pre_message WHERE type IN ({$type}) AND (`title` like ? OR content like ?) and active=1 ORDER BY top DESC,sort ASC,id DESC LIMIT {$offset},{$pagesize}", array('%' . $kw . '%', '%' . $kw . '%'));
        $msgrow   = array();
        if ($rs) {
            $msgrow = $DB->fetchAll($rs);
        }
        $link     = "&kw=" . $kw;
        $seotitle = '网文章搜索';
    } else {
        $seotitle = '网论坛首页 网资讯 网教程';
        $msgcount = $DB->count("SELECT count(*) FROM pre_message WHERE active=1");
        $pagesize = 10;
        $pages    = ceil($msgcount / $pagesize);
        $page     = isset($_GET['page']) ? intval($_GET['page']) : 1;
        $offset   = $pagesize * ($page - 1);

        $rs     = $DB->query("SELECT * FROM pre_message WHERE type IN ($type) AND active=1 ORDER BY top DESC,sort ASC,id DESC LIMIT {$offset},{$pagesize}");
        $msgrow = array();
        if ($rs) {
            $msgrow = $DB->fetchAll($rs);
        }
    }

    $seokeywords    = '新闻中心,帮助中心,系统公告';
    $seodescription = '平台新闻中心，为你提供一些平台快讯和帮助教程，帮助大家提供更好的购物体验！';

    ?>
<!DOCTYPE html>
<html lang="zh-cn">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"/>
    <title><?php echo $seotitle ?> - <?php echo $conf['sitename'] ?></title>
    <meta name="keywords" content="<?php echo $seokeywords ?>">
    <meta name="description" content="<?php echo $seodescription ?>">
    <link href="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/plugins.css">
    <link rel="stylesheet" href="<?php echo $cdnserver ?>assets/simple/css/main.css">
    <script src="<?php echo $cdnpublic ?>modernizr/2.8.3/modernizr.min.js"></script>
    <!--[if lt IE 9]>
      <script src="<?php echo $cdnpublic ?>html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="<?php echo $cdnpublic ?>respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
<style>
.onclick{cursor: pointer;touch-action: manipulation;}
#msglist a:hover, a:active, a {text-decoration: none;display: block;color: #337ab7;}
body{
    background:#ecedf0 url("<?php echo $background_image ?>") fixed;
    <?php echo $repeat ?>
}
</style>
</head>
<body>
<br/>
<div class="col-xs-12 col-sm-10 col-md-8 col-lg-6 center-block" style="float: none;">
    <div class="block">
        <div class="block-title">
            <h2><i class="fa fa-list"></i>&nbsp;&nbsp;<b>文章列表</b></h2>
        </div>
        <?php if (!empty($kw) && $conf['message_search'] == 1) {?>
        <div class="form-group">
           <a href="./" class="btn btn-info btn-sm btn-block">返回文章列表</a>
        </div>
        <?php }?>
        <div class="form-group <?php if ($conf['message_search'] != 1) {?>hide<?php }?>">
            <div class="input-group"><div class="input-group-addon">搜索</div>
                <input type="text" name="kw" value="" class="form-control" placeholder="输入关键词" onkeydown="if(event.keyCode==13){doSearch.click()}" required/>
                <span class="input-group-addon btn" id="doSearch"><span class="glyphicon glyphicon-search" title="搜索"></span></span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <tbody id="msglist">
                <?php
foreach ($msgrow as $row) {
        $content = strip_tags($row['content']);
        if (mb_strlen($content) > 80) {
            $content = mb_substr($content, 0, 80, 'utf-8') . '......';
        }

        echo '<tr class="animation-fadeInQuick"><td><a href="//' . $hosturl . '/article/?id=' . $row['id'] . '.html"><div>
                    <b class="pull-left">' . strip_tags($row['title']) . '</b>
                    <small class="pull-right"><span class="text-muted">' . $row['addtime'] . '</span></small>
                    </div>
                    <br>
                    <p style="margin-bottom: 0;color: #6b6b6b;white-space: normal;word-break: break-all;word-wrap: break-word;">' . $content . '</p>
                    </a>
                    </td>
                    </tr>';
    }
    if ($msgcount == 0) {
        echo '<tr><td class="text-center"><font color="grey">文章列表空空如也</font></td></tr>';
    }
    ?>
                </tbody>
            </table>
            <?php if ($msgcount > $pagesize) {
        if ($page > 1) {
            echo '<a href="../article/index.php?page=' . ($page - 1) . $link . '" class="btn btn-default">上一页</a>';
        }
        if ($page < $pages) {
            echo '<a href="../article/index.php?page=' . ($page + 1) . $link . '" class="btn btn-default pull-right">下一页</a>';
        }
    }
    ?>
            </div>
            <hr>
            <div class="form-group">
            <a href="../" class="btn btn-primary btn-rounded"><i class="fa fa-home"></i>&nbsp;返回首页</a>
            <a href="../user/" class="btn btn-info btn-rounded pull-right"><i class="fa fa-user"></i>&nbsp;用户中心</a>
            </div>
        </div>
      </div>
    </div>
  </div>
<?php
}?>
<script src="<?php echo $cdnpublic ?>jquery/1.12.4/jquery.min.js"></script>
<script src="<?php echo $cdnpublic ?>twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="<?php echo $cdnpublic ?>layer/2.3/layer.js"></script>
<script type="text/javascript">
//百度自动推送  By 星河
(function() {
    var bp = document.createElement('script');
    var curProtocol = window.location.protocol.split(':')[0];
    if (curProtocol === 'https') {
        bp.src = 'https://zz.bdstatic.com/linksubmit/push.js';
    } else {
        bp.src = 'http://push.zhanzhang.baidu.com/push.js';
    }
    var s = document.getElementsByTagName("script")[0];
    s.parentNode.insertBefore(bp, s);
})();
var $_GET = (function() {
    var url = window.document.location.href.toString();
    var u = url.split("?");
    if (typeof(u[1]) == "string") {
        u = u[1].split("&");
        var get = {};
        for (var i in u) {
            var j = u[i].split("=");
            get[j[0]] = j[1];
        }
        return get;
    } else {
        return {};
    }
})();
$(document).ready(function() {
    if ($_GET['kw']) {
        $("input[name='kw']").val(decodeURIComponent($_GET['kw']))
    }
    $("#doSearch").click(function() {
        var kw = $("input[name='kw']").val();
        window.location.href = "?kw=" + encodeURIComponent(kw);
    });
});
</script>
</body>
</html>