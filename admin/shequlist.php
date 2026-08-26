 <?php
/**
 * 货源对接 By 秋云
 */
include '../includes/common.php';
checkLogin();

$title = '对接社区管理';

checkAuthority('shequs');

$my = isset($_GET['my']) ? $_GET['my'] : null;
if ($my == 'del') {
    $id  = intval(getParams('id', true));
    $sql = "delete from `pre_shequ` where id= ?";
    if ($DB->query($sql, [$id])) {
        $result = array('code' => 0, 'msg' => "删除成功！");
    } else {
        $result = array('code' => -1, 'msg' => '删除失败！[error]' . $DB->error());
    }
    exit(json_encode($result));
}

checkFileSize();

include_once "head.php";

//<option value="23">橙子平台</option><option value="24">挂客宝平台</option>
$select_type = '';

$path  = ROOT . 'includes/core/extend/';
$files = scandir($path);
if (is_array($files)) {
    foreach ($files as $key => $file) {
        if ($file != "." && $file != ".." && !strpos($file, ".")) {
            $name = ucfirst($file);
            if (is_file($path . $name . '/config.php')) {
                $config = include $path . $name . '/config.php';
                $select_type .= '<option value="' . $config['type'] . '" alias="' . $config['alias'] . '" extend="1" cron="' . $config['cron'] . '" tips=\'' . $config['tips'] . '\' paypwd=\'' . json_encode($config['paypwd']) . '\' username=\'' . json_encode($config['username']) . '\' password=\'' . json_encode($config['password']) . '\'>' . $config['name'] . '（插件）</option>';
            }
        }
    }
}

//$select_type = '<option value="22">商战网</option><option value="26">好方网</option><option value="21">时空云社区</option><option value="13">同系统对接</option><option value="1">亿樂系统</option><option value="0" paytype="1">玖伍系统</option><option value="6" paypwd="1">卡易信</option><option value="9">卡商网</option><option value="18">卡卡云系统</option><option value="25">直客SUP</option><option value="7" paypwd="1">卡乐购</option><option value="12">彩虹系统</option><option value="15">优云宝（支持新版和旧版）</option><option value="16">视多系统</option><option value="4">九流社区</option>';

$select_type .= '<option value="7" paypwd="1">卡乐购</option><option value="16">视多系统</option><option value="4">九流社区</option>';

echo '
  <div class="col-sm-12 col-md-12 center-block" style="float: none;padding-top:10px ">';

