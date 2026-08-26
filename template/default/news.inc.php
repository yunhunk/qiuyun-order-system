<?php
if (!defined('IN_CRONLITE')) {
    exit();
}

if (!isset($msgrow)) {
    $msgcount = $DB->count("SELECT count(*) FROM pre_message WHERE active=1");
    $msgrow   = [];
    $rs       = $DB->query("SELECT * FROM pre_message WHERE active=1 ORDER BY sort asc");
    if ($rs) {
        $msgrow = $DB->fetchAll($rs);
    }
}

if ($conf['message_static'] == 1) {
    $_indexUrl = '/article/index.html';
} else {
    $_indexUrl = '/article/index.php';
}
?>
<div id="newsTab" class="panel-body text-center block" style="padding: 0">
    <ul class="nav nav-tabs">
        <li data-target="newsTab" style="width: 50%;" class="active"><a data-toggle="tab" href="#newsTab-shopArticle" aria-expanded="true" style="color: red">商品通知区</a></li>
        <li data-target="newsTab" style="width: 50%;"><a data-toggle="tab" href="#newsTab-ggArticle" aria-expanded="false" style="color: blue">平台公告栏</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade in active" role="tabpanel" id="newsTab-shopArticle">
            <table class="table table-hover table-bordered">
            <tbody id="msglist">
            <?php
$i = 0;
if ($conf['message_page'] < 1) {
    $conf['message_page'] = '10';
}

foreach ($msgrow as $row) {
    if ($row['cid'] == 2 && $i < $conf['message_page']) {
        $_url = '';
        if ($conf['message_static'] == 1) {
            $_url = $weburl . 'article/' . $row['id'] . '.html';
        } else {
            $_url = $weburl . 'article/?id=' . $row['id'] . '.html';
        }
        echo '<tr class="widget animation-fadeInQuick onclick" onclick="window.location.href=\'' . $_url . '\'" style="font-size:12px;padding: 2px 3px;text-align:left"><td style="font-size:12px;"><a href="' . $_url . '"><span class="btn btn-info btn-xs pull-left">查看</a>&nbsp;' . $row['title'] . '</a></td></tr>';
        $i++;
    }
}
if ($i == 0) {
    echo '<tr><td class="text-center"><font color="grey">商品通知文章空空如也</font></td></tr>';
}
?>
             </tbody>
             </table>
            <a href="<?php echo $_indexUrl ?>" class="btn btn-primary btn-block">查看全部商品通知</a>
        </div>
        <div class="tab-pane fade" role="tabpanel" id="newsTab-ggArticle">
            <table class="table table-hover table-bordered">
            <tbody id="msglist">
            <?php
$i = 0;
foreach ($msgrow as $row) {
    if ($row['cid'] == 1 && $i < $conf['message_page']) {
        $_url = '';
        if ($conf['message_static'] == 1) {
            $_url = '' . $weburl . 'article/' . $row['id'] . '.html';
        } else {
            $_url = '' . $weburl . 'article/?id=' . $row['id'] . '.html';
        }
        echo '<tr class="widget animation-fadeInQuick onclick" onclick="window.location.href=\'' . $_url . '\'" style="font-size:12px;padding: 2px 3px;text-align:left"><td style="font-size:12px;"><a href="' . $_url . '"><span class="btn btn-info btn-xs pull-left">查看</a>&nbsp;' . $row['title'] . '</a></td></tr>';
        $i++;
    }
}
if ($i == 0) {
    echo '<tr><td class="text-center"><font color="grey">平台公告文章空空如也</font></td></tr>';
}
?>
             </tbody>
             </table>
             <a href="<?php echo $_indexUrl ?>" class="btn btn-primary btn-block">查看全部公告通知</a>
        </div>
   </div>
</div>