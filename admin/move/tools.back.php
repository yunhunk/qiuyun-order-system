<?php
include '../../includes/common.php';

if ($islogin == 1) {} else {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

$title = '站点数据回退小工具V1.0.1';

$dbqz   = isset($_GET['dbqz']) && $_GET['dbqz'] ? trim($_GET['dbqz']) : 'cmy';
$ok     = 0;
$ok2    = 0;
$count  = intval($DB->getCount("SELECT * FROM `pre_price`"));
$count2 = intval($DB->getCount("SELECT * FROM `pre_tools`"));
if ($count2 > 0) {
    try {
        $rs = $DB->query("SELECT * FROM `pre_price`");
        if ($rs) {
            while ($value = $rs->fetch()) {
                $id  = $value['id'];
                $p_0 = $value['type'] == 1 ? $value['p_0'] : (1 + $value['p_0'] / 100);
                $p_1 = $value['type'] == 1 ? $value['p_1'] : (1 + $value['p_1'] / 100);
                $p_2 = $value['type'] == 1 ? $value['p_2'] : (1 + $value['p_2'] / 100);
                $sql = $DB->query("UPDATE `pre_price` SET `p_0`='{$p_0}',`p_1`='{$p_1}',`p_2`='{$p_2}' where `id`='{$id}'");
                if ($sql) {
                    $ok++;
                }
            }
        }
        $ok2 = $DB->exec("UPDATE `pre_tools` SET `price`=`price1`");
    } catch (\Exception $e) {
        die('系统错误，' . $e->getMessage());
    }
    echo "查询到{$count}数据，成功迁移回退{$ok}条加价模板数据，成功迁移回退{$ok2}条商品数据";
} else {
    echo "查询到{$count}数据，无需迁移";
}
?>
<br/>
数据迁移完成
