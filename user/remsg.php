<?php
/**
 * 分站续费提醒页面
**/
?>
    <div class="col-sm-12 col-md-10 col-lg-9 center-block" style="float: none;padding-top:55px;">
      <div class="block full2 text-center">
			<br>
			<h3 style="color:red">该站点已到期，请先续费!</h3>
			 <small>普及版<?php $conf['ktfz_price']?>，专业版<?php echo $conf['ktfz_price2'];?>元起</small>
			<br>
			<a href="./renew.php" class="btn btn-success btn-block" style="width: 97%;margin:3px auto;">点我去续费</a>
			<br>
    </div>
</div>