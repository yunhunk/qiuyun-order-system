<?php
include '../../includes/common.php';

if ($islogin == 1) {} else {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}
$title = '站点数据回退小工具V1.0.1';

$dbqz  = isset($_GET['dbqz']) && $_GET['dbqz'] ? trim($_GET['dbqz']) : 'cmy';
$ok    = 0;
$count = intval($DB->getCount("SELECT * FROM `pre_site` where 1"));
if ($count > 0) {
    $ok = intval($DB->exec("UPDATE `pre_site` SET `rmb`=`money`,`domain`=`siteurl`,`domain2`=`siteurl2`"));
    echo "查询到{$count}数据，成功迁移回退{$ok}条数据";
} else {
    echo "查询到{$count}数据，无需迁移";
}
?>
<br/>
数据迁移完成
