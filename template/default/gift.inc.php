<?php
if(!defined('IN_CRONLITE'))exit();
if ($conf['gift_open']==1) {
?>
<div class="panel-body text-center">
	<div id="roll">点击下方按钮开始抽奖</div>
	<hr>
	<p>
	<a class="btn btn-info" id="start" style="display:block;">开始抽奖</a>
	<a class="btn btn-danger" id="stop" style="display:none;">停止</a>
	</p> 
	<div id="result"></div><br/>
	<div class="giftlist" style="display:none;"><strong>最近中奖记录</strong><ul id="pst_1"></ul></div>
</div>
<?php }?>