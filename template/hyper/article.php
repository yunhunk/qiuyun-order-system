<?php
if (!defined('IN_CRONLITE')) {
    exit();
}

$id  = isset($_GET['id']) ? intval(input('get.id', 1)) : sysmsg('文章ID不存在');
$row = $DB->get_row("SELECT * from pre_message where id='$id' and active=1 limit 1");
if (!$row) {
    sysmsg('当前文章不存在！');
}

$downResult = $DB->get_row("SELECT * FROM pre_message WHERE id<'$id' AND active=1 ORDER BY id DESC LIMIT 1");
$upResult   = $DB->get_row("SELECT * FROM pre_message WHERE id>'$id' AND active=1 ORDER BY id DESC LIMIT 1");
$DB->exec("UPDATE `pre_message` SET `count`=`count`+1 WHERE id='$id'");

$title = $row['title'];
include_once TEMPLATE_ROOT . 'hyper/inc/header.php';
?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-none d-md-block">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="./">首页</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo article_url() ?>">文章列表</a></li>
                            <li class="breadcrumb-item active">当前</li>
                        </ol>
                    </div>
                    <h4 class="page-title">文章详情</h4>
                </div>
                <?php if ($is_mobile): ?>
                <?php include_once TEMPLATE_ROOT . 'hyper/inc/mobileNav.php';?>
                <?php endif;?>
            </div>
            <div class="col-12 p-1">
                <div class="card">
                    <div class="card-body">
                        <h3 class="text-center">
                            <?php echo $row['title'] ?>
                        </h3>
                        <div class="text-center text-muted mb-3"><span class="badge badge-light mr-1"><i class="fas fa-clock"></i> <?php echo substr($row['addtime'], 0, -8); ?></span><span class="badge badge-light"><i class="fas fa-eye"></i> <?php echo $row['count']; ?></span></div>
                        <div class="text-monospace text-break mb-2" id="content">
                            <?php echo $row['content'] ?>
                        </div>
                    </div>
                    <div class="p-3 rounded border border-light m-3">
                        <div class="row">
                            <div class="col-6">
                                <?php if ($upResult): ?>
                                <a href="./?mod=article&id=<?php echo $upResult['id']; ?>">
                                    <i class="fas fa-chevron-left"></i> <?php if (!$is_mobile): ?> <span class="text-break d-none d-lg-inline"><?php echo $upResult['title']; ?></span><?php else: ?><h4 class="d-inline">上一篇</h4><?php endif;?>
                                </a>
                                <?php else: ?>
                                    <i class="fas fa-chevron-left"></i> <span class="text-muted">没有更多了</span>
                                <?php endif;?>
                            </div>
                            <div class="col-6 text-right">
                                <?php if ($downResult): ?>
                                <a href="./?mod=article&id=<?php echo $downResult['id']; ?>">
                                    <?php if (!$is_mobile): ?> <span class="text-break d-none d-lg-inline"><?php echo $downResult['title']; ?></span><?php else: ?><h4 class="d-inline">下一篇</h4><?php endif;?> <i class="fas fa-chevron-right"></i>
                                </a>
                                <?php else: ?>
                                    <span class="text-muted">没有更多了</span> <i class="fas fa-chevron-right"></i>
                                <?php endif;?>
                            </div>
                        </div>
                    </div>
                    <div class="position-fixed wxd-b-menu d-lg-none">
                        <div class="mt-2">
                            <a href="./?mod=articlelist" class="btn btn-danger shadow rounded-circle" title="返回文章列表">
                                <i class="fas fa-times fa-2x"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php include_once TEMPLATE_ROOT . 'hyper/inc/footer.php';?>
    </body>
</html>