<?php
if (!defined('IN_CRONLITE')) {
    exit();
}

if ($_GET['buyok'] == 1) {include_once TEMPLATE_ROOT . 'hyper/query.php';exit;}
include_once TEMPLATE_ROOT . 'hyper/inc/header.php';
// 经典模式
$rs           = $DB->query("SELECT * FROM pre_class WHERE active=1 order by sort asc");
$select       = '<option value="0">请选择分类</option>';
$select_count = 0;
while ($res = $rs->fetch()) {
    if ($is_fenzhan && in_array($res['cid'], $classhide)) {
        continue;
    }

    $select_count++;
    $select .= '<option value="' . $res['cid'] . '">' . $res['name'] . '</option>';
}
if ($select_count == 0) {
    $classhide = true;
}

?>
    <div class="container-fluid" id="pjax-container">
        <div class="row">
            <div class="col-12">
                <?php if (!$is_mobile): ?>
                <div class="page-title-box d-none d-md-block">
                    <div class="page-title-right">
                        <div class="app-search py-0 d-md-block">
                            <div class="input-group">
                                <span class="fa fa-search"></span>
                                <input type="text" class="form-control" placeholder="输入商品关键词" id="searchkw" onkeydown="if(event.keyCode==13){$('#doSearch').click()}">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" id="doSearch" type="submit">搜索</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h4 class="page-title">商品列表</h4>
                </div>
                <?php else: ?>
                <?php include_once TEMPLATE_ROOT . 'hyper/inc/mobileNav.php';?>
                <?php endif;?>
            </div>
            <?php if ($is_mobile && $conf['ui_shop'] == 0): ?>
            <div class="col-12 px-0 mt-n1">
                <?php if (!empty($conf['appurl'])): ?>
               <div class="alert alert-primary text-center mb-1 position-relative" id="AppDown"><img src="<?php echo $cdnserver ?>assets/template/hyper/img/hot.png" class="position-absolute" style="max-height: 100%;left: 0;top: 0;border-top-left-radius:.25rem; "/> <a href="<?php echo $conf['appurl']; ?>" target="_blank"><b>【APP】点击这里下载APP，下单更方便！</b></a></div> <!---->
                <?php endif;?>
                <div class="card">
                    <div class="card-body">
                        <div class="tab-content b-0 mb-0">
                            <!-- 商城 -->
                            <div class="tab-pane mx-n2 active" id="shop">
                                <div class="input-group mb-2" id="display_selectclass" <?php if ($classhide) {?> style="display:none;"<?php }?>>
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">选择分类</span>
                                    </div>
                                    <select name="cid" id="cid" class="form-control"><?php echo $select ?></select>
                                    <div class="input-group-append">
                                        <span class="input-group-text onclick" title="搜索商品" id="showSearchBar"><i class="fa fa-search"></i></span>
                                    </div>
                                </div>
                                <div class="input-group mb-2" id="display_selectclass_sub" style="display:none;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">二级分类</span>
                                    </div>
                                    <select name="sub_cid" id="sub_cid" class="form-control"></select>
                                </div>
                                <div class="input-group mb-2" id="display_searchBar" style="display:none;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">搜索商品</span>
                                    </div>
                                    <input type="text" id="searchkw" class="form-control" placeholder="搜索商品" onkeydown="if(event.keyCode==13){$('#doSearch').click()}"/>
                                    <div class="input-group-append">
                                        <span class="input-group-text onclick" title="搜索" id="doSearch"><i class="fa fa-search"></i></span>
                                        <span class="input-group-text onclick" title="关闭" id="closeSearchBar"><i class="fa fa-times"></i></span>
                                    </div>
                                </div>
                                <div class="input-group mb-2">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">选择商品</span>
                                    </div>
                                    <select name="tid" id="tid" class="form-control" onchange="getPoint();">
                                        <option value="0">请选择商品</option>
                                    </select>
                                </div>
                                <div class="input-group mb-2" id="display_price" style="display: none;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">商品价格</span>
                                    </div>
                                    <input type="text" name="need" style=" center;color:#4169E1; font-weight:bold" id="need" class="form-control" disabled/>
                                </div>
                                <div class="input-group mb-2" id="display_left" style="display: none;">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">库存数量</span>
                                    </div>
                                    <input type="text" name="leftcount" id="leftcount" class="form-control" disabled/>
                                </div>
                                <div class="form-group mb-2" id="display_num" style="display: none;">
                                    <div class="input-group bootstrap-touchspin bootstrap-touchspin-injected">
                                        <span class="input-group-prepend">
                                            <span class="input-group-text">下单数量</span>
                                        </span>
                                        <span class="input-group-btn input-group-prepend">
                                            <button id="num_min" class="btn btn-primary" type="button">━</button>
                                        </span>
                                        <input id="num" name="num" class="form-control text-center" type="number" min="1" value="1"/>
                                        <span class="input-group-btn input-group-append">
                                            <button id="num_add" class="btn btn-primary" type="button">✚</button>
                                        </span>
                                        <span class="input-group-btn input-group-append">
                                            <a href="#numModel" data-toggle="modal" class="btn btn-warning text-white"><i class="fa fa-question-circle"></i></a>
                                        </span>
                                    </div>
                                </div>
                                <div id="inputsname"></div>
                                <div id="alert_frame" class="alert alert-success" style="display:none;font-weight: bold;"></div>
                                <?php if ($conf['shoppingcart'] == 1) {?>
                                <div class="btn-group btn-group-justified form-group" style="width:100%">
                                    <button class="btn btn-success" id="submit_cart_shop" style="width:30%">加入购物车</button>
                                    <button id="submit_buy" class="btn btn-primary" style="width:70%">立即购买</button>
                                </div>
                                <?php } else {?>
                                <div class="form-group">
                                    <input type="submit" id="submit_buy" class="btn btn-primary btn-block" value="立即购买">
                                </div>
                                <?php }?>
                                <div class="card-body border-t alert_cart" id="alert_cart" style="display:none"><i class="fa fa-shopping-cart"></i>&nbsp;当前购物车已添加<b id="cart_count">0</b>个商品<a class="btn btn-sm btn-danger" href="javascript:openCart()" style="float:right">购物车列表</a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="row mt-md-0" id="goodType" <?php if (isset($_GET['cid'])) {?>style="display: none"<?php }?>>
                <?php if ($is_mobile): ?>
                <div class="col-12">
                    <div class="app-search d-block">
                        <div class="input-group">
                            <span class="fa fa-search"></span>
                            <input type="text" class="form-control" placeholder="输入商品关键词" id="searchkw" onkeydown="if(event.keyCode==13){$('#doSearch').click()};">
                            <div class="input-group-append">
                                <button class="btn btn-primary" id="doSearch" type="submit">搜索</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif;?>
                <?php
