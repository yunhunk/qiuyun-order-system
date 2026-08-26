<?php
if (!defined('IN_CRONLITE')) die();

$mod = isset($_GET['mod']) ? $_GET['mod'] : '';

if($_GET['buyok']==1||$_GET['chadan']==1){include_once TEMPLATE_ROOT.'store/query.php';exit;}
if(isset($_GET['tid']) && !empty($_GET['tid']))
{
	$tid=intval($_GET['tid']);
    $tool=$DB->getRow("select tid from pre_tools where tid='$tid' limit 1");
    if($tool)
    {
		exit("<script language='javascript'>window.location.href='./?mod=buy&tid=".$tool['tid']."';</script>");
    }
}

$cid = intval($_GET['cid']);
if(!$cid && !empty($conf['defaultcid']) && $conf['defaultcid']!=='0'){
	$cid = intval($conf['defaultcid']);
}

// 获取一级分类
$parent_class = $DB->query("SELECT * FROM `pre_parent_class` ORDER BY `sort` ASC");
$first_parent = null;
$parent_data = [];
while($row = $parent_class->fetch()){
    if(!$first_parent) $first_parent = $row['cid'];
    $parent_data[] = $row;
}

// 获取二级分类
$ar_data = [];
$classhide = explode(',',$siterow['class']);
$cat_name = "";
if($cid){
    $re = $DB->query("SELECT * FROM `pre_class` WHERE `active` = 1 AND `cid`='$cid' limit 1");
    $res = $re->fetch();
    if($res){
        $cat_name = $res['name'];
        $qcid = $cid;
    }
}

$class_show_num = intval($conf['index_class_num_style'])?intval($conf['index_class_num_style']):2; //分类展示几组
?>
<!DOCTYPE html>
<html lang="zh" style="font-size: 102.4px;">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=no"/>
    <script>document.documentElement.style.fontSize = document.documentElement.clientWidth / 750 * 40 + "px";</script>
    <meta name="format-detection" content="telephone=no">
    <meta name="csrf-param" content="_csrf">
    <title><?php echo $hometitle?></title>
    <meta name="keywords" content="<?php echo $conf['keywords'] ?>">
    <meta name="description" content="<?php echo $conf['description'] ?>">
    <script src="<?php echo $cdnpublic?>jquery/1.12.4/jquery.min.js"></script>
    <link href="<?php echo $cdnpublic?>Swiper/6.4.5/swiper-bundle.min.css" rel="stylesheet">
    <link href="<?php echo $cdnserver?>template/storenews/css/style.css" rel="stylesheet">
    <link href="<?php echo $cdnpublic?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <?php echo str_replace('body','html',$background_css)?>
    <style>
    .headerbox {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 999;
        max-width: 750px;
        margin: 0 auto;
    }
    .header {
        padding: 10px 15px;
        background: #fff;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .hbox {
        height: 52px; /* 与header高度保持一致 */
    }
    </style>
