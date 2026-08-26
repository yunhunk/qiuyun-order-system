<?php
if (!defined('IN_CRONLITE')) die();

$cid = intval($_GET['cid']);

// 获取分类信息
$class_info = $DB->getRow("SELECT * FROM pre_class WHERE cid='$cid' LIMIT 1");
if(!$class_info) {
    exit("<script language='javascript'>window.location.href='./';</script>");
}

// 获取上级分类信息
$parent_info = $DB->getRow("SELECT * FROM pre_parent_class WHERE cid='{$class_info['parent_cid']}' LIMIT 1");

// 获取所有一级分类
$parent_class = $DB->query("SELECT * FROM pre_parent_class ORDER BY sort ASC");
$parent_data = [];
while($row = $parent_class->fetch()){
    $parent_data[] = $row;
}

// 获取当前一级分类下的所有二级分类
$sub_class = $DB->query("SELECT * FROM pre_class WHERE active=1 AND parent_cid='{$class_info['parent_cid']}' ORDER BY sort ASC");
$sub_data = [];
while($row = $sub_class->fetch()){
    if($is_fenzhan && in_array($row['cid'], $classhide)) continue;
    $sub_data[] = $row;
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
    <title><?php echo $class_info['name']?> - <?php echo $conf['sitename']?></title>
    <meta name="keywords" content="<?php echo $conf['keywords'] ?>">
    <meta name="description" content="<?php echo $conf['description'] ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/store/css/foxui.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/store/css/style.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/store/css/iconfont.css">
    <link href="<?php echo $cdnpublic?>layui/2.5.7/css/layui.min.css" rel="stylesheet">
</head>
<style>
body {
    max-width: 750px;
    margin: 0 auto;
    background: #f3f3f3;
}
.header {
    position: fixed;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
    background: #fff;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    width: 100%;
    max-width: 650px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    box-sizing: border-box;
}
.back-btn {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 8px;
    color: #666;
    font-size: 20px;
    flex-shrink: 0;
    text-decoration: none;
}
.back-btn:active {
    opacity: 0.8;
}
.header-title {
    flex: 1;
    font-size: 16px;
    color: #333;
    font-weight: 500;
}
.category-path {
    font-size: 12px;
    color: #999;
    margin-top: 4px;
}
.goods-container {
    padding-top: 200px;
    padding-bottom: 60px;
    height: 100vh;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch; /* 增加弹性滚动效果 */
}
.goods_sort {
    position: fixed;
    top: 150px;
    left: 0;
    right: 0;
    z-index: 997;
    background: #fff;
    display: flex;
    max-width: 750px;
    margin: 0 auto;
    border-bottom: 1px solid #eee;
}
.goods_sort .item {
    flex: 1;
    text-align: center;
    position: relative;
    padding: 12px 0;
    font-size: 14px;
    color: #666;
    cursor: pointer;
}
.goods_sort .item.on {
    color: #1492fb;
}
.goods_sort .item .sorting {
    display: inline-block;
    width: 8px;
    margin-left: 3px;
    position: relative;
    vertical-align: middle;
}
.goods_sort .item .sorting .icon {
    position: absolute;
    right: 0;
    width: 8px;
    height: 8px;
    line-height: 8px;
    font-size: 8px;
}
.goods_sort .item .sorting .icon-sanjiao2 {
    top: -4px;
}
.goods_sort .item .sorting .icon-sanjiao1 {
    top: 4px;
}
.goods_sort .item.DESC .sorting .icon-sanjiao1,
.goods_sort .item.ASC .sorting .icon-sanjiao2 {
    color: #1492fb;
}
.fui-goods-group {
    background: #f3f3f3;
    padding: 10px;
    margin-bottom: 60px; /* 为底部留出空间 */
}
.fui-goods-group .fui-goods-item {
    width: 100%;
    background: #fff;
    margin-bottom: 8px;
    padding: 12px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    display: flex;
    align-items: center;
}

.fui-goods-group .fui-goods-item .image {
    width: 100px;
    height: 100px;
    margin-right: 12px;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
    flex-shrink: 0;
}

.fui-goods-group .fui-goods-item .image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.fui-goods-group .fui-goods-item .detail {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-width: 0;
    height: 100px;
}

.fui-goods-group .fui-goods-item .name {
    font-size: 14px;
    color: #333;
    line-height: 1.4;
    margin-bottom: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    font-weight: 500;
}

.fui-goods-group .fui-goods-item .price {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: auto;
}

.fui-goods-group .fui-goods-item .price .text {
    color: #ff4444;
    font-size: 16px;
    font-weight: bold;
}

.fui-goods-group .fui-goods-item .price .buy {
    background: linear-gradient(45deg, #ff6b6b, #ff4444);
    color: #fff;
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 13px;
    font-weight: 500;
    box-shadow: 0 2px 6px rgba(255,68,68,0.2);
}

.fui-goods-group .fui-goods-item .sale {
    font-size: 12px;
    color: #999;
    margin-top: 4px;
}

.fui-goods-group .fui-goods-item .tag {
    position: absolute;
    top: 6px;
    right: 6px;
    background: #ff4444;
    color: #fff;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 500;
}

.fui-goods-group .fui-goods-item .sales-count {
    position: absolute;
    left: 6px;
    bottom: 6px;
    background: rgba(0,0,0,0.5);
    color: #fff;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
    display: flex;
    align-items: center;
    font-weight: 500;
}

.fui-goods-group .fui-goods-item .sales-count i {
    font-size: 11px;
    margin-right: 2px;
}

.parent-select {
    position: relative;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    padding: 5px 0;
}

.parent-select:after {
    content: '';
    width: 6px;
    height: 6px;
    border-right: 1.5px solid #666;
    border-bottom: 1.5px solid #666;
    transform: rotate(45deg);
    margin-left: 6px;
    margin-top: -2px;
    transition: all 0.3s ease;
}

.parent-select.active:after {
    transform: rotate(-135deg);
    margin-top: 2px;
}

.parent-dropdown {
    display: none;
    position: fixed;
    top: 52px;
    left: 0;
    right: 0;
    background: #fff;
    margin: 0 auto;
    z-index: 1001;
    padding: 15px;
    max-width: 750px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        transform: translateY(-10px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.parent-dropdown.show {
    display: block;
}

.parent-dropdown .items-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    padding: 0 5px;
}

.parent-dropdown .item {
    padding: 12px 5px;
    font-size: 14px;
    color: #666;
    transition: all 0.3s ease;
    text-align: center;
    border-radius: 8px;
    background: #f7f7f7;
    position: relative;
    overflow: hidden;
}

.parent-dropdown .item:hover {
    transform: translateY(-2px);
    background: #f0f7ff;
    color: #1492fb;
}

.parent-dropdown .item.active {
    color: #fff;
    font-weight: 500;
    background: linear-gradient(45deg, #1492fb, #1299ff);
}

.parent-dropdown .item.active:after {
    content: '';
    position: absolute;
    right: 8px;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: #fff;
}

.sub-categories {
    position: fixed;
    top: 52px;
    left: 0;
    right: 0;
    z-index: 998;
    background: #fff;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
    max-width: 750px;
    margin: 0 auto;
}

.sub-categories .wrapper {
    display: flex;
    padding: 0 10px;
    transition: all 0.3s ease;
}

.sub-categories .wrapper.collapsed {
    height: 85px;
    overflow: hidden;
    flex-wrap: nowrap;
}

.sub-categories .wrapper.expanded {
    flex-wrap: wrap;
    height: auto;
    padding-bottom: 10px;
}

.sub-categories .item {
    width: 20%;
    text-align: center;
    padding: 8px 5px;
    text-decoration: none;
    position: relative;
}

.sub-categories .item p {
    font-size: 12px;
    color: #666;
    margin: 0;
    line-height: 1.3;
    padding: 0 ;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    width: 5em;  /* 限制宽度为5个字 */
    margin: 0 auto;  /* 居中显示 */
}

.sub-categories .wrapper.expanded .item p {
    width: auto;  /* 展开后取消宽度限制 */
    white-space: normal;
    -webkit-line-clamp: 2;
    display: -webkit-box;
    -webkit-box-orient: vertical;
}

.sub-categories .item .icon {
    width: 45px;
    height: 45px;
    margin: 0 auto 5px;
    border-radius: 50%;
    overflow: hidden;
    background: #f7f7f7;
}

.sub-categories .item .icon img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.sub-categories .item .more-icon {
    width: 45px;
    height: 45px;
    margin: 0 auto 5px;
    border-radius: 50%;
    background: #f7f7f7;
    display: flex;
    align-items: center;
    justify-content: center;
}

.sub-categories .item .more-icon i {
    font-size: 24px;
    color: #999;
}

.sub-categories .item.more-btn p {
    width: auto;  /* 查看更多按钮不限制宽度 */
}

.sub-categories .item.active .icon {
    box-shadow: 0 2px 8px rgba(20,146,251,0.2);
}

.sub-categories .item.active p {
    color: #1492fb;
    font-weight: 500;
}

/* 添加遮罩层 */
.dropdown-mask {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.3);
    z-index: 1000;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.dropdown-mask.show {
    display: block;
}

/* 确保内容区域可以滚动 */
.page-container {
    height: 100%;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
}

.goods-list {
    padding-bottom: 60px; /* 为底部导航栏留出空间 */
}
</style>
<body>
<div class="dropdown-mask"></div>
<div class="header">
    <a href="javascript:history.back()" class="back-btn">
        <i class="fa fa-chevron-left"></i>
    </a>
    <div class="header-title">
        <div class="parent-select">
            <?php echo $parent_info['name']?>
            <div class="parent-dropdown">
                <div class="items-grid">
                    <?php foreach($parent_data as $row){?>
                    <div class="item <?php echo $row['cid']==$parent_info['cid']?'active':''?>" data-cid="<?php echo $row['cid']?>"><?php echo $row['name']?></div>
                    <?php }?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sub-categories">
    <div class="wrapper collapsed">
        <?php 
        $total_categories = count($sub_data);
        foreach($sub_data as $key => $row){
            if($key < 4){ // 只显示前4个分类
        ?>
            <a class="item <?php echo $row['cid']==$cid?'active':''?>" href="./?mod=goods&cid=<?php echo $row['cid']?>">
                <div class="icon">
                    <img src="<?php echo $row['shopimg']?$row['shopimg']:'template/storenews/404.png'?>" alt="<?php echo $row['name']?>">
                </div>
                <p><?php echo $row['name']?></p>
            </a>
        <?php 
            }
        }
        // 如果总数大于4个，显示查看更多按钮
        if($total_categories > 4){
        ?>
            <div class="item more-btn">
                <div class="more-icon">
                    <i class="layui-icon layui-icon-down"></i>
                </div>
                <p>查看更多</p>
            </div>
        <?php } ?>
    </div>
</div>

<div class="goods-container">
    <div class="goods_sort">
        <div class="item item-price on" data-order="sort" data-sort="ASC">
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

    <div class="fui-goods-group">
        <div class="flow_load">
            <div id="goods_list"></div>
        </div>
    </div>
</div>

<?php include TEMPLATE_ROOT.'storenews/common/footer.php';?>

<input type="hidden" name="_curr_time" value="<?php echo time(); ?>">
<input type="hidden" name="_template_virtualdata" value="<?php echo $conf['template_virtualdata']?>">
<input type="hidden" name="_template_showsales" value="<?php echo $conf['template_showsales']?>">
<input type="hidden" name="_sort_type" value="">
<input type="hidden" name="_sort" value="">

<script src="<?php echo $cdnpublic?>jquery/3.4.1/jquery.min.js"></script>
<script src="<?php echo $cdnpublic?>layui/2.5.7/layui.all.js"></script>
<script>
// 获取系统变量
var curr_time = $("input[name=_curr_time]").val();
var template_virtualdata = $("input[name=_template_virtualdata]").val();
var template_showsales = $("input[name=_template_showsales]").val();

// 获取商品列表
function get_goods(){
    $("#goods_list").remove();
    $(".flow_load").append("<div id=\"goods_list\"></div>");
    layui.use(['flow'], function(){
        var flow = layui.flow;
        var cid = <?php echo $cid?>;
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
                                    item.shopimg = "<?php echo $cdnserver?>template/storenews/404.png";
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
    // 一级分类下拉菜单
    $('.parent-select').click(function(e){
        e.stopPropagation();
        $(this).toggleClass('active');
        $('.parent-dropdown').toggleClass('show');
        $('.dropdown-mask').toggleClass('show');
    });
    
    // 点击遮罩层或其他地方关闭下拉菜单
    $('.dropdown-mask, body').click(function(e){
        if(!$(e.target).closest('.parent-dropdown').length) {
            $('.parent-select').removeClass('active');
            $('.parent-dropdown').removeClass('show');
            $('.dropdown-mask').removeClass('show');
        }
    });
    
    // 切换一级分类
    $('.parent-dropdown .item').click(function(e){
        e.stopPropagation();
        var cid = $(this).data('cid');
        
        // 更新选中状态
        $('.parent-dropdown .item').removeClass('active');
        $(this).addClass('active');
        
        // 关闭下拉菜单
        $('.parent-select').removeClass('active');
        $('.parent-dropdown').removeClass('show');
        $('.dropdown-mask').removeClass('show');
        
        // 获取该一级分类下的二级分类
        $.ajax({
            type: 'GET',
            url: './?mod=ajax&act=getCategory2',
            data: {pcid: cid},
            dataType: 'json',
            success: function(res) {
                if(res.code == 0 && res.data && res.data.length > 0) {
                    // 跳转到该一级分类下的第一个二级分类
                    window.location.href = './?mod=goods&cid=' + res.data[0].cid;
                } else {
                    layer.msg('该分类下暂无商品');
                }
            },
            error: function() {
                layer.msg('加载失败，请重试');
            }
        });
    });
    
    // 查看更多二级分类
    $('.more-btn').click(function(){
        var wrapper = $('.sub-categories .wrapper');
        if(wrapper.hasClass('collapsed')){
            // 展开显示所有分类
            wrapper.removeClass('collapsed').addClass('expanded');
            // 加载所有二级分类
            var html = '';
            <?php foreach($sub_data as $row){ ?>
                html += '<a class="item <?php echo $row['cid']==$cid?'active':''?>" href="./?mod=goods&cid=<?php echo $row['cid']?>">';
                html += '<div class="icon">';
                html += '<img src="<?php echo $row['shopimg']?$row['shopimg']:'template/storenews/404.png'?>" alt="<?php echo $row['name']?>">';
                html += '</div>';
                html += '<p><?php echo $row['name']?></p>';
                html += '</a>';
            <?php } ?>
            wrapper.html(html);
            $(this).find('i').removeClass('layui-icon-down').addClass('layui-icon-up');
            $(this).find('p').text('收起');
        } else {
            // 收起分类列表，恢复到初始状态
            wrapper.removeClass('expanded').addClass('collapsed');
            var html = '';
            <?php 
            foreach($sub_data as $key => $row){ 
                if($key < 4){
            ?>
                html += '<a class="item <?php echo $row['cid']==$cid?'active':''?>" href="./?mod=goods&cid=<?php echo $row['cid']?>">';
                html += '<div class="icon">';
                html += '<img src="<?php echo $row['shopimg']?$row['shopimg']:'template/storenews/404.png'?>" alt="<?php echo $row['name']?>">';
                html += '</div>';
                html += '<p><?php echo $row['name']?></p>';
                html += '</a>';
            <?php 
                }
            } 
            ?>
            html += '<div class="item more-btn">';
            html += '<div class="more-icon">';
            html += '<i class="layui-icon layui-icon-down"></i>';
            html += '</div>';
            html += '<p>查看更多</p>';
            html += '</div>';
            wrapper.html(html);
        }
    });
    
    // 页面加载完成后加载商品列表
    get_goods();
    
    // 排序切换
    $('.goods_sort .item').click(function(){
        var order = $(this).data('order');
        var sort = $(this).data('sort');
        
        if(sort == 'ASC'){
            $(this).data('sort', 'DESC');
            $(this).addClass('DESC').removeClass('ASC');
        }else{
            $(this).data('sort', 'ASC');
            $(this).addClass('ASC').removeClass('DESC');
        }
        
        $('.goods_sort .item').not(this).removeClass('ASC DESC');
        $('.goods_sort .item').removeClass('on');
        $(this).addClass('on');
        
        get_goods();
    });
});
</script>
</body>
</html> 