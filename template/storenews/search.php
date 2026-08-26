<?php
if (!defined('IN_CRONLITE')) die();
$kw = isset($_GET['kw'])?trim(daddslashes($_GET['kw'])):null;

// 获取随机商品名作为热门搜索
$hot_searches = [];
$stmt = $DB->query("SELECT name FROM pre_tools WHERE active=1 ORDER BY RAND() LIMIT 10");
while($row = $stmt->fetch()){
    $hot_searches[] = $row['name'];
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1,user-scalable=no"/>
    <title>搜索结果 - <?php echo $conf['sitename']?></title>
    <meta name="keywords" content="<?php echo $conf['keywords'] ?>">
    <meta name="description" content="<?php echo $conf['description'] ?>">
    <link href="<?php echo $cdnserver?>template/storenews/css/style.css" rel="stylesheet">
    <link href="<?php echo $cdnpublic?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/store/css/foxui.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/store/css/style.css">
    <link rel="stylesheet" type="text/css" href="<?php echo $cdnserver; ?>assets/store/css/iconfont.css">
    <link href="<?php echo $cdnpublic?>layui/2.5.7/css/layui.min.css" rel="stylesheet">
</head>
<style>
.search-page {
    min-height: 100vh;
    background: #fff;
    padding-bottom: 20px;
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
}
.search-page .search-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    background: #fff;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    width: 100%;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    box-sizing: border-box;
    flex-shrink: 0;
}
.search-page .search-header .inner {
    display: flex;
    align-items: center;
    width: 100%;
    max-width: 650px;
    margin: 0 auto;
}
.search-page .back-btn {
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
.search-page .search-container {
    flex: 1;
    position: relative;
}
.search-page .search-box {
    display: flex;
    align-items: center;
    background: #f5f5f5;
    border-radius: 100px;
    padding: 0 6px 0 12px;
    height: 40px;
    border: 1px solid transparent;
    transition: all 0.3s ease;
}
.search-page .search-box:focus-within {
    background: #fff;
    border-color: #e5e5e5;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.search-page .search-icon {
    color: #999;
    margin-right: 8px;
    font-size: 14px;
}
.search-page .search-input {
    flex: 1;
    border: none;
    background: none;
    font-size: 14px;
    color: #333;
    height: 100%;
    padding: 0;
    min-width: 0;
}
.search-page .search-input:focus {
    outline: none;
}
.search-page .search-input::placeholder {
    color: #999;
}
.search-page .search-btn {
    background: #1492fb;
    color: #fff;
    border: none;
    padding: 0 16px;
    border-radius: 100px;
    font-size: 14px;
    margin-left: 8px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    white-space: nowrap;
    transition: all 0.3s ease;
    flex-shrink: 0;
}
.search-page .search-btn:active {
    transform: scale(0.96);
}
.search-page .search-btn i {
    margin-right: 4px;
    font-size: 12px;
}
.search-page .search-history {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    margin-top: 10px;
    border-radius: 12px;
    padding: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    z-index: 1001;
}
.search-page .search-history.show {
    display: block;
}
.search-page .history-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: #999;
    font-size: 13px;
    margin-bottom: 10px;
}
.search-page .clear-history {
    color: #1492fb;
}
.search-page .history-list {
    list-style: none;
    padding: 0;
    margin: 0;
}
.search-page .history-item {
    display: flex;
    align-items: center;
    padding: 8px 0;
    color: #666;
    font-size: 13px;
    cursor: pointer;
}
.search-page .history-item i {
    margin-right: 8px;
    color: #999;
}
.search-page .search-suggestions {
    margin-top: 60px;
    padding: 15px;
    background: #fff;
    flex-shrink: 0;
}
.search-page .hot-searches h3 {
    font-size: 14px;
    color: #333;
    margin: 0 0 10px;
}
.search-page .hot-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.search-page .hot-tag {
    padding: 6px 12px;
    background: #f7f7f7;
    border-radius: 15px;
    color: #666;
    font-size: 13px;
    text-decoration: none;
}
.search-page .search-result {
    flex: 1;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    margin-top: 60px;
    padding: 0 15px;
}
.search-page .result-header {
    font-size: 14px;
    color: #999;
    margin-bottom: 10px;
}
.search-page .fui-goods-group {
    background: #f3f3f3;
    padding: 10px;
    margin-top: 0;
}
.search-page .fui-goods-item {
    width: 100%;
    background: #fff;
    margin-bottom: 8px;
    padding: 12px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    display: flex;
    align-items: center;
    text-decoration: none;
}
.search-page .fui-goods-item .image {
    width: 100px;
    height: 100px;
    margin-right: 12px;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
    flex-shrink: 0;
}
.search-page .fui-goods-item .image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.search-page .fui-goods-item .detail {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-width: 0;
    height: 100px;
}
.search-page .fui-goods-item .name {
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
.search-page .fui-goods-item .price {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: auto;
}
.search-page .fui-goods-item .price .text {
    color: #ff4444;
    font-size: 16px;
    font-weight: bold;
}
.search-page .fui-goods-item .price .buy {
    background: linear-gradient(45deg, #ff6b6b, #ff4444);
    color: #fff;
    padding: 6px 12px;
    border-radius: 15px;
    font-size: 13px;
    font-weight: 500;
    box-shadow: 0 2px 6px rgba(255,68,68,0.2);
}
.search-page .fui-goods-item .sale {
    font-size: 12px;
    color: #999;
    margin-top: 4px;
}
.search-page .no-result {
    text-align: center;
    padding: 40px 0;
}
.search-page .no-result img {
    width: 120px;
    margin-bottom: 15px;
}
.search-page .no-result p {
    color: #999;
    font-size: 14px;
}

@media (max-width: 360px) {
    .search-page .search-header {
        padding: 8px;
    }
    .search-page .back-btn {
        width: 36px;
        height: 36px;
        font-size: 18px;
    }
    .search-page .search-box {
        height: 36px;
    }
    .search-page .search-btn {
        padding: 0 12px;
        height: 28px;
        font-size: 13px;
    }
    .search-page .search-input {
        font-size: 13px;
    }
    .search-page .search-input::placeholder {
        font-size: 13px;
    }
}

#body {
    position: relative;
    width: 100%;
    max-width: 650px;
    margin: 0 auto;
    background: #fff;
    height: 100vh;
    overflow: hidden;
    padding-bottom: env(safe-area-inset-bottom);
}
</style>
<body>
<div id="body">
    <div class="search-page">
        <div class="search-header">
            <div class="inner">
                <a href="./" class="back-btn">
                    <i class="fa fa-chevron-left"></i>
                </a>
                <div class="search-container">
                    <div class="search-box">
                        <i class="fa fa-search search-icon"></i>
                        <input type="text" class="search-input" placeholder="搜索商品" id="searchInput" value="<?php echo htmlspecialchars($kw)?>">
                        <button type="button" class="search-btn" id="searchBtn">
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
            </div>
        </div>
        
        <?php if(!$kw): ?>
        <div class="search-suggestions">
            <!-- 热门搜索 -->
            <div class="hot-searches">
                <h3>热门搜索</h3>
                <div class="hot-tags">
                    <?php foreach($hot_searches as $hot): ?>
                    <a class="hot-tag" href="javascript:;" onclick="searchKeyword('<?php echo htmlspecialchars($hot)?>')">
                        <?php echo htmlspecialchars($hot)?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="search-result">
            <?php
            if($kw){
                $sql = " (`name` LIKE :kw OR `desc` LIKE :kw)";
                $data = $DB->query("SELECT * FROM pre_tools WHERE {$sql} AND active=1 ORDER BY sort ASC", [':kw'=>"%{$kw}%"]);
                $count = $data->rowCount();
                
                if($count > 0){
                    echo '<div class="result-header">找到 '.$count.' 个相关商品</div>';
                    echo '<div class="fui-goods-group">';
                    while($res = $data->fetch()){
                        echo '<a class="fui-goods-item" href="./?mod=buy&tid='.$res['tid'].'">';
                        echo '<div class="image">';
                        if(!$res['shopimg']){
                            $res['shopimg'] = $cdnserver."template/storenews/404.png";
                        }
                        
                        if($res['show_tag'] || (time()-($res['addtime'])) <= 259200){
                            echo '<div class="tag">'.($res['show_tag'] || "新款").'</div>';
                        }
                        
                        if($res['is_stock_err'] == 1){
                            echo '<img src="./assets/store/picture/ysb.png" alt="" style="width:100%;top: 0;position: absolute;height:100%">';
                        }
                        
                        echo '<img src="'.$res['shopimg'].'" alt="'.$res['name'].'">';
                        echo '</div>';
                        echo '<div class="detail">';
                        echo '<div class="name">'.htmlspecialchars($res['name']).'</div>';
                        echo '<div class="price">';
                        echo '<span class="text">￥'.htmlspecialchars($res['price']).'</span>';
                        echo '<span class="buy">立即购买</span>';
                        echo '</div>';
                        if($res['stock'] > 0){
                            echo '<div class="sale">库存:'.$res['stock'].'份</div>';
                        }
                        echo '</div>';
                        echo '</a>';
                    }
                    echo '</div>';
                }else{
                    echo '<div class="no-result">';
                    echo '<img src="'.$cdnserver.'template/storenews/tupian/no-result.png" alt="无结果">';
                    echo '<p>未找到相关商品</p>';
                    echo '</div>';
                }
            }
            ?>
        </div>
    </div>
</div>

<script src="<?php echo $cdnpublic?>jquery/3.4.1/jquery.min.js"></script>
<script src="<?php echo $cdnpublic?>layui/2.5.7/layui.all.js"></script>
<script>
// 添加返回函数
function goBack() {
    if(document.referrer) {
        window.location.href = document.referrer;
    } else {
        window.location.href = './';
    }
}

$(function(){
    // 搜索功能
    function performSearch() {
        var keyword = $('#searchInput').val().trim();
        if(keyword) {
            // 保存搜索历史
            var history = localStorage.getItem('searchHistory') ? JSON.parse(localStorage.getItem('searchHistory')) : [];
            if(!history.includes(keyword)) {
                history.unshift(keyword);
                if(history.length > 10) history.pop();
                localStorage.setItem('searchHistory', JSON.stringify(history));
            }
            window.location.href = '?mod=search&kw=' + encodeURIComponent(keyword);
        }
    }

    $('#searchInput').on('keyup', function(e) {
        if(e.keyCode === 13) {
            performSearch();
        }
    });

    $('#searchBtn').on('click', function() {
        performSearch();
    });

    // 加载搜索历史
    function loadHistory() {
        var history = localStorage.getItem('searchHistory') ? JSON.parse(localStorage.getItem('searchHistory')) : [];
        var html = '';
        history.forEach(function(item) {
            html += '<li class="history-item" onclick="searchKeyword(\'' + item + '\')">';
            html += '<i class="fa fa-history"></i>';
            html += '<span>' + item + '</span>';
            html += '</li>';
        });
        $('#historyList').html(html);
    }

    // 清空搜索历史
    $('#clearHistory').on('click', function() {
        localStorage.removeItem('searchHistory');
        loadHistory();
    });

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

    loadHistory();
});

function searchKeyword(keyword) {
    $('#searchInput').val(keyword);
    $('#searchBtn').click();
}
</script>
</body>
</html> 