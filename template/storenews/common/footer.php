<?php
if (!defined('IN_CRONLITE')) die();
?>
<link href="<?php echo $cdnpublic ?>font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
<div class="footer footer-bar">
    <ul>
        <li>
            <a href="./" class="nav-item <?php echo $mod===''?'active':''?>">
                <i class="fa fa-home"></i>
                <span>首页</span>
            </a>
        </li>
        <li>
            <a href="./?mod=category" class="nav-item <?php echo $mod==='category'?'active':''?>">
                <i class="fa fa-th-large"></i>
                <span>分类</span>
            </a>
        </li>
        <li>
            <a href="./?mod=query" class="nav-item <?php echo $mod==='query'?'active':''?>">
                <i class="fa fa-file-text-o"></i>
                <span>订单</span>
            </a>
        </li>
        <li>
            <a href="./user/" class="nav-item">
                <i class="fa fa-user-circle-o"></i>
                <span>我的</span>
            </a>
        </li>
    </ul>
</div>

<style>
body .footer.footer-bar {
    position: fixed !important;
    bottom: 0 !important;
    left: 50% !important;
    transform: translateX(-50%) !important;
    width: 100% !important;
    max-width: 650px !important;
    z-index: 999 !important;
    height: 56px !important;
    background: #fff !important;
    display: flex !important;
    align-items: center !important;
    padding-bottom: env(safe-area-inset-bottom) !important;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.05) !important;
    border-top: 1px solid rgba(0,0,0,0.03) !important;
}

body .footer.footer-bar ul {
    display: flex !important;
    width: 100% !important;
    height: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    list-style: none !important;
    align-items: center !important;
    justify-content: space-around !important;
    background: none !important;
}

body .footer.footer-bar ul li {
    flex: 1 !important;
    height: 100% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border: none !important;
    background: none !important;
}

body .footer.footer-bar ul li a {
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: center !important;
    width: 100% !important;
    height: 100% !important;
    color: #999 !important;
    text-decoration: none !important;
    padding: 8px 0 !important;
    border: none !important;
    background: none !important;
    transition: all 0.3s ease !important;
}

body .footer.footer-bar ul li a.active {
    color: #ff4d4f !important;
    background: none !important;
}

body .footer.footer-bar ul li a.active i {
    color: #ff4d4f !important;
    transform: translateY(-2px) scale(1.1) !important;
}

body .footer.footer-bar ul li i {
    display: block !important;
    font-size: 22px !important;
    height: 22px !important;
    line-height: 22px !important;
    margin-bottom: 3px !important;
    transition: all 0.3s ease !important;
}

body .footer.footer-bar ul li span {
    display: block !important;
    font-size: 12px !important;
    height: 16px !important;
    line-height: 16px !important;
    transition: all 0.3s ease !important;
}

body .footer.footer-bar ul li a:hover {
    color: #ff4d4f !important;
}

body .footer.footer-bar ul li a:hover i {
    transform: translateY(-2px) scale(1.1) !important;
}

@media (max-width: 360px) {
    body .footer.footer-bar {
        height: 50px !important;
    }
    body .footer.footer-bar ul li i {
        font-size: 20px !important;
        height: 20px !important;
        line-height: 20px !important;
        margin-bottom: 2px !important;
    }
    body .footer.footer-bar ul li span {
        font-size: 11px !important;
        height: 14px !important;
        line-height: 14px !important;
    }
}

body .fui-page .fui-content,
body .fui-page-group .fui-content {
    padding-bottom: calc(60px + env(safe-area-inset-bottom)) !important;
}
</style> 