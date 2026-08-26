<?php
if (!defined('IN_CRONLITE')) {
    exit();
}

$title = '成为代理';
include_once TEMPLATE_ROOT . 'hyper/inc/header.php';
?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-none d-md-block">
                    <h4 class="page-title">成为代理</h4>
                </div>
                <?php if ($is_mobile): ?>
                <?php include_once TEMPLATE_ROOT . 'hyper/inc/mobileNav.php';?>
                <?php endif;?>
            </div>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12">
                        <div class="text-center">
                            <img src="<?php echo $cdnserver ?>assets/template/hyper/img/headsite.png" width="280px;" alt="File not found Image">
                            <h3 class="mt-4">加入我们，自己也是站长</h3>
                            <p class="text-muted">商品齐全、货源稳定、价格超低、全职售后客服，售后保障！</p>
                            <div class="row mt-5">
                                <div class="col-md-4">
                                    <div class="text-center mt-3 pl-1 pr-1">
                                        <i class="fas fa-mobile bg-primary maintenance-icon text-white mb-2"></i>
                                        <h5 class="text-uppercase">光凭一步手机也能赚钱？</h5>
                                        <p class="text-muted">对的，每天无聊的时候发发广告，拉点下级代理，躺着也能轻松日赚几百+</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center mt-3 pl-1 pr-1">
                                        <i class="fas fa-tachometer-alt bg-primary maintenance-icon text-white mb-2"></i>
                                        <h5 class="text-uppercase">网站管理起来麻烦吗？</h5>
                                        <p class="text-muted">有专业的技术人员负责服务器的维护；专人上架、整理商品；专门的售后客服；零技术门槛。</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center mt-3 pl-1 pr-1">
                                        <i class="fas fa-bolt bg-primary maintenance-icon text-white mb-2"></i>
                                        <h5 class="text-uppercase">为何要选择我们？</h5>
                                        <p class="text-muted">拥有丰富优质商品，实时掌握市场的动态，加入我们，只要你坚持，你不用担心不赚钱，我们即使不敢保证你月入上万！但是在网上赚点零花钱也是轻轻松松！</p>
                                    </div>
                                </div>
                                <div class="col-12 text-center mt-3 d-none d-md-block">
                                    <a href="<?php echo $cdnserver ?>user/regsite.php" class="btn btn-danger btn-rounded btn-block d-inline-block" target="_blank" style="max-width: 38%;"><h3>注册分站</h3></a>
                                    <a href="#siteModel" data-toggle="modal" class="btn btn-success btn-rounded d-inline-block ml-1 px-3"><h3><i class="fas fa-info-circle"></i></h3></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white position-fixed py-2 d-md-none text-center" style="bottom: 0;left: 0;right: 0;z-index: 10;">
                    <a href="<?php echo $cdnserver ?>user/regsite.php" class="btn btn-danger btn-rounded btn-block d-inline-block" target="_blank" style="max-width: 50%;">注册分站</a>
                    <a href="#siteModel" data-toggle="modal" class="btn btn-success btn-rounded ml-1"><i class="fas fa-info-circle"></i></a>
                </div>
            </div>
        </div>
        <div class="modal fade" id="siteModel">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="text-center my-3">
                            <h3>版本介绍</h3>
                        </div>
                        <div class="table-responsive-sm">
                            <table class="table table-hover table-centered mb-0">
                                <thead>
                                    <tr>
                                        <th>功能</th>
                                        <th>普及版</th>
                                        <th>专业版</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>专属平台</td>
                                        <td><span class="badge badge-success-lighten badge-pill"><i class="fas fa-check"></i></span></td>
                                        <td><span class="badge badge-success-lighten badge-pill"><i class="fas fa-check"></i></span></td>
                                    </tr>
                                    <tr>
                                        <td>专属网站域名</td>
                                        <td><span class="badge badge-success-lighten badge-pill"><i class="fas fa-check"></i></span></td>
                                        <td><span class="badge badge-success-lighten badge-pill"><i class="fas fa-check"></i></span></td>
                                    </tr>
                                    <tr>
                                        <td>三网在线支付接口</td>
                                        <td><span class="badge badge-success-lighten badge-pill"><i class="fas fa-check"></i></span></td>
                                        <td><span class="badge badge-success-lighten badge-pill"><i class="fas fa-check"></i></span></td>
                                    </tr>
                                    <tr>
                                        <td>设置商品价格</td>
                                        <td><span class="badge badge-success-lighten badge-pill"><i class="fas fa-check"></i></span></td>
                                        <td><span class="badge badge-success-lighten badge-pill"><i class="fas fa-check"></i></span></td>
                                    </tr>
                                    <tr>
                                        <td>设置下级网站商品价格</td>
                                        <td><span class="badge badge-danger-lighten badge-pill"><i class="fas fa-times text-danger"></i></span></td>
                                        <td><span class="badge badge-success-lighten badge-pill"><i class="fas fa-check"></i></span></td>
                                    </tr>
                                    <tr>
                                        <td>赚取用户提成</td>
                                        <td><span class="badge badge-success-lighten badge-pill"><i class="fas fa-check"></i></span></td>
                                        <td><span class="badge badge-success-lighten badge-pill"><i class="fas fa-check"></i></span></td>
                                    </tr>
                                    <tr>
                                        <td>赚取下级网站提成</td>
                                        <td><span class="badge badge-danger-lighten badge-pill"><i class="fas fa-times text-danger"></i></span></td>
                                        <td><span class="badge badge-success-lighten badge-pill"><i class="fas fa-check"></i></span></td>
                                    </tr>
                                    <tr>
                                        <td>搭建下级网站</td>
                                        <td><span class="badge badge-danger-lighten badge-pill"><i class="fas fa-times text-danger"></i></span></td>
                                        <td><span class="badge badge-success-lighten badge-pill"><i class="fas fa-check"></i></span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">关闭</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-3 d-md-none"></div>
<?php include_once TEMPLATE_ROOT . 'hyper/inc/footer.php';?>


    </body>
</html>