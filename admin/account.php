 <?php
# 员工列表 By 秋云
include '../includes/common.php';
checkLogin();

checkAuthority('super');

$title = '员工列表';

$my = isset($_GET['my']) ? $_GET['my'] : null;
if ($my == 'del') {
    $aid = intval($_POST['aid']);
    $sql = "delete from `pre_admin` where aid='" . $aid . "'";
    if ($DB->query($sql)) {
        $result = array('code' => 0, 'msg' => "删除员工成功！");
    } else {
        $result = array('code' => -1, 'msg' => '删除员工失败，' . $DB->error());
    }
    exit(json_encode($result));
}

checkFileSize();

function display_zt($aid, $zt)
{
    if ($zt == 1) {
        return '<span onclick="setActive(' . $aid . ',0)" title="点击封禁员工" class="btn btn-success btn-xs">正常</span>';
    } else {
        return '<span onclick="setActive(' . $aid . ',1)" title="点击开启员工" class="btn btn-danger btn-xs">封禁</span>';
    }
}

include_once "head.php";

echo '
  <div class="col-sm-12 col-md-12 center-block" style="float: none;padding-top:10px ">';

if ($my == 'add_submit') {
    $user = daddslashes($_POST['user']);
    $pwd  = daddslashes($_POST['pwd']);
    $qq   = daddslashes($_POST['qq']);
    if (!preg_match('/^[a-zA-Z0-9\.\+\-\@]{6,12}$/', $user)) {
        showmsg('员工账户名仅支持由[字母数字下划线小数点+-@]组成的6~12字符！', 4);
    } elseif (!preg_match('/^[a-zA-Z0-9\.\+\-\@]{6,16}$/', $pwd)) {
        showmsg('员工密码仅支持由[字母数字下划线小数点+-@]组成的6~16字符！', 4);
    } elseif (!preg_match('/^[1-9]{1}[0-9]{5,11}$/', $qq)) {
        showmsg('QQ号码格式不正确，仅支持5~12位的QQ号码！', 4);
    }
    $authority = "";
    foreach ($_POST as $key => $value) {
        if (stripos($key, 'auth_') !== false && $value == 1) {
            $arr = explode('_', $key);
            $authority .= $arr[1] . '|';
        }
    }

    $authority = trim($authority, '|');
    $data      = [':user' => $user, ':pwd' => $pwd, ':qq' => $qq, ':authority' => $authority, ':addtime' => $date];
    $sql       = "INSERT INTO `pre_admin` (`user`,`pwd`,`qq`,`authority`,`status`,`addtime`) values (:user,:pwd,:qq,:authority,'1',:addtime)";
    if ($aid = $DB->insert($sql, $data)) {
        showmsg('添加员工成功！<br/><br/><a href="./account.php">>>返回员工列表</a>', 0);
    } else {
        showmsg('添加员工失败！' . $DB->error(), 4);
    }

} elseif ($my == 'edit_submit') {
    $aid  = intval($_POST['account_id']);
    $user = daddslashes($_POST['user']);
    $pwd  = daddslashes($_POST['pwd']);
    $qq   = daddslashes($_POST['qq']);
    if (!preg_match('/^[a-zA-Z0-9\.\+\-\@]{6,12}$/', $user)) {
        showmsg('员工账户名仅支持由[字母数字下划线小数点+-@]组成的6~12字符！', 4);
    } elseif (!empty($pwd) && !preg_match('/^[a-zA-Z0-9\.\+\-\@]{6,16}$/', $pwd)) {
        showmsg('员工密码仅支持由[字母数字下划线小数点+-@]组成的6~16字符！', 4);
    } elseif (!preg_match('/^[1-9]{1}[0-9]{5,11}$/', $qq)) {
        showmsg('QQ号码格式不正确，仅支持5~12位的QQ号码！', 4);
    }
    $authority = "";
    foreach ($_POST as $key => $value) {
        if (stripos($key, 'auth_') !== false && $value == 1) {
            $arr = explode('_', $key);
            $authority .= $arr[1] . '|';
        }
    }

    $authority = trim($authority, '|');
    $data      = [':user' => $user, ':qq' => $qq, ':authority' => $authority, ':aid' => $aid];

    $sql = "UPDATE `pre_admin` SET `user`=:user,`qq`=:qq,`authority`=:authority WHERE aid=:aid";
    if ($DB->query($sql, $data)) {
        if (!empty($pwd)) {
            $DB->query("UPDATE `pre_admin` SET `pwd` = :pwd WHERE aid=:aid", [':pwd' => $pwd, ':aid' => $aid]);
        }
        showmsg('修改员工成功！<br/><br/><a href="./account.php">>>返回员工列表</a>', 0);
    } else {
        showmsg('修改员工失败！' . $DB->error(), 4);
    }

} elseif ($my == 'add') {
    echo '<div class="block">
<div class="block-title"><h3 class="panel-title">添加一个员工</h3></div><div class=""><form action="?my=add_submit" method="POST">
<div class="form-group">
<label id="user">登录名:</label><br>
<input type="text" class="form-control" name="user" value="" required>
</div>
<div class="form-group">
<label id="pwd">登录密码:</label><br>
<input type="text" class="form-control" name="pwd" value="" required>
</div>
<div class="form-group">
<label id="qq">联系QQ:</label><br>
<input type="text" class="form-control" name="qq" value="" required>
</div>
<div class="form-group">
<label id="authority">权限设置:</label><br>
<input class="inp-cmckb-xs" name="auth_orders" id="orders" type="checkbox" onclick="setVal(this)" value="0" style="display: none;"/>
	<label class="cmckb-xs" for="orders"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>订单管理</span></label>
	<input class="inp-cmckb-xs" name="auth_shops" id="shops" type="checkbox" onclick="setVal(this)" value="0" style="display: none;"/>
	<label class="cmckb-xs" for="shops"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>商品管理</span></label>
	<input class="inp-cmckb-xs" name="auth_class" id="class" type="checkbox" onclick="setVal(this)" value="0" style="display: none;"/>
	<label class="cmckb-xs" for="class"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>分类管理</span></label>
	<input class="inp-cmckb-xs" name="auth_shequs" id="shequs" type="checkbox" onclick="setVal(this)" value="0" style="display: none;"/>
	<label class="cmckb-xs" for="shequs"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>社区管理</span></label>
	<input class="inp-cmckb-xs" name="auth_prices" id="prices" type="checkbox" onclick="setVal(this)" value="0" style="display: none;"/>
	<label class="cmckb-xs" for="prices"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>加价模板</span></label>
	<input class="inp-cmckb-xs" name="auth_fakas" id="fakas" type="checkbox" onclick="setVal(this)" value="0" style="display: none;"/>
	<label class="cmckb-xs" for="fakas"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>**管理</span></label>
	<br/>
	<input class="inp-cmckb-xs" name="auth_fenzhan" id="fenzhan" type="checkbox" onclick="setVal(this)" value="0" style="display: none;"/>
	<label class="cmckb-xs" for="fenzhan"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>分站管理</span></label>
	<input class="inp-cmckb-xs" name="auth_users" id="users" type="checkbox" onclick="setVal(this)" value="0" style="display: none;"/>
	<label class="cmckb-xs" for="users"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>用户管理</span></label>
	<input class="inp-cmckb-xs" name="auth_works" id="works" type="checkbox" onclick="setVal(this)" value="0" style="display: none;"/>
	<label class="cmckb-xs" for="works"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>工单管理</span></label>
	<input class="inp-cmckb-xs" name="auth_tixian" id="tixian" type="checkbox" onclick="setVal(this)" value="0" style="display: none;"/>
	<label class="cmckb-xs" for="tixian"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>提现管理</span></label>
	<input class="inp-cmckb-xs" name="auth_message" id="message" type="checkbox" onclick="setVal(this)" value="0" style="display: none;"/>
	<label class="cmckb-xs" for="message"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>文章管理</span></label>
	  <pre>员工只能访问这里的已设置权限页面和后台首页，安全考虑其他页面和核心功能均无法访问！后期会根据需求更新增加不同权限分配</pre>
</div>
<!--SVG Sprites-->
<svg class="inline-svg">
  <symbol id="check" viewbox="0 0 12 10">
    <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
  </symbol>
</svg>
<input type="submit" class="btn btn-primary btn-block" value="确定添加"></form><br/><a href="./account.php">>>返回员工列表</a></div>
</div>    </div>
  </div>
</div>
<<script>
function setVal(that){
	if(that.value==\'1\'){
		that.value=\'0\';
	}else{
		that.value=\'1\';
	};
}
</script>
';
} elseif ($my == 'edit') {
    $aid = intval($_GET['aid']);
    $row = $DB->get_row("select * from pre_admin where aid= ? limit 1", [$aid]);
    if (!$row) {
        showmsg('该员工账户不存在或已被删除<br/><br/><a href="./account.php">>>返回员工列表</a>', 4);
    }
    $authority = explode('|', $row['authority']);
    echo '<div class="block">
<div class="block-title"><h3 class="panel-title">编辑员工</h3></div><div class=""><form action="?my=edit_submit" method="POST">
<input type="text" class="hide" name="account_id" value="' . $row['aid'] . '" required>
<div class="form-group">
<label id="shequ_name">登录名:</label><br>
<input type="text" class="form-control" name="user" value="' . $row['user'] . '" required>
</div>
<div class="form-group">
<label id="shequ_url">登录密码:</label><br>
<input type="text" class="form-control" name="pwd" value="' . $row['pwd'] . '" placeholder="不修改请留空">
</div>
<div class="form-group">
<label id="username">联系QQ:</label><br>
<input type="text" class="form-control" name="qq" value="' . $row['qq'] . '" required>
</div>
<div class="form-group">
<label>权限设置:</label><br>
<input class="inp-cmckb-xs" name="auth_orders" id="orders" type="checkbox" onclick="setVal(this)" ';
    echo in_array('orders', $authority) ? 'checked="checked" value="1"' : 'value="0"';
    echo ' style="display: none;"/>
	<label class="cmckb-xs" for="orders"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>订单管理</span></label>
	<input class="inp-cmckb-xs" name="auth_shops" id="shops" type="checkbox" onclick="setVal(this)" ';
    echo in_array('shops', $authority) ? 'checked="checked" value="1"' : 'value="0"';
    echo ' style="display: none;"/>
	<label class="cmckb-xs" for="shops"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>商品管理</span></label>
	<input class="inp-cmckb-xs" name="auth_class" id="class" type="checkbox" onclick="setVal(this)" ';
    echo in_array('class', $authority) ? 'checked="checked" value="1"' : 'value="0"';
    echo ' style="display: none;"/>
	<label class="cmckb-xs" for="class"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>分类管理</span></label>
	<input class="inp-cmckb-xs" name="auth_prices" id="prices" type="checkbox" onclick="setVal(this)"  ';
    echo in_array('prices', $authority) ? 'checked="checked" value="1"' : 'value="0"';
    echo ' style="display: none;"/>
	<label class="cmckb-xs" for="prices"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>加价模板</span></label>
	<input class="inp-cmckb-xs" name="auth_shequs" id="shequs" type="checkbox" onclick="setVal(this)" ';
    echo in_array('shequs', $authority) ? 'checked="checked" value="1"' : 'value="0"';
    echo ' style="display: none;"/>
	<label class="cmckb-xs" for="shequs"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>社区管理</span></label>
	<input class="inp-cmckb-xs" name="auth_fakas" id="fakas" type="checkbox" onclick="setVal(this)" ';
    echo in_array('fakas', $authority) ? 'checked="checked" value="1"' : 'value="0"';
    echo ' style="display: none;"/>
	<label class="cmckb-xs" for="fakas"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>**管理</span></label>
	<br/>
	<input class="inp-cmckb-xs" name="auth_fenzhan" id="fenzhan" type="checkbox" onclick="setVal(this)" ';
    echo in_array('fenzhan', $authority) ? 'checked="checked" value="1"' : 'value="0"';
    echo ' style="display: none;"/>
	<label class="cmckb-xs" for="fenzhan"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>分站管理</span></label>
	<input class="inp-cmckb-xs" name="auth_users" id="users" type="checkbox" onclick="setVal(this)" ';
    echo in_array('users', $authority) ? 'checked="checked" value="1"' : 'value="0"';
    echo ' style="display: none;"/>
	<label class="cmckb-xs" for="users"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>用户管理</span></label>
	<input class="inp-cmckb-xs" name="auth_works" id="works" type="checkbox" onclick="setVal(this)" ';
    echo in_array('works', $authority) ? 'checked="checked" value="1"' : 'value="0"';
    echo ' style="display: none;"/>
	<label class="cmckb-xs" for="works"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>工单管理</span></label>
	<input class="inp-cmckb-xs" name="auth_tixian" id="tixian" type="checkbox" onclick="setVal(this)" ';
    echo in_array('tixian', $authority) ? 'checked="checked" value="1"' : 'value="0"';
    echo ' style="display: none;"/>
	<label class="cmckb-xs" for="tixian"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>提现管理</span></label>
	<input class="inp-cmckb-xs" name="auth_message" id="message" type="checkbox" onclick="setVal(this)" ';
    echo in_array('message', $authority) ? 'checked="checked" value="1"' : 'value="0"';
    echo ' style="display: none;"/>
	<label class="cmckb-xs" for="message"><span>
	  <svg width="12px" height="10px">
	    <use xlink:href="#check"></use>
	  </svg>
	  </span><span>文章管理</span></label>
	  <pre>员工只能访问这里的已设置权限页面和后台首页，安全考虑其他页面和核心功能均无法访问！后期会根据需求更新增加不同权限分配</pre>
</div>
<!--SVG Sprites-->
<svg class="inline-svg">
  <symbol id="check" viewbox="0 0 12 10">
    <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
  </symbol>
</svg>
<input type="submit" class="btn btn-primary btn-block" value="确定修改"></form><br/><a href="./account.php">>>返回员工列表</a></div>
</div>    </div>
  </div>
</div>
<<script>
function setVal(that){
	if(that.value==\'1\'){
		that.value=\'0\';
	}else{
		that.value=\'1\';
	};
}
</script>
';

} else {
    $numrows = $DB->count("SELECT count(*) FROM pre_admin");
    echo '<div class="block">
<div class="block-title clearfix">
<h2>系统共有 <b>' . $numrows . '</b> 个员工</h2>
</div>
     <a href="?my=add" class="btn btn-primary"><i class="fa fa-plus"></i>&nbsp;添加员工</a>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>Aid</th><th>用户名</th><th>联系QQ</th><th>状态</th><th>操作</th></tr></thead>
          <tbody>   ';

    $rs = $DB->query("SELECT * FROM pre_admin WHERE 1 order by aid desc");
    while ($res = $DB->fetch($rs)) {
        echo '<tr id="tr_' . $res['aid'] . '"><td><b>' . $res['aid'] . '</b></td><td>' . $res['user'] . '</td><td>' . $res['qq'] . '</td><td>' . display_zt($res['aid'], $res['status']) . '</td><td><a href="?my=edit&aid=' . $res['aid'] . '" class="btn btn-info btn-xs">编辑</a>&nbsp;<a class="btn btn-xs btn-danger" onclick="if(confirm(\'你确实要删除此员工吗？\')){Delete(' . $res['aid'] . ');};">删除</a></td></tr>';
    }

    echo "          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>";
}

echo '<script>
function setActive(aid,status) {
    $.ajax({
        type : \'GET\',
        url : \'ajax.php?act=setAccount&aid=\'+aid+\'&status=\'+status,
        dataType : \'json\',
        success : function(data) {
            layer.msg(\'状态切换成功\');
            window.location.reload();
        },
        error:function(data){
            layer.msg(\'服务器错误\');
            return false;
        }
    });
}
function Delete(aid){
    var ii = layer.load(2, {shade:[0.1,\'#fff\']});
    $.ajax({
		type : "POST",
		url : "?my=del",
		data : {aid:aid},
		dataType : "json",
		success : function(data) {
			layer.close(ii);
			if(data.code == 0){
				layer.msg("删除成功");
				$(\'#tr_\'+aid).fadeOut();
			}else{
				layer.alert(data.msg);
			}
		}
	});
}

</script>';
include_once 'footer.php';
