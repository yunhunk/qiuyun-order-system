<?php
if (!defined('IN_CRONLITE')) {
    exit();
}
//博客站最新文章
?>
<div class="block block-primary block animated bounceInDown" style="box-shadow:0 5px 10px 0 rgba(0, 0, 0, 0.25);">
    <div class="panel-heading">
    <h3 class="panel-title"><font color="#1e90ff"><i class="fa fa-skyatlas"></i>&nbsp;&nbsp;<b>最新文章</b></font></h3>
    </div>
    <div class="article-card">
         <div class="article-card-header article-elip animated zoomInLeft">
            <img src="<?php echo $cdnserver ?>assets/xiaoyao/hot.gif">
            <a onclick="wx('微信支付教程','tj')"><font color="red"><b>微信支付不了，点击这里。</b></font></font></div></a>
<?php

/* 配置博客站数据库 By 星河网络工作室*/
$blogConfig['dbname'] = $conf['template_purpleYear_blog_dbname'];
$blogConfig['dbuser'] = $conf['template_purpleYear_blog_dbuser'];
$blogConfig['dbpwd']  = $conf['template_purpleYear_blog_dbpwd'];
$blogConfig['dbqz']   = !empty($conf['template_purpleYear_blog_dbqz']) ? $conf['template_purpleYear_blog_dbqz'] : 'zbp';
$blogConfig['static'] = $conf['template_purpleYear_blog_static'];

try {
    $link = new mysqli('127.0.0.1', $blogConfig['dbuser'], $blogConfig['dbpwd'], $blogConfig['dbname']);
    if ($link) {
        $link->set_charset("utf8");
        if (strpos($blogConfig['dbqz'], '_') !== false) {
            $sql = "SELECT log_ID,log_Title from `{$blogConfig['dbqz']}post` WHERE log_CateID>0 order by log_ID DESC limit 8";
        } else {
            $sql = "SELECT log_ID,log_Title from `{$blogConfig['dbqz']}_post` WHERE log_CateID>0 order by log_ID DESC limit 8";
        }
        $res   = $link->query($sql);
        $data  = [];
        $count = 0;
        if ($res) {
            $data  = $res->fetch_all(1);
            $count = count($data);
            $dir   = $conf['template_purpleYear_blog_dir'];
            foreach ($data as $v) {
                if ($blogConfig['static'] == 1) {
                    $url = "./" . $dir . "/index.php/post/" . $v['log_ID'] . ".html";
                } else {
                    $url = "./" . $dir . "/blog/index.php?id=" . $v['log_ID'];
                }

                echo "<a target=\"_blank\" class=\"list-group-item animated zoomInLeft\" title=\"QQ网\" href=\"" . $url . "\"><img src=\"<?php echo $cdnserver ?>assets/xiaoyao/hot.gif\">&nbsp;<font color=\"#000000\"><b>" . $v['log_Title'] . "</b></font></a>";
            }
            if ($count == 0) {
                echo "<a onclick=\"return false;\" class=\"list-group-item animated zoomInLeft\" title=\"QQ网\"><img src=\"<?php echo $cdnserver ?>assets/xiaoyao/hot.gif\">&nbsp;<font color=\"#000000\"><b>当前一条文章都木有~</b></font></a>";
            }
        } else {
            echo "<a onclick=\"return false;\" class=\"list-group-item animated zoomInLeft\">文章获取异常，" . mysqli_connect_error() . "</a>";
        }
    } else {
        echo "<a onclick=\"return false;\" class=\"list-group-item animated zoomInLeft\">数据库连接异常，" . mysqli_connect_error() . "</a>";
    }

} catch (Exception $e) {
    echo "<a onclick=\"return false;\" class=\"list-group-item animated zoomInLeft\">当前最新文章获取失败，数据库连接异常</a>";
    //die("连接失败：" . $link->connect_error);
}
?>
</div>
</div>