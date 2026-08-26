<?php
/**
 * 自助更换域名
 **/
include "../includes/common.php";
if ($isLogin2 == 1) {} else {
    $goto = @getHostUrl();

    exit("<script language='javascript'>window.location.href='./login.php?goto=" . $goto . "';</script>");
}

$title = '自助更换域名';

$fenzhan_siteurl = $conf['fenzhan_siteurl'];
if ($fenzhan_siteurl == "") {
    $fenzhan_siteurl = $conf['fenzhan_domain'];
}

if (daddslashes($_GET['act']) == 'changeurl') {
    $price          = $conf['fenzhan_editd_price'];
    $fenzhan_remain = explode(',', $conf['zz_fenzhan_remain']);
    $qz             = strtolower(input('post.qz', 1));
    $type           = input('post.type', 1);
    $siteurl        = strtolower(input('post.siteurl', 1));

    if (!in_array($siteurl, explode(',', $fenzhan_siteurl))) {
        json_error('该域名后缀不能使用！');
    }

    $siteurl = $qz . '.' . $siteurl;
    if (strlen($qz) < 2 || strlen($qz) > 10 || !preg_match('/^[a-z0-9\-]+$/', $qz)) {
        $result = array('code' => -1, 'msg' => '域名前缀不合格！');
    } elseif ($siteurl == $userrow['siteurl'] || $siteurl == $userrow['siteurl2']) {
        $result = array('code' => -1, 'msg' => '不能和之前绑定的域名重复！');
    } elseif (!preg_match('/^[a-zA-Z0-9\_\-\.]+$/', $siteurl)) {
        $result = array('code' => -1, 'msg' => '域名格式不正确！');
    } elseif ($siteurl == $userrow['siteurl'] || $siteurl == $userrow['siteurl2']) {
        $result = array('code' => -1, 'msg' => '不能和之前的域名一样！');
    } elseif ($DB->get_row("SELECT * FROM pre_site WHERE siteurl= ? or siteurl2= ? limit 1", array($siteurl, $siteurl)) || $qz == 'www' || in_array($siteurl, $fenzhan_remain)) {
        $result = array('code' => -1, 'msg' => '此前缀已被使用！', 'siteurl' => $siteurl);
    } else {
        if ($price > $userrow['money']) {
            $result = array('code' => -1, 'msg' => '你的余额不足，请充值！');
        } else {
            if ($type == 'url1') {
                $DB->query("UPDATE `pre_site` set `siteurl`= ?,`money`=`money`- ? where `zid`= ?", array($siteurl, $price, $userrow['zid']));
            } else {
                $DB->query("UPDATE `pre_site` set `siteurl2`= ?,`money`=`money`- ? where `zid`= ?", array($siteurl, $price, $userrow['zid']));
            }
            addPointLogs($userrow['zid'], $price, '消费', '自助更换域名消费' . $price . '元！', null);
            $result = array('code' => 0, 'msg' => '成功更换域名为' . $siteurl . '，共花费' . $price . '元！');
        }
    }
    exit(json_encode($result));
}

include './head.php';

?>
  <div class="wrapper">
    <div class="col-sm-12 col-md-8 col-lg-6 center-block" style="float: none;">
<?php
if ($userrow['power'] == 0) {
    showmsg('你没有权限使用此功能！', 3);
}

if ($conf['fenzhan_editd_open'] != 1) {
    showmsg('未开启自助更换域名功能', 3);
}

$price = $conf['fenzhan_editd_price'];

$siteurls = explode(',', $conf['fenzhan_domain']);
$select   = '';
foreach ($siteurls as $siteurl) {
    $select .= '<option value="' . $siteurl . '">' . $siteurl . '</option>';
}
if (empty($select)) {
    showmsg('请先到后台分站设置，填写可选分站域名', 3);
}

?>
	  <div class="panel panel-default text-center" id="recharge">
		<div class="panel-heading">
			<h2 class="panel-title">自助更换域名</h2>
		</div>
		<div class="panel-body">
			<div class="form-group">
				<div class="input-group">
					<div class="input-group-addon">
						选择域名
					</div>
                    <select name="type" class="form-control">
                        <option value="url1"><?php echo $userrow['siteurl'] ?></option>
                        <?php if ($userrow['siteurl2']): ?>
                        <option value="url2"><?php echo $userrow['siteurl2'] ?></option>
                        <?php endif;?>
                    </select>
					<!-- <input name="siteurl" class="form-control" value="<?php echo $userrow['siteurl'] ?>" disabled/> -->
				</div>
                <pre>请选择好要修改的域名</pre>
			</div>
			<div class="form-group">
				<div class="input-group">
					<div class="input-group-addon">
						新的域名
					</div>
					<input type="text" id="qz" name="qz"
						   class="form-control" required data-parsley-length="[2,8]"
						   placeholder="输入你想要的二级前缀">
					<select id="siteurl" name="siteurl" class="form-control"><?php echo $select ?></select>
				</div>
			</div>
			<div class="form-group">
				<div class="input-group">
					<div class="input-group-addon">
						更换费用
					</div>
					<input name="need" class="form-control" value="<?php echo $price ?>" disabled/>
					<div class="input-group-addon">
						元
					</div>
				</div>
			</div>
			<div class="form-group">
				<input type="submit" onclick="changeurl()" value="确定更换" class="btn btn-primary form-control"/>
			</div>
		</div>
	</div>
  </div>
</div>
<script type="text/javascript">

function changeurl(){
	var ii = layer.load(1);
	var qz=$("#qz").val();
    var type=$("[name=type]").val();
	var siteurl=$("#siteurl").val();
	$.ajax({
		type : 'POST',
		url  : '?act=changeurl',
		data : "qz="+qz+"&siteurl="+siteurl +"&type="+type,
		dataType : 'json',
		success : function(data) {
		      layer.close(ii);
		      if(data.code==0){
		          layer.alert(data.msg, {
		          	icon:6,
		          	yes:function(){
		          		window.location.reload();
		          	}
		          });
		      }
		      else{
		          layer.alert(data.msg);
		      }
		},
		error:function(data){
		  layer.close(ii);
		  layer.msg('服务器错误，请联系平台站长处理！');
		  return false;
		}
	});
}

$(document).on('change', '#oldurl',function () {

});
</script>
<?php include 'footer.php'?>