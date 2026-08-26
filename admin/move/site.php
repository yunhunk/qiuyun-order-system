<?php
include '../../includes/common.php';
$title = '站点数据迁移小工具V1.1.1';
checkLogin();

$dbqz   = isset($_GET['dbqz']) && $_GET['dbqz'] ? trim($_GET['dbqz']) : 'shua';
$offset = isset($_GET['offset']) && $_GET['offset'] ? intval($_GET['offset']) : 0;
$limit  = isset($_GET['limit']) && $_GET['limit'] ? intval($_GET['limit']) : 3000;
$count  = intval($DB->count("SELECT count(*) FROM `{$dbqz}_site` where `power`>0"));
$count2 = intval($DB->count("SELECT count(*) FROM `pre_site` where `power`>0"));
if ($count2 <= 0) {
    die('已迁移数据为空，请先转移数据后再访问执行');
}
$ok = 0;
if ($count > 0) {
    $rs = $DB->select("SELECT zid,iprice,domain2 FROM `{$dbqz}_site` where `power`>0 limit {$offset},{$limit}");
    if ($rs) {
        foreach ($rs as $key => $value) {
            $zid      = $value['zid'];
            $siteurl  = $value['domain'];
            $siteurl2 = $value['domain2'];

            $iprice = null;
            if (isset($value['iprice']) && $value['iprice']) {
                $arr = @unserialize($value['iprice']);
                if ($arr) {
                    $iprice = [];

                    foreach ($arr as $key => $value) {
                        $iprice[$key] = [
                            'kind'  => 3,
                            'price' => $value,
                        ];
                    }

                    $iprice = json_encode($iprice, 25);
                }
            }

            if ($iprice) {
                $sql = $DB->query("UPDATE `pre_site` SET `siteurl`='{$siteurl}',`siteurl`='{$siteurl}',`iprice`='{$iprice}' where `zid`='{$zid}'");
            } else {
                $sql = $DB->query("UPDATE `pre_site` SET `siteurl`='{$siteurl}',`siteurl`='{$siteurl}' where `zid`='{$zid}'");
            }

            if ($sql) {
                $ok++;
            }
        }
    }
    echo "查询到{$count}数据，成功迁移{$ok}条数据";
} else {
    echo "查询到{$count}数据，无需迁移";
}
?>
<?php if ($count > 0 && $count >= $offset): ?>
<h4>3.5秒后开始迁移第<?php echo ($offset + $limit) ?>到<?php echo ($offset + $limit + $limit) ?>的数据</h4>
<script type="text/javascript">
setTimeout(function () {
    window.location.href='?dbqz=<?php echo $dbqz; ?>&offset=<?php echo ($offset + $limit); ?>&limit=<?php echo $limit; ?>';
}, 3500);
</script>
<?php else: ?>
数据迁移完成
<?php endif;?>
