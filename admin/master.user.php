
<?php

use core\Db;

include '../includes/common.php';
$title = '供货商管理';
checkLogin();
$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;
if ($act == "recharge") {
    $zid   = intval(input('post.zid'));
    $do    = intval(input('post.actdo'));
    $money = floatval(input('post.money'));
    $bz    = trim(input('post.bz'));
    $row   = $DB->get_row("SELECT * from pre_master where zid='$zid' limit 1");
    if (!$row) {
        exit('{"code":-1,"msg":"当前分站不存在！"}');
    }

    $event     = 'safecheck';
    $adm_email = conf('adm_email');

    if ($do == 0 && conf('adm_safe_check_email') == 1 && validateData($adm_email, 'email')) {
        $code  = input('code2');
        $sms   = new \core\Ems();
        $check = $sms->check($adm_email, $code ? $code : null, $event);
        if ($check !== true) {
            json($check, 406);
        }
    }

    if ($do == 1 && $money > $row['income']) {
        $money = $row['income'];
    }

    if ($do == 0) {
        if ($bz) {
            $bz = ', 备注：' . $bz;
        }

        $bz = '后台充值到余额' . $money . '元' . $bz . '！当前余额' . ($row['income'] + $money) . '元';
        $DB->query("UPDATE pre_master set `income`=`income`+{$money} where zid='{$zid}'");
        addMasterPointLogs($zid, $money, '充值', $bz, null);
    } else {
        if ($bz) {
            $bz = ', 备注：' . $bz;
        }
        $bz = '后台从余额扣款' . $money . '元' . $bz . '！当前余额' . ($row['income'] - $money) . '元';
        $DB->query("UPDATE pre_master set `income`=`income`-{$money} where zid='{$zid}'");
        addMasterPointLogs($zid, $money, '扣款', $bz, null);
    }
    exit('{"code":0,"msg":"succ"}');
} elseif ($act == 'delete') {
    $zid = intval(input('zid', 1));
    $sql = "DELETE from pre_master where zid='" . $zid . "'";
    if ($DB->query($sql)) {
        $result = array('code' => 0, 'msg' => "删除供货商" . $zid . "成功！");
    } else {
        $result = array('code' => -1, 'msg' => '删除供货商失败, ' . $DB->error());
    }
    exit(json_encode($result));
} elseif ($act == 'setSite') {
    $zid    = intval(input('zid', 1));
    $active = intval(input('active', 1));
    $sql    = Db::name('master')->where(['zid' => $zid])->update(['status' => $active]);
    if ($sql !== false) {
        $result = array('code' => 0, 'msg' => "切换成功！");
    } else {
        $result = array('code' => -1, 'msg' => '切换失败, ' . $DB->error());
    }
    exit(json_encode($result));
} elseif ($act == 'info') {
    $zid = intval(input('zid', 1));
    $row = $DB->get_row("SELECT * from pre_master where zid='{$zid}' limit 1");
    if ($row) {
        $result = array('code' => 0, 'msg' => "成功", 'data' => $row);
    } else {
        $result = array('code' => -1, 'msg' => '该供货商不存在');
    }
    exit(json_encode($result));
}

function display_moneyzt($zid, $zt)
{
    if ($zt == 1) {
        return '<span onclick="setRegular(' . $zid . ')" title="固定后分站每次下单后余额都会置为0元，不管是否有余额" class="btn btn-danger btn-xs">已固定</span>';
    } else {
        return '<span onclick="setRegular(' . $zid . ')" title="固定后分站每次下单后余额都会置为0元，不管是否有余额" class="btn btn-info btn-xs">未固定</span>';
    }
}

checkAuthority('users');
include './head.php';

echo '
   <div class="col-md-12 center-block" style="float: none;padding-top:10px">
<div class="modal fade" id="modal-money">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">余额修改</h4>
            </div>
            <div class="modal-body">
                <form id="form-money">
                    <input type="hidden" name="zid" value="">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon p-0">
                                <SELECT name="do"
                                        style="-webkit-border-radius: 0;height:20px;border: 0;outline: none !important;border-radius: 5px 0 0 5px;padding: 0 5px 0 5px;">
                                    <option value="0">充值</option>
                                    <option value="1">扣除</option>
                                </SELECT>
                            </span>
                            <input type="number" class="form-control" name="money" placeholder="输入金额">
                            <span class="input-group-addon">元</span>
                        </div><br/>
                        <div class="form-group">
                            <div class="input-group" id="input-group"><div class="input-group-addon" id="inputname">备注信息</div>
                            <input type="text" class="form-control" name="bz" placeholder="输入备注"/>
                           </div>
                        </div>
                        ';

