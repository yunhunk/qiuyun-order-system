<?php
if (!defined('IN_CRONLITE')) {
    exit();
}

$classhide = explode(',', $siterow['class']);
?>
        <div class="list-group media">
          <span class="pull-left thumb-sm"><img
                      src="http://q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq']; ?>&spec=100"
                      alt="..." class="img-circle img-thumbnail img-avatar"></span>
          <div class="pull-right push-15-t">
                <?php if ($conf['kfwx'] && validateData($conf['kfwx'], 'url')): ?>
                 <a href="<?php echo $conf['kfwx']; ?>" target="_blank"
                 class="btn btn-sm btn-info btn-rounded">联系客服</a>
                <?php else: ?>
                 <a onclick="service()" target="_blank"
                 class="btn btn-sm btn-info btn-rounded">联系</a>
                <?php endif;?>
          </div>
          <div class="pull-left push-10-t">
              <div class="font-w600 push-5">订单售后客服</div>
              <div class="text-muted">
                  <script>
                      if (online[0] == 0)
                          document.write("不定时在线回复");
                      else
                          document.write("不定时在线回复");
                  </script>
              </div>
          </div>
      </div>
        <div class=" form-group">
        <?php
if ($conf['gg_search'] != "") {
    echo '<li class="list-group-item">';
    echo $conf['gg_search'];
    echo ' </li>';
} else {
    ?>
          <ul class="list-group animated bounceIn">
          <li class="list-group-item" style="font-size: 12px;">
            <b>如果查到的订单状态显示：</b><br>
            <i class="glyphicon glyphicon-refresh text-info fa-spin"></i> 待处理：<font color="#6699FF">说明订单还未开始，等待处理！</font><br>
            <font color="#f0a63a"><i class="fa fa-spinner text-warning fa-spin"></i></font> 已提交：<font
                    color="#f0a63a">已经在处理中，耐心等待发货完成！<br></font>
            <font color="#FF6347"><i class="glyphicon glyphicon-time text-danger"></i></font> 已退单：<font
                    color="#9999FF">订单处理失败，联系客服退款！<br></font>
            <font color="#2E8B57"><i class="glyphicon glyphicon-ok-circle text-success"></i></font> 已完成：<font
                    color="#2E8B57">说明订单已处理完毕，但可能有延迟等情况！<br></font>
            <font color="#FF6347"><i class="glyphicon glyphicon-remove-circle text-danger"></i></font>
            异　常：<font color="#9999FF">说明下单信息错误，请联系客服处理！<br></font>
            <font color="#9999FF"><i class="fa fa-exclamation-circle"></i></font> 注　意：<font
                    color="#FF6347">超过72小时未到账，联系客服!<br></font>
            <?php if ($conf['invite_open'] == 1): ?>
            <font color="blue"><i class=" fa fa-volume-up"></i> 分享网站免费领取大礼包</font>  <a href="./?mod=invite" class=" label label-danger"><font color="#FFFFFF">点击查看</font></a><br>
            <?php endif;?>

            <?php if ($isLogin2 != 1): ?>
            <font color="blue"><i class=" fa fa-volume-up"></i> 建议登录后下单,订单不丢失</font>  <a href="./user/login.php" class=" label label-danger"><font color="#FFFFFF">立即登录</font></a><br>
            <?php endif;?>
        </li>
        </ul>
        <?php
}?>
        </div>
       <div class=" form-group">
        <div class=" input-group">
          <div class=" input-group-addon">任意下单内容</div>
          <input type="text" name="qq" id="qq3" value="" class=" form-control" placeholder="留空则根据浏览器缓存查询" onkeydown="if(event.keyCode==13){submit_query.click()}" required/>
          <span class=" input-group-btn"><a href="#cxsm" target="_blank" data-toggle="modal" class=" btn btn-warning"><i class=" glyphicon glyphicon-exclamation-sign"></i></a></span>
        </div>
      </div>
      <input type="submit" id="submit_query" class=" btn btn-primary btn-block" value="立即查询">
  <div id="result2" class=" form-group" style="display:none;">
                  <center><small><font color="#ff0000">手机用户可以左右滑动</font></small></center>
                <div class=" table-responsive text-center">
                  <table class=" table table-bordered table-striped table-hover" style="font-size: 13px;">
                    <thead>
                      <tr>
                        <th style="font-size: 12px;">
                          <center>详情</center></th>
                        <th style="font-size: 12px;">
                          <center>下单账号</center></th>
                        <th style="font-size: 12px;">
                          <center>商品名称</center></th>
                        <th style="font-size: 12px;" class="hidden-xs">
                          <center>数量</center></th>
                        <th style="font-size: 12px;" class="hidden-xs">
                          <center>时间</center></th>
                        <th style="font-size: 12px;">
                          <center>状态</center></th>
                        <th style="font-size: 12px;">
                          <center>操作</center></th>
                      </tr>
                    </thead>
                    <tbody id="list"></tbody>
                  </table>
                </div>

 </div>
<br/>
<script type="text/javascript">
var kfqq='<?php echo $conf['kfwx'] ? $conf['kfwx'] : $conf['kfqq'] ?>';
var kfqq2='<?php echo $conf['kfqq2'] ?>';
var kfname='<?php echo $conf['kfname'] ?>';
var kfname2='<?php echo $conf['kfname2'] ?>';
var on_line='<?php echo $conf['on_line'] ?>';
function service() {
    if (kfname == "") {
        kfname = "在线客服";
    }
    var html_kf = '<i class=" fa fa-user-circle-o" style="color:blue"></i>&nbsp;' + kfname + '：' + kfqq + '<a class=" btn btn-info btn-xs" href="http://wpa.qq.com/msgrd?v=3&uin=' + kfqq + '&site=qq&menu=yes">咨询</a>';
    if (kfname == "") {
        kfname2 = "在线客服";
    }
    var html_kf2 = kfqq2 != '' ? '<br><i class=" fa fa-user-circle-o" style="color:blue"></i>&nbsp;' + kfname2 + '：' + kfqq2 + '<a class=" btn btn-info btn-xs" href="http://wpa.qq.com/msgrd?v=3&uin=' + kfqq2 + '&site=qq&menu=yes">咨询</a>' : '';
    if (!on_line) {
        on_line = '早10:30~晚21:30';
    }
    layer.alert('<p style="color:green;font-size:16px;margin:0;padding:0;">' + html_kf + html_kf2 + '</p><p style="color:green;font-size:16px;margin:0;padding:0;"><i class=" fa fa-history" style="color:#d44b39"></i>&nbsp;在线时间：' + on_line + ' </p><p style="color:blue;font-size:14px;margin:0;padding:0;"><i class=" fa fa-exclamation-circle" style="color:red"></i>&nbsp;有问题要先添加好友，否则可能会漏信息！<br/><i class=" fa fa-check-circle" style="color:#30ad65"></i>&nbsp;请发订单编号(网站查单记录的左边)，并描述清楚问题！<br/><i class=" fa fa-check-circle" style="color:#30ad65"></i>&nbsp;回复处理需要时间耐心等待下哈！互相理解哦<center>', {
        title: '联系售后客服',
        btn: '我知道了'
    });
}
$("#qq3").focus(function () {
  layer.tips('输入下单时某个框填写的内容，例如**的填QQ号即可，注意填写完整', this,{tips: 1,time:5000});
});
</script>