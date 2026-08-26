<?php
//文章通知 By 星河
include '../includes/common.php';
if (!defined('IN_CRONLITE')) {
    exit();
}

$hosturl = addslashes($_SERVER['HTTP_HOST']); //获取当前host域名
if (isset($_GET['id'])) {
    $id  = intval(getParams('id', true));
    $row = $DB->get_row("SELECT * from pre_message where id= ? limit 1", [$id]);

    if (!$row || $row['active'] != 1) {
        $active = false;
    } else {
        $active = true;
    }

    $lasetRow = $DB->get_row("SELECT * from pre_message where id<'" . $row['id'] . "' and active=1 order by id desc limit 1");
    if ($lasetRow) {
        $lasetId = $lasetRow['id'];
    } else {
        $lasetId = false;
    }

    $prevRow = $DB->get_row("SELECT * from pre_message where id>'" . $row['id'] . "' and active=1 order by id asc limit 1");
    if ($prevRow) {
        $prevId = $prevRow['id'];
    } else {
        $prevId = false;
    }
    if ($clientip != $_COOKIE["msg_read_ip"]) {
        setcookie('msg_read_ip', $clientip, time() + 86400);
        $DB->query("update pre_message set count=count+1 where id='" . $id . "'");
    }
} else {
    $active = false;
}

if ($conf['fenzhan_logo_open'] == 1 && $is_fenzhan == true && $siterow['logo'] && file_exists(ROOT . $siterow['logo'])) {
    $logo = $siterow['logo'];
} else {
    $logo = $conf['zz_logo'];
    if ($logo == "") {
        $logo = 'assets/img/logo.png';
    }
}

$themePath = getArticleTheme();

$file1    = $themePath . 'articleStatic.php';
$file2    = $themePath . 'article.php';
$file_404 = $themePath . '404.html';

if ($conf['article_static'] == 1) {
    if (!file_exists($file1)) {
        die('该页面维护中，主题不存在！请等待修复');
    }
    include_once $file1;
    exit;
}

if (!file_exists($file2)) {
    die('该页面维护中，主题不存在！请等待修复');
}
include_once $file2;
