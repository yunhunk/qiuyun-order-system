<?php
if (!defined('IN_CRONLITE')) {
    exit();
}
//顶部背景UI
if ($conf['template_purpleYear_topui'] == 2) {
    $bg_image = $cdnserver . 'assets/template/purpleYear/img/bg_02.png';
} else {
    $bg_image = $cdnserver . 'assets/template/purpleYear/img/bg_01.png';
}
?>
<div class="block block-link-hover3" style="box-shadow:0 5px 10px 0 rgba(0, 0, 0, 0.25);">
    <div class="block-content block-content-full text-center bg-image" style="background-image: url('<?php echo $bg_image ?>');background-size: 100% 100%;">
        <center>
            <img alt="/user_tx" class="img-avatar img-avatar80 img-avatar-thumb" id="/user_tx" src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq']; ?>&spec=100"/>
            <h3>
                <a href="javascript:void(alert('QQ**网，建议收藏到浏览器书签哦！'));">
                    <b>
                        <font color="#fec57d">
                            <?php echo date("Y"); ?>新年快乐
                        </font>
                    </b>
                </a>
            </h3>
        </center>
        <div class="block-content block-content-mini block-content-full animated zoomInLeft">
            <div class="btn-group btn-group-justified">
                <div class="btn-group">
                    <a class="btn btn-default" data-toggle="modal" href="#anounce2">
                        <font color="#fec57d">
                            <i class="fa fa-bolt">
                            </i>
                            商品通知
                        </font>
                    </a>
                </div>
                <div class="btn-group">
                    <a class="btn btn-default" data-toggle="modal" href="?mod=invite">
                        <font color="#fec57d">
                            <i class="fa fa-exclamation-circle">
                            </i>
                        </font>
                        <span style="font-weight:bold">
                            推广领钻
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <td align="center" style="width: 25%;">
                    <font color="#808080">
                        本站网址
                        <br/>
                        <a href="javascript:void(alert('建议先添加本站收藏到浏览器书签哦！'));" rel="nofollow" style="color:#808080;align:center">
                            <font color="#ff0000">
                                <b>
                                    <?php echo $_SERVER['HTTP_HOST']; ?>
                                </b>
                            </font>
                        </a>
                    </font>
                </td>
                <td align="center" style="width: 25%;">
                    <font color="#808080">
                        售后客服
                        <br/>
                        <a href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo $conf['kfqq'] ?>&site=qq&menu=yes" rel="nofollow" style="color:#ff0000;align:center">
                            <b>
                                客服：点我联系
                            </b>
                        </a>
                    </font>
                </td>
            </tr>
        </thead>
    </table>
</div>