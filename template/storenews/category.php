<?php
if (!defined('IN_CRONLITE')) die();

$cid = intval($_GET['cid']);

// 获取所有一级分类
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

// 获取第一个一级分类的二级分类
if($first_parent) {
    $sub_class = $DB->query("SELECT * FROM `pre_class` WHERE `active`=1 AND `parent_cid`='$first_parent' ORDER BY `sort` ASC");
    while($row = $sub_class->fetch()){
        if($is_fenzhan && in_array($row['cid'], $classhide)) continue;
        $ar_data[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="zh" style="font-size: 102.4px;">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=no"/>
    <script> document.documentElement.style.fontSize = document.documentElement.clientWidth / 750 * 40 + "px";</script>
    <meta name="format-detection" content="telephone=no">
    <meta name="csrf-param" content="_csrf">
    <title><?php echo $cid?$cat_name:'商品分类'?> - <?php echo $conf['sitename']?></title>
    <meta name="keywords" content="<?php echo $conf['keywords'] ?>">
    <meta name="description" content="<?php echo $conf['description'] ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/store/css/foxui.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/store/css/style.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/store/css/iconfont.css">
    <link href="<?php echo $cdnpublic?>layui/2.5.7/css/layui.min.css" rel="stylesheet">
    <link href="<?php echo $cdnserver?>template/storenews/css/style.css" rel="stylesheet">
    <link href="<?php echo $cdnpublic?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
</head>
<style>
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    background: #f3f3f3;
    overflow: hidden;
}
body {
    max-width: 750px;
    margin: 0 auto;
    position: relative;
}

.category-container {
    position: fixed;
    top: 46px;
    left: 0;
    right: 0;
    bottom: 0;
    display: flex;
    max-width: 750px;
    margin: 0 auto;
    background: #fff;
}

.category-left {
    width: 85px;
    height: 100%;
    background: #f7f7f7;
    overflow-y: auto;
}

.category-left .item {
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: #666;
    position: relative;
    text-align: center;
    line-height: 1.3;
    padding: 5px;
    cursor: pointer;
}

.category-left .item.active {
    background: #fff;
    color: #1492fb;
    font-weight: 500;
}

.category-left .item.active:before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 16px;
    background: #1492fb;
}

.category-right {
    flex: 1;
    height: 100%;
    overflow-y: auto;
    padding: 15px;
    background: #fff;
}

.sub-category {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

.sub-category-item {
    text-align: center;
    text-decoration: none;
    transition: all 0.3s ease;
}

.sub-category-item:hover {
    transform: translateY(-2px);
}

.sub-category-item img {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    margin-bottom: 8px;
}

.sub-category-item:hover img {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.sub-category-item p {
    font-size: 13px;
    color: #666;
    margin: 0;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

/* 商品列表样式保持不变 */
.goods-container {
    display: none;
    position: fixed;
    top: 52px;
    left: 0;
    right: 0;
    bottom: 0;
    max-width: 750px;
    margin: 0 auto;
    background: #f3f3f3;
}

.goods-container.show {
    display: block;
}

.category-container.hide {
    display: none;
}

.category-left .item a {
    color: inherit;
    text-decoration: none;
    display: block;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.category-section {
    margin-bottom: 20px;
    scroll-margin-top: 52px; /* 防止锚点定位被头部遮挡 */
}

.category-title {
    font-size: 15px;
    color: #333;
    padding: 12px 15px;
    background: #fff;
    margin-bottom: 10px;
    display: block;
    white-space: normal;
    line-height: 1.4;
    word-break: break-all;
}

.category-right {
    scroll-behavior: smooth; /* 平滑滚动效果 */
}
</style>
<body>
<div class="fui-page-group">
    <div class="fui-page fui-page-current">
        <div class="search-container">
            <div class="search-box" onclick="window.location.href='?mod=search'">
                <i class="fa fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="搜索商品" readonly>
                <button type="button" class="search-btn">
                    <i class="fa fa-search"></i>搜索
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

        <!-- 分类列表 -->
        <div class="category-container <?php echo $cid?'hide':''?>">
            <div class="category-left">
                <?php foreach($parent_data as $row){?>
                <div class="item <?php echo $row['cid'] === $first_parent?'active':''?>" data-cid="<?php echo $row['cid']?>">
                    <a href="#category-<?php echo $row['cid']?>"><?php echo $row['name']?></a>
                </div>
                <?php }?>
            </div>
            <div class="category-right">
                <?php 
                // 获取所有一级分类下的二级分类
                foreach($parent_data as $parent) {
                    $sub_class = $DB->query("SELECT * FROM `pre_class` WHERE `active`=1 AND `parent_cid`='{$parent['cid']}' ORDER BY `sort` ASC");
                    $sub_data = [];
                    while($row = $sub_class->fetch()){
                        if($is_fenzhan && in_array($row['cid'], $classhide)) continue;
                        $sub_data[] = $row;
                    }
                ?>
                <div class="category-section" id="category-<?php echo $parent['cid']?>">
                    <div class="category-title"><?php echo $parent['name']?></div>
                    <div class="sub-category">
                        <?php foreach($sub_data as $row){?>
                        <a class="sub-category-item" href="./?mod=goods&cid=<?php echo $row['cid']?>">
                            <img src="<?php echo $row['shopimg']?$row['shopimg']:'template/storenews/404.png'?>" alt="<?php echo $row['name']?>">
                            <p><?php echo $row['name']?></p>
                        </a>
                        <?php }?>
                    </div>
                </div>
                <?php }?>
            </div>
        </div>

        <!-- 商品列表，复用原有的商品列表代码 -->
        <div class="goods-container <?php echo $cid?'show':''?>">
            <div class="goods_sort">
                <div class="item item-price" data-order="sort" data-sort="ASC">
                    <span class="text">综合</span>
                    <span class="sorting">
                        <i class="icon icon-sanjiao2"></i>
                        <i class="icon icon-sanjiao1"></i>
                    </span>
                </div>
                <div class="item item-price" data-order="sales" data-sort="ASC">
                    <span class="text">销量</span>
                    <span class="sorting">
                        <i class="icon icon-sanjiao2"></i>
                        <i class="icon icon-sanjiao1"></i>
                    </span>
                </div>
                <div class="item item-price" data-order="price" data-sort="ASC">
                    <span class="text">价格</span>
                    <span class="sorting">
                        <i class="icon icon-sanjiao2"></i>
                        <i class="icon icon-sanjiao1"></i>
                    </span>
                </div>
            </div>
            <div class="page-container">
                <div class="goods-list">
                    <div class="flow_load">
                        <div id="goods_list"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" name="_curr_time" value="<?php echo time(); ?>">
<input type="hidden" name="_template_virtualdata" value="<?php echo $conf['template_virtualdata']?>">
<input type="hidden" name="_template_showsales" value="<?php echo $conf['template_showsales']?>">
<input type="hidden" name="_sort_type" value="">
<input type="hidden" name="_sort" value="">

<script src="<?php echo $cdnpublic?>jquery/3.4.1/jquery.min.js"></script>
<script src="<?php echo $cdnpublic?>layui/2.5.7/layui.all.js"></script>
<script src="<?php echo $cdnpublic?>jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
<script>
// 获取系统变量
var curr_time = $("input[name=_curr_time]").val();
var template_virtualdata = $("input[name=_template_virtualdata]").val();
var template_showsales = $("input[name=_template_showsales]").val();

// 加载二级分类
function loadSubCategories(cid) {
    // 更新选中状态
    $('.category-left .item').removeClass('active');
    $('.category-left .item a[href="#category-'+cid+'"]').parent().addClass('active');
    
    // 加载二级分类
    $.ajax({
        type: 'POST',
        url: './ajax.php?act=getClass',
        data: {parent_cid: cid},
        dataType: 'json',
        success: function(res) {
            if(res.code == 0) {
                var html = '';
                res.data.forEach(function(item) {
                    html += '<a class="sub-category-item" href="./?mod=goods&cid='+item.cid+'">';
                    html += '<img src="'+(item.shopimg ? item.shopimg : 'template/storenews/404.png')+'" alt="'+item.name+'">';
                    html += '<p>'+item.name+'</p>';
                    html += '</a>';
                });
                $('#category-'+cid+' .sub-category').html(html);
            }
        },
        error: function() {
            layer.msg('加载失败，请重试');
        }
    });
}

// 获取商品列表
function get_goods(){
    $("#goods_list").remove();
    $(".flow_load").append("<div id=\"goods_list\" ></div>");
    layui.use(['flow'], function(){
        var flow = layui.flow;
        var cid = <?php echo $cid?$cid:0?>;
        var order = $('.goods_sort .item.on').data('order') || 'sort';
        var sort = $('.goods_sort .item.on').data('sort') || 'ASC';
        
        flow.load({
            elem: '#goods_list',
            isAuto: true,
            end: '没有更多商品了',
            done: function(page, next){
                $.ajax({
                    type: 'POST',
                    url: './ajax.php?act=gettoolnew',
                    data: {
                        page: page,
                        limit: 10,
                        cid: cid,
                        sort_type: order,
                        sort: sort
                    },
                    dataType: 'json',
                    success: function(res){
                        var pageHtml = '';
                        if(res.data){
                            layui.each(res.data, function(index, item){
                                var html = '<a class="fui-goods-item" href="./?mod=buy&tid='+item.tid+'">';
                                html += '<div class="image">';
                                if(!item.shopimg){
                                    item.shopimg = "./assets/store/picture/error_img.png";
                                }
                                
                                if(item.show_tag || (curr_time-(item.addtime)) <= 259200){
                                    html += '<div class="tag">'+(item.show_tag || "新款")+'</div>';
                                }
                                
                                if(item.is_stock_err == 1){
                                    html += '<img src="./assets/store/picture/ysb.png" alt="" style="width:100%;top: 0;position: absolute;height:100%">';
                                }
                                
                                if(template_showsales == 1){
                                    html += '<div class="sales-count"><i class="layui-icon layui-icon-fire"></i>'+item.sales+'</div>';
                                }
                                
                                html += '<img src="'+item.shopimg+'" alt="'+item.name+'">';
                                html += '</div>';
                                html += '<div class="detail">';
                                html += '<div class="name">'+item.name+'</div>';
                                html += '<div class="price">';
                                html += '<span class="text">￥'+item.price+'</span>';
                                html += '<span class="buy"><span>立即购买</span></span>';
                                html += '</div>';
                                if(item.stock > 0){
                                    html += '<div class="sale">库存:'+item.stock+'份</div>';
                                }
                                html += '</div>';
                                html += '</a>';
                                pageHtml += html;
                            });
                        }
                        next(pageHtml, page < Math.ceil(res.count/10));
                    }
                });
            }
        });
    });
}

$(function(){
    // 监听滚动位置来更新左侧菜单激活状态
    $('.category-right').on('scroll', function() {
        var scrollTop = $(this).scrollTop();
        
        // 找到当前滚动位置对应的分类section
        $('.category-section').each(function() {
            var top = $(this).position().top;
            var bottom = top + $(this).outerHeight();
            
            if (scrollTop >= top - 10 && scrollTop < bottom) {
                var id = $(this).attr('id');
                $('.category-left .item').removeClass('active');
                $('.category-left .item a[href="#'+id+'"]').parent().addClass('active');
                return false;
            }
        });
    });
    
    // 点击左侧菜单项时更新激活状态
    $('.category-left .item a').click(function(e) {
        $('.category-left .item').removeClass('active');
        $(this).parent().addClass('active');
    });
});

// 搜索功能
$(document).ready(function(){
    // 搜索按钮点击事件
    $('#searchBtn').click(function(){
        doSearch();
    });

    // 搜索输入框回车事件
    $('#searchInput').keypress(function(e){
        if(e.which == 13){
            doSearch();
        }
    });

    // 搜索历史点击事件
    $(document).on('click', '.history-item', function(){
        var keyword = $(this).find('span').text();
        $('#searchInput').val(keyword);
        doSearch();
    });

    // 清空历史
    $('#clearHistory').click(function(){
        $.cookie('searchHistory', '', {expires: 365, path: '/'});
        $('#historyList').empty();
    });

    // 加载搜索历史
    loadSearchHistory();

    // 搜索框获得焦点时显示搜索历史
    $('#searchInput').focus(function(){
        $('.search-history').addClass('show');
    });

    // 点击其他地方时隐藏搜索历史
    $(document).click(function(e){
        if(!$(e.target).closest('.search-container').length){
            $('.search-history').removeClass('show');
        }
    });
});

// 执行搜索
function doSearch(){
    var keyword = $('#searchInput').val().trim();
    if(keyword){
        // 保存搜索历史
        saveSearchHistory(keyword);
        // 跳转到搜索结果页
        window.location.href = './?mod=search&keyword=' + encodeURIComponent(keyword);
    }
}

// 保存搜索历史
function saveSearchHistory(keyword){
    var history = $.cookie('searchHistory');
    var keywords = history ? JSON.parse(history) : [];
    // 删除已存在的相同关键词
    keywords = keywords.filter(item => item !== keyword);
    // 添加到开头
    keywords.unshift(keyword);
    // 只保留最近10条
    keywords = keywords.slice(0, 10);
    // 保存到cookie
    $.cookie('searchHistory', JSON.stringify(keywords), {expires: 365, path: '/'});
}

// 加载搜索历史
function loadSearchHistory(){
    var history = $.cookie('searchHistory');
    if(history){
        var keywords = JSON.parse(history);
        var html = '';
        keywords.forEach(function(keyword){
            html += '<li class="history-item"><i class="fa fa-history"></i><span>' + keyword + '</span></li>';
        });
        $('#historyList').html(html);
    }
}
</script>
</body>
</html>

<?php include TEMPLATE_ROOT.'storenews/common/footer.php';?> 