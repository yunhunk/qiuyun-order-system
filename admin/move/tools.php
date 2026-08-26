<?php

include '../../includes/common.php';
$title = '站点数据迁移小工具V1.1.1';
checkLogin();

$dbqz   = isset($_GET['dbqz']) && $_GET['dbqz'] ? trim($_GET['dbqz']) : 'shua';
$offset = isset($_GET['offset']) && $_GET['offset'] ? intval($_GET['offset']) : 0;
$limit  = isset($_GET['limit']) && $_GET['limit'] ? intval($_GET['limit']) : 500;
$DB->setDbqzList();

$count  = intval($DB->count("SELECT count(*) FROM `{$dbqz}_tools`"));
$count2 = intval($DB->count("SELECT count(*) FROM `pre_tools`"));

if ($count2 <= 0) {
    die('已迁移数据为空，请先转移数据后再访问执行');
}
$ok     = 0;
$error  = 0;
$count3 = 0;
if ($count > 0) {
    $rs = $DB->select("SELECT `tid`,`goods_sid`,`prid`,`is_curl`,`cost`,`stock`,`cost2`,`price` FROM `{$dbqz}_tools` limit {$offset},{$limit}");
    if ($rs) {
        $count3 = count($rs);
        foreach ($rs as $key => $value) {
            $tid = $value['tid'];
            $sql = '';
            if (isset($value['sales'])) {
                $sale = $value['sales'];
                $sql .= "`sale`='{$sale}'";
            }

            if (isset($value['audit_status'])) {
                $audit_status = $value['audit_status'];
                $sql .= ",`condition`={$audit_status}";
            }

            if (isset($value['goods_sid'])) {
                $zid = intval($value['goods_sid']);
                $sql .= ",`zid`={$zid}";
            }

            if (isset($value['stock'])) {
                $stock_time = time();
                if ($value['is_curl'] == 4) {
                    $stock = $DB->count("SELECT count(kid) FROM `pre_faka` WHERE tid='{$value['tid']}' AND `orderid`<1");
                    $sql .= ",`stock`={$stock}";
                    $sql .= ",`stock_time`={$stock_time}";
                } else {
                    $stock = $value['stock'] === null ? 999999 : $value['stock'];
                    $sql .= ",`stock`={$stock}";
                    $sql .= ",`stock_time`={$stock_time}";
                }
            }

            if (isset($value['prid']) && $value['prid'] == 0) {
                $sql .= ",`prid`='{$value['prid']}',`cost`='{$value['cost']}',`cost2`='{$value['cost2']}',`price`='{$value['price']}',`price1`='{$value['cost2']}',";
            }

            $sql = trim($sql, ',');

            if ($sql) {
                $sql = $DB->query("UPDATE `pre_tools` SET {$sql} where `tid`='{$tid}'");
                if ($sql) {
                    $ok++;
                } else {
                    $error++;
                }
            } else {
                $ok++;
            }
        }
    }
    echo "查询到{$count}数据, 本次查询到{$count3}条数据, 成功迁移{$ok}条数据, 失败{$error}条";
} else {
    echo "查询到{$count}数据，无需迁移";
}

function filter($value = '')
{
    $value = str_replace(["\r", "\n", "\t", "\'", "\\"], ['', '', '', "'", ''], $value);
    $value = addcslashes(guolv($value), "'");
    return $value;
}

?>
<?php if ($count > 0 && $count >= $offset): ?>
<h4>3.5秒后开始迁移第<?php echo $offset ?>到<?php echo ($offset + $limit) ?>的数据</h4>
<script type="text/javascript">
setTimeout(function () {
	window.location.href='?dbqz=<?php echo $dbqz; ?>&offset=<?php echo ($offset + $limit); ?>&limit=<?php echo $limit; ?>';
}, 3500);
</script>
<?php else: ?>
数据迁移完成
<?php endif;?>
