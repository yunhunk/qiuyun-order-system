<?php
include '../../includes/common.php';

if ($islogin == 1) {} else {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

$title = '站点数据回退小工具V1.0.1';

$dbqz  = isset($_GET['dbqz']) && $_GET['dbqz'] ? trim($_GET['dbqz']) : 'cmy';
$ok    = 0;
$ok2   = 0;
$count = intval($DB->getCount("SELECT * FROM `pre_tools`"));
if ($count > 0) {
    try {
        $rs = $DB->query("SELECT * FROM `pre_tools`");
        if ($rs) {
            while ($value = $rs->fetch()) {
                $tid    = $value['tid'];
                $active = $value['close'] == 0 ? 1 : 0;
                $close  = $value['active'] == 1 ? 0 : 1;
                $sql    = $DB->query("UPDATE `pre_tools` SET `active`='{$active}',`close`='{$close}' where `tid`='{$tid}'");
                if ($sql) {
                    $ok++;
                }
            }
        }
    } catch (\Exception $e) {
        die('系统错误，' . $e->getMessage());
    }
    echo "查询到{$count}数据，成功迁移回退{$ok}条商品数据";
} else {
    echo "查询到{$count}数据，无需迁移";
}
?>
<br/>
数据迁移完成
