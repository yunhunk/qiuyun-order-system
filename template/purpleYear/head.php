<?php
if (!defined('IN_CRONLITE')) {
    exit();
}
//侧边栏自定义内容
?>
<style>
body{ background: linear-gradient(to right,#d00123,#ea875a) fixed;}
.img-avatar{
    -webkit-animation:rotateImg 3s linear infinite;
}
@keyframes rotateImg {
0% {transform : rotate(0deg);}
100% {transform : rotate(360deg);}
}
@-webkit-keyframes rotateImg {
0%{-webkit-transform : rotate(0deg);}
100%{-webkit-transform : rotate(360deg);}
</style>
<style>
.input-group-addon{color:#3a1616;background:linear-gradient(to right,#FFF6B7,#F6416C);border-color:#fbacd4;border-radius:18px;}.input-group-addon{padding:6px 13px;font-size:15px;font-weight:normal;line-height:1;text-align:center;border:1px solid #fbbfa1;}.form-control{color:#f64e72;border:1px solid #fcc0a1;border-radius:18px;box-shadow:none;transition:all 0.15s ease-out;}.form-control{height:34px;padding:6px 15px;font-size:14.5px;line-height:1.42857143;background-color:#fffcf5;background-image:none;}.table{width:100%;max-width:100%;margin-bottom:10px;}
</style>
<style type="text/css">
.col-xs-6{width:33%;}.btn-success{color:#ffffff;background-color:#f317e6;border-color:#f317e6;}.btn-success:hover,.btn-success:active,.btn-success:focus,.btn-success.active,.btn-success.focus{color:#ffffff;background-color:#dc16d0!important;border-color:#dc16d0!important;}.btn-primary{color:#ffffff;background-color:#e61b20;border-color:#e61b20;}.btn-primary:hover,.btn-primary:active,.btn-primary:focus,.btn-primary.active,.btn-primary.focus{color:#ffffff;background-color:#cb161b!important;border-color:#cb161b!important;}.article-card:last-child{margin-bottom:0;}.btn{font-weight:600;border-radius:20px;}.btn-sm,.btn-group-sm > .btn{padding:6px 10px;font-size:12px;line-height:1.5;}.alert{padding-bottom:10px;border-radius:10px;border:none;}.article-card{margin-bottom:15px;border-radius:2px;background-color:#fff;box-shadow:0 1px 2px 0 rgba(0,0,0,.05);}.article-card-header{position:relative;height:42px;line-height:42px;padding:0 15px;border-bottom:0px solid #f6f6f6;color:#333;border-radius:2px 2px 0 0;font-size:14px;}.article-elip,.article-form-checkbox span,.article-form-pane .article-form-label{text-overflow:ellipsis;white-space:nowrap;}.article-body,.article-edge,.article-elip{overflow:hidden;}.article-badge{height:18px;line-height:18px;}.article-badge,.article-badge-dot,.article-badge-rim{position:relative;display:inline-block;padding:0 6px;font-size:12px;text-align:center;background-color:#FF5722;color:#fff;border-radius:2px;}.article-bg-orange{background-color:#FFB800!important;}.article-bg-green{background-color:#009688!important;}.article-bg-gray{background-color:#eee!important;color:#666!important;}.btn-default{color:#fec57d;background-color:rgba(255,255,255,0.15);border-color:rgba(255,255,255,0.15);}
</style>
<style>
.elevator_item {
    position: fixed;
    right: 0;
    bottom: 95px;
    z-index: 11;
}
.elevator_item .feedback {
    width: 36px;
    height: 41px;
    font-size: 12px;
    padding: 5px 6px;
    display: block;
    border-radius: 5px;
    text-align: center;
    margin-top: 10px;
    box-shadow: 0 1px 2px rgba(0,0,0,.35);
    cursor: pointer;
}
.graHover {
    position: relative;
    overflow: hidden;
}
</style>
</head>
<?php if (!empty($conf['template_purpleYear_right'])) {?>
<div class="elevator_item" id="elevator_item" style="display:block;">
<?php echo $conf['template_purpleYear_right'] ?>
</div>
<?php }?>