<?php

include_once TEMPLATE_ROOT . 'store/inc/function.php';
$mod = isset($_GET['mod']) ? input('get.mod') : 'index';
if ($mod == 'query') {
    $style = 'style="color:#999;line-height: unset;font-weight: inherit;"';
}
?>
<div class="fui-navbar" style="max-width: 650px;z-index: 100;">
    <a href="?" class="nav-item <?php echo store_checkActive('index') ?>"> <span class="icon icon-home "></span> <span class="label" <?=$style?>>首页</span>
    </a>
    <a href="?mod=query" class="nav-item <?php echo store_checkActive('query') ?>"> <span class="icon icon-dingdan1"></span> <span class="label" <?=$style?>>订单</span> </a>
    <a href="?mod=cart" class="nav-item <?php echo store_checkActive('cart') ?>" <?php if ($conf['shoppingcart'] == 0) {?>style="display:none"<?php }?>> <span class="icon icon-cart2"></span> <span class="label" <?=$style?>>购物车</span> </a>
    <a href="?mod=kf" class="nav-item <?php echo store_checkActive('kf') ?>"> <span class=" icon icon-service1"></span> <span class="label" <?=$style?>>客服</span>
    </a>
    <a href="<?php echo $weburl; ?>user/" class="nav-item <?php echo store_checkActive('home') ?>"> <span class="icon icon-person2"></span> <span class="label" <?=$style?>>会员中心</span> </a>
</div>