if (conf('adm_safe_check_email') == 1 && validateData(conf('adm_email'), 'email')) {
    echo '
                            <div class="form-group">
                                    <div class="input-group" id="input-group">
                                        <div class="input-group-addon">邮件验证码</div>
                                        <input type="text" class="form-control" name="code2" placeholder="输入操作验证码"/>
                                        <div class="input-group-addon sendCode" data-type="ems">发送</div>
                                    </div>
                            </div>
                            ';
}

echo '
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-info" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" id="recharge">确定</button>
            </div>
        </div>
    </div>
</div>
';

$my = (isset($_GET['my']) ? $_GET['my'] : null);
if ($my == 'delete') {
    $zid = $_GET['zid'];
    $sql = 'DELETE FROM pre_master WHERE zid=\'' . $zid . '\'';
    if ($DB->query($sql)) {
        showmsg('删除成功！<br/><br/><a href="./master.user.php">>>返回分站列表</a>', 1);
    } else {
        showmsg('删除失败！' . $DB->error(), 4);
    }
} elseif ($my == 'add') {
    echo '<div class="block">
        <div class="block-title"><h3 class="panel-title">添加供货商</h3></div>';
    echo '<div class="">';
    echo '<form action="?my=add_submit" method="POST">
        <div class="form-group">
        <label>用户账号:</label><br>
        <input type="text" class="form-control" name="user" value="" placeholder="格式：字母数字，区分大小写">
        </div>
        <div class="form-group">
        <label>用户密码:</label><br>
        <input type="text" class="form-control" name="pwd" value="" placeholder="格式：字母数字，区分大小写">
        </div>
        <div class="form-group">
        <label>QQ:</label><br>
        <input type="text" class="form-control" name="qq" value="">
        </div>
        <div class="form-group">
        <label>邮箱:</label><br>
        <input type="text" class="form-control" name="email" value="">
        </div>
        <div class="form-group">
            <label>保证金:</label><br>
            <input type="text" class="form-control" name="master_price" value="0">
        </div>
    <input type="submit" class="btn btn-primary btn-block" value="确定添加"></form>';
    echo '<br/><a href="./master.user.php">>>返回供货商列表</a>';
    echo '</div></div>';
} elseif ($my == 'edit') {
    $zid = intval($_GET['zid']);
    $row = $DB->get_row('SELECT * from pre_master where zid=\'' . $zid . '\' limit 1');
    if (!$row) {
        showmsg("该用户不存在！", 3);
    }

    echo '<div class="block">
        <div class="block-title"><h3 class="panel-title">修改供货商信息</h3></div>';
    echo '<div class="">';
    echo '<form action="?my=edit_submit&zid=' . $zid . '" method="POST">
    <div class="form-group">
    <label>用户名:</label><br>
    <input type="text" class="form-control" name="user" value="' . $row['user'] . '" required>
    </div>
    <div class="form-group">
    <label>QQ:</label><br>
    <input type="text" class="form-control" name="qq" value="' . $row['qq'] . '">
    </div>
    <div class="form-group">
        <label>邮箱:</label><br>
        <input type="text" class="form-control" name="email" value="' . $row['email'] . '">
    </div>
    <div class="form-group">
    <label>保证金:</label><br>
    <input type="text" class="form-control" name="master_price" value="' . $row['master_price'] . '">
    </div>
    <div class="form-group">
    <label>重置密码:</label><br>
    <input type="text" class="form-control" name="pwd" value="" placeholder="不重置请留空">
    </div>
    <input type="submit" class="btn btn-primary btn-block" value="确定修改"></form>';
    echo '<br/><a href="./master.user.php">>>返回供货商列表</a>';
    echo '</div></div>';
} elseif ($my == 'add_submit') {
    $user         = input('post.user');
    $pwd          = input('post.pwd');
    $qq           = input('post.qq');
    $email        = input('post.email');
    $master_price = input('post.master_price');
    if (empty($user) || empty($pwd) || empty($qq)) {
        showmsg('添加错误，请确保账号、密码、QQ任意一项都不为空!', 3);
    } else {
        $row = $DB->get_row("SELECT * from `pre_master` where `user`=:user limit 1", [':user' => $user]);
        if ($row) {
            showmsg('添加错误，该用户名已被使用!', 3);
        } else {

            $insert = Db::name('master')->insert([
                'user'         => $user,
                'income'       => 0,
                'pwd'          => $pwd,
                'email'        => $email,
                'qq'           => $qq,
                'master_open'  => 1,
                'master_price' => $master_price,
                'createtime'   => time(),
                'updatetime'   => time(),
                'status'       => 1,
            ]);

            if ($insert) {
                showmsg('添加成功！<br/><br/><a href="./master.user.php">>>返回供货商列表</a>', 1);
            } else {
                showmsg('添加失败, ' . Db::error(), 4);
            }
        }
    }

} elseif ($my == 'edit_submit') {
    $zid  = intval($_GET['zid']);
    $rows = $DB->get_row('SELECT * from pre_master where zid=\'' . $zid . '\' limit 1');
    if (!$rows) {
        showmsg('该用户不存在！', 3);
    }

    $user         = input('post.user');
    $pwd          = input('post.pwd');
    $qq           = input('post.qq');
    $email        = input('post.email');
    $master_price = input('post.master_price');

    $row = $DB->get_row("SELECT * from `pre_master` where `user`=:user limit 1", [':user' => $user]);
    if ($row && $row['zid'] != $zid) {
        showmsg('添加错误，该用户名已被使用!', 3);
    }

    if ($qq == null) {
        showmsg('保存错误,请确保每项都不为空!', 3);
    } elseif (Db::name('master')->where(['zid' => $zid])->update([
        'user'         => $user,
        'master_price' => $master_price,
        'email'        => $email,
        'qq'           => $qq ? $qq : $rows['qq'],
        'pwd'          => $pwd ? $pwd : $rows['pwd'],
        'updatetime'   => time(),
    ]) !== false) {
        showmsg('修改供货商成功！<br/><br/><a href="./master.user.php">>>返回供货商列表</a>', 1);
    } else {
        showmsg('修改供货商失败, ' . $DB->error(), 4);
    }

} else {
    if (isset($_GET['zid'])) {
        $sql  = ' master_open=1 and  zid=' . $_GET['zid'];
        $link = '&zid=' . $_GET['zid'];
        $rows = $DB->get_row("SELECT * from pre_master where zid='" . intval($_GET['zid']) . "' limit 1");
    } elseif (isset($_GET['kw']) && $_GET['kw']) {
        $kw   = input('kw', 1);
        $sql  = ' master_open=1 and (zid=\'' . $kw . '\' or user=\'' . $kw . '\' or tel=\'' . $kw . '\'  or qq=\'' . $kw . '\')';
        $link = '&kw=' . $kw;
    } else {
        $sql = ' master_open=1';
    }

    $numrows = $DB->count('SELECT count(*) from pre_master where ' . $sql);
    $count   = $DB->count('SELECT count(*) from pre_master where master_open=1 and (tel is not null and tel!=\'\')');
    echo '
            <div class="block">
            <div class="block-title"><h3 class="panel-title">供货商列表</h3></div>
            <div class="alert alert-info">系统共有 <b>' . $numrows . '</b> 个供货商，' . $count . '个已绑定手机号<br/>
            </div>
            <form method="GET" class="form-inline">
            <div class="form-group">
            <input type="text" onfocus="tips()" class="form-control" id="kw" name="kw" placeholder="请输入ID、用户名、QQ、手机等">
            <button type="submit" class="btn btn-success">搜索</button>&nbsp;<a href="./master.user.php?my=add" class="btn btn-primary">添加供货商</a>
            </div>
            </form>
            ';
    echo '      <div class="table-responsive">
            <table class="table table-striped">
              <thead><tr><th>ID</th><th>用户名</th><th>余额</th><th>保证金</th><th>QQ/手机/邮箱</th><th>状态</th><th>注册/更新时间</th><th>操作</th></tr></thead>
              <tbody>
    ';
    $pagesize = 30;
    $pages    = intval($numrows / $pagesize);
    if ($numrows % $pagesize) {
        $pages++;
    }
    if (isset($_GET['page'])) {
        $page = intval($_GET['page']);
    } else {
        $page = 1;
    }
    $offset = $pagesize * ($page - 1);
    $rs     = $DB->query('SELECT * FROM pre_master WHERE ' . $sql . ' order by zid desc limit ' . $offset . ',' . $pagesize);
    while ($res = $DB->fetch($rs)) {
        ?>
    <tr id="tr_<?php echo $res['zid']; ?>">
        <td><?php echo $res['zid']; ?></td>
        <td><?php echo $res['user']; ?></td>
        <td> <button type="button" id="income" data-zid="<?php echo $res['zid']; ?>"><?php echo $res['income']; ?></button> </td>
        <td><?php echo $res['master_price']; ?></td>
        <td><?php echo $res['qq'] ? $res['qq'] : '未绑定'; ?><br/><?php echo $res['tel'] ? $res['tel'] : '未绑定'; ?><br/><?php echo $res['email'] ? $res['email'] : $res['qq'] . '@qq.com'; ?></td>
        <td>
            <span id="change" title="点击切换"  data-zid="<?php echo $res['zid']; ?>" data-active="<?php echo $res['status'] == 1 ? 0 : 1; ?>" class="btn btn-<?php echo $res['status'] == 1 ? 'success' : 'danger' ?> btn-xs"><?php echo $res['status'] == 1 ? '正常' : '禁用' ?></span>
        </td>
        <td><?php echo $res['createtime'] ? date('Y-m-d H:i:s', $res['createtime']) : ($res['addtime'] ? $res['addtime'] : '——'); ?><br/><?php echo $res['updatetime'] ? date('Y-m-d H:i:s', $res['updatetime']) : '——'; ?></td>
        <td>
            <a title="登录"  data-zid="<?php echo $res['zid']; ?>" class="btn btn-warning btn-xs onLogin">登录</a>
            <a href="./master.record.php?zid=<?php echo $res['zid']; ?>" class="btn btn-info btn-xs">明细</a>
            <a href="./master.user.php?my=edit&zid=<?php echo $res['zid']; ?>" class="btn btn-success btn-xs">编辑</a>
            <span title="删除"  data-zid="<?php echo $res['zid']; ?>" class="btn btn-danger btn-xs onDel">删除</span>
        </td>
    </tr>
<?php

    }

    echo '          </tbody>
        </table>
      </div>
    ';
    #分页
    $pageList = new \core\Page($numrows, $pagesize, 0, $link);
    echo $pageList->showPage();
}