if ($my == 'add_submit') {
    $url         = input('post.url', 1);
    $type        = input('post.type', 1);
    $name        = input('post.name', 1);
    $username    = input('post.username', 1);
    $password    = input('post.password', 1);
    $paypwd      = input('post.paypwd', 1);
    $alias       = input('post.alias', 1);
    $paytype     = input('post.paytype', 1);
    $proxy       = intval(input('post.proxy', 1));
    $crontime    = intval(input('post.crontime', 1));
    $orderstatus = intval(input('post.orderstatus', 1));
    if ($paypwd == "" && $type == 6) {
        showmsg('该对接类型下支付密码不能为空！', 4);
    } elseif (!preg_match('/^(https|http):\/\/[\w\.\-]+\.[\w\:\/]+\/$/', $url)) {
        showmsg('域名格式不正确，请选择如下其中一个格式填写：<br/>格式一：http(s)://xxx.xxx.cn/<br/>格式二：http(s)://xxx.xxx.cn:888/<br/>格式三：http(s)://xxx.xxx.cn:888/buy/<br/>注意：尾部的斜杠一定要加上！', 4);
    }
    $url = rtrim($url, '/') . '/';

    if ($password != "" && strlen($password) < 128) {
        $password = strSafeEnCode($password, "ENCODE");
    }

    if ($paypwd != "" && strlen($paypwd) < 128) {
        $paypwd = strSafeEnCode($paypwd, "ENCODE");
    }

    $sql = "INSERT into `pre_shequ` (`type`,`url`,`name`,`username`,`password`,`paypwd`,`alias`,`paytype`,`proxy`,`orderstatus`,`crontime`,`status`) values ('" . $type . "','" . $url . "','" . $name . "','" . $username . "','" . $password . "','" . $paypwd . "','" . $alias . "','" . $paytype . "','" . $proxy . "','" . $orderstatus . "','" . $crontime . "','1')";
    if ($id = $DB->insert($sql)) {
        showmsg('添加新对接站点成功！<br/><br/><a href="./shequlist.php?my=edit&id=' . $id . '">>>编辑此对接站</a><br/><br/><a href="./shequlist.php">>>返回社区列表</a>', 0);
    } else {
        showmsg('添加新对接站点失败！' . $DB->error(), 4);
    }

} elseif ($my == 'edit_submit') {
    $id  = intval(input('post.shequ_id', 1));
    $row = $DB->get_row("SELECT * from cmy_shequ where id= ? limit 1", [$id]);
    if (!$row) {
        showmsg('该对接社区不存在或已被删除<br/><br/><a href="./shequlist.php">>>返回社区列表</a>', 4);
    }
    $url         = input('post.url', 1);
    $type        = input('post.type', 1);
    $name        = input('post.name', 1);
    $username    = input('post.username', 1);
    $password    = input('post.password', 1);
    $paypwd      = input('post.paypwd', 1);
    $alias       = input('post.alias', 1);
    $paytype     = input('post.paytype', 1);
    $proxy       = intval(input('post.proxy', 1));
    $crontime    = intval(input('post.crontime', 1));
    $orderstatus = intval(input('post.orderstatus', 1));

    if ($paypwd == "" && $type == 6) {
        showmsg('该对接类型下支付密码不能为空！', 4);
    } elseif (!preg_match('/^(https|http):\/\/[\w\.\-]+\.[\w\:\/]+\/$/', $url)) {
        showmsg('域名格式不正确，请选择如下其中一个格式填写：<br/>格式一：http(s)://xxx.xxx.cn/<br/>格式二：http(s)://xxx.xxx.cn:888/<br/>格式三：http(s)://xxx.xxx.cn:888/buy/<br/>注意：尾部的斜杠一定要加上！', 4);
    }

    $url = rtrim($url, '/') . '/';

    if ($password != "" && strlen($password) < 128) {
        $password = strSafeEnCode($password, "ENCODE");
    }

    if ($paypwd != "" && strlen($paypwd) < 128) {
        $paypwd = strSafeEnCode($paypwd, "ENCODE");
    }

    $sql = "UPDATE `pre_shequ` SET url='{$url}',name='{$name}',type='{$type}',username='{$username}',password='{$password}',paypwd='{$paypwd}',`alias`='{$alias}',paytype='{$paytype}',proxy='{$proxy}',orderstatus='{$orderstatus}',`crontime`='{$crontime}' WHERE id='" . $id . "'";
    if ($DB->query($sql)) {
        showmsg('修改对接社区成功！<br/><br/><a href="./shequlist.php?my=edit&id=' . $id . '">>>编辑此对接站</a><br/><br/><a href="./shequlist.php">>>返回社区列表</a>', 0);
    } else {
        showmsg('修改对接社区 ' . $url . ' 失败！' . $DB->error(), 4);
    }
} elseif ($my == 'add') {
    echo '<div class="block">
<div class="block-title"><h3 class="panel-title">添加一个货源站对接</h3></div><div class=""><form action="./shequlist.php?my=add_submit" method="POST">
<div class="alert alert-info">
说明1：如彩虹自助下单系统站点需要对接本站，对方添加时网站类型选【同系统】即可<br>
说明2：如需要对接彩虹或祥云系统，网站类型选【彩虹系统】即可<br>
<span style="color:red">注意：如使用第三方未核查的插件时，如有被黑情况后果自负！<a href="https://www.kancloud.cn/superkinger/chenm_webmall/2979386" target="_blank">对接插件开发教程</a></span>
</div>
<input type="hidden" name="alias" id="alias" value="">
<div class="form-group">
<label>网站类型:</label><br>
<div class="input-group">
<select class="form-control" name="type" onchange="setShequInfo()" default="0">';
    echo $select_type;
    echo '
    </select>
<a class="input-group-addon" onclick="showAlert()"><i class="fa fa-question-circle"></i></a>
</div>
<pre id="shequtips" style="display:none"></pre>
</div>
<div class="form-group">
<label id="shequ_name">网站名称:</label><br>
<input type="text" class="form-control" name="name" value="" required>
</div>
<div class="form-group">
    <label id="shequ_url">网站域名:</label><br>
    <div class="input-group">
        <input type="text" class="form-control" name="url" value="" required>
        <a class="input-group-addon urlAlert"><i class="fa fa-question-circle"></i></a>
    </div>
    <small>填完整地址，如：http://xx.domain.cn/&nbsp;&nbsp;&nbsp;&nbsp;支持https和http两种开头</small>
</div>
<div class="form-group">
<label id="username">登录账号:</label><br>
<input type="text" class="form-control" name="username" value="" required>
</div>
<div class="form-group">
<label id="password">登录密码:</label><br>
<input type="text" class="form-control" name="password" value="" required>
</div>
<div class="form-group" id="paypwd" style="display:none;">
<label>支付密码:</label><br>
<input type="text" class="form-control" name="paypwd" value="" placeholder="没有请留空">
</div>
<div class="form-group">
<label>商品同步间隔时间:</label><br>
<input type="text" class="form-control" name="crontime" value="120" placeholder="监控检测间隔时间">
<small>默认120秒。用于对接商品监控同步，对接数量越多时间应越大来保证能同步完！例如对接100个商品，间隔60秒；对接500个商品，间隔200秒；</small>
</div>
<div class="form-group" id="paytype" style="display:none;">
<label>支付方式:</label><br>
<select class="form-control" name="paytype"><option value="0">点数</option><option value="1" selected>余额</option></select>
</div>
<div class="form-group">
<label>代理服务器:</label><br>
<select class="form-control" name="proxy" default="0"><option value="0">0_关闭</option><option value="1">1_启用</option></select>
<pre class="">当使用国外服务器且该货源站拦截国外时，请选择开启&nbsp;<a href="./set.php?mod=proxy">代理服务器配置</a></pre>
</div>
<div class="form-group hide">
<label>是否SSL协议(https):</label><br>
<select class="form-control" name="ssl" default="0"><option value="0">0_否</option><option value="1">1_是</option></select>
<pre class="">如果该平台需要https模式访问(又叫SSL模式)才能正常对接，请选择是，不需要请选选择否</pre>
</div>
<div class="form-group" id="orderstatus">
<label>对接后动作:</label><br>
<select class="form-control" name="orderstatus"><option value="1">已完成</option><option value="2" selected>进行中</option></select>
<pre id="status_tips" style="display:none"></pre>
</div>
<!--input type="button" class="btn btn-default btn-block" onclick="checkurl()" value="检测目标网站连通性"-->
<input type="submit" class="btn btn-primary btn-block" value="确定添加"></form><br/><a href="./shequlist.php">>>返回对接列表</a></div>
<div class="panel-footer">
<span class="glyphicon glyphicon-info-sign"></span>
社区类型是指该社区使用的网站程序系统类型，并不代表具体的某个社区网站！
</div>
</div>    </div>
  </div>
</div>';

} elseif ($my == 'edit') {
    $id  = intval(input('get.id'));
    $row = $DB->get_row("SELECT * from cmy_shequ where id= ? limit 1", [$id]);
    if (!$row) {
        showmsg('该对接社区不存在或已被删除<br/><br/><a href="./shequlist.php">>>返回社区列表</a>', 4);
    }

    if ($row['type'] == 26) {
        $row['url'] = 'www.wsjinhuo.com';
    }

    if (!preg_match('/^(https|http):\/\/[\w\.\-]+\.[\w\:\/]+\/$/', $row["url"])) {
        preg_match('/[\w\.\-]+\.[\w\:]+/', $row["url"], $arr);
        if ($row["ssl"] == 1) {
            $row["url"] = 'https://' . $arr[0] . '/';
        } else {
            $row["url"] = 'http://' . $arr[0] . '/';
        }
    }

    echo '<div class="block">
<div class="block-title"><h3 class="panel-title">修改货源站对接</h3></div><div class=""><form action="./shequlist.php?my=edit_submit" method="POST">

<div class="alert alert-info">
说明1：如彩虹自助下单系统站点需要对接本站，对方添加时网站类型选【同系统】即可<br>
说明2：如需要对接彩虹或祥云系统，网站类型选【彩虹系统】即可
</div>
<input type="hidden" name="shequ_id" id="shequ_id" value="';
    echo $id;
    echo '">
<input type="hidden" name="alias" id="alias" value="">
<div class="form-group">
<label>网站类型:</label><br>
<div class="input-group">
<select class="form-control" name="type" onchange="setShequInfo()" default="';
    echo $row['type'];
    echo '">';
    echo $select_type;
    echo '
    </select>
<a class="input-group-addon" onclick="showAlert()"><i class="fa fa-question-circle"></i></a>
</div>
<pre id="shequtips" style="display:none"></pre>
</div>
<div class="form-group">
<label id="shequ_name">网站名称:</label><br>
<input type="text" class="form-control" name="name" value="';
    echo $row['name'];
    echo '" required>
</div>
<div class="form-group">
    <label id="shequ_url">网站域名:</label><br>
    <div class="input-group">
        <input type="text" class="form-control" name="url" value="';
    echo $row['url'];
    echo '" required>
        <a class="input-group-addon urlAlert"><i class="fa fa-question-circle"></i></a>
    </div>
    <small>填完整地址，如：http://xx.domain.cn/&nbsp;&nbsp;&nbsp;&nbsp;支持https和http两种开头</small>
</div>
<div class="form-group">
<label id="username">登录账号:</label><br>
<input type="text" class="form-control" name="username" value="';
    echo $row['username'];
    echo '" required>
</div>
<div class="form-group">
<label id="password">登录密码:</label><br>
    <div class="input-group">
        <input type="text" class="form-control" name="password" value="';
    echo $row['password'];
    echo '" required>
        <span class="input-group-addon" onclick="checkPwd(\'' . $row['password'] . '\')">验证密码</span>
    </div>
    <pre style="color:red">注意：为提高货源资料安全性，此处信息已被加密。修改时请填写正确的提交即可</pre>
</div>
<div class="form-group" id="paypwd" style="display:none;">
    <label>支付密码:</label><br>
    <div class="input-group">
        <input type="text" class="form-control" name="paypwd" value="';
    echo $row['paypwd'];
    echo '" placeholder="没有请留空">
        <span class="input-group-addon" onclick="checkPwd(\'' . $row['paypwd'] . '\')">验证密码</span>
    </div>
    <pre style="color:red">注意：为提高货源资料安全性，此处信息已被加密。修改时请填写正确的提交即可</pre>
</div>
<div class="form-group">
    <label>商品同步间隔时间:</label><br>
    <input type="text" class="form-control" name="crontime" value="';
    echo $row['crontime'];
    echo '" placeholder="商品同步间隔时间">
    <small>默认120秒。用于对接商品监控同步，对接数量越多时间应越大来保证能同步完！例如对接100个商品，间隔60秒；对接500个商品，间隔200秒；</small>
</div>
<div class="form-group" id="paytype" style="display:none;">
<label>支付方式:</label><br>
<select class="form-control" name="paytype"><option value="0">点数</option><option value="1" selected>余额</option></select>
</div>
<div class="form-group">
<label>代理服务器:</label><br>
<select class="form-control" name="proxy" default="';
    echo $row['proxy'];
    echo '"><option value="0">0_关闭</option><option value="1">1_启用</option></select>
<pre class="">当使用国外服务器且该货源站拦截国外时，请选择开启&nbsp;<a href="./set.php?mod=proxy">代理服务器配置</a></pre>
</div>
<div class="form-group hide">
<label>是否SSL协议(https):</label><br>
<select class="form-control" name="ssl" default="';
    echo $row['ssl'];
    echo '"><option value="0">0_否</option><option value="1">1_是</option></select>
<pre class="">如果该平台需要https模式访问(又叫SSL模式)才能正常对接，请选择是，不需要请选选择否</pre>
</div>
<div class="form-group" id="orderstatus">
<label>对接后动作:</label><br>
<select class="form-control" name="orderstatus" default="';
    echo $row['orderstatus'];
    echo '"><option value="1">已完成</option><option value="2" selected>进行中</option></select>
<pre id="status_tips" style="display:none"></pre>
</div>
<!--input type="button" class="btn btn-default btn-block" onclick="checkurl()" value="检测目标网站连通性"-->
<input type="submit" class="btn btn-primary btn-block" value="确定修改"></form><br/><a href="./shequlist.php">>>返回对接列表</a></div>
<div class="panel-footer">
<span class="glyphicon glyphicon-info-sign"></span>
社区类型是指该社区使用的网站程序系统类型，并不代表具体的某个社区网站！
</div>
</div>    </div>
  </div>
</div>';

} else {
    $numrows = $DB->count("SELECT count(*) FROM cmy_shequ");
    echo '<div class="block">
<div class="block-title clearfix">
<h2>系统共有 <b>' . $numrows . '</b> 个对接网站</h2>
</div>
<a href="./shequlist.php?my=add" class="btn btn-primary"><i class="fa fa-plus"></i>&nbsp;添加一个对接站点</a>&nbsp;<a href="./set.php?mod=shequ" class="btn btn-info"><i class="fa fa-cog"></i>&nbsp;其他设置</a>&nbsp;<a href="./set.php?mod=proxy" class="btn btn-danger">代理服务器设置</a>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead><tr><th>ID</th><th>网站名称</th><th>网站域名</th><th>类型</th><th>商品数量</th><th>用户名</th><th>密码</th><th>操作</th></tr></thead>
          <tbody>   ';

    $rs = $DB->query("SELECT * FROM cmy_shequ WHERE 1 order by id desc");
    while ($res = $DB->fetch($rs)) {
        $count = $DB->count("SELECT count(tid) FROM `pre_tools` WHERE `shequ`='{$res['id']}'");
        echo '<tr id="tr_' . $res['id'] . '"><td style="    padding-bottom: 15px;"><b>' . $res['id'] . '</b></td><td>' . $res['name'] . '</td><td><a href="./shequlist.php?my=edit&id=' . $res['id'] . '" target="_blank" rel="noreferrer">' . $res['url'] . '</a></td><td>' . getShequTypeName($res['type']) . '</td><td>' . $count . '个</td><td>' . $res['username'] . '</td><td>******</td><td><a href="./shequlist.php?my=edit&id=' . $res['id'] . '" class="btn btn-info btn-xs" data-tip="点击修改此货源站账号、密码等">修改</a>&nbsp;<a href="./shoplist.php?shequ=' . $res['id'] . '" class="btn btn-primary btn-xs" data-tip="点击查看此货源站添加的商品">商品</a>&nbsp;<a class="btn btn-xs btn-danger" onclick="if(confirm(\'你确实要删除此记录吗？\')){Delete(' . $res['id'] . ');};">删除</a></td></tr>';
    }

    echo "          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>";
}

