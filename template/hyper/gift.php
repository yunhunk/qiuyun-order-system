<?php
if (!defined('IN_CRONLITE')) {
    exit();
}

$title = '每日抽奖';
include_once TEMPLATE_ROOT . 'hyper/inc/header.php';
if ($conf['gift_open'] == 0) {
    exit('<script type="text/javascript">alert("抽奖功能暂未开启！");window.location.href="./";</script>');
}

?>
    <div class="container-fluid" id="pjax-container">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-none d-md-block">
                    <h4 class="page-title">每日抽奖</h4>
                </div>
                <?php if ($is_mobile): ?>
                <?php include_once TEMPLATE_ROOT . 'hyper/inc/mobileNav.php';?>
                <?php endif;?>
            </div>
            <div class="col-12 col-lg-7">
                <div class="card">
                    <img class="card-img-top" src="<?php echo $cdnserver ?>assets/template/hyper/img/gift.jpg" style="max-height: 300px;">
                    <div class="card-body" style="background: #681aa6;color: white;">
                        <div class="card text-white bg-primary overflow-hidden" id="gift" style="display: none;">
                            <div class="card-body">
                                <div class="toll-free-box text-center">
                                    <h4> <i class="fas fa-gift"></i> <span id="roll"></span></h4>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-success btn-rounded btn-block" id="start" style="display: block;">开始抽奖</button>
                        <button type="button" class="btn btn-danger btn-rounded btn-block" id="stop" style="display: none;">停止</button>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-5">
                <div class="card text-white bg-success giftlist" style="display: none;">
                    <div class="card-body text-center">
                        <h3 class="mt-n1 mb-3">中奖记录</h3>
                        <marquee height="330">
                              <ul class="list-group text-center" id="pst_1"></ul>
                          </marquee>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            var homepage=true;
            var hashsalt=<?php echo $addsalt_js ?>;
        </script>
    </div>
<?php include_once TEMPLATE_ROOT . 'hyper/inc/footer.php';?>
    </body>
</html>