echo "    </div>
  </div>";

?>
<script>

$(document).on('click', '#income',  function () {
    // 充值
    var zid = $(this).data('zid');
    $("input[name='zid']").val(zid);
    $("#modal-money").modal('show');
});

$(document).ready(function(){
    $("#recharge").click(function(){
        var zid=$("input[name='zid']").val();
        var actdo=$("SELECT[name='do']").val();
        var money=$("input[name='money']").val();
        var bz=$("input[name='bz']").val();
        if(money==''){layer.alert('请输入金额');return false;}
        var ii = layer.load(2, {shade:[0.1,'#fff']});
        $.ajax({
            type : "POST",
            url : "?act=recharge",
            data : {zid:zid,actdo:actdo,money:money,bz:bz},
            dataType : 'json',
            success : function(data) {
                layer.close(ii);
                if(data.code == 0){
                    layer.msg('修改余额成功');
                    window.location.reload();
                }else{
                    layer.alert(data.msg);
                }
            },
            error:function(data){
                layer.msg('服务器错误');
                return false;
            }
        });
    });
    $(document).on('click', '#change',  function () {
        var zid = $(this).data('zid');
        var active = $(this).data('active');
        var ii = layer.load(2, {shade:[0.1,'#fff']});
        $.ajax({
            type : 'GET',
            url : '?act=setSite&zid='+zid+'&active='+active,
            dataType : 'json',
            success : function(data) {
                layer.close(ii);
                window.location.reload();
            },
            error:function(data){
                layer.close(ii);
                layer.msg('服务器错误');
                return false;
            }
        });
    });

    $(document).on('click', '.onDel',  function () {
        var zid = $(this).data('zid');
        var ii = layer.load(2, {shade:[0.1,'#fff']});
        $.ajax({
            type : 'GET',
            url : '?act=delete&zid='+zid,
            dataType : 'json',
            success : function(data) {
                layer.close(ii);
                layer.msg(res.msg);
                $("#tr_" + zid).remove();
            },
            error:function(data){
                layer.close(ii);
                layer.msg('服务器错误');
                return false;
            }
        });
    });

    $(document).on('click', '.onLogin',  function () {
        var zid = $(this).data('zid');
        var ii = layer.load(2, {shade:[0.1,'#fff']});
        $.ajax({
            type : 'GET',
            url : '?act=info&zid='+zid,
            dataType : 'json',
            success : function(res) {
                layer.close(ii);
                if (res.code==0) {
                    window.open('../sup/ajax2.php?act=fastlogin&user=' +res.data.user +'&pwd=' + res.data.pwd);
                }else{
                    layer.msg(res.msg);
                }
            },
            error:function(data){
                layer.close(ii);
                layer.msg('服务器错误');
                return false;
            }
        });
    });

    $(document).on('click', '.sendCode',  function () {
        var type = $(this).data('type');
        var ii = layer.load(2, {shade:[0.1,'#fff']});
        $.ajax({
            type : 'GET',
            url : 'ajax.public.php?act='+ (type == 'sms'?'sms_send':'ems_send'),
            data:{
                event: 'safecheck',
                email: '<?php echo $conf['adm_email']; ?>'
            },
            dataType : 'json',
            success : function(res) {
                layer.close(ii);
                if (res.code==0) {
                    layer.msg(res.msg);
                }else{
                    layer.alert(res.msg);
                }
            },
            error:function(data){
                layer.close(ii);
                layer.msg('服务器错误');
                return false;
            }
        });
    });
});
</script>
<?php include 'footer.php';?>