echo '<script>

$(document).on("click", ".urlAlert", function(event) {
    event.preventDefault();
    /* Act on the event */
    var html = \'<h4 style="color:red">问：怎么填，支持格式有哪些？</h4><br/>填写完整地址，支持https和http两种开头<br/>格式一：http(s)://xxx.xxx.cn/<br/>格式二：http(s)://xxx.xxx.cn:888/<br/>格式三：http(s)://xxx.xxx.cn:888/buy/<br/>注意：尾部的斜杠一定要加上！\';
    layer.alert(html);
});

function showAlert(){
    var html = \'<h4 style="color:red">问：什么是网站类型？</h4>答：网站类型指你要对接那个平台的程序系统类型，并不表示某个网站！如果不清楚是什么系统类型，请先咨询你要对接的平台网站站长再添加对接\';
    layer.alert(html);
}

function Delete(id){
    var ii = layer.load(2, {shade:[0.1,\'#fff\']});
    $.ajax({
        type : "POST",
        url : "?my=del",
        data : {id:id},
        dataType : "json",
        success : function(data) {
            layer.close(ii);
            if(data.code == 0){
                layer.msg("删除成功");
                $(\'#tr_\'+id).fadeOut();
            }else{
                layer.alert(data.msg);
            }
        }
    });
}

function checkurl(){
    var url = $("input[name=\'url\']").val();
    if(url == \'\'){layer.alert(\'请先填写网站域名！\');return false;}
    if(url.indexOf(\'http\')<0 && url.substr(-1) != \'/\'){
        var ii = layer.load(2, {shade:[0.1,\'#fff\']});
        $.ajax({
            type : "POST",
            url : "ajax.php?act=checkshequ",
            data : {url:url},
            dataType : \'json\',
            success : function(data) {
                layer.close(ii);
                if(data.code == 1){
                    layer.msg(\'连通性良好\');
                }else{
                    layer.alert(\'该网站由于防火墙原因国外主机无法连接，请使用国内主机\');
                }
            } ,
            error:function(data){
                layer.close(ii);
                layer.msg(\'目标社区连接超时\');
                return false;
            }
        });
    }else{
        layer.alert(\'网站域名不能带http和/符号，只填写域名\');
    }
}

function checkPwd(pwd){
    $.ajax({
        type: "POST",
        url: "ajax.php?act=checkPwd",
        data: {pwd: pwd},
        dataType: "json",
        success: function (data) {
            layer.alert(data.msg);
        },
        error: function (data) {
            layer.msg("服务器错误");
            return false;
        }
    });
}

function setShequInfo(){
    var shequ = $("select[name=\'type\']").val();
    var paytype = $("select[name=\'type\'] option:selected").attr(\'paytype\');
    var paypwd = $("select[name=\'type\'] option:selected").attr(\'paypwd\');
    var extend = $("select[name=\'type\'] option:selected").attr(\'extend\');
    $("#paytype").hide();
    //console.log(shequ);
    if( extend == 1 ){
        var username_obj={}, password_obj={}, paypwd_obj={};
        var username_text = $("select[name=\'type\'] option:selected").attr(\'username\');
        if("string" == typeof username_text){
            username_obj = eval(\'(\' + username_text + \')\');
        }
        else{
            console.log(username_text);
        }
        var password_text = $("select[name=\'type\'] option:selected").attr(\'password\');
        if("string" == typeof password_text){
            password_obj = eval(\'(\' + password_text + \')\');
        }
        var paypwd_text = $("select[name=\'type\'] option:selected").attr(\'paypwd\');
        if("string" == typeof paypwd_text){
            paypwd_obj = eval(\'(\' + paypwd_text + \')\');
        }
        var cron = $("select[name=\'type\'] option:selected").attr(\'cron\');
        var tips = $("select[name=\'type\'] option:selected").attr(\'tips\');
        var alias = $("select[name=\'type\'] option:selected").attr(\'alias\');
        if(!!username_obj.show){
            $("#username").html(username_obj.text);
            if(username_obj.tips){
                $("input[name=\'username\']").attr("placeholder", username_obj.tips);
            }
            $("input[name=\'username\']").attr("required", true);
        }

        if(!!password_obj.show){
            $("#password").html(password_obj.text);
            if(password_obj.tips){
                $("input[name=\'password\']").attr("placeholder", password_obj.tips);
            }
            $("input[name=\'password\']").attr("required", true);
        }

        if(!!paypwd_obj.show){
            $("#paypwd label").html(paypwd_obj.text);
            if(paypwd_obj.tips){
                $("input[name=\'paypwd\']").attr("placeholder", paypwd_obj.tips);
            }
            $("#paypwd").show();
        }

        if(!!cron){
            $("#status_tips").html(\'该平台支持自动同步订单状态，可设置为“进行中”并通过配置监控链接实现！<a href="./cron.php" target="_blank">点我配置监控</a>\').show();
        }

        $("#alias").val(alias);
        $("#shequtips").hide();
        if(!!tips){
           $("#shequtips").html(tips).show();
        }
        return false;
    }
    else if(shequ == 1){
        $("#username").html("TokenID：");
        $("#password").html("密匙：");
        $("#shequtips").hide();
        $("#status_tips").html(\'该平台支持自动同步订单状态，可设置为“进行中”并通过配置监控链接实现！<a href="./cron.php" target="_blank">点我配置监控</a>\').show();
        $.ajax({
            type : "GET",
            url : "ajax.php?act=getServerIp",
            dataType : \'json\',
            success : function(data) {
                $("#shequtips").html(\'<font color=red>请前往亿樂货源站登录->右上角菜单->我的密钥页面，添加当前服务器IP到白名单：\'+data.ip+\'</font>\');
                $("#shequtips").show();
            }
        });
    }
    else if(shequ == 4){
        $("#shequ_url").html("网站域名：");
        $("#username").html("账号请留空：");
        $("#password").html("密码请留空：");
        $("#shequtips").html("九流社区的卡号和密码请在添加商品时填写（选择本社区时会自动弹出），所以本系统支持对接九流社区的多个商品！");
        $("#shequtips").show();
    }
    else if(shequ == 6){
        $("#shequtips").html("<span style=\"color:red\">支持代充和卡密对接！如果出现不能对接请先换域名或加白试试</span>");
        $("#shequtips").show();
        $("#status_tips").html(\'该平台支持自动同步订单状态，可设置为“进行中”并通过配置监控链接实现！<a href="./cron.php" target="_blank">点我配置监控</a>\').show();
    }
    else if(shequ == 9){
        $("#shequ_url").html("网站域名：");
        $("#username").html("商家编号：");
        $("#password").html("接口密钥：");
        $("#shequtips").html("接口地址填：<span style=\'color:red;\'>http://www.kashangwl.com/</span>。 支持对接直冲、卡密、租号商品");
        $("#shequtips").show();
        $("#status_tips").html(\'该平台支持自动同步订单状态，可设置为“进行中”并通过配置监控链接实现！<a href="./cron.php" target="_blank">点我配置监控</a>\').show();
    }
    else if(shequ == 13 || shequ == 12){
        $("#status_tips").html(\'该平台支持自动同步订单状态，可设置为“进行中”并通过配置监控链接实现！<a href="./cron.php" target="_blank">点我配置监控</a>\').show();
    }
    else if(shequ == 15){
        $("#shequ_url").html("网站域名：");
        $("#username").html("登录账号：");
        $("#password").html("登录密码：");

        $("input[name=\'url\']").attr("placeholder","");
        $("input[name=\'password\']").attr("placeholder","如果提示密码错误，请在此处填二级密码");
        $("#paypwd label").html("二级密码或对接密码：");
        $("#paypwd input").attr("placeholder","如果没有二级/对接密码，或对方是旧版优云宝请务必留空");
        $("#shequtips").html("账号和网站都需要先开通Api权限！需要代充和卡密订单自动同步时对接后动作请选择【进行中】。<span style=\'color:red\'>注意：如果对接提示密码错误，请在【登录密码】处填二级密码</span>");
        $.ajax({
            type : "GET",
            url : "ajax.php?act=getProxyIp",
            dataType : \'json\',
            success : function(data) {
                $("#shequtips").html("1、账号和网站都需要先开通Api权限！需要代充和卡密订单自动同步时对接后动作请选择【进行中】。<span style=\'color:red\'>注意：如果对接提示密码错误，请在【登录密码】处填二级密码</span><br/>2、如果Api是新版2.0且你服务器不是国内请开启代理服务器并将如下IP添加到优云宝->Api配置->IP白名单中：<span style=\'color:red\'>"+data.ip+"</span>");
            }
        });
        $("#paypwd").show();
        $("#shequtips").show();
        return;
    }
    else if(shequ == 16){
        $("#shequ_url").html("网站域名：");
        $("#username").html("APPID：");
        $("#password").html("APPKEY：");
        $("#shequtips").html("只支持对接卡密，对接成功后卡密内容在处理信息里面");
        $("#shequtips").show();
    }
    else if(shequ == 18){
        $("#shequ_url").html("网站域名：");
        $("#username").html("商户ID：");
        $("#password").html("商户KEY：");
        $("#shequtips").html("支持对接直冲/卡密，注册账号后需联系站长开通对接权限后刷新才能看到商户ID、商户KEY");
        $("#shequtips").show();
        $("#status_tips").html(\'该平台支持自动同步订单状态，可设置为“进行中”并通过配置监控链接实现！<a href="./cron.php" target="_blank">点我配置监控</a>\').show();
    }
    else if(shequ == 19){
        $("#shequ_url").html("网站域名：");
        $("#username").html("登录账号：");
        $("#password").html("登录密码：");
        $("#shequtips").html("支持对接代充、卡密，需要代充和卡密订单自动同步时对接后动作请选择【进行中】");
        $("#shequtips").show();
        $("#paypwd").show();
        $("#paypwd label").html("Api密钥KEY");
        $("#status_tips").html(\'该平台支持自动同步订单状态，可设置为“进行中”并通过配置监控链接实现！<a href="./cron.php" target="_blank">点我配置监控</a>\').show();
        return false;
    }
    else if(shequ == 21){
        $("#shequ_url").html("网站域名：");
        $("#username").html("登录账号：");
        $("#password").html("登录密码：");
        $("#shequtips").hide();
        $("#paypwd").hide();
        $("#status_tips").html(\'该平台支持自动同步订单状态，可设置为“进行中”并通过配置监控链接实现！<a href="./cron.php" target="_blank">点我配置监控</a>\').show();
        return false;
    }
    else if(shequ == 22){
        //商战网
        $("#shequ_url").html("网站域名：");
        $("#username").html("用户编号：");
        $("#password").html("对接Key：");
        $("#shequtips").html("对接Key请联系该平台站长或客服获得，平台地址：<a href=\'http://www.qqkami.com/\' target=\'_blank\'>http://www.qqkami.com/</a>").show();
        $("#paytype").hide();
        $("#paypwd").show();
        $("#status_tips").html(\'该平台支持自动同步订单状态，可设置为“进行中”并通过配置监控链接实现！<a href="./cron.php" target="_blank">点我配置监控</a>\').show();
        return false;
    }
    else if(shequ == 23){
        $("#shequ_url").html("网站域名：");
        $("#username").html("此处留空：");
        $("input[name=\'username\']").attr("placeholder", "此处留空即可");
        $("input[name=\'username\']").attr("required", false);
        $("#password").html("对接秘钥：");
        $("#shequtips").html("对接秘钥请登录该平台进入用户后台获得").show();
        $("#paytype").hide();
        $("#paypwd").hide();
        return false;
    }
    else if(shequ == 24){
        $("#shequ_url").html("网站域名：");
        $("input[name=\'url\']").attr("placeholder", "举例：guakebao.com");
        $("#username").html("用户ID：");
        $("input[name=\'username\']").attr("required", true);
        $("#password").html("对接APIKEY：");
        $("#shequtips").html("对接APIKEY请登录该平台，点击“账号设置”，在页面下方生成获得").show();
        $("#paytype").hide();
        $("#paypwd").hide();
        return false;
    }
    else if(shequ == 25 || shequ == 26){
        $("#shequ_url").html("网站域名：");
        $("#username").html("AppId：");
        $("input[name=\'username\']").attr("required", true);
        $("#password").html("AppSecret：");
        $("input[name=\'password\']").attr("required", true);

        $("#status_tips").html(\'该平台支持自动同步订单状态，可设置为“进行中”并通过配置监控链接实现！<a href="./cron.php" target="_blank">点我配置监控</a>\').show();
        $("#paytype").hide();
        $("#paypwd").hide();
        if( shequ == 26 ){
            $("input[name=\'name\']").val("好方网");
            $("input[name=\'url\']").val("www.wsjinhuo.com");
            $("#shequtips").html(\'注册地址：<a href="//www.wsjinhuo.com" target="_blank">www.wsjinhuo.com</a>，AppId和AppSecret注册登陆后获取\').show();
        }
        else{
            $("#shequtips").html("AppId和AppSecret登陆直客平台后可获取").show();
        }
        return false;
    }
    else if(shequ == 27){
        $("#shequ_url").html("网站域名：");
        $("#username").html("用户编号(customerid)：");
        $("input[name=\'username\']").attr("required", true);
        $("#password").html("交易密码(tradepassword)：");
        $("input[name=\'password\']").attr("required", true);

        $("#status_tips").html(\'该平台支持自动同步订单状态，可设置为“进行中”并通过配置监控链接实现！<a href="./cron.php" target="_blank">点我配置监控</a>\').show();
        $("#paytype").hide();
        $("#paypwd label").html("对接密钥key：");
        $("input[name=\'paypwd\']").attr("placeholder", "对接密钥key请查看平台或咨询站长");
        $("#paypwd").show();
        $("#shequtips").html("对接密钥key请查看平台或咨询站长").show();
        return false;
    }
    else if(shequ == 10){
        $("#shequ_url").html("业务名称(仅用于标记)：");
        $("#username").html("联系方式(随便填写)：");
        $("#password").html("卡密：");
        $("#shequtips").hide();
    }else{
        $("#shequ_url").html("网站域名：");
        $("#username").html("登录账号：");
        $("#password").html("登录密码：");
        $("#shequtips").hide();
        $("#status_tips").hide();
    }

    $("#paytype").hide();
    if(shequ == 9){
        $("#statustips").show();
    }else{
        $("#statustips").hide();
    }
    if(paypwd!=undefined && shequ != 7){
        $("#paypwd").show();
    }else{
        $("#paypwd").hide();
    }
}
</script>';
include_once 'footer.php';
