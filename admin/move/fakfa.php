<?php

include '../../includes/common.php';
$title = '站点数据迁移小工具V1.1.1';
checkLogin();

$dbqz   = isset($_GET['dbqz']) && $_GET['dbqz'] ? trim($_GET['dbqz']) : 'shua';
$offset = isset($_GET['offset']) && $_GET['offset'] ? intval($_GET['offset']) : 0;
$limit  = isset($_GET['limit']) && $_GET['limit'] ? intval($_GET['limit']) : 3000;
$reset  = isset($_GET['reset']) && $_GET['reset'] ? intval($_GET['reset']) : 0;

$DB->setDbqzList();

$count = intval($DB->count("SELECT count(*) FROM `{$dbqz}_faka`"));
$ok    = 0;
if ($count > 0) {
    $rs = $DB->select("SELECT `sid`,`kid`,`km`,'pw' FROM `{$dbqz}_faka` where 1 limit {$offset},{$limit}");
    if ($rs) {
        foreach ($rs as $key => $value) {

            $value['zid'] = $value['sid'] ? $value['sid'] : 0;

            $kid = $value['kid'];
            unset($value['kid'], $value['sid']);
            if ($reset == 1) {
                $sql = \core\Db::name('faka')->insert($value);
            } else {
                $sql = \core\Db::name('faka')->where(['kid' => $kid])->update($value);
            }

            if ($sql !== false) {
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
    window.location.href='?reset=<?php echo $reset; ?>&dbqz=<?php echo $dbqz; ?>&offset=<?php echo ($offset + $limit); ?>&limit=<?php echo $limit; ?>';
}, 3500);
</script>
<?php else: ?>
数据迁移完成
<?php endif;?>
