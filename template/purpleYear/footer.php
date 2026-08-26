<?php
$friendlink = $conf['template_purpleYear_friendlink'];
if (empty($friendlink)) {
    $friendlink = '<a rel="nofollow" href="/" title="QQ**" target="_blank">QQ**</a>&nbsp;|&nbsp;
<a rel="nofollow" href="/" title="QQ**" target="_blank">QQ**</a>';
}
?>
<?php
//加载插件代码
hook('bottom');
?>
<div class="block panel-body text-center block animated bounceInDown " style="box-shadow:0px 5px 10px 0 rgba(0, 0, 0, 0.25);">
    <b>友情链接：
    <?php echo $friendlink ?>
    </b>
    </div>

<div class="block panel-body text-center block animated bounceInDown" style="box-shadow:0px 5px 10px 0 rgba(0, 0, 0, 0.25);">
  <a href="javascript:void(0);" onclick="AddFavorite('QQ网', location.href)">
        <i class="fa fa-heart text-danger animation-pulse"></i><b>
        <font color="#CB0034">本</font>
        <font color="#BE0041">站</font>
        <font color="#B1004E">网</font>
        <font color="#A4005B">址</font>
        <font color="#970068">：<?php echo $_SERVER['HTTP_HOST']; ?></font>&nbsp;
        <a data-toggle="modal" class="btn btn-xs btn-danger pull-center" href="#disclaimer">免责声明</a>
        </a><br/>
    <?php echo $conf['index_html_bottom'] ?>
        <!--<a href="http://www.miibeian.gov.cn/" target="_blank">黑ICP备20001514号-1</a></b>-->
</div>

<?php
//加载插件代码
hook('footer_after');
?>