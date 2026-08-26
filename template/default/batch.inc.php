<?php
/**
 * 批量下单
 */
if (!defined('IN_CRONLITE')) {
    exit();
}
?>
<div class="form-group" id="display_batch" style="display: none">
	<div class="panel panel-default">
		<div class="panel-heading" data-toggle="collapse" href="#collapseBatch">
	        <h3 class="panel-title">批量下单</h3>
	    </div>
		<div class="panel-body collapse" id="collapseBatch">
			<span class="label label-info">格式：<span style="color: #e643cd;margin-right: 3px;" id="batch_label"></span>&nbsp;<br/>&nbsp;&nbsp;多个输入框数据用竖线<span style="color: #e643cd;margin-right: 3px;margin-left: 3px">|</span>隔开，份数前面用-隔开。一行一个订单</span>
			<input type="hidden" id="batch_length" value="1">
			<textarea class="form-control" id="batch_text" rows="5" style="margin-bottom: 15px;margin-top: 15px;" placeholder="18864656586|张三|中国大陆|示例1
17666459796|李四|中国大陆|示例2
17764836498|小明|中国大陆|示例3
"></textarea>
			<a id="submit_batch" class="btn btn-block btn-success">批量加入购物车</a>
		</div>
	</div>
</div>
