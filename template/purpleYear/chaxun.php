<div class="tab-pane" id="search">
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
                    color="#f0a63a">已经在处理中，耐心等待到账！<br></font>
            <font color="#FF6347"><i class="glyphicon glyphicon-time text-danger"></i></font> 已退单：<font
                    color="#9999FF">订单处理失败，联系客服退款！<br></font>
            <font color="#2E8B57"><i class="glyphicon glyphicon-ok-circle text-success"></i></font> 已完成：<font
                    color="#2E8B57">说明提交到服务器，并非到账！<br></font>
            <font color="#FF6347"><i class="glyphicon glyphicon-remove-circle text-danger"></i></font>
            异　常：<font color="#9999FF">说明下单信息错误，后果自负！<br></font>
            <font color="#9999FF"><i class="fa fa-exclamation-circle"></i></font> 注　意：<font
                    color="#FF6347">超过72小时未到账，联系客服!<br></font>
            <font color="blue"><i class=" fa fa-volume-up"></i> 分享网站免费领取10万**</font>  <a href="/?mod=invite" class=" label label-danger"><font color="#FFFFFF">点击查看</font></a><br>
        </li>
        </ul>
        <?php }?>
	<ul class="list-group animated bounceIn">
      <li class="list-group-item">
        <div class="media">
          <span class="pull-left thumb-sm"><img src="//q4.qlogo.cn/headimg_dl?dst_uin=<?php echo $conf['kfqq'] ?>&spec=100" class="img-circle img-thumbnail img-avatar"></span>
          <div class="pull-right push-15-t">
            	  <a href="#customerservice" target="_blank" data-toggle="modal" class="btn btn-sm btn-info">联系客服</a>
        </div>
          <div class="pull-left push-10-t">
            <div class="font-w600 push-5">订单售后QQ客服</div>
            <div class="text-muted">
              	<i class="fa fa-circle text-success"></i>&nbsp;<?php echo !empty($conf['on_line']) ? $conf['on_line'] : '早10:00~晚10:00' ?>
          	</div>
          </div>
        </div>
      </li>
    </ul>
			<div class="form-group">
				<div class="input-group">
					<div class=" input-group-addon">任意下单内容</div>
					<input type="text" name="qq" id="qq3" value="<?php echo $qq ?>" class="form-control" placeholder="请输入要查询的内容（留空则显示最新订单）" onkeydown="if(event.keyCode==13){submit_query.click()}" required/>
					<span class="input-group-btn"><a href="#cxsm" data-toggle="modal" class="btn btn-warning"><i class="glyphicon glyphicon-exclamation-sign"></i></a></span>
				</div>
			</div>
			<input type="submit" id="submit_query" class="btn btn-primary btn-block btn-rounded" style="background: linear-gradient(to right,#FFF6B7,#F6416C);" value="查询订单">
			<div id="result2" class="form-group" style="display:none;">
              <center><small><font color="#ff0000">手机用户可以左右滑动</font></small></center>
				<div class="table-responsive">
					<table class="table table-vcenter table-condensed table-striped">
					<thead><tr><th>下单账号</th><th>商品名称</th><th>数量</th><th class="hidden-xs">购买时间</th><th>状态</th><th>操作</th></tr></thead>
					<tbody id="list">
					</tbody>
					</table>
				</div>
			</div><br/>
   </div>