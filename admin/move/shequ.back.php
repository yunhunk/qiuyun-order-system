<?php
include '../../includes/common.php';

if ($islogin == 1) {} else {
    exit("<script language='javascript'>window.location.href='./login.php';</script>");
}

$title = '站点数据回退小工具V1.0.1';

$dbqz  = isset($_GET['dbqz']) && $_GET['dbqz'] ? trim($_GET['dbqz']) : 'cmy';
$ok    = 0;
$count = intval($DB->getCount("SELECT * FROM `pre_shequ`"));
if ($count > 0) {
    try {
        //<select class="form-control" name="type" default="0"><option value="jiuwu">玖伍社区</option><option value="yile">亿乐社区</option><option value="kayixin">卡易信</option><option value="kayisu">卡易速</option><option value="kashangwl">卡商网</option><option value="shangzhanwl">商战网</option><option value="shangmeng">商盟网</option><option value="yiqida">亿奇达</option><option value="zhike">直客SUP</option><option value="yunbao">云宝发卡</option><option value="kakayun">卡卡云</option><option value="liuliangka">发傲流量卡</option><option value="daishua">同系统对接</option></select>
        $rs = $DB->query("SELECT * FROM `pre_shequ`");
        if ($rs) {
            while ($value = $rs->fetch()) {
                $id       = $value['id'];
                $type     = null;
                $protocol = substr($value['url'], 0, 5) == 'https' ? 1 : 0;
                $url      = preg_match('/[\w\-\.\/]+/', $value['url'], $match) ? $match[0] : $value['url'];
                switch ($value['type']) {
                    case 22:
                        $type = 'shangzhanwl';
                        break;
                    case 0:
                        $type = 'jiuwu';
                        break;
                    case 1:
                        $type = 'yile';
                        break;
                    case 6:
                        $type = 'kayixin';
                    case 9:
                        $type = 'kashangwl';
                    case 25:
                        $type = 'zhike';
                    case 15:
                        $type = 'yunbao';
                    case 18:
                        $type = 'kakayun';
                    case 12:
                        $type = 'daishua';
                    case 13:
                        $type = 'daishua';
                        break;
                    default:
                        $type = 'jiuwu';
                        break;
                }
                if (!is_null($type)) {
                    $sql = $DB->query("UPDATE `pre_shequ` SET `type`='{$type}',`url`='{$url}',`protocol`='{$protocol}' where `id`='{$id}'");
                    if ($sql) {
                        $ok++;
                    }
                }
            }
        }
    } catch (\Exception $e) {
        die('系统错误，' . $e->getMessage());
    }
    echo "查询到{$count}数据，成功迁移回退{$ok}条数据";
} else {
    echo "查询到{$count}数据，无需迁移";
}
?>
<br/>
数据迁移完成