</head>
<body>
<div id="body">
    <div class="fui-page-group">
        <div class="fui-page fui-page-current">
            <div class="fui-content navbar" id="container" style="background-color: #fafafc;">
                <div class="default-items">
                    <div class="search-container">
                        <div class="search-box" onclick="window.location.href='?mod=search'">
                            <i class="fa fa-search search-icon"></i>
                            <input type="text" class="search-input" placeholder="搜索商品" readonly>
                            <button type="button" class="search-btn">
                                <i class="fa fa-search"></i>
                                <span>搜索</span>
                            </button>
                        </div>
                        <div class="search-history">
                            <div class="history-title">
                                <span>搜索历史</span>
                                <a class="clear-history" id="clearHistory">清空历史</a>
                            </div>
                            <ul class="history-list" id="historyList"></ul>
                        </div>
                    </div>
                    <div class="banner-wrapper">
                        <div class="swiper-container">
                            <div class="swiper-wrapper">
                            <?php
                            $banner = explode('|', $conf['banner']);
                            foreach ($banner as $v) {
                                $image_url = explode('*', $v);
                                $url = (strpos($image_url[1], 'http') === 0) ? $image_url[1] : 'http://' . $image_url[1];
                                    echo '<div class="swiper-slide">
                                        <a href="' . $url . '" target="_blank">
                                            <img src="' . $image_url[0] . '" alt="banner">
                                        </a>
                                    </div>';
                            }
                            ?>
                        </div>
                            <div class="swiper-pagination"></div>
                        </div>
                    </div>
                    <div class="feature-cards">
                        <a href="<?php echo $cdnserver?>user/regsite.php" class="feature-card primary">
                            <div class="feature-icon">
                                <img src="<?php echo $cdnserver?>template/storenews/tupian/zhunqian.png" alt="开通分站">
                            </div>
                            <h3 class="feature-title">开通分站赚钱</h3>
                            <p class="feature-desc">快速开通属于您的分站，轻松实现被动收入</p>
                        </a>
                        <a href="<?php echo $cdnserver?>?mod=article&id=3" class="feature-card secondary">
                            <div class="feature-icon">
                                <img src="<?php echo $cdnserver?>template/storenews/tupian/tuandui.png" alt="平台模式">
                            </div>
                            <h3 class="feature-title">平台赚钱模式</h3>
                            <p class="feature-desc">了解完整的赚钱模式，开启您的创业之旅</p>
                        </a>
                    </div>

                    <div class="category-cards">
                        <?php foreach($parent_data as $parent): ?>
                        <?php
                            $sub_class = $DB->query("SELECT * FROM `pre_class` WHERE `active`=1 AND `parent_cid`='{$parent['cid']}' ORDER BY `sort` ASC");
                            $sub_data = [];
                            while($row = $sub_class->fetch()){
                                if($is_fenzhan && in_array($row['cid'], $classhide)) continue;
                                $sub_data[] = $row;
                            }
                        ?>
                        <div class="category-card">
                            <div class="card-header">
                                <div class="header-left">
                                    <img src="<?php echo $parent['shopimg']?$parent['shopimg']:'template/storenews/404.png'?>" alt="<?php echo $parent['name']?>">
                                    <span><?php echo $parent['name']?></span>
                                </div>
                                    <?php if(count($sub_data) > 6): ?>
                                <div class="header-right">
                                    <a href="javascript:;" class="more" data-cid="<?php echo $parent['cid']?>">查看更多</a>
                                </div>
                                    <?php endif; ?>
                            </div>
                            <div class="card-content collapsed">
                                <?php 
                                $display_count = 0;
                                    foreach($sub_data as $row): 
                                    $display_count++;
                                        $hidden = $display_count > 6 ? ' hidden' : '';
                                ?>
                                    <div class="sub-category-item<?php echo $hidden?>" onclick="window.location.href='?mod=goods&cid=<?php echo $row['cid']?>'">
                                        <div class="sub-item-inner">
                                            <div class="sub-item-img">
                                    <img src="<?php echo $row['shopimg']?$row['shopimg']:'template/storenews/404.png'?>" alt="<?php echo $row['name']?>">
                                            </div>
                                            <div class="sub-item-text">
                                                <div class="text-content">
                                                    <p class="title"><?php echo $row['name']?></p>
                                                    <p class="buy-now">立即购买 >></p>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        </div>
        <input type="hidden" name="_cid" value="<?php echo $cid; ?>">
        <input type="hidden" name="_cidname" value="<?php echo $cat_name; ?>">
        <?php include TEMPLATE_ROOT.'storenews/common/footer.php'; ?>
</div>
<script src="<?php echo $cdnpublic?>Swiper/6.4.5/swiper-bundle.min.js"></script>
<script src="<?php echo $cdnserver?>template/storenews/js/main.js"></script>
</body>
</html>