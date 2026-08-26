<?php
if (!defined('IN_CRONLITE')) {
    exit();
}

$title = '订单查询';
include_once TEMPLATE_ROOT . 'hyper/inc/header.php';
?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-none d-md-block">
                    <h4 class="page-title">订单查询</h4>
                </div>
                <?php if ($is_mobile): ?>
                <?php include_once TEMPLATE_ROOT . 'hyper/inc/mobileNav.php';?>
                <?php endif;?>
            </div>
            <div class="col-12 col-lg-5">
                <div class="alert alert-primary text-left" role="alert" <?php if (empty($conf['gg_search'])) {?>style="display:none;"<?php }?>><?php echo $conf['gg_search'] ?></div>
            </div>
            <div class="col-12 col-lg-7">
                <div class="form-group mb-2">
                    <div class="custom-control custom-radio d-inline-block m-1 mb-2">
                        <input type="radio" id="query_default" name="queryType" class="custom-control-input" value="0" checked="">
                        <label class="custom-control-label" for="query_default">下单账号</label>
                    </div>
                    <div class="custom-control custom-radio d-inline-block m-1 mb-2">
                        <input type="radio" id="query_order" name="queryType" class="custom-control-input" value="1">
                        <label class="custom-control-label" for="query_order">订单号</label>
                    </div>
                    <div class="input-group">
                        <input type="text" name="qq" id="qq3" value="" class="form-control pl-2 mb-2 rounded-right" placeholder="输入查询的内容（留空则显示最新订单）" onkeydown="if(event.keyCode==13){$('#submit_query').click()}" required/>
                        <input type="submit" id="submit_query" class="btn btn-primary btn-block" value="立即查询">
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div id="result2" class="form-group" style="display:none;">
                <center class="my-1 d-md-none"><small><font color="#ff0000">下方表单可以左右滑动哦！</font></small></center>
                    <div class="table-responsive">
                        <table class="table table-vcenter table-hover table-condensed table-striped table-sm">
                            <thead><tr><th>详情</th><th>下单账号</th><th>商品名称</th><th>数量</th><th class="hidden-xs">购买时间</th><th>状态</th><th>操作</th></tr></thead>
                            <tbody id="list">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(document).ready(function(){
                $("#submit_query").click();
            });
        </script>
    </div>
<script type="text/javascript">
    var isModal=false;
    var homepage=false;
</script>
<?php include_once TEMPLATE_ROOT . 'hyper/inc/footer.php';?>
    </body>
</html>