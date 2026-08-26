<?php
if (!defined('IN_CRONLITE')) {
    exit();
}

$title = '更多菜单';
include_once TEMPLATE_ROOT . 'hyper/inc/header.php';
if (!$is_mobile) {
    exit('<script type="text/javascript">window.location.href="./";</script>');
}

?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-none d-md-block">
                    <h4 class="page-title">更多功能</h4>
                </div>
                <?php if ($is_mobile): ?>
                <?php include_once TEMPLATE_ROOT . 'hyper/inc/mobileNav.php';?>
                <style type="text/css">
                    .toll-free-box i {
                        bottom: -10px;
                    }
                </style>
                <?php endif;?>
            </div>
            <div class="row">
                <?php if ($conf['fz_regkg'] == 0): ?>
                <div class="col-6 col-md-3 px-2 mb-n2">
                    <a href="./?mod=site" data-pjax class="card btn-outline-danger overflow-hidden shadow-sm px-3 py-2">
                        <div class="card-body">
                            <div class="toll-free-box text-center">
                                <h3> <i class="fas fa-users"></i> 成为代理</h3>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endif;?>
                <?php if ($conf['gift_open'] == 1): ?>
                <div class="col-6 col-md-3 px-2 mb-n2">
                    <a href="?mod=gift" class="card btn-outline-success overflow-hidden shadow-sm px-3 py-2">
                        <div class="card-body">
                            <div class="toll-free-box text-center">
                                <h3> <i class="fas fa-gift"></i> 每日抽奖</h3>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endif;?>
                <?php if ($conf['invite_open']): ?>
                <div class="col-6 col-md-3 px-2 mb-n2">
                    <a href="?mod=invite" class="card btn-outline-danger overflow-hidden shadow-sm px-3 py-2">
                        <div class="card-body">
                            <div class="toll-free-box text-center">
                                <h3> <i class="fas fa-share-alt"></i> 邀请有礼</h3>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endif;?>
                <div class="col-6 col-md-3 px-2 mb-n2">
                    <a href="<?php getArticleLink()?>" class="card btn-outline-info overflow-hidden shadow-sm px-3 py-2">
                        <div class="card-body">
                            <div class="toll-free-box text-center">
                                <h3> <i class="fas fa-file-alt"></i> 文章教程</h3>
                            </div>
                        </div>
                    </a>
                </div>
               <!-- div class="col-6 col-md-3 px-2 mb-n2">
                    <a href="?mod=faq" data-pjax class="card btn-outline-warning overflow-hidden shadow-sm px-4 py-2">
                        <div class="card-body">
                            <div class="toll-free-box text-center">
                                <h3> <i class="mdi mdi-comment-question"></i> 常见问题</h3>
                            </div>
                        </div>
                    </a>
                </div-->
            </div>
        </div>
    </div>
<?php include_once TEMPLATE_ROOT . 'hyper/inc/footer.php';?>
    </body>
</html>