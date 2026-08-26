<?php
if (!defined('IN_CRONLITE')) {
    exit();
}

$title = '文章教程';
include_once TEMPLATE_ROOT . 'hyper/inc/header.php';

$kw = !empty($_GET['kw']) ? input('get.kw', 1) : null;
if ($kw) {
    $sql  = " title LIKE '%$kw%' OR content LIKE '%$kw%'";
    $link = "&kw=" . $kw;
} else {
    $sql = " 1";
}
$msgcount = $DB->count("SELECT count(*) FROM pre_message WHERE active=1");
$pagesize = 10;
$pages    = ceil($msgcount / $pagesize);
$page     = isset($_GET['page']) ? intval(input('get.page', 1)) : 1;
$offset   = $pagesize * ($page - 1);
$rs       = $DB->query("SELECT id,title,content,addtime FROM pre_message WHERE{$sql} AND active=1 ORDER BY `sort` ASC,id DESC LIMIT $offset,$pagesize");
$msgrow   = array();
if ($rs) {
    $msgrow = $DB->fetchAll($rs);
}

?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-none d-md-block">
                    <div class="page-title-right">
                        <?php if (!$is_mobile): ?>
                        <div class="app-search py-0 d-md-block">
                            <form action="./?mod=articlelist" method="get" data-pjax>
                                <div class="input-group">
                                    <span class="fas fa-search"></span>
                                    <input type="text" class="form-control" placeholder="输入文章关键词" name="kw">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">搜索</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <?php endif;?>
                    </div>
                    <h4 class="page-title">文章教程</h4>
                </div>
                <?php if ($is_mobile): ?>
                <?php include_once TEMPLATE_ROOT . 'hyper/inc/mobileNav.php';?>
                <?php endif;?>
            </div>
            <?php if ($is_mobile): ?>
            <div class="col-12">
                <div class="app-search d-block">
                    <form action="./?mod=articlelist" method="get" data-pjax>
                        <div class="input-group">
                            <span class="fas fa-search"></span>
                            <input type="text" class="form-control" placeholder="输入文章关键词" name="kw">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">搜索</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif;?>
            <div class="col-12 px-1">
                <?php if ($_GET['kw']): ?>
                <p class="text-center">包含关键词 <span class="badge badge-primary"><?php echo htmlspecialchars($_GET['kw']); ?></span> 结果如下！</p>
                <?php endif;?>
                <div class="list-group">
                    <?php
foreach ($msgrow as $row) {
    $content = strip_tags($row['content']);
    if (mb_strlen($content) > 80) {
        $content = mb_substr($content, 0, 80, 'utf-8') . '......';
    }

    echo '<a href="' . article_url($row['id']) . '" class="list-group-item list-group-item-action p-1">
                        <div class="media" style="display:-webkit-box!important;">
                            <div class="media-body">
                                <div class="row">
                                    <div class="col-12 col-lg-10">
                                        <h4 class="mb-1 text-truncate">' . strip_tags($row['title']) . '</h4>
                                    </div>
                                    <div class="col-2 d-none d-lg-block text-right">
                                        <span class="badge badge-light">' . substr($row['addtime'], 0, -8) . '</span>
                                    </div>
                                </div>
                                <p class="mb-1 text-muted text-wrap">' . $content . '</p>
                            </div>
                        </div>
                    </a>';
}
if ($msgcount == 0) {
    echo '<div class="text-center">暂无文章！</div>';
}
?>
                </div>
                <div class="mt-2">
                <?php if ($msgcount > $pagesize) {
    if ($page > 1) {
        echo '<a href="' . article_url(0, 'page=' . ($page - 1) . $link) . '" class="btn btn-primary btn-sm btn-rounded">上一页</a>';
    }
    if ($page < $pages) {
        echo '<a href="' . article_url(0, 'page=' . ($page + 1) . $link) . '" class="btn btn-primary btn-sm btn-rounded" style="float:right">下一页</a>';
    }
}?>
                </div>
            </div>
        </div>
    </div>
<?php include_once TEMPLATE_ROOT . 'hyper/inc/footer.php';?>
    </body>
</html>