$rs = $DB->query("SELECT * FROM pre_class WHERE active=1 AND (upcid IS NULL OR upcid=0) order by sort asc");
while ($row = $rs->fetch()) {
    if ($is_fenzhan && in_array($row['cid'], $classhide)) {
        continue;
    }
    $productimg = $row["shopimg"];
    if (!empty($row["shopimg"]) && strpos($productimg, 'http') === false) {
        $productimg = $cdnserver . 'assets/img/Product/default.png';
    }

    if (empty($productimg)) {
        $productimg = $cdnserver . 'assets/img/Product/default.png';
    }
    //宫格
    ?>
                <div class="col-6 col-lg-4 px-1 px-md-2 cid<?php echo $row["cid"] ?>">
                    <a href="javascript:void(0);" class="goodTypeChange" data-id="<?php echo $row["cid"] ?>">
                        <div class="card mb-2 mb-md-3 shadow">
                            <img class="card-img-top" src="<?php echo $productimg ?>" width="100%" alt="商品图片" style="min-height: 80px;">
                            <div class="card-img-overlay d-none d-md-inline-block" style="right: unset;bottom: unset;">
                                <div class="badge badge-info text-white p-1"><?php echo $row["name"] ?></div>
                            </div>
                            <div class="card-body text-center py-2">
                                <h5 class="card-title d-md-none"><?php echo $row["name"] ?></h5>
                                <button class="btn btn-outline-primary btn-rounded btn-block">点击进入 <i class="fa fa-chevron-circle-right"></i></button>
                            </div>
                        </div>
                    </a>
                </div>
                <?php
}?>
                <div class="position-fixed wxd-b-menu">
                    <div class="mt-3" id="top" style="display: none;">
                        <button class="btn btn-primary shadow wxd-b-but" style="padding:.55rem 1rem;">
                            <i class="fa fa-angle-up" style="font-size: 30px;"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body p-1" id="goodTypeContent" <?php if (!isset($_GET['cid'])) {?>style="display: none"<?php }?>>
                <div class="row">
                    <div class="col-12 col-lg-6 text-center d-none d-md-inline-block mb-2">
                        <img src="<?php echo $cdnserver ?>assets/img/Product/default.png" class="rounded-lg border border-light shadow-sm" data-name="thumb" width="80%" >
                    </div>
                    <div class="col-12 col-lg-6 mb-2">
                        <input type="hidden" name="cid" id="cid" value="0"/>
                        <div class="input-group mb-2" id="display_selectclass_sub" style="display:none;">
                            <div class="input-group-prepend">
                                <span class="input-group-text">二级分类</span>
                            </div>
                            <select name="sub_cid" id="sub_cid" class="form-control"></select>
                        </div>
                        <div class="input-group mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text">选择商品</span>
                            </div>
                            <select name="tid" id="tid" class="form-control" onchange="getPoint();">
                                <option value="0">请选择商品</option>
                            </select>
                        </div>
                        <div class="input-group mb-2" id="display_price" style="display: none;">
                            <div class="input-group-prepend">
                                <span class="input-group-text">商品价格</span>
                            </div>
                            <input type="text" name="need" id="need" class="form-control" disabled/>
                        </div>
                        <div class="input-group mb-2" id="display_left" style="display: none;">
                            <div class="input-group-prepend">
                                <span class="input-group-text">库存数量</span>
                            </div>
                            <input type="text" name="leftcount" id="leftcount" class="form-control" disabled/>
                        </div>
                        <div class="form-group mb-2" id="display_num" style="display: none;">
                            <div class="input-group bootstrap-touchspin bootstrap-touchspin-injected">
                                <span class="input-group-prepend">
                                    <span class="input-group-text">下单数量</span>
                                </span>
                                <span class="input-group-btn input-group-prepend">
                                    <button id="num_min" class="btn btn-primary" type="button">━</button>
                                </span>
                                <input id="num" name="num" class="form-control text-center" type="number" min="1" value="1"/>
                                <span class="input-group-btn input-group-append">
                                    <button id="num_add" class="btn btn-primary" type="button">✚</button>
                                </span>
                                <span class="input-group-btn input-group-append">
                                    <a href="#numModel" data-toggle="modal" class="btn btn-warning text-white"><i class="fa fa-question-circle"></i></a>
                                </span>
                            </div>
                        </div>
                        <div id="inputsname"></div>
                        <?php if ($is_mobile): ?>
                        <div id="alert_frame" class="alert alert-success" style="display:none;font-weight: bold;"></div>
                        <?php endif;?>
                        <?php if ($conf['shoppingcart'] == 1) {?>
                        <div class="btn-group btn-group-justified form-group" style="width:100%">
                            <button class="btn btn-success" id="submit_cart_shop" style="width:30%">加入购物车</button>
                            <button id="submit_buy" class="btn btn-primary" style="width:70%">立即购买</button>
                        </div>
                        <?php } else {?>
                        <div class="form-group">
                            <input type="submit" id="submit_buy" class="btn btn-primary btn-block" value="立即购买">
                        </div>
                        <?php }?>
                    </div>
                    <div class="col-12">
                        <?php if (!$is_mobile): ?>
                        <div id="alert_frame" class="alert alert-success" style="display:none;font-weight: bold;"></div>
                        <?php endif;?>
                    </div>
                    <?php if ($conf['shop_batch'] == 2 || $conf['shop_batch'] == 1 && $isLogin2 == 1): ?>
                        <div class="col-12">
                            <?php if ($conf['shop_batch'] == 1 && $isLogin2 == 1 && file_exists(TEMPLATE_ROOT . 'default/batch.inc.php')) {include_once TEMPLATE_ROOT . 'default/batch.inc.php';}?>
                            <?php if ($conf['shop_batch'] == 2 && file_exists(TEMPLATE_ROOT . 'default/batch.inc.php')) {include_once TEMPLATE_ROOT . 'default/batch.inc.php';}?>
                        </div>
                    <?php endif;?>
                    <div class="position-fixed wxd-b-menu">
                        <div class="mt-2" id="alert_cart" class="alert_cart" style="display:none">
                            <button class="btn btn-info shadow rounded-circle" onclick="openCart()" title="购物车列表" style="padding: 0.45rem 0.5rem;">
                                <i class="fa fa-shopping-cart fa-2x"></i><div class="nav-counter nav-counter-big" id="cart_count"></div>
                            </button>
                        </div>
                        <div class="mt-2">
                            <button class="btn btn-danger shadow rounded-circle backType" title="返回重选分类">
                                <i class="fa fa-times fa-2x"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif;?>
        </div>
        <div class="modal fade" id="numModel">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="text-center my-3"><h3>数量说明</h3></div><img src="<?php echo $cdnserver ?>assets/template/hyper/img/numdesc.png" width="100%"/>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">关闭</button>
                    </div>
                </div>
            </div>
        </div>
        <?php hook('footer_before');?>
        <script type="text/javascript">
            var isModal=<?php echo empty($conf['modal']) ? 'false' : 'true'; ?>;
            var _modalType=<?php echo $conf['modal_type'] > 0 ? '1' : '0'; ?>;
            var homepage=true;
            var hashsalt=<?php echo $addsalt_js ?>;
        </script>
<script>
var serverPath = 'string' === typeof serverPath ? serverPath : './';
<?php if ($conf['shoppingcart'] == 1) {?>
$.ajax({
    type : "GET",
    url : serverPath + "ajax.php?act=cart_info",
    dataType : 'json',
    async: true,
    success : function(data) {
        if(data.count != null && data.count>0){
            $('#cart_count').html(data.count);
            $('#alert_cart').show();
        }
    }
});
<?php }?>
</script>
    </div>

    <?php include_once TEMPLATE_ROOT . 'hyper/inc/footer.php';?>
   </body>
</html>