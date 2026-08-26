<?php
if (!defined('IN_CRONLITE')) {
    exit();
}

$classhide = [];
if ($is_fenzhan === true) {
    $classhide = explode(',', $siterow['class']);
}

$defaultImg = parse_image('assets/img/Product/default.png');

if ($isLogin2) {
    $rs = $DB->select("SELECT * FROM `pre_class` where  active=1 AND upcid=0 order by sort asc");
} else {
    $rs = $DB->select("SELECT * FROM `pre_class` where `islogin`=0 AND active=1 AND upcid=0 order by sort asc");
}

if (!is_array($rs)) {
    $rs = [
        [
            'cid'  => 0,
            'name' => '获取分类失败，请联系站长处理！错误信息：' . $DB->error(),
        ],
    ];
}

if (function_exists('hook')) {
    hook('goodsCateAfter', $rs, function ($data = null) {
        global $rs;
        if (is_array($data)) {
            $rs = $data;
        }
    });
}

if ($conf['ui_shop'] > 0) {
//分类图片宫格
    ?>
    <?php
if ($conf['alert']) {
        echo '<div class="form-group">';
        echo $conf['alert'];
        echo '</div>';
    }
    ?>
    <?php if ($conf['search_close'] != 1) {?>
    <div class="form-group" id="display_searchBar">
		<div class="input-group"><div class="input-group-addon">搜索商品</div>
		<input type="text" id="searchkw" class="form-control" placeholder="例如：手机充值" onkeydown="if(event.keyCode==13){$('#doSearch').click()}"/>
		<div class="input-group-addon" title="搜索" id="doSearch">开始搜索</div>
	</div></div>
	<?php }?>
	<div id="goodType" <?php if (isset($_GET['cid'])) {?>style="display: none"<?php }?>>
<?php

    if ($conf['ui_shop'] == 1) {
        ?>
	<div class="row">
<?php

        foreach ($rs as $key => $row) {
            if (in_array($row['cid'], $classhide)) {
                continue;
            }

            if (!empty($row["shopimg"])) {
                $productimg = parse_image($row["shopimg"]);
            } else {
                $productimg = parse_image('assets/img/Product/default.png');
            }

            $count = $DB->count("SELECT count(*) from `pre_tools` where cid=:cid and active=1", [':cid' => $row['cid']]);
            ?>
		<div class="col-lg-4 col-xs-6">
			<a href="javascipt:void(0)" class="widget animation-fadeInQuick goodTypeChange" data-id="<?php echo $row["cid"] ?>">
				<img class="lazy" width="100%" data-original="<?php echo $productimg ?>" src="<?php echo $productimg ?>" onerror="this.src='<?php echo $defaultImg; ?>'">
				<div class="widget-content text-center">
					<strong><?php echo $row["name"] ?></strong>
					<p class="text-muted" style="margin-bottom:10px;text-align:center;">分类<?php echo $count ?>个商品</p>
					<button type="button" data-id="<?php echo $row["cid"] ?>" class="btn btn-rounded btn-info btn-block goodTypeChange">点击进入</button>
				</div>
			</a>
		</div>
<?php
}?>
	</div>
<?php
} elseif ($conf['ui_shop'] == 2) {
        ?>
<style type="text/css">
	.table>tbody>tr>td{vertical-align: baseline;}
</style>
    <?php
if ($conf['alert']) {
            echo '<div class="form-group">';
            echo $conf['alert'];
            echo '</div>';
        }
        ?>
	<table class="table table-striped table-borderless table-vcenter table-hover">
         <tbody>
<?php

        foreach ($rs as $key => $row) {
            if (in_array($row['cid'], $classhide)) {
                continue;
            }

            if (!empty($row["shopimg"])) {
                $productimg = parse_image($row["shopimg"]);
            } else {
                $productimg = parse_image('assets/img/Product/default.png');
            }

            $count = $DB->count("SELECT count(*) from cmy_tools where cid={$row['cid']} and active=1");
            ?>
			<tr class="widget animation-fadeInQuick onclick goodTypeChange" data-id="<?php echo $row["cid"] ?>">
                <td class="text-center" style="width: 100px;">
                    <img data-original="<?php echo $productimg ?>" src="<?php echo $productimg ?>" onerror="this.src='<?php echo $defaultImg; ?>'" width="50" style="height:50px" alt="avatar" class="lazy img-circle img-thumbnail img-thumbnail-avatar">
                </td>
                <td>
                    <h3 class="widget-heading h4"><strong><?php echo $row["name"] ?></strong></h3>
					<span class="text-muted">分类<?php echo $count ?>个商品</span>
                </td>
                <td class="text-right">
                    <button type="button" data-id="<?php echo $row["cid"] ?>" class="btn btn-rounded btn-info goodTypeChange">点击进入</button>
                </td>
            </tr>
<?php
}
        ?>
		   </tbody>
        </table>
<?php
} elseif ($conf['ui_shop'] == 3) {
        ?>
	<?php
if ($conf['alert']) {
            echo '<div class="form-group">';
            echo $conf['alert'];
            echo '</div>';
        }
        ?>
	<div class="row">
	<?php

        foreach ($rs as $key => $row) {
            if (in_array($row['cid'], $classhide)) {
                continue;
            }

            if (!empty($row["shopimg"])) {
                $productimg = parse_image($row["shopimg"]);
            } else {
                $productimg = parse_image('assets/img/Product/default.png');
            }

            ?>
			<div class="col-lg-3 col-xs-4" style="padding:0px">
			<div class="thumbnail" style="margin-bottom:3px;width:95%;margin: 2px auto;">
				<a href="javascipt:void(0)" class="widget animation-fadeInQuick goodTypeChange" data-id="<?php echo $row["cid"] ?>">
				<center style="margin-top:0;">
					<img class="lazy" data-original="<?php echo $productimg ?>" src="<?php echo $productimg ?>" style="height: 88px;" onerror="this.src='<?php echo $defaultImg; ?>'">
					<strong style="white-space:nowrap"><?php echo $row["name"] ?></strong>
					<span type="button" data-id="<?php echo $row["cid"] ?>" class="btn btn-sm btn-info btn-block goodTypeChange">点击进入</span>
				</center>
				</a>
			</div>
			</div>
	<?php
}?>
		</div>
	<?php
}?>
	</div>
	<div id="goodTypeContent" <?php if (!isset($_GET['cid'])) {?>style="display: none"<?php }?>>
		<div style="text-align: center;">
			<h3><span id="className"></span></h3>
			<img src="" id="classImg" width="50%" >
		</div>
		<br>
		<input type="hidden" name="cid" id="cid" value="0"/>
		<div class="form-group" id="display_selectclass_sub" style="display:none;">
			<div class="input-group"><div class="input-group-addon">二级分类</div>
			<select name="sub_cid" id="sub_cid" class="form-control"></select>
		</div></div>
		<div class="form-group" id="display_tool" style="<?php echo $conf['tool_show'] == 0 ? 'display: none;' : null ?>">
			<div class="input-group"><div class="input-group-addon">选择商品</div>
			<select name="tid" id="tid" class="form-control" onchange="getPoint();"><option value="0">请选择商品</option></select>
		</div></div>
		<div class="form-group">
			<div class="input-group"><div class="input-group-addon">商品价格</div>
			<input type="text" name="need" id="need" class="form-control" disabled/>
			<?php if ($conf['cmkj_open'] == 1) {?>
				<a href="/kjshop.php" class="input-group-addon">我要砍价</a>
				<?php }?>
		  </div>
		  <?php if ($conf['tjreg_open'] == 1 && $isLogin2 != 1) {?>
		    <br>
			<div class="form-group text-center">
				<a href="<?php echo $cdnserver ?>user/reg.php"><i class="fa fa-hand-o-right" aria-hidden="true"></i><span style="color:red">点我注册</font></span><font color="#ADADAD">后价格更优惠，</font><span style="color:red">签到领红包</span><span style="color:blue">免费下单</span></a>
		    </div>
		    <?php }?>
		</div>
		<div class="form-group" id="display_left" style="display:none;">
			<div class="input-group"><div class="input-group-addon">库存数量</div>
			<input type="text" name="leftcount" id="leftcount" class="form-control" disabled/>
		</div></div>
		<div class="form-group" id="display_num" style="display:none;">
			<div class="input-group">
			<div class="input-group-addon">下单份数</div>
			<span class="input-group-btn"><input id="num_min" type="button" class="btn btn-info" style="border-radius: 0px;" value="━"></span>
			<input id="num" name="num" class="form-control" type="number" min="1" value="1"/>
			<span class="input-group-btn"><input id="num_add" type="button" class="btn btn-info" style="border-radius: 0px;" value="✚"></span>
			<span class="input-group-btn"><a href="#numAlert" target="_blank" data-toggle="modal" class="btn btn-warning">说明</a></span>
		</div></div>
		<div id="inputsname"></div>
		<div id="alert_frame" class="alert alert-success animated rubberBand" style="display:none;background-color: #b4ffe0;color: #214cb1;">
			<div id="alert_title">----商品说明 必看----</div>
			<div id="alert_content"></div>
		</div>
		<?php if ($conf['shoppingcart'] == 1) {?>
		<div class="btn-group btn-group-justified form-group">
			<a class="btn btn-block btn-success" type="button" id="submit_cart_shop">加入购物车</a>
			<a type="submit" id="submit_buy" class="btn btn-block btn-primary">立即免费领取</a>
		</div>
		<?php } else {?>
		<div class="form-group">
			<input type="submit" id="submit_buy" class="btn btn-primary btn-block" value="立即免费领取">
		</div>
		<?php }?>
		<?php if ($conf['shop_batch'] == 1 && $isLogin2 == 1 && file_exists(TEMPLATE_ROOT . 'default/batch.inc.php')) {include_once TEMPLATE_ROOT . 'default/batch.inc.php';}?>
		<?php if ($conf['shop_batch'] == 2 && file_exists(TEMPLATE_ROOT . 'default/batch.inc.php')) {include_once TEMPLATE_ROOT . 'default/batch.inc.php';}?>
		<div class="panel-body border-t" id="alert_cart" style="display:none;"><i class="fa fa-shopping-cart"></i>&nbsp;当前购物车已添加<b id="cart_count">0</b>个商品<a class="btn btn-xs btn-danger pull-right" href="javascript:openCart()">购物车列表</a></div>
			<br>
		<div class="form-group"><button type="button" class="btn btn-default btn-block btn-sm backType">返回重选分类</button></div>
	</div>
	<ul class="layui-fixbar" id="alert_cart" style="display:none;">
	  <li class="layui-icon" style="background-color:#3e4425db" onclick="openCart()"><i class="fa fa-shopping-cart"></i><div class="nav-counter" id="cart_count"></div></li>
	</ul>
	<br>
	<?php if ($isLogin2 != 1) {?>
	<div class="panel-body border-t"><i class="fa fa-gift"></i> 注册下单更优惠，签到领现金！<a class="btn btn-xs btn-danger pull-right" href="<?php echo $cdnserver ?>user/reg.php" style="background-color: #FFCC33; border-color: #FFCC33;">点击注册</a></div>
	<?php }?>
<?php
} else {
    $select       = '<option value="0">请先选择分类</option>';
    $select_count = 0;
    foreach ($rs as $key => $res) {
        if (in_array($res['cid'], $classhide)) {
            continue;
        }

        $select_count++;
        // 检测分类名称是否包含分隔符"—"
        $disabled = (strpos($res['name'], '—') !== false) ? ' disabled="disabled"' : '';
        $select .= '<option value="' . $res['cid'] . '"' . $disabled . '>' . $res['name'] . '</option>';
    }
    if ($select_count == 0) {
        $hideclass = true;
    }

    ?>          <?php
if ($conf['alert']) {
        echo '<div class="form-group">';
        echo $conf['alert'];
        echo '</div>';
    }
    ?>
            <div id="display_list" style="z-index: 9999">
			    <div id="goodTypeContents">
			    <?php if ($conf['search_close'] != 1) {?>
			    <div class="form-group" id="display_searchBar">
					<div class="input-group"><div class="input-group-addon">搜索商品</div>
					<input type="text" id="searchkw" class="form-control" placeholder="例如：手机充值" onkeydown="if(event.keyCode==13){$('#doSearch').click()}"/>
					<div class="input-group-addon" title="搜索" id="doSearch">开始搜索</div>
				</div></div>
				<?php }?>
				<div class="form-group" id="display_selectclass"<?php if ($hideclass) {?> style="display:none;"<?php }?>>
					<div class="input-group"><div class="input-group-addon">分类列表</div>
					<select name="tid" id="cid" class="form-control"><?php echo $select ?></select>
				</div></div>
				<div class="form-group" id="display_selectclass_sub" style="display:none;">
					<div class="input-group"><div class="input-group-addon">二级分类</div>
					<select name="sub_cid" id="sub_cid" class="form-control"></select>
				</div></div>

				<div class="form-group" id="display_tool" style="<?php echo $conf['tool_show'] == 0 ? 'display: none;' : null ?>">
					<div class="input-group"><div class="input-group-addon">商品列表</div>
					<select name="tid" id="tid" class="form-control" onchange="getPoint();"><option value="0">点我选择商品</option></select>
				</div></div>
		        </div>
		   </div>

			<div class="form-group text-center" id="display_toolname" style="display: none;">
			   <a onclick="showlist()" id="back" class="btn btn-primary btn-block">返回商品列表重选</a>
			   <br>
			   <span style="color: blue" id="toolname"></span>
		    </div>
			<div class="form-group" id="display_price"  style="display: none;">
				<div class="input-group"><div class="input-group-addon">商品价格</div>
				<input type="text" name="need" id="need" class="form-control" disabled/>
				<?php if ($conf['cmkj_open'] == 1) {?>
				<a href="/kjshop.php" class="input-group-addon">我要砍价</a>
				<?php }?>
			</div>
			<?php if ($conf['tjreg_open'] == 1 && $isLogin2 != 1) {?>
			<br>
			<div class="form-group text-center">
				<a href="<?php echo $cdnserver ?>user/reg.php"><i class="fa fa-hand-o-right" aria-hidden="true"></i><span style="color:red">点我注册</font></span><font color="#ADADAD">后价格更优惠，</font><span style="color:red">签到领红包</span><span style="color:blue">免费下单</span></a>
		    </div>
		    <?php }?>
			</div>
			<div class="form-group" id="display_left" style="display:none;">
				<div class="input-group"><div class="input-group-addon">库存数量</div>
				<input type="text" name="leftcount" id="leftcount" class="form-control" disabled/>
			</div></div>
			<div class="form-group" id="display_num" style="display:none;">
                <div class="input-group">
                    <div class="input-group-addon">下单份数</div>
                    <span class="input-group-btn"><input id="num_min" type="button" class="btn btn-info" style="border-radius: 0px;" value="━"></span>
                    <input id="num" name="num" class="form-control" type="number" min="1" value="1"/>
                    <span class="input-group-btn"><input id="num_add" type="button" class="btn btn-info" style="border-radius: 0px;" value="✚"></span>
                    <span class="input-group-btn"><a href="#numAlert" target="_blank" data-toggle="modal" class="btn btn-warning">说明</a></span>
                </div>
		    </div>
			<div id="inputsname"></div>
            <!-- 点赞模块 开始 -->
            <?php if ($conf['master_goods_like'] == 1): ?>
            <style>
                .goods-like-box{
                    display: flex;
                    width: 100%;
                    justify-content: space-around;
                }
                .goods-like-box .like-box-left,
                .goods-like-box .like-box-right{
                    width: auto;
                }

                .goods-like-box .like-icon{
                     width: 20px;
                     height: 20px;
                     display: inline-block;
                     cursor: pointer;
                    position: relative;
                    display: inline-block;
                }

                .goods-like-box .like-icon svg{
                     width: 100%;
                     height: 100%;
                     display: inline-block;
                }

                .goods-like-box .like-icon-left svg{
                    transform: rotate(0deg);
                }

                .goods-like-box .like-icon-right svg{
                    transform: rotate(180deg);
                }

                .goods-like-box .like-num{
                    font-size: 20px;
                }

                .like-particle {
                    position: absolute;
                    top: 0;
                    left: 50%;
                    transform: translateX(-50%);
                    z-index: 99999;
                     width: 20px;
                     height: 20px;
                }

                .like-particle0 {
                    animation: moveUpAndFadeOut0 1s forwards;
                }

                .like-particle1 {
                    animation: moveUpAndFadeOut1 1s forwards;
                }

                .like-particle2 {
                    animation: moveUpAndFadeOut2 1s forwards;
                }

                .like-particle3 {
                    animation: moveUpAndFadeOut3 1s forwards;
                }

                @keyframes moveUpAndFadeOut0 {
                    0% {
                        top: 0;
                        opacity: 1;
                    }
                    25% {
                        left:5px;
                    }
                    50% {
                        left:0px;
                    }
                    75% {
                        left:5px;
                    }
                    100% {
                        left:0px;
                        opacity: 0;
                        transform: scale(0.5)
                    }
                }
                @keyframes moveUpAndFadeOut1 {
                    0% {
                        top: 0;
                        opacity: 1;
                    }
                    25% {
                        left:15px;
                    }
                    50% {
                        left:11px;
                    }
                    75% {
                        left:15px;
                    }
                    100% {
                        left:11px;
                        opacity: 0;
                        transform: scale(0.5)
                    }
                }
                @keyframes moveUpAndFadeOut2 {
                    0% {
                        top: 0;
                        opacity: 1;
                    }
                    25% {
                        left:42px;
                    }
                    50% {
                        left:36px;
                    }
                    75% {
                        left:42px;
                    }
                    100% {
                        left:36px;
                        opacity: 0;
                        transform: scale(0.5)
                    }
                }
                @keyframes moveUpAndFadeOut3 {
                    0% {
                        top: 0;
                        opacity: 1;
                    }
                    25% {
                        left:-15px;
                    }
                    50% {
                        left:-3px;
                    }
                    75% {
                        left:-15px;
                    }
                    100% {
                        left:-3px;
                        opacity: 0;
                        transform: scale(0.5)
                    }
                }
            </style>
            <div class="form-group" id="display_like" style="display:none;">
                <div class="goods-like-box">
                    <div class="like-box-left">
                        <i class="like-icon like-icon-left"><svg t="1722329537951" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1361" ><path d="M775.6 938.1H374.1c-39.7 0-113.8-26.6-113.8-90.6V393.9l12.9-9.6c1.5-1.1 166.7-113.5 166.7-249.6l1.5-11.4c4.1-63.5 47.7-76.8 83.4-76.8l3.5 0.2c4.8 0.5 47.4 6.6 79.6 53.9 39.8 58.4 45.3 151.7 16.5 277.5h233.5c8.6-0.4 39.8-0.1 63.3 22 11.3 10.6 24.8 30.1 24.8 62.8 0 3.6-0.9 90.7-83.2 370.6-12.3 39.3-39.7 104.6-87.2 104.6zM324 425.3v422.2c0 16.2 35.7 26.9 50.1 26.9H772c8.2-7.2 22.1-34.7 29.9-59.4 78.4-266.7 80.5-351.6 80.5-352.4 0-5.2-0.8-12.5-4.6-16.1-4.3-4.1-13-5.2-16.9-4.9l-2.2 0.1H542.1l10.9-40.1c44.5-164.5 21.5-235.5 3.8-263.1-13.7-21.5-30-27.1-34.9-28.4-15.9 0.2-16 2.5-17 17.4-0.2 3.8-0.5 7.9-1.4 12.1-2.7 141-139 252.2-179.5 285.7z" p-id="1362"></path><path d="M255 937.7h-71.6c-38 0-68.9-30.8-68.9-68.9v-420c0-38 30.8-68.9 68.9-68.9H256c38 0 68.9 30.8 68.9 68.9v419.1c-0.1 38.5-31.3 69.8-69.9 69.8z" fill="#FF4C4D" p-id="1363"></path></svg></i>
                        <span class="like-num like-num-up" id="like-num-up"></span>
                     </div>
                     <div class="like-box-right">
                        <i class="like-icon like-icon-right"><svg t="1722329537951" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1361"><path d="M775.6 938.1H374.1c-39.7 0-113.8-26.6-113.8-90.6V393.9l12.9-9.6c1.5-1.1 166.7-113.5 166.7-249.6l1.5-11.4c4.1-63.5 47.7-76.8 83.4-76.8l3.5 0.2c4.8 0.5 47.4 6.6 79.6 53.9 39.8 58.4 45.3 151.7 16.5 277.5h233.5c8.6-0.4 39.8-0.1 63.3 22 11.3 10.6 24.8 30.1 24.8 62.8 0 3.6-0.9 90.7-83.2 370.6-12.3 39.3-39.7 104.6-87.2 104.6zM324 425.3v422.2c0 16.2 35.7 26.9 50.1 26.9H772c8.2-7.2 22.1-34.7 29.9-59.4 78.4-266.7 80.5-351.6 80.5-352.4 0-5.2-0.8-12.5-4.6-16.1-4.3-4.1-13-5.2-16.9-4.9l-2.2 0.1H542.1l10.9-40.1c44.5-164.5 21.5-235.5 3.8-263.1-13.7-21.5-30-27.1-34.9-28.4-15.9 0.2-16 2.5-17 17.4-0.2 3.8-0.5 7.9-1.4 12.1-2.7 141-139 252.2-179.5 285.7z" p-id="1362"></path><path d="M255 937.7h-71.6c-38 0-68.9-30.8-68.9-68.9v-420c0-38 30.8-68.9 68.9-68.9H256c38 0 68.9 30.8 68.9 68.9v419.1c-0.1 38.5-31.3 69.8-69.9 69.8z" fill="#FF4C4D" p-id="1363"></path></svg></i>
                        <span class="like-num like-num-down" id="like-num-down"></span>
                     </div>
                </div>
		    </div>
            <?php endif;?>
            <!-- 点赞模块 end -->
			<div id="alert_frame" class="alert alert-success animated rubberBand" style="display:none;background-color: #b4ffe0;color: #214cb1;">
				<div id="alert_title">----商品说明 必看----</div>
				<div id="alert_content"></div>
			</div>
			<?php if ($conf['shoppingcart'] == 1) {?>
			<div class="btn-group btn-group-justified form-group">
			    <a class="btn btn-block btn-success" type="button" id="submit_cart_shop">加入购物车</a>
				<a type="submit" id="submit_buy" class="btn btn-block btn-primary">立即购买</a>
            </div>
			<?php } else {?>
			<div class="form-group">
				<input type="submit" id="submit_buy" class="btn btn-primary btn-block" value="立即购买">
			</div>
			<?php }?>
			<?php if ($conf['shop_batch'] == 1 && $isLogin2 == 1 && file_exists(TEMPLATE_ROOT . 'default/batch.inc.php')) {include_once TEMPLATE_ROOT . 'default/batch.inc.php';}?>
			<?php if ($conf['shop_batch'] == 2 && file_exists(TEMPLATE_ROOT . 'default/batch.inc.php')) {include_once TEMPLATE_ROOT . 'default/batch.inc.php';}?>
			<div class="panel-body border-t" id="alert_cart" style="display:none;"><i class="fa fa-shopping-cart"></i>&nbsp;当前购物车已添加<b id="cart_count">0</b>个商品<a class="btn btn-xs btn-danger pull-right" href="javascript:openCart()">购物车列表</a></div>
			<br>
			<?php if ($isLogin2 != 1) {?>
			<div class="panel-body border-t"><i class="fa fa-gift"></i> 注册下单更优惠，签到领现金！<a class="btn btn-xs btn-danger pull-right" href="<?php echo $cdnserver ?>user/reg.php" style="background-color: #FFCC33; border-color: #FFCC33;">点击注册</a></div>
			<?php }?>
<?php
}?>
<script type="text/javascript">
var ui_tool=<?php echo $conf['ui_tool'] > 0 ? '1' : '0' ?>;
var tool_show=<?php echo $conf['tool_show'] > 0 ? '1' : '0' ?>;
var cartBuy=<?php echo $conf['shoppingcart'] > 0 ? '1' : '0' ?>;
var kf_qq='<?php echo $conf['kfqq'] ? $conf['kfqq'] : $conf['zzqq'] ?>';
var isLogin2=<?php echo $isLogin2 == 1 ? 'true' : 'false'; ?>;